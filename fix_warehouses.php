<?php
// إصلاح جدول warehouses - إضافة عمود is_active

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// إعداد قاعدة البيانات
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => '127.0.0.1',
    'port' => '5432',
    'database' => 'CRM_ALL',
    'username' => 'postgres',
    'password' => '',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "🔧 إصلاح جدول warehouses - إضافة عمود is_active\n";
echo "===============================================\n\n";

try {
    // فحص ما إذا كان العمود موجود بالفعل
    $columns = DB::select("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'warehouses' 
        AND column_name = 'is_active'
    ");
    
    if (count($columns) > 0) {
        echo "✅ عمود is_active موجود بالفعل في warehouses\n";
    } else {
        echo "⚠️ عمود is_active غير موجود في warehouses. يتم إضافته الآن...\n";
        
        // إضافة العمود
        DB::statement('ALTER TABLE warehouses ADD COLUMN is_active BOOLEAN DEFAULT true');
        
        echo "✅ تم إضافة عمود is_active بنجاح\n";
        
        // تحديث جميع السجلات الموجودة لتكون نشطة
        DB::table('warehouses')->update(['is_active' => true]);
        
        echo "✅ تم تحديث جميع المستودعات لتكون نشطة\n";
    }
    
    echo "\n🎉 تم إصلاح جدول warehouses بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إصلاح الجدول: " . $e->getMessage() . "\n";
}










