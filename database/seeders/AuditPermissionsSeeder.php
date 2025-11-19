<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class AuditPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create audit permissions
        $permissions = [
            [
                'name' => 'view-audit-logs',
                'display_name' => 'عرض سجل العمليات',
                'description' => 'إمكانية عرض سجل العمليات في النظام',
                'module' => 'audit',
            ],
            [
                'name' => 'export-audit-logs',
                'display_name' => 'تصدير سجل العمليات',
                'description' => 'إمكانية تصدير سجل العمليات',
                'module' => 'audit',
            ],
            [
                'name' => 'manage-audit-settings',
                'display_name' => 'إدارة إعدادات التدقيق',
                'description' => 'إمكانية إدارة إعدادات نظام التدقيق',
                'module' => 'audit',
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $auditPermissions = Permission::where('module', 'audit')->get();
            $adminRole->permissions()->syncWithoutDetaching($auditPermissions->pluck('id'));
        }

        // Assign view permission to HR role
        $hrRole = Role::where('name', 'hr')->first();
        if ($hrRole) {
            $viewPermission = Permission::where('name', 'view-audit-logs')->first();
            if ($viewPermission) {
                $hrRole->permissions()->syncWithoutDetaching([$viewPermission->id]);
            }
        }

        // Assign view permission to manager role
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $viewPermission = Permission::where('name', 'view-audit-logs')->first();
            if ($viewPermission) {
                $managerRole->permissions()->syncWithoutDetaching([$viewPermission->id]);
            }
        }
    }
}
