<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Hash;

class AddAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add-admin {email} {password} {--name=أحمد محمد (مدير)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new admin user with all permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');
        
        $this->info("🚀 بدء عملية إضافة المستخدم الجديد...");
        
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->warn("⚠️  المستخدم '{$email}' موجود بالفعل.");
            $this->info("📧 Email: {$existingUser->email}");
            $this->info("👤 Name: {$existingUser->name}");
            $this->info("🆔 User ID: {$existingUser->id}");
            return 0;
        }

        $this->info("✅ المستخدم غير موجود، سيتم إنشاؤه...");

        // Get or create admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if (!$adminRole) {
            $this->info("🔧 إنشاء دور المدير...");
            $adminRole = Role::create([
                'name' => 'Administrator',
                'name_ar' => 'مدير النظام',
                'slug' => 'admin',
                'description' => 'Full system administrator with all permissions',
                'description_ar' => 'مدير النظام الكامل مع جميع الصلاحيات',
                'is_active' => true,
                'sort_order' => 1
            ]);
            $this->info("✅ تم إنشاء دور المدير بنجاح (ID: {$adminRole->id})");
        } else {
            $this->info("✅ دور المدير موجود (ID: {$adminRole->id})");
        }

        // Create the new user
        $this->info("👤 إنشاء المستخدم الجديد...");
        $user = User::create([
            'name' => $name,
            'name_ar' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $adminRole->id,
            'phone_work' => '966112345678',
            'phone_mobile' => '966501234567',
            'department_id' => 1,
            'manager_id' => null,
            'position' => 'System Administrator',
            'position_ar' => 'مدير النظام',
            'hiring_date' => now(),
            'is_active' => true,
        ]);

        $this->info("✅ تم إنشاء المستخدم بنجاح (ID: {$user->id})");

        // Get all permissions and assign them
        $allPermissions = Permission::all();
        $this->info("🔐 العثور على {$allPermissions->count()} صلاحية في النظام...");
        
        $assignedCount = 0;
        foreach ($allPermissions as $permission) {
            $existing = RolePermission::where('role_id', $adminRole->id)
                ->where('permission_id', $permission->id)
                ->first();
            
            if (!$existing) {
                RolePermission::create([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                ]);
                $assignedCount++;
            }
        }

        $this->info("✅ تم تعيين {$assignedCount} صلاحية جديدة لدور المدير");

        // Get final permissions count
        $finalPermissionsCount = RolePermission::where('role_id', $adminRole->id)->count();

        $this->line("");
        $this->info("🎉 تم إنشاء المستخدم بنجاح!");
        $this->line("================================");
        $this->info("📧 Email: {$user->email}");
        $this->info("🔑 Password: {$password}");
        $this->info("👤 Name: {$user->name}");
        $this->info("🆔 User ID: {$user->id}");
        $this->info("🎭 Role: {$adminRole->name} ({$adminRole->slug})");
        $this->info("🔐 Total Permissions: {$finalPermissionsCount}");
        $this->info("📅 Created: {$user->created_at->format('Y-m-d H:i:s')}");
        $this->line("================================");
        
        $this->line("");
        $this->info("📊 إحصائيات النظام:");
        $this->info("👥 Total Users: " . User::count());
        $this->info("🎭 Total Roles: " . Role::count());
        $this->info("🔐 Total Permissions: " . Permission::count());
        
        return 0;
    }
}


