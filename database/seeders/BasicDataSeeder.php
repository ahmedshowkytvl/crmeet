<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'name_ar' => 'مدير النظام',
            'display_name' => 'System Administrator',
            'slug' => 'admin',
            'description' => 'Full system access',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'name_ar' => 'مدير',
            'display_name' => 'Department Manager',
            'slug' => 'manager',
            'description' => 'Department management access',
            'is_active' => true,
            'sort_order' => 2
        ]);

        $employeeRole = Role::create([
            'name' => 'Employee',
            'name_ar' => 'موظف',
            'display_name' => 'Regular Employee',
            'slug' => 'employee',
            'description' => 'Basic employee access',
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Create Departments
        $hrDepartment = Department::create([
            'name' => 'Human Resources',
            'name_ar' => 'الموارد البشرية',
            'description' => 'Human Resources Department',
            'code' => 'HR',
            'is_active' => true
        ]);

        $itDepartment = Department::create([
            'name' => 'Information Technology',
            'name_ar' => 'تكنولوجيا المعلومات',
            'description' => 'IT Department',
            'code' => 'IT',
            'is_active' => true
        ]);

        $financeDepartment = Department::create([
            'name' => 'Finance',
            'name_ar' => 'المالية',
            'description' => 'Finance Department',
            'code' => 'FIN',
            'is_active' => true
        ]);

        $marketingDepartment = Department::create([
            'name' => 'Marketing',
            'name_ar' => 'التسويق',
            'description' => 'Marketing Department',
            'code' => 'MKT',
            'is_active' => true
        ]);

        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'name_ar' => 'مدير النظام',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'department_id' => $hrDepartment->id,
            'role_id' => $adminRole->id,
            'job_title' => 'System Administrator',
            'company' => 'Egypt Express Travel',
            'work_email' => 'admin@company.com',
            'employee_id' => 'ADMIN001',
            'EmployeeCode' => 1,
            'hire_date' => now()->subYears(2),
            'phone_work' => '+201234567890',
            'nationality' => 'Egyptian',
            'city' => 'Cairo',
            'country' => 'Egypt'
        ]);

        // Create Sample Employee
        $employee = User::create([
            'name' => 'Ahmed Mohamed',
            'name_ar' => 'أحمد محمد',
            'email' => 'ahmed.mohamed@company.com',
            'password' => Hash::make('password'),
            'department_id' => $itDepartment->id,
            'role_id' => $employeeRole->id,
            'manager_id' => $admin->id,
            'job_title' => 'Software Developer',
            'company' => 'Egypt Express Travel',
            'work_email' => 'ahmed.mohamed@company.com',
            'employee_id' => 'EMP001',
            'EmployeeCode' => 1001,
            'hire_date' => now()->subMonths(6),
            'phone_work' => '+201234567891',
            'phone_personal' => '+201234567892',
            'nationality' => 'Egyptian',
            'city' => 'Cairo',
            'country' => 'Egypt',
            'birthday' => '1990-05-15',
            'birth_date' => '1990-05-15'
        ]);

        $this->command->info('Basic data created successfully!');
        $this->command->info('Admin: admin@company.com / password');
        $this->command->info('Employee: ahmed.mohamed@company.com / password');
    }
}