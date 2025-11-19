<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = \App\Models\Role::where('slug', 'admin')->first() ?? \App\Models\Role::where('slug', 'ceo')->first();
        $managerRole = \App\Models\Role::where('slug', 'manager')->first() ?? \App\Models\Role::where('slug', 'head_manager')->first();
        $employeeRole = \App\Models\Role::where('slug', 'employee')->first() ?? \App\Models\Role::where('slug', 'software_developer')->first();
        
        // إنشاء مدير عام
        User::create([
            'name' => 'أحمد محمد',
            'email' => 'admin@stafftobia.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole ? $adminRole->id : 1,
            'phone_work' => '966112345678',
            'phone_home' => '966112345679',
            'phone_personal' => '966501234567',
            'department_id' => 1, // إدارة الموارد البشرية
            'manager_id' => null,
        ]);

        // إنشاء مدراء الأقسام
        $managers = [
            [
                'name' => 'فاطمة علي',
                'email' => 'fatima@stafftobia.com',
                'role_id' => $managerRole ? $managerRole->id : 2,
                'department_id' => 1, // إدارة الموارد البشرية
            ],
            [
                'name' => 'محمد سالم',
                'email' => 'mohammed@stafftobia.com',
                'role_id' => $managerRole ? $managerRole->id : 2,
                'department_id' => 2, // تقنية المعلومات
            ],
            [
                'name' => 'نورا أحمد',
                'email' => 'nora@stafftobia.com',
                'role_id' => $managerRole ? $managerRole->id : 2,
                'department_id' => 3, // المالية والمحاسبة
            ],
        ];

        foreach ($managers as $manager) {
            User::create([
                'name' => $manager['name'],
                'email' => $manager['email'],
                'password' => Hash::make('password'),
                'role' => $manager['role'],
                'phone_work' => '966112345678',
                'phone_home' => '966112345679',
                'phone_personal' => '966501234567',
                'department_id' => $manager['department_id'],
                'manager_id' => 1, // المدير العام
            ]);
        }

        // إنشاء موظفين عاديين
        $employees = [
            [
                'name' => 'سارة خالد',
                'email' => 'sara@stafftobia.com',
                'department_id' => 1,
                'manager_id' => 2,
            ],
            [
                'name' => 'عبدالله يوسف',
                'email' => 'abdullah@stafftobia.com',
                'department_id' => 2,
                'manager_id' => 3,
            ],
            [
                'name' => 'مريم حسن',
                'email' => 'mariam@stafftobia.com',
                'department_id' => 3,
                'manager_id' => 4,
            ],
        ];

        foreach ($employees as $employee) {
            User::create([
                'name' => $employee['name'],
                'email' => $employee['email'],
                'password' => Hash::make('password'),
                'role_id' => $employeeRole ? $employeeRole->id : 3,
                'phone_work' => '966112345678',
                'phone_home' => '966112345679',
                'phone_personal' => '966501234567',
                'department_id' => $employee['department_id'],
                'manager_id' => $employee['manager_id'],
            ]);
        }
    }
}
