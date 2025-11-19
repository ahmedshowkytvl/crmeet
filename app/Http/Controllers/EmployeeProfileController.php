<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Branch;
use App\Models\PhoneType;
use App\Models\UserPhone;
use App\Models\HiringDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EmployeeProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['role', 'department', 'branch', 'phones.phoneType', 'hiringDocuments']);
        return view('employee-profiles.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Check if user can edit this profile
        if (!Auth::user()->canManageUser($user) && Auth::id() !== $user->id) {
            abort(403, __('messages.permission_denied'));
        }

        $roles = Role::active()->ordered()->get();
        $departments = Department::active()->ordered()->get();
        $branches = Branch::active()->ordered()->get();
        $phoneTypes = PhoneType::active()->ordered()->get();
        
        $user->load(['phones.phoneType', 'hiringDocuments']);
        
        return view('employee-profiles.edit', compact('user', 'roles', 'departments', 'branches', 'phoneTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Check if user can edit this profile
        if (!Auth::user()->canManageUser($user) && Auth::id() !== $user->id) {
            abort(403, __('messages.permission_denied'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'hrid' => 'required|string|unique:users,hrid,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'position' => 'nullable|string|max:255',
            'position_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'microsoft_teams_id' => 'nullable|string|max:255',
            'avaya_extension' => 'nullable|string|max:255',
            'hiring_date' => 'required|date',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phones' => 'array',
            'phones.*.phone_type_id' => 'required|exists:phone_types,id',
            'phones.*.phone_number' => 'required|string|max:20',
            'phones.*.country_code' => 'required|string|max:5',
            'phones.*.is_primary' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::delete('public/profiles/' . $user->profile_picture);
                }
                
                $file = $request->file('profile_picture');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/profiles', $filename);
                $user->profile_picture = $filename;
            }

            // Update user basic info
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'hrid' => $request->hrid,
                'role_id' => $request->role_id,
                'department_id' => $request->department_id,
                'branch_id' => $request->branch_id,
                'position' => $request->position,
                'position_ar' => $request->position_ar,
                'address' => $request->address,
                'address_ar' => $request->address_ar,
                'microsoft_teams_id' => $request->microsoft_teams_id,
                'avaya_extension' => $request->avaya_extension,
                'hiring_date' => $request->hiring_date,
            ]);

            // Update phone numbers
            if ($request->has('phones')) {
                // Delete existing phones
                $user->phones()->delete();
                
                // Add new phones
                foreach ($request->phones as $phoneData) {
                    UserPhone::create([
                        'user_id' => $user->id,
                        'phone_type_id' => $phoneData['phone_type_id'],
                        'phone_number' => $phoneData['phone_number'],
                        'country_code' => $phoneData['country_code'],
                        'is_primary' => $phoneData['is_primary'] ?? false,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('employee-profiles.show', $user)
                ->with('success', __('messages.profile_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly uploaded document
     */
    public function storeDocument(Request $request, User $user)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
            'document_name_ar' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'document_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:document_date',
            'is_required' => 'boolean',
        ]);

        try {
            $file = $request->file('document_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/documents', $filename);

            HiringDocument::create([
                'user_id' => $user->id,
                'document_name' => $request->document_name,
                'document_name_ar' => $request->document_name_ar,
                'document_type' => $request->document_type,
                'file_path' => 'documents/' . $filename,
                'file_name' => $file->getClientOriginalName(),
                'file_extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'document_date' => $request->document_date,
                'expiry_date' => $request->expiry_date,
                'is_required' => $request->is_required ?? false,
            ]);

            return back()->with('success', __('messages.document_uploaded_successfully'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(HiringDocument $document)
    {
        try {
            // Check if user can delete this document
            if (!Auth::user()->canManageUser($document->user) && Auth::id() !== $document->user_id) {
                abort(403, __('messages.permission_denied'));
            }

            // Delete file from storage
            if (Storage::exists('public/' . $document->file_path)) {
                Storage::delete('public/' . $document->file_path);
            }

            $document->delete();
            return back()->with('success', __('messages.document_deleted_successfully'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get team members for the current user
     */
    public function teamMembers()
    {
        $user = Auth::user();
        $teamMembers = collect();

        if ($user->role && in_array($user->role->slug, ['head_manager', 'team_leader'])) {
            // Get users from same department
            $teamMembers = User::where('department_id', $user->department_id)
                ->where('id', '!=', $user->id)
                ->with(['role', 'phones.phoneType'])
                ->get();
        }

        return view('employee-profiles.team-members', compact('teamMembers'));
    }

    /**
     * Get shared tasks for the current user
     */
    public function sharedTasks()
    {
        $user = Auth::user();
        $sharedTasks = collect();

        if ($user->department_id) {
            // Get tasks from same department
            $sharedTasks = \App\Models\Task::whereHas('assignedTo', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })->with(['assignedTo', 'assignedBy'])->get();
        }

        return view('employee-profiles.shared-tasks', compact('sharedTasks'));
    }
}
