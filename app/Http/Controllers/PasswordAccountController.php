<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasswordAccount;
use App\Models\PasswordCategory;
use App\Models\PasswordAssignment;
use App\Models\PasswordAuditLog;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PasswordAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PasswordAccount::query();

        // Role-based filtering
        // Check if user is admin by email or role
        $isAdmin = $user->email === 'admin@company.com' || 
                   ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'system_admin']));
        
        if (!$isAdmin) {
            // Other users can see:
            // 1. Accounts they created (created_by = user_id)
            // 2. Accounts assigned to them (via assignments)
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignments', function ($assignmentQuery) use ($user) {
                      $assignmentQuery->where('user_id', $user->id)
                                      ->whereNull('revoked_at');
                  });
            });
        }
        // System Admins can see all accounts (no filtering)

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('category_ar', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->expiringSoon();
                    break;
                case 'shared':
                    $query->shared();
                    break;
            }
        }

        // Employee filter (assigned user)
        if ($request->filled('employee')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('user_id', $request->employee)
                  ->whereNull('revoked_at');
            });
        }

        // Get paginated accounts
        $accounts = $query->with(['creator', 'category', 'assignments.user'])
                          ->orderBy('name')
                          ->paginate(20);
        
        // Get all accounts for statistics (without pagination)
        $allAccountsQuery = PasswordAccount::query();
        if (!$isAdmin) {
            // Other users can see:
            // 1. Accounts they created (created_by = user_id)
            // 2. Accounts assigned to them (via assignments)
            $allAccountsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignments', function ($assignmentQuery) use ($user) {
                      $assignmentQuery->where('user_id', $user->id)
                                      ->whereNull('revoked_at');
                  });
            });
        }
        $allAccounts = $allAccountsQuery->get();

        // Get categories for filter dropdown
        $categories = PasswordCategory::active()
            ->ordered()
            ->select('id', 'name', 'name_ar')
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->display_name,
                ];
            })
            ->toArray();

        // Add legacy categories for backward compatibility
        $legacyCategories = [
            ['value' => 'Social Media', 'label' => __('passwords.social_media')],
            ['value' => 'Work Tools', 'label' => __('passwords.work_tools')],
            ['value' => 'Email Services', 'label' => __('passwords.email_services')],
            ['value' => 'Cloud Storage', 'label' => __('passwords.cloud_storage')],
            ['value' => 'Development', 'label' => __('passwords.development')],
            ['value' => 'Design', 'label' => __('passwords.design')],
            ['value' => 'Marketing', 'label' => __('passwords.marketing')],
            ['value' => 'Finance', 'label' => __('passwords.finance')],
            ['value' => 'Other', 'label' => __('passwords.other')],
        ];

        $categories = array_merge($categories, $legacyCategories);

        // Get all users for employee filter
        $employees = \App\Models\User::select('id', 'name')
                                     ->orderBy('name')
                                     ->get();

        return view('password-accounts.index', compact('accounts', 'allAccounts', 'categories', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', PasswordAccount::class);
        
        $users = User::activeEmployees()->get();
        $categories = $this->getCategories();
        
        return view('password-accounts.create', compact('users', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', PasswordAccount::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'url' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
            'requires_2fa' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
            'is_shared' => 'boolean',
            'category' => 'nullable|string|max:100',
            'category_ar' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:password_categories,id',
            'icon' => 'nullable|string|max:500',
            'assigned_users' => 'array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $account = PasswordAccount::create([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'username' => $request->email, // استخدام email كـ username
                'email' => $request->email,
                'password' => $request->password,
                'url' => $request->url,
                'notes' => $request->notes,
                'notes_ar' => $request->notes_ar,
                'requires_2fa' => $request->boolean('requires_2fa'),
                'expires_at' => $request->expires_at,
                'is_shared' => $request->boolean('is_shared'),
                'category' => $request->category,
                'category_ar' => $request->category_ar,
                'category_id' => $request->category_id,
                'icon' => $request->icon,
                'created_by' => Auth::id(),
            ]);

            // Assign to users if specified
            if ($request->has('assigned_users')) {
                foreach ($request->assigned_users as $userId) {
                    PasswordAssignment::create([
                        'account_id' => $account->id,
                        'user_id' => $userId,
                        'access_level' => 'read_only',
                        'can_view_password' => true,
                        'can_edit_password' => false,
                        'can_delete_account' => false,
                        'assigned_by' => Auth::id(),
                    ]);
                }
            }

            // Log the creation
            $this->logAudit($account, 'created', 'Account created');

            DB::commit();

            return redirect()->route('password-accounts.index')
                           ->with('success', __('Account created successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password Account Creation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Failed to create account: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PasswordAccount $passwordAccount)
    {
        $this->authorize('view', $passwordAccount);

        $passwordAccount->load(['creator', 'assignments.user', 'auditLogs.user']);

        return view('password-accounts.show', compact('passwordAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PasswordAccount $passwordAccount)
    {
        $this->authorize('update', $passwordAccount);

        $users = User::activeEmployees()->get();
        $categories = $this->getCategories();
        $assignedUsers = $passwordAccount->assignedUsers()->pluck('users.id')->toArray();

        return view('password-accounts.edit', compact('passwordAccount', 'users', 'categories', 'assignedUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PasswordAccount $passwordAccount)
    {
        $this->authorize('update', $passwordAccount);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'url' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
            'notes_ar' => 'nullable|string',
            'requires_2fa' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
            'is_shared' => 'boolean',
            'category' => 'nullable|string|max:100',
            'category_ar' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:password_categories,id',
            'icon' => 'nullable|string|max:500',
            'assigned_users' => 'array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $passwordAccount->toArray();
            $passwordChanged = false;

            // Check if password is being changed
            if ($request->filled('password') && $request->password !== $passwordAccount->password) {
                // Store old password in history
                PasswordHistory::create([
                    'account_id' => $passwordAccount->id,
                    'old_password' => $passwordAccount->password,
                    'new_password' => $request->password,
                    'changed_by' => Auth::id(),
                    'change_reason' => 'Password updated by admin',
                    'change_reason_ar' => 'تم تحديث كلمة المرور من قبل المدير',
                ]);
                $passwordChanged = true;
            }

            $passwordAccount->update([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'email' => $request->email,
                'password' => $request->password ?: $passwordAccount->password,
                'url' => $request->url,
                'notes' => $request->notes,
                'notes_ar' => $request->notes_ar,
                'requires_2fa' => $request->boolean('requires_2fa'),
                'expires_at' => $request->expires_at,
                'is_shared' => $request->boolean('is_shared'),
                'category' => $request->category,
                'category_ar' => $request->category_ar,
                'category_id' => $request->category_id,
                'icon' => $request->icon,
            ]);

            // Update assignments
            if ($request->has('assigned_users')) {
                // Remove existing assignments
                $passwordAccount->assignments()->delete();
                
                // Create new assignments
                foreach ($request->assigned_users as $userId) {
                    PasswordAssignment::create([
                        'account_id' => $passwordAccount->id,
                        'user_id' => $userId,
                        'access_level' => 'read_only',
                        'can_view_password' => true,
                        'can_edit_password' => false,
                        'can_delete_account' => false,
                        'assigned_by' => Auth::id(),
                    ]);
                }
            }

            // Log the update
            $action = $passwordChanged ? 'password_changed' : 'updated';
            $description = $passwordChanged ? 'Password updated' : 'Account updated';
            $this->logAudit($passwordAccount, $action, $description, $oldValues, $passwordAccount->toArray());

            DB::commit();

            return redirect()->route('password-accounts.index')
                           ->with('success', __('Account updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => __('Failed to update account')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PasswordAccount $passwordAccount)
    {
        $this->authorize('delete', $passwordAccount);

        DB::beginTransaction();
        try {
            // Log the deletion
            $this->logAudit($passwordAccount, 'deleted', 'Account deleted');

            $passwordAccount->delete();

            DB::commit();

            return redirect()->route('password-accounts.index')
                           ->with('success', __('Account deleted successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => __('Failed to delete account')]);
        }
    }

    /**
     * Show password for viewing (with audit log).
     */
    public function showPassword(PasswordAccount $passwordAccount)
    {
        $this->authorize('viewPassword', $passwordAccount);

        // Log password view
        $this->logAudit($passwordAccount, 'viewed', 'Password viewed');

        return response()->json([
            'password' => $passwordAccount->password,
            'requires_2fa' => $passwordAccount->requires_2fa,
        ]);
    }

    /**
     * Get categories for dropdown.
     */
    private function getCategories()
    {
        // Get dynamic categories from database
        $dynamicCategories = PasswordCategory::active()
            ->ordered()
            ->select('id', 'name', 'name_ar')
            ->get()
            ->mapWithKeys(function ($category) {
                return [$category->id => $category->display_name];
            })
            ->toArray();

        // Only return dynamic categories from database
        return $dynamicCategories;
    }

    /**
     * Log audit action.
     */
    private function logAudit($account, $action, $description, $oldValues = null, $newValues = null)
    {
        PasswordAuditLog::create([
            'account_id' => $account->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $description,
        ]);
    }

    /**
     * Get Arabic description for action.
     */
    private function getArabicDescription($action)
    {
        $descriptions = [
            'created' => 'تم إنشاء الحساب',
            'updated' => 'تم تحديث الحساب',
            'deleted' => 'تم حذف الحساب',
            'viewed' => 'تم عرض كلمة المرور',
            'password_changed' => 'تم تغيير كلمة المرور',
        ];

        return $descriptions[$action] ?? $action;
    }

    /**
     * Batch delete multiple accounts
     */
    public function batchDelete(Request $request)
    {
        $accountIds = $request->input('account_ids', []);
        
        if (empty($accountIds)) {
            return redirect()->back()->with('error', __('passwords.no_accounts_selected'));
        }

        $accounts = PasswordAccount::whereIn('id', $accountIds)->get();
        $deletedCount = 0;

        foreach ($accounts as $account) {
            if ($this->authorize('delete', $account)) {
                // Log the deletion
                PasswordAuditLog::create([
                    'account_id' => $account->id,
                    'user_id' => Auth::id(),
                    'action' => 'deleted',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                $account->delete();
                $deletedCount++;
            }
        }

        return redirect()->route('password-accounts.index')
            ->with('success', __('passwords.batch_delete_success', ['count' => $deletedCount]));
    }

    /**
     * Batch export multiple accounts
     */
    public function batchExport(Request $request)
    {
        $accountIds = $request->input('account_ids', []);
        $format = $request->input('format', 'csv');
        
        if (empty($accountIds)) {
            return redirect()->back()->with('error', __('passwords.no_accounts_selected'));
        }

        $accounts = PasswordAccount::whereIn('id', $accountIds)->get();
        
        if ($format === 'csv') {
            return $this->exportToCsv($accounts);
        }
        
        // Add other formats as needed
        return redirect()->back()->with('error', __('passwords.unsupported_export_format'));
    }

    /**
     * Show batch assign form
     */
    public function batchAssign(Request $request)
    {
        $accountIds = $request->input('account_ids', []);
        
        if (empty($accountIds)) {
            return redirect()->back()->with('error', __('passwords.no_accounts_selected'));
        }

        $accounts = PasswordAccount::whereIn('id', $accountIds)->get();
        $users = User::where('is_active', true)->get();

        return view('password-accounts.batch-assign', compact('accounts', 'users', 'accountIds'));
    }

    /**
     * Store batch assignments
     */
    public function batchAssignStore(Request $request)
    {
        $accountIds = $request->input('account_ids', []);
        $userIds = $request->input('user_ids', []);
        $accessLevel = $request->input('access_level', 'read-only');
        
        if (empty($accountIds) || empty($userIds)) {
            return redirect()->back()->with('error', __('passwords.no_accounts_or_users_selected'));
        }

        $assignedCount = 0;

        foreach ($accountIds as $accountId) {
            foreach ($userIds as $userId) {
                // Check if assignment already exists
                $existingAssignment = PasswordAssignment::where('account_id', $accountId)
                    ->where('user_id', $userId)
                    ->first();

                if (!$existingAssignment) {
                    PasswordAssignment::create([
                        'account_id' => $accountId,
                        'user_id' => $userId,
                        'access_level' => $accessLevel,
                        'can_view_password' => true,
                        'can_edit_password' => $accessLevel === 'manage',
                        'can_delete_account' => false,
                        'assigned_by' => Auth::id(),
                    ]);

                    // Log the assignment
                    PasswordAuditLog::create([
                        'account_id' => $accountId,
                        'user_id' => $userId,
                        'action' => 'assigned',
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    $assignedCount++;
                }
            }
        }

        return redirect()->route('password-accounts.index')
            ->with('success', __('passwords.batch_assign_success', ['count' => $assignedCount]));
    }

    /**
     * Export accounts to CSV
     */
    private function exportToCsv($accounts)
    {
        $filename = 'password_accounts_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($accounts) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                __('passwords.account_name'),
                __('passwords.email_username'),
                __('passwords.url'),
                __('passwords.category'),
                __('passwords.is_shared'),
                __('passwords.requires_2fa'),
                __('passwords.expires_at'),
                __('passwords.created_at'),
            ]);

            // CSV data
            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->name,
                    $account->email,
                    $account->url,
                    $account->is_shared ? __('passwords.shared') : __('passwords.private'),
                    $account->requires_2fa ? __('passwords.yes') : __('passwords.no'),
                    $account->expiration_date ? $account->expiration_date->format('Y-m-d') : '',
                    $account->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate AI notes for password account
     */
    public function generateNotes(Request $request)
    {
        Log::info('Generate notes request received', $request->all());
        
        $request->validate([
            'account_name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:password_categories,id',
            'email' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
        ]);

        try {
            Log::info('Creating GeminiService...');
            $geminiService = new GeminiService();
            
            // Get category name if category_id is provided
            $categoryName = null;
            if ($request->category_id) {
                $category = PasswordCategory::find($request->category_id);
                $categoryName = $category ? $category->display_name : null;
            }

            // Generate notes based on current locale
            Log::info('Generating notes...', [
                'locale' => app()->getLocale(),
                'account_name' => $request->account_name,
                'category_name' => $categoryName,
                'email' => $request->email,
                'url' => $request->url
            ]);
            
            $notes = app()->getLocale() === 'ar' 
                ? $geminiService->generatePasswordNotesArabic(
                    $request->account_name,
                    $categoryName,
                    $request->email,
                    $request->url
                )
                : $geminiService->generatePasswordNotes(
                    $request->account_name,
                    $categoryName,
                    $request->email,
                    $request->url
                );

            Log::info('Notes generated', ['notes' => $notes]);

            if ($notes) {
                return response()->json([
                    'success' => true,
                    'notes' => $notes
                ]);
            } else {
                Log::warning('Failed to generate notes');
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to generate notes. Please try again.')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error generating notes: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while generating notes.')
            ], 500);
        }
    }
}