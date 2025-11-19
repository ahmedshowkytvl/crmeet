<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create default department if not exists
        $department = Department::firstOrCreate(
            ['name' => 'IT Department'],
            [
                'name' => 'IT Department',
                'description' => 'Information Technology Department',
                'manager_id' => null
            ]
        );

        // Get admin role
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        if (!$adminRole) {
            $adminRole = \App\Models\Role::where('slug', 'ceo')->first();
        }
        
        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@stafftobia.com'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@stafftobia.com',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole ? $adminRole->id : 1,
                'department_id' => $department->id,
                'phone_work' => '+1234567890',
                'job_title' => 'System Administrator',
                'company' => 'StaffTobia',
            ]
        );

        // Get manager role
        $managerRole = \App\Models\Role::where('slug', 'manager')->first();
        if (!$managerRole) {
            $managerRole = \App\Models\Role::where('slug', 'head_manager')->first();
        }
        
        // Create manager user if not exists
        User::firstOrCreate(
            ['email' => 'manager@stafftobia.com'],
            [
                'name' => 'Team Manager',
                'email' => 'manager@stafftobia.com',
                'password' => Hash::make('manager123'),
                'role_id' => $managerRole ? $managerRole->id : 2,
                'department_id' => $department->id,
                'phone_work' => '+1234567891',
                'job_title' => 'Team Manager',
                'company' => 'StaffTobia',
            ]
        );

        // Get employee role
        $employeeRole = \App\Models\Role::where('slug', 'employee')->first();
        if (!$employeeRole) {
            $employeeRole = \App\Models\Role::where('slug', 'software_developer')->first();
        }
        
        // Create employee user if not exists
        User::firstOrCreate(
            ['email' => 'employee@stafftobia.com'],
            [
                'name' => 'John Employee',
                'email' => 'employee@stafftobia.com',
                'password' => Hash::make('employee123'),
                'role_id' => $employeeRole ? $employeeRole->id : 3,
                'department_id' => $department->id,
                'manager_id' => User::where('email', 'manager@stafftobia.com')->first()->id,
                'phone_work' => '+1234567892',
                'job_title' => 'Software Developer',
                'company' => 'StaffTobia',
            ]
        );
    }
}