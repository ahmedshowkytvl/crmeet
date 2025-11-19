<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Hash;

class AddAdminUserController extends Controller
{
    /**
     * Add new admin user with full permissions
     */
    public function addAdminUser(Request $request)
    {
        try {
            $email = 'adminx@stafftobia.com';
            $password = 'ahmed1no2have';
            $name = 'أحمد محمد (مدير)';
            $nameAr = 'أحمد محمد (مدير)';
            
            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => "User with email '{$email}' already exists."
                ], 400);
            }

            // Get or create admin role
            $adminRole = Role::where('slug', 'admin')->first();
            if (!$adminRole) {
                $adminRole = Role::create([
                    'name' => 'Administrator',
                    'name_ar' => 'مدير النظام',
                    'slug' => 'admin',
                    'description' => 'Full system administrator with all permissions',
                    'description_ar' => 'مدير النظام الكامل مع جميع الصلاحيات',
                    'is_active' => true,
                    'sort_order' => 1
                ]);
            }

            // Create the new user
            $user = User::create([
                'name' => $name,
                'name_ar' => $nameAr,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $adminRole->id,
                'phone_work' => '966112345678',
                'phone_mobile' => '966501234567',
                'department_id' => 1, // Assuming department 1 exists
                'manager_id' => null, // Admin has no manager
                'position' => 'System Administrator',
                'position_ar' => 'مدير النظام',
                'hiring_date' => now(),
                'is_active' => true,
            ]);

            // Get all permissions
            $allPermissions = Permission::all();
            
            // Assign all permissions to admin role
            foreach ($allPermissions as $permission) {
                RolePermission::updateOrCreate([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                ]);
            }

            // Also ensure the role has admin permissions in the old system
            $adminPermissions = [
                'users.create',
                'users.edit',
                'users.delete',
                'users.manage_team',
                'departments.create',
                'departments.edit',
                'departments.delete',
                'roles.create',
                'roles.edit',
                'roles.delete',
                'tasks.create',
                'tasks.edit',
                'tasks.delete',
                'requests.approve',
                'requests.reject',
                'settings.manage',
                'reports.view',
                'reports.export'
            ];

            foreach ($adminPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    RolePermission::updateOrCreate([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Admin user '{$email}' has been successfully created with all permissions.",
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $adminRole->name,
                    'role_slug' => $adminRole->slug,
                    'permissions_count' => $allPermissions->count(),
                    'created_at' => $user->created_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating admin user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if admin user exists
     */
    public function checkAdminUser(Request $request)
    {
        try {
            $email = 'adminx@stafftobia.com';
            $user = User::where('email', $email)->with('role')->first();

            if ($user) {
                $permissions = RolePermission::where('role_id', $user->role_id)
                    ->with('permission')
                    ->get()
                    ->pluck('permission.name');

                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ? $user->role->name : 'No Role',
                        'role_slug' => $user->role ? $user->role->slug : null,
                        'permissions' => $permissions,
                        'permissions_count' => $permissions->count(),
                        'created_at' => $user->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'message' => "User '{$email}' does not exist."
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking admin user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    public function getSystemStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_roles' => Role::count(),
                'total_permissions' => Permission::count(),
                'admin_users' => User::whereHas('role', function($query) {
                    $query->where('slug', 'admin');
                })->count(),
                'roles' => Role::with('permissions')->get()->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'permissions_count' => $role->permissions->count()
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting system stats: ' . $e->getMessage()
            ], 500);
        }
    }
}


