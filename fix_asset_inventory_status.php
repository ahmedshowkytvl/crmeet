<?php
// إصلاح جدول assets - إضافة عمود inventory_status

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

echo "🔧 إصلاح جدول assets - إضافة عمود inventory_status\n";
echo "==================================================\n\n";

try {
    // فحص ما إذا كان العمود موجود بالفعل
    $columns = DB::select("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'assets' 
        AND column_name = 'inventory_status'
    ");
    
    if (count($columns) > 0) {
        echo "✅ عمود inventory_status موجود بالفعل في assets\n";
    } else {
        echo "⚠️ عمود inventory_status غير موجود في assets. يتم إضافته الآن...\n";
        
        // إضافة العمود
        DB::statement('ALTER TABLE assets ADD COLUMN inventory_status VARCHAR(50) DEFAULT \'in_stock\'');
        
        echo "✅ تم إضافة عمود inventory_status بنجاح\n";
        
        // تحديث جميع السجلات الموجودة لتكون في المخزون
        DB::table('assets')->update(['inventory_status' => 'in_stock']);
        
        echo "✅ تم تحديث جميع الأصول لتكون في المخزون\n";
    }
    
    // فحص البيانات الموجودة
    echo "\n📊 البيانات الموجودة في assets:\n";
    $assets = DB::table('assets')->get();
    
    if ($assets->isEmpty()) {
        echo "   لا توجد أصول\n";
    } else {
        foreach ($assets as $asset) {
            echo "   - ID: {$asset->id}, Name: {$asset->name}, Inventory Status: {$asset->inventory_status}\n";
        }
    }
    
    echo "\n🎉 تم إصلاح جدول assets بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إصلاح الجدول: " . $e->getMessage() . "\n";
}










