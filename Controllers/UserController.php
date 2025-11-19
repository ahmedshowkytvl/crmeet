<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\ZohoTicketCache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Set locale if provided
        if ($request->has('locale')) {
            app()->setLocale($request->get('locale'));
            session(['locale' => $request->get('locale')]);
        }
        
        $query = User::activeEmployees()
                    ->select('users.*') // Explicitly select all columns from users table
                    ->with([
                        'department:id,name,name_ar',
                        'role:id,name,name_ar,slug',
                        'phones.phoneType:id,name',
                        'manager:id,name,email'
                    ]);
        
        // Check permissions based on new role system
        $user = Auth::user();
        if ($user && $user->role && $user->role->slug === 'employee') {
            // Employees can only see themselves and team members
            $query->where(function($q) use ($user) {
                $q->where('id', $user->id)
                  ->orWhere('department_id', $user->department_id);
            });
        }
        // Other roles can see all users
        
        // Handle search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(work_email) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereHas('department', function($departmentQuery) use ($searchTerm) {
                      $departmentQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                                     ->orWhereRaw('LOWER(name_ar) LIKE LOWER(?)', ["%{$searchTerm}%"]);
                  })
                  ->orWhereHas('role', function($roleQuery) use ($searchTerm) {
                      $roleQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                               ->orWhereRaw('LOWER(name_ar) LIKE LOWER(?)', ["%{$searchTerm}%"]);
                  });
            });
        }
        
        // Handle department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->get('department'));
        }
        
        // Handle role filter
        if ($request->filled('role')) {
            $query->where('role_id', $request->get('role'));
        }
        
        // Handle manager filter
        if ($request->filled('manager')) {
            $query->where('manager_id', $request->get('manager'));
        }
        
        // Handle job title filter
        if ($request->filled('job_title')) {
            $query->where('job_title', $request->get('job_title'));
        }
        
        // Handle status filter
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Handle birthday filter
        if ($request->filled('birthday_from')) {
            $query->where(function($q) use ($request) {
                $q->where('birthday', '>=', $request->get('birthday_from'))
                  ->orWhere('birth_date', '>=', $request->get('birthday_from'));
            });
        }
        
        if ($request->filled('birthday_to')) {
            $query->where(function($q) use ($request) {
                $q->where('birthday', '<=', $request->get('birthday_to'))
                  ->orWhere('birth_date', '<=', $request->get('birthday_to'));
            });
        }
        
        // Handle per_page parameter
        $perPage = $request->get('per_page', 15);
        
        if ($perPage === 'all') {
            $users = $query->latest()->get();
            // Create a custom paginator for "all" option
            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                $users,
                $users->count(),
                $users->count(),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
        } else {
            $users = $query->latest()->paginate($perPage);
        }
        
        // Use select() to only get needed columns for better performance
        $departments = Department::select('id', 'name', 'name_ar')->get();
        $roles = \App\Models\Role::active()->ordered()->select('id', 'name', 'name_ar', 'slug')->get();
        
        // Optimize managers query - get users who are managers OR have subordinates
        $managers = User::select('id', 'name', 'email')
            ->where(function($q) {
                $q->whereHas('role', function($roleQuery) {
                    $roleQuery->whereIn('slug', ['manager', 'head_manager', 'team_leader']);
                })->orWhereHas('subordinates');
            })
            ->get();
        
        // Get unique job titles - optimized
        $jobTitles = User::select('job_title')
            ->whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->pluck('job_title')
            ->sort()
            ->values();
        
        return view('users.index', compact('users', 'departments', 'roles', 'managers', 'jobTitles'));
    }

    public function batchUpdate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
            'office_address' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ]);

        $userIds = $request->input('user_ids');
        $updateData = [];

        // Only include fields that have values
        if ($request->filled('department_id')) {
            $updateData['department_id'] = $request->input('department_id');
        }

        if ($request->filled('role_id')) {
            $updateData['role_id'] = $request->input('role_id');
        }

        if ($request->has('manager_id')) {
            $updateData['manager_id'] = $request->input('manager_id') == 0 ? null : $request->input('manager_id');
        }

        if ($request->filled('office_address')) {
            $updateData['office_address'] = $request->input('office_address');
        }

        if ($request->filled('company')) {
            $updateData['company'] = $request->input('company');
        }

        if ($request->filled('job_title')) {
            $updateData['job_title'] = $request->input('job_title');
        }

        if (empty($updateData)) {
            return redirect()->back()->with('error', 'لم يتم تحديد أي تغييرات لتطبيقها.');
        }

        // Update users
        $updatedCount = User::whereIn('id', $userIds)->update($updateData);

        return redirect()->back()->with('success', "تم تحديث {$updatedCount} موظف بنجاح.");
    }

    public function batchDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->input('user_ids');
        
        // Check if any of the users to delete is the current user
        $currentUser = Auth::user();
        if (in_array($currentUser->id, $userIds)) {
            return redirect()->back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        // Delete users
        $deletedCount = User::whereIn('id', $userIds)->delete();

        return redirect()->back()->with('success', "تم حذف {$deletedCount} موظف بنجاح.");
    }

    public function batchEdit(Request $request)
    {
        $query = User::activeEmployees()
                    ->with(['department', 'role', 'manager']);
        
        // Handle search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(work_email) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereHas('department', function($departmentQuery) use ($searchTerm) {
                      $departmentQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                                     ->orWhereRaw('LOWER(name_ar) LIKE LOWER(?)', ["%{$searchTerm}%"]);
                  })
                  ->orWhereHas('role', function($roleQuery) use ($searchTerm) {
                      $roleQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                               ->orWhereRaw('LOWER(name_ar) LIKE LOWER(?)', ["%{$searchTerm}%"]);
                  });
            });
        }
        
        $users = $query->latest()->paginate(20);
        $departments = Department::all();
        $roles = \App\Models\Role::active()->ordered()->get();
        $managers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['manager', 'head_manager', 'team_leader']);
        })->orWhereHas('subordinates')->get();
        
        // Get unique job titles
        $jobTitles = User::whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->pluck('job_title')
            ->sort()
            ->values();
        
        return view('users.batch-edit', compact('users', 'departments', 'roles', 'managers', 'jobTitles'));
    }

    public function create()
    {
        $departments = Department::all();
        $roles = \App\Models\Role::active()->ordered()->get();
        
        // Managers can only assign to their team
        $user = Auth::user();
        if ($user && $user->role && $user->role->slug === 'manager') {
            $users = User::where('manager_id', $user->id)->get();
        } else {
            $users = User::all();
        }
        
        return view('users.create', compact('departments', 'users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'phone_work' => 'nullable|string|max:20',
            'phone_personal' => 'nullable|string|max:20',
            'work_email' => 'nullable|email|max:255',
            'avaya_extension' => 'nullable|string|max:20',
            'microsoft_teams_id' => 'nullable|email|max:255',
            'job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'office_address' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'birthday' => 'nullable|date',
            'bio' => 'nullable|string',
            'notes' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Zoho fields
            'zoho_agent_name' => 'nullable|string|max:255',
            'zoho_agent_id' => 'nullable|string|max:255',
            'zoho_email' => 'nullable|email|max:255',
            'is_zoho_enabled' => 'nullable|boolean',
            'insurance_status' => 'nullable|in:insured,not_insured',
        ]);

        $userData = [
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'role_id' => $request->role_id,
            'phone_work' => $request->phone_work,
            'phone_personal' => $request->phone_personal,
            'work_email' => $request->work_email,
            'avaya_extension' => $request->avaya_extension,
            'microsoft_teams_id' => $request->microsoft_teams_id ?: $request->work_email,
            'job_title' => $request->job_title,
            'company' => $request->company,
            'manager_id' => $request->manager_id,
            'office_address' => $request->office_address,
            'linkedin_url' => $request->linkedin_url,
            'website_url' => $request->website_url,
            'birthday' => $request->birthday,
            'birth_date' => $request->birthday,
            'bio' => $request->bio,
            'notes' => $request->notes,
            // Zoho fields
            'zoho_agent_name' => $request->zoho_agent_name,
            'zoho_agent_id' => $request->zoho_agent_id,
            'zoho_email' => $request->zoho_email,
            'is_zoho_enabled' => $request->boolean('is_zoho_enabled'),
            'zoho_linked_at' => $request->boolean('is_zoho_enabled') ? now() : null,
            'insurance_status' => $request->insurance_status,
        ];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $profilePicture = $request->file('profile_picture');
            $filename = time() . '_' . $profilePicture->getClientOriginalName();
            $path = $profilePicture->storeAs('profile_pictures', $filename, 'public');
            $userData['profile_picture'] = $path;
        }

        User::create($userData);

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function show(User $user)
    {
        $user->load(['department', 'assignedTasks', 'createdTasks', 'employeeRequests', 'managedRequests']);
        
        // جلب المهام المكتملة للمستخدم
        $completedTasks = $user->assignedTasks()
            ->where('status', 'completed')
            ->orderBy('actual_end_datetime', 'desc')
            ->get();
        
        // جلب تذاكر Zoho إذا كان لدى المستخدم zoho_agent_name
        $zohoTickets = [];
        if (!empty($user->zoho_agent_name)) {
            try {
                $zohoTickets = ZohoTicketCache::where('closed_by_name', $user->zoho_agent_name)
                    ->orderBy('created_at_zoho', 'desc')
                    ->limit(3000)
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching Zoho tickets for user', [
                    'user_id' => $user->id,
                    'zoho_agent_name' => $user->zoho_agent_name,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $trans = app('translator')->get('messages');
        return view('users.show', compact('user', 'trans', 'completedTasks', 'zohoTickets'));
    }

    public function edit(User $user)
    {
        // Check if user has edit permission
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasPermission('users.edit')) {
            abort(403, 'You do not have permission to edit this user.');
        }
        
        
        $departments = Department::all();
        $roles = \App\Models\Role::active()->ordered()->get();
        
        // Managers can only assign to their team
        if ($currentUser && $currentUser->role && $currentUser->role->slug === 'manager') {
            $users = User::where('manager_id', $currentUser->id)
                        ->where('id', '!=', $currentUser->id)
                        ->get();
        } else {
            $users = User::where('id', '!=', $currentUser->id)->get();
        }
        
        // Load all phones with phone types (filtering will be done in view)
        $user->load(['phones.phoneType']);
        
        return view('users.edit', compact('user', 'departments', 'users', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Check if user has edit permission
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasPermission('users.edit')) {
            abort(403, 'You do not have permission to edit this user.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'phone_personal' => 'nullable|string|max:20',
            'work_email' => 'nullable|email|max:255',
            'avaya_extension' => 'nullable|string|max:20',
            'microsoft_teams_id' => 'nullable|email|max:255',
            'job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'office_address' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'birthday' => 'nullable|string|regex:/^\d{2}-\d{2}-\d{4}$/',
            'birth_date' => 'nullable|string|regex:/^\d{2}-\d{2}-\d{4}$/',
            'employee_id' => 'nullable|string|max:255',
            'EmployeeCode' => 'nullable|integer',
            'nationality' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'notes' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Zoho fields
            'zoho_agent_name' => 'nullable|string|max:255',
            'zoho_agent_id' => 'nullable|string|max:255',
            'zoho_email' => 'nullable|email|max:255',
            'is_zoho_enabled' => 'nullable|boolean',
            'insurance_status' => 'nullable|in:insured,not_insured',
            // Work phones
            'work_phones' => 'nullable|array',
            'work_phones.*.number' => 'nullable|string|max:20',
            'work_phones.*.id' => 'nullable|exists:user_phones,id',
            'main_work_phone' => 'nullable|integer|min:0',
        ]);

        $updateData = $request->only([
            'name', 'name_ar', 'email', 'department_id', 'role_id', 'phone_personal',
            'work_email', 'avaya_extension', 'microsoft_teams_id', 'job_title', 'company', 'manager_id', 'office_address',
            'linkedin_url', 'website_url', 'birthday', 'birth_date', 'employee_id', 'EmployeeCode',
            'nationality', 'city', 'country', 'bio', 'notes', 'zoho_agent_name', 'zoho_agent_id', 'zoho_email'
        ]);
        
        // Handle insurance_status separately - always include it in updateData
        $updateData['insurance_status'] = $request->input('insurance_status', null);
        
        // Handle Zoho fields
        $updateData['is_zoho_enabled'] = $request->boolean('is_zoho_enabled');
        if ($request->boolean('is_zoho_enabled') && !$user->zoho_linked_at) {
            $updateData['zoho_linked_at'] = now();
        } elseif (!$request->boolean('is_zoho_enabled')) {
            $updateData['zoho_linked_at'] = null;
        }
        
        // Convert DD-MM-YYYY to YYYY-MM-DD for database storage
        if ($request->has('birthday') && $request->birthday) {
            $updateData['birthday'] = $this->convertDateFormat($request->birthday);
        }
        
        if ($request->has('birth_date') && $request->birth_date) {
            $updateData['birth_date'] = $this->convertDateFormat($request->birth_date);
        }
        
        // Set birth_date to birthday value if birth_date is not provided
        if ($request->has('birthday') && !$request->has('birth_date')) {
            $updateData['birth_date'] = $updateData['birthday'] ?? null;
        }
        
        // Set Microsoft Teams ID to work email if not provided
        if (!$updateData['microsoft_teams_id'] && $updateData['work_email']) {
            $updateData['microsoft_teams_id'] = $updateData['work_email'];
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            
            $profilePicture = $request->file('profile_picture');
            $filename = time() . '_' . $profilePicture->getClientOriginalName();
            $path = $profilePicture->storeAs('profile_pictures', $filename, 'public');
            $updateData['profile_picture'] = $path;
        }

        $user->update($updateData);

        // Handle work phones
        $this->handleWorkPhones($request, $user);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * Handle multiple work phones for a user
     */
    private function handleWorkPhones(Request $request, User $user)
    {
        // Get or create work phone type
        $workPhoneType = \App\Models\PhoneType::firstOrCreate(
            ['slug' => 'work'],
            ['name' => 'Work', 'name_ar' => 'عمل', 'is_active' => true, 'sort_order' => 1]
        );

        $workPhones = $request->input('work_phones', []);
        $mainPhoneIndex = $request->input('main_work_phone', 0);
        $existingPhoneIds = [];
        $primaryPhoneNumber = null;

        // Process each work phone
        foreach ($workPhones as $index => $phoneData) {
            if (empty($phoneData['number'])) {
                continue; // Skip empty phones
            }

            $phoneId = isset($phoneData['id']) && !str_starts_with($phoneData['id'], 'temp_') ? $phoneData['id'] : null;
            $isPrimary = $index == $mainPhoneIndex;

            if ($phoneId) {
                // Update existing phone
                $phone = \App\Models\UserPhone::find($phoneId);
                if ($phone && $phone->user_id == $user->id) {
                    $phone->update([
                        'phone_number' => $phoneData['number'],
                        'phone_type_id' => $workPhoneType->id,
                        'is_primary' => $isPrimary,
                    ]);
                    $existingPhoneIds[] = $phoneId;
                    if ($isPrimary) {
                        $primaryPhoneNumber = $phoneData['number'];
                    }
                }
            } else {
                // Create new phone
                $phone = \App\Models\UserPhone::create([
                    'user_id' => $user->id,
                    'phone_number' => $phoneData['number'],
                    'phone_type_id' => $workPhoneType->id,
                    'is_primary' => $isPrimary,
                ]);
                $existingPhoneIds[] = $phone->id;
                if ($isPrimary) {
                    $primaryPhoneNumber = $phoneData['number'];
                }
            }
        }

        // Remove phones that are no longer in the list
        $user->phones()
            ->whereHas('phoneType', function($query) {
                $query->where('slug', 'work');
            })
            ->whereNotIn('id', $existingPhoneIds)
            ->delete();

        // Ensure only one primary phone
        if ($primaryPhoneNumber) {
            // Set all work phones to non-primary first
            $user->phones()
                ->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })
                ->update(['is_primary' => false]);

            // Set the main phone as primary (use first to avoid duplicates)
            $primaryPhone = $user->phones()
                ->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })
                ->where('phone_number', $primaryPhoneNumber)
                ->first();
            
            if ($primaryPhone) {
                $primaryPhone->update(['is_primary' => true]);
            }

            // Update phone_work field for backward compatibility
            $user->update(['phone_work' => $primaryPhoneNumber]);
        } else {
            // If no phones, clear phone_work and remove primary flag
            $user->phones()
                ->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })
                ->update(['is_primary' => false]);
            $user->update(['phone_work' => null]);
        }
    }

    public function destroy(User $user)
    {
        // Check if user has delete permission
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasPermission('users.delete')) {
            abort(403, 'You do not have permission to delete this user.');
        }
        
        // Prevent users from deleting themselves
        if ($currentUser && $currentUser->id === $user->id) {
            return redirect()->route('users.index')->with('error', 'لا يمكنك حذف حسابك الشخصي');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Archive a user instead of deleting
     */
    public function archive(User $user)
    {
        // Check if user has delete permission
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasPermission('users.delete')) {
            abort(403, 'You do not have permission to archive this user.');
        }
        
        // Prevent users from archiving themselves
        if ($currentUser && $currentUser->id === $user->id) {
            return redirect()->route('users.index')->with('error', 'لا يمكنك أرشفة حسابك الشخصي');
        }
        
        $user->update([
            'is_archived' => true,
            'archived_at' => now()
        ]);
        
        return redirect()->route('users.index')->with('success', 'تم أرشفة المستخدم بنجاح');
    }

    /**
     * Restore an archived user
     */
    public function restore(User $user)
    {
        // Check if user has delete permission
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasPermission('users.delete')) {
            abort(403, 'You do not have permission to restore this user.');
        }
        
        $user->update([
            'is_archived' => false,
            'archived_at' => null
        ]);
        
        return redirect()->route('users.archived')->with('success', 'تم استعادة المستخدم بنجاح');
    }

    /**
     * Show archived users
     */
    public function archived(Request $request)
    {
        $query = User::archived()->with(['role', 'department', 'branch']);
        
        // Handle search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(email) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(work_email) LIKE LOWER(?)', ["%{$searchTerm}%"])
                  ->orWhereHas('department', function($departmentQuery) use ($searchTerm) {
                      $departmentQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                                     ->orWhereRaw('LOWER(name_ar) LIKE LOWER(?)', ["%{$searchTerm}%"]);
                  })
                  ->orWhereHas('role', function($roleQuery) use ($searchTerm) {
                      $roleQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchTerm}%"])
                               ->orWhereRaw('LOWER(name_ar) LIKE LOWER(?)', ["%{$searchTerm}%"]);
                  });
            });
        }
        
        $users = $query->latest('archived_at')->paginate(20);
        return view('users.archived', compact('users'));
    }

    public function contactCard(User $user)
    {
        $user->load(['department', 'manager', 'subordinates', 'assignedTasks', 'employeeRequests']);
        return view('users.contact-card', compact('user'));
    }
    
    /**
     * Convert date from DD-MM-YYYY to YYYY-MM-DD format
     */
    private function convertDateFormat($dateString)
    {
        if (!$dateString) {
            return null;
        }
        
        // Check if it's already in DD-MM-YYYY format
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dateString, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            
            // Validate the date
            if (checkdate($month, $day, $year)) {
                return $year . '-' . $month . '-' . $day;
            }
        }
        
        // If it's already in YYYY-MM-DD format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // Try to parse as Carbon date and return in YYYY-MM-DD format
        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
