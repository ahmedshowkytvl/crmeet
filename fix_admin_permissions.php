<?php
/**
 * إصلاح صلاحيات المستخدم الإداري
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "إصلاح صلاحيات المستخدم الإداري...\n";

try {
    // الحصول على دور CEO
    $ceoRole = DB::table('roles')->where('slug', 'ceo')->first();
    
    if (!$ceoRole) {
        echo "❌ دور CEO غير موجود\n";
        exit(1);
    }
    
    echo "✅ تم العثور على دور CEO: {$ceoRole->name}\n";
    
    // تحديث المستخدم الإداري
    $updated = DB::table('users')
        ->where('id', 1)
        ->update([
            'role_id' => $ceoRole->id,
            'updated_at' => now()
        ]);
    
    if ($updated) {
        echo "✅ تم تعيين دور CEO للمستخدم الإداري\n";
    } else {
        echo "❌ فشل في تعيين دور CEO\n";
        exit(1);
    }
    
    // التحقق من الصلاحيات
    $permissions = DB::table('role_permissions')
        ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
        ->where('role_permissions.role', 'ceo')
        ->get();
    
    echo "✅ الصلاحيات المتاحة: {$permissions->count()} صلاحية\n";
    
    // عرض بعض الصلاحيات
    echo "\nأمثلة على الصلاحيات:\n";
    foreach ($permissions->take(5) as $permission) {
        echo "- {$permission->name} ({$permission->display_name_ar})\n";
    }
    
    echo "\n🎉 تم إصلاح الصلاحيات بنجاح!\n";
    echo "يمكنك الآن الوصول لصفحة تعديل المستخدمين.\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}
?>
