<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::active()->ordered()->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'slug' => 'required|string|unique:roles,slug',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'slug' => $request->slug,
                'is_active' => true,
                'sort_order' => Role::max('sort_order') + 1,
            ]);

            if ($request->has('permissions')) {
                foreach ($request->permissions as $permissionId) {
                    RolePermission::create([
                        'role' => $role->slug,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('roles.index')
                ->with('success', __('messages.role_created_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $permissions = $role->permissions;
        return view('roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'slug' => 'required|string|unique:roles,slug,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'slug' => $request->slug,
            ]);

            // Update permissions
            RolePermission::where('role', $role->slug)->delete();
            if ($request->has('permissions')) {
                foreach ($request->permissions as $permissionId) {
                    RolePermission::create([
                        'role' => $role->slug,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('roles.index')
                ->with('success', __('messages.role_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Check if role is being used by users
        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => __('messages.role_in_use')]);
        }

        DB::beginTransaction();
        try {
            // Delete role permissions
            RolePermission::where('role', $role->slug)->delete();
            
            // Delete role
            $role->delete();

            DB::commit();
            return redirect()->route('roles.index')
                ->with('success', __('messages.role_deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle role status
     */
    public function toggleStatus(Role $role)
    {
        $role->update(['is_active' => !$role->is_active]);
        
        $status = $role->is_active ? 'activated' : 'deactivated';
        return back()->with('success', __('messages.role_' . $status . '_successfully'));
    }
}
