<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إنشاء الصلاحيات
        $permissions = [
            // User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'display_name_ar' => 'عرض المستخدمين', 'description' => 'Can view users list', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'display_name_ar' => 'إنشاء المستخدمين', 'description' => 'Can create new users', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'display_name_ar' => 'تعديل المستخدمين', 'description' => 'Can edit users', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'display_name_ar' => 'حذف المستخدمين', 'description' => 'Can delete users', 'module' => 'users'],
            
            // Department Management
            ['name' => 'departments.view', 'display_name' => 'View Departments', 'display_name_ar' => 'عرض الأقسام', 'description' => 'Can view departments', 'module' => 'departments'],
            ['name' => 'departments.create', 'display_name' => 'Create Departments', 'display_name_ar' => 'إنشاء الأقسام', 'description' => 'Can create departments', 'module' => 'departments'],
            ['name' => 'departments.edit', 'display_name' => 'Edit Departments', 'display_name_ar' => 'تعديل الأقسام', 'description' => 'Can edit departments', 'module' => 'departments'],
            ['name' => 'departments.delete', 'display_name' => 'Delete Departments', 'display_name_ar' => 'حذف الأقسام', 'description' => 'Can delete departments', 'module' => 'departments'],
            
            // Role Management
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'display_name_ar' => 'عرض الأدوار', 'description' => 'Can view roles', 'module' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'display_name_ar' => 'إنشاء الأدوار', 'description' => 'Can create roles', 'module' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'display_name_ar' => 'تعديل الأدوار', 'description' => 'Can edit roles', 'module' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'display_name_ar' => 'حذف الأدوار', 'description' => 'Can delete roles', 'module' => 'roles'],
            
            // Task Management
            ['name' => 'tasks.view', 'display_name' => 'View Tasks', 'display_name_ar' => 'عرض المهام', 'description' => 'Can view tasks', 'module' => 'tasks'],
            ['name' => 'tasks.create', 'display_name' => 'Create Tasks', 'display_name_ar' => 'إنشاء المهام', 'description' => 'Can create tasks', 'module' => 'tasks'],
            ['name' => 'tasks.edit', 'display_name' => 'Edit Tasks', 'display_name_ar' => 'تعديل المهام', 'description' => 'Can edit tasks', 'module' => 'tasks'],
            ['name' => 'tasks.delete', 'display_name' => 'Delete Tasks', 'display_name_ar' => 'حذف المهام', 'description' => 'Can delete tasks', 'module' => 'tasks'],
            
            // Asset Management
            ['name' => 'assets.view', 'display_name' => 'View Assets', 'display_name_ar' => 'عرض الأصول', 'description' => 'Can view assets', 'module' => 'assets'],
            ['name' => 'assets.create', 'display_name' => 'Create Assets', 'display_name_ar' => 'إنشاء الأصول', 'description' => 'Can create assets', 'module' => 'assets'],
            ['name' => 'assets.edit', 'display_name' => 'Edit Assets', 'display_name_ar' => 'تعديل الأصول', 'description' => 'Can edit assets', 'module' => 'assets'],
            ['name' => 'assets.delete', 'display_name' => 'Delete Assets', 'display_name_ar' => 'حذف الأصول', 'description' => 'Can delete assets', 'module' => 'assets'],
            
            // Contact Management
            ['name' => 'contacts.view', 'display_name' => 'View Contacts', 'display_name_ar' => 'عرض جهات الاتصال', 'description' => 'Can view contacts', 'module' => 'contacts'],
            ['name' => 'contacts.create', 'display_name' => 'Create Contacts', 'display_name_ar' => 'إنشاء جهات الاتصال', 'description' => 'Can create contacts', 'module' => 'contacts'],
            ['name' => 'contacts.edit', 'display_name' => 'Edit Contacts', 'display_name_ar' => 'تعديل جهات الاتصال', 'description' => 'Can edit contacts', 'module' => 'contacts'],
            ['name' => 'contacts.delete', 'display_name' => 'Delete Contacts', 'display_name_ar' => 'حذف جهات الاتصال', 'description' => 'Can delete contacts', 'module' => 'contacts'],
            
            // Password Management
            ['name' => 'passwords.view', 'display_name' => 'View Passwords', 'display_name_ar' => 'عرض كلمات المرور', 'description' => 'Can view password accounts', 'module' => 'passwords'],
            ['name' => 'passwords.create', 'display_name' => 'Create Passwords', 'display_name_ar' => 'إنشاء كلمات المرور', 'description' => 'Can create password accounts', 'module' => 'passwords'],
            ['name' => 'passwords.edit', 'display_name' => 'Edit Passwords', 'display_name_ar' => 'تعديل كلمات المرور', 'description' => 'Can edit password accounts', 'module' => 'passwords'],
            ['name' => 'passwords.delete', 'display_name' => 'Delete Passwords', 'display_name_ar' => 'حذف كلمات المرور', 'description' => 'Can delete password accounts', 'module' => 'passwords'],
            
            // Supplier Management
            ['name' => 'suppliers.view', 'display_name' => 'View Suppliers', 'display_name_ar' => 'عرض الموردين', 'description' => 'Can view suppliers', 'module' => 'suppliers'],
            ['name' => 'suppliers.create', 'display_name' => 'Create Suppliers', 'display_name_ar' => 'إنشاء الموردين', 'description' => 'Can create suppliers', 'module' => 'suppliers'],
            ['name' => 'suppliers.edit', 'display_name' => 'Edit Suppliers', 'display_name_ar' => 'تعديل الموردين', 'description' => 'Can edit suppliers', 'module' => 'suppliers'],
            ['name' => 'suppliers.delete', 'display_name' => 'Delete Suppliers', 'display_name_ar' => 'حذف الموردين', 'description' => 'Can delete suppliers', 'module' => 'suppliers'],
            
            // System Administration
            ['name' => 'system.admin', 'display_name' => 'System Administration', 'display_name_ar' => 'إدارة النظام', 'description' => 'Full system administration access', 'module' => 'system'],
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'display_name_ar' => 'عرض التقارير', 'description' => 'Can view system reports', 'module' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // إنشاء الأدوار
        $roles = [
            ['name' => 'CEO', 'name_ar' => 'الرئيس التنفيذي', 'slug' => 'ceo', 'description' => 'Chief Executive Officer', 'description_ar' => 'الرئيس التنفيذي', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Head Manager', 'name_ar' => 'المدير العام', 'slug' => 'head_manager', 'description' => 'Head Manager', 'description_ar' => 'المدير العام', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Manager', 'name_ar' => 'مدير', 'slug' => 'manager', 'description' => 'Department Manager', 'description_ar' => 'مدير القسم', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Team Leader', 'name_ar' => 'قائد الفريق', 'slug' => 'team_leader', 'description' => 'Team Leader', 'description_ar' => 'قائد الفريق', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Employee', 'name_ar' => 'موظف', 'slug' => 'employee', 'description' => 'Regular Employee', 'description_ar' => 'موظف عادي', 'is_active' => true, 'sort_order' => 5],
            ['name' => 'Supplier', 'name_ar' => 'مورد', 'slug' => 'supplier', 'description' => 'External Supplier', 'description_ar' => 'مورد خارجي', 'is_active' => true, 'sort_order' => 6],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // تعيين الصلاحيات للأدوار
        $rolePermissions = [
            // CEO - جميع الصلاحيات
            ['role' => 'ceo', 'permissions' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
                'assets.view', 'assets.create', 'assets.edit', 'assets.delete',
                'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
                'passwords.view', 'passwords.create', 'passwords.edit', 'passwords.delete',
                'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
                'system.admin', 'reports.view'
            ]],
            
            // Head Manager - معظم الصلاحيات
            ['role' => 'head_manager', 'permissions' => [
                'users.view', 'users.create', 'users.edit',
                'departments.view', 'departments.create', 'departments.edit',
                'roles.view',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
                'assets.view', 'assets.create', 'assets.edit', 'assets.delete',
                'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
                'passwords.view', 'passwords.create', 'passwords.edit',
                'suppliers.view', 'suppliers.create', 'suppliers.edit',
                'reports.view'
            ]],
            
            // Manager - صلاحيات محدودة
            ['role' => 'manager', 'permissions' => [
                'users.view', 'users.edit',
                'departments.view',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
                'assets.view', 'assets.create', 'assets.edit',
                'contacts.view', 'contacts.create', 'contacts.edit',
                'passwords.view',
                'suppliers.view',
                'reports.view'
            ]],
            
            // Team Leader - صلاحيات أساسية
            ['role' => 'team_leader', 'permissions' => [
                'users.view',
                'departments.view',
                'tasks.view', 'tasks.create', 'tasks.edit',
                'assets.view',
                'contacts.view', 'contacts.create',
                'passwords.view',
                'suppliers.view'
            ]],
            
            // Employee - صلاحيات محدودة جداً
            ['role' => 'employee', 'permissions' => [
                'users.view',
                'departments.view',
                'tasks.view',
                'assets.view',
                'contacts.view',
                'passwords.view',
                'suppliers.view'
            ]],
            
            // Supplier - صلاحيات محدودة
            ['role' => 'supplier', 'permissions' => [
                'users.view',
                'departments.view',
                'tasks.view',
                'assets.view',
                'contacts.view'
            ]],
        ];

        foreach ($rolePermissions as $rolePermission) {
            $role = $rolePermission['role'];
            foreach ($rolePermission['permissions'] as $permissionName) {
                $permission = DB::table('permissions')->where('name', $permissionName)->first();
                if ($permission) {
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف البيانات
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
    }
};