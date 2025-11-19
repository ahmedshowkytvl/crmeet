<?php

/**
 * سكريبت لتحديث المستخدمين المحددين إلى دور Admin مع أعلى الصلاحيات
 * 
 * المستخدمون:
 * 1. Amr.Atef@eetglobal.net
 * 2. m.anwar@egyptexpresstvl.com
 * 3. kareem@eetglobal.net
 * 4. Khaled_ahmed@egyptexpresstvl.com
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== بدء تحديث المستخدمين إلى دور Admin ===\n\n";
    
    // قائمة المستخدمين المطلوب تحديثهم
    $userEmails = [
        'Amr.Atef@eetglobal.net',
        'm.anwar@egyptexpresstvl.com',
        'kareem@eetglobal.net',
        'Khaled_ahmed@egyptexpresstvl.com'
    ];
    
    // الحصول على أو إنشاء دور Admin
    $adminRole = Role::where('slug', 'admin')->first();
    if (!$adminRole) {
        echo "إنشاء دور Admin...\n";
        $adminRole = Role::create([
            'name' => 'Administrator',
            'name_ar' => 'مدير النظام',
            'slug' => 'admin',
            'description' => 'Full system administrator with all permissions',
            'description_ar' => 'مدير النظام الكامل مع جميع الصلاحيات',
            'is_active' => true,
            'sort_order' => 1
        ]);
        echo "✓ تم إنشاء دور Admin بنجاح (ID: {$adminRole->id})\n\n";
    } else {
        echo "✓ تم العثور على دور Admin (ID: {$adminRole->id})\n\n";
    }
    
    // التأكد من أن دور Admin لديه جميع الصلاحيات
    echo "تحديث صلاحيات دور Admin...\n";
    $allPermissions = Permission::all();
    
    if ($allPermissions->count() > 0) {
        // التحقق من بنية جدول role_permissions
        $hasRoleIdColumn = DB::getSchemaBuilder()->hasColumn('role_permissions', 'role_id');
        $hasRoleColumn = DB::getSchemaBuilder()->hasColumn('role_permissions', 'role');
        
        foreach ($allPermissions as $permission) {
            if ($hasRoleIdColumn) {
                // استخدام role_id إذا كان موجوداً
                RolePermission::updateOrCreate(
                    [
                        'role_id' => $adminRole->id,
                        'permission_id' => $permission->id,
                    ]
                );
            } elseif ($hasRoleColumn) {
                // استخدام role (slug) إذا كان role_id غير موجود
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role' => $adminRole->slug,
                        'permission_id' => $permission->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
        echo "✓ تم تحديث {$allPermissions->count()} صلاحية لدور Admin\n\n";
    } else {
        echo "⚠ لا توجد صلاحيات في النظام\n\n";
    }
    
    // تحديث كل مستخدم
    $updatedUsers = [];
    $notFoundUsers = [];
    
    foreach ($userEmails as $email) {
        echo "معالجة المستخدم: {$email}\n";
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            echo "  ✗ المستخدم غير موجود\n\n";
            $notFoundUsers[] = $email;
            continue;
        }
        
        // تحديث role_id في جدول users
        $user->role_id = $adminRole->id;
        $user->save();
        
        // إضافة الدور في جدول user_roles (إذا كان موجوداً)
        if (DB::getSchemaBuilder()->hasTable('user_roles')) {
            // البحث عن مستخدم موجود لاستخدامه كـ assigned_by
            $assignedBy = User::where('id', '!=', $user->id)->first();
            $assignedById = $assignedBy ? $assignedBy->id : null;
            
            DB::table('user_roles')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'role_id' => $adminRole->id,
                ],
                [
                    'active' => true,
                    'assigned_at' => now(),
                    'assigned_by' => $assignedById,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            echo "  ✓ تم إضافة الدور في جدول user_roles\n";
        }
        
        echo "  ✓ تم تحديث المستخدم بنجاح\n";
        echo "    - الاسم: {$user->name}\n";
        echo "    - البريد: {$user->email}\n";
        echo "    - معرف المستخدم: {$user->id}\n";
        echo "    - معرف الدور: {$adminRole->id}\n\n";
        
        $updatedUsers[] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
    
    // ملخص النتائج
    echo "=== ملخص النتائج ===\n\n";
    echo "المستخدمون المحدثون بنجاح: " . count($updatedUsers) . "\n";
    foreach ($updatedUsers as $user) {
        echo "  ✓ {$user['email']} (ID: {$user['id']})\n";
    }
    
    if (count($notFoundUsers) > 0) {
        echo "\nالمستخدمون غير الموجودين: " . count($notFoundUsers) . "\n";
        foreach ($notFoundUsers as $email) {
            echo "  ✗ {$email}\n";
        }
    }
    
    echo "\n=== تم الانتهاء بنجاح ===\n";
    echo "جميع المستخدمين المحدثين لديهم الآن دور Admin مع جميع الصلاحيات.\n";
    
} catch (\Exception $e) {
    echo "\n❌ خطأ: " . $e->getMessage() . "\n";
    echo "السطر: " . $e->getLine() . "\n";
    echo "الملف: " . $e->getFile() . "\n";
    exit(1);
}

