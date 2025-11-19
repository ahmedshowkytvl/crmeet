<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait HasPermissions
{
    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $query = Permission::query();
            $query->where('name', $permission);
            if (str_contains($permission, ':')) {
                [$resource, $action] = explode(':', $permission, 2);
                $query->orWhere(function($q) use ($resource, $action) {
                    $q->where('resource', $resource)->where('action', $action);
                });
            }
            $permission = $query->first();
        }

        if (!$permission) {
            return false;
        }

        // Resolve role ids from relation and pivot table (ANY grants access)
        $roleIds = [];
        if ($this->role && isset($this->role->id)) {
            $roleIds[] = (int) $this->role->id;
        }
        $pivotRoleIds = \Illuminate\Support\Facades\DB::table('user_roles')
            ->where('user_id', $this->id)
            ->where('active', true)
            ->pluck('role_id')
            ->all();
        $roleIds = array_values(array_unique(array_merge($roleIds, array_map('intval', $pivotRoleIds))));

        if (empty($roleIds)) {
            return false;
        }

        $query = RolePermission::query()->where('permission_id', $permission->id);
        if (Schema::hasColumn('role_permissions', 'role_id')) {
            $query->whereIn('role_id', $roleIds);
        } elseif (Schema::hasColumn('role_permissions', 'role')) {
            // Fallback: legacy schema stores role slug/text in 'role'
            $possibleRoleTexts = [];
            if ($this->role && isset($this->role->slug)) {
                $possibleRoleTexts[] = (string) $this->role->slug;
            }
            // Include common admin alias
            $possibleRoleTexts[] = 'admin';
            $query->whereIn('role', array_values(array_unique($possibleRoleTexts)));
        } else {
            return false;
        }
        return $query->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for user's role
     */
    public function getPermissions()
    {
        $roleIds = [];
        if ($this->role && isset($this->role->id)) {
            $roleIds[] = (int) $this->role->id;
        }
        $pivotRoleIds = \Illuminate\Support\Facades\DB::table('user_roles')
            ->where('user_id', $this->id)
            ->where('active', true)
            ->pluck('role_id')
            ->all();
        $roleIds = array_values(array_unique(array_merge($roleIds, array_map('intval', $pivotRoleIds))));

        if (empty($roleIds)) {
            return collect();
        }

        $query = RolePermission::query();
        if (Schema::hasColumn('role_permissions', 'role_id')) {
            $query->whereIn('role_id', $roleIds);
        } elseif (Schema::hasColumn('role_permissions', 'role')) {
            $possibleRoleTexts = [];
            if ($this->role && isset($this->role->slug)) {
                $possibleRoleTexts[] = (string) $this->role->slug;
            }
            $possibleRoleTexts[] = 'admin';
            $query->whereIn('role', array_values(array_unique($possibleRoleTexts)));
        } else {
            return collect();
        }
        return $query->with('permission')->get()->pluck('permission');
    }

    /**
     * Check if user can manage a specific user (for managers)
     */
    public function canManageUser($user)
    {
        // Check if user has manager role
        if ($this->role && $this->role->slug === 'manager') {
            // Manager can manage their subordinates
            if ($user->manager_id === $this->id) {
                return true;
            }
            
            // Manager can manage users in their department
            if ($user->department_id === $this->department_id) {
                return true;
            }
        }

        // Check if user has employee_manager role
        if ($this->role && $this->role->slug === 'employee_manager') {
            // Employee Manager can manage users in their department
            if ($user->department_id === $this->department_id) {
                return true;
            }
        }

        // Users can manage themselves
        if ($this->id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can view a specific task
     */
    public function canViewTask($task)
    {
        // Admin can view all tasks
        if ($this->role && $this->role->slug === 'admin') {
            return true;
        }

        // Manager can view team tasks
        if ($this->role && $this->role->slug === 'manager') {
            return $task->assigned_to === $this->id || 
                   $this->subordinates->contains('id', $task->assigned_to);
        }

        // Employee can view their own tasks
        return $task->assigned_to === $this->id;
    }

    /**
     * Check if user can manage a specific task
     */
    public function canManageTask($task)
    {
        // Admin can manage all tasks
        if ($this->role && $this->role->slug === 'admin') {
            return true;
        }

        // Manager can manage team tasks
        if ($this->role && $this->role->slug === 'manager') {
            return $this->subordinates->contains('id', $task->assigned_to);
        }

        // Employee can manage their own tasks
        return $task->assigned_to === $this->id;
    }
}
