<?php
// إصلاح جدول asset_categories - إضافة عمود is_active

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

echo "🔧 إصلاح جدول asset_categories - إضافة عمود is_active\n";
echo "====================================================\n\n";

try {
    // فحص ما إذا كان العمود موجود بالفعل
    $columns = DB::select("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'asset_categories' 
        AND column_name = 'is_active'
    ");
    
    if (count($columns) > 0) {
        echo "✅ عمود is_active موجود بالفعل\n";
    } else {
        echo "⚠️ عمود is_active غير موجود. يتم إضافته الآن...\n";
        
        // إضافة العمود
        DB::statement('ALTER TABLE asset_categories ADD COLUMN is_active BOOLEAN DEFAULT true');
        
        echo "✅ تم إضافة عمود is_active بنجاح\n";
        
        // تحديث جميع السجلات الموجودة لتكون نشطة
        DB::table('asset_categories')->update(['is_active' => true]);
        
        echo "✅ تم تحديث جميع فئات الأصول لتكون نشطة\n";
    }
    
    // فحص الجدول النهائي
    echo "\n📋 بنية الجدول النهائية:\n";
    $finalColumns = DB::select("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'asset_categories' 
        ORDER BY ordinal_position
    ");
    
    foreach ($finalColumns as $column) {
        echo "   - {$column->column_name}: {$column->data_type} " . 
             ($column->is_nullable === 'YES' ? '(nullable)' : '(not null)') . 
             ($column->column_default ? " default: {$column->column_default}" : '') . "\n";
    }
    
    // فحص البيانات الموجودة
    echo "\n📊 البيانات الموجودة:\n";
    $categories = DB::table('asset_categories')->get();
    
    if ($categories->isEmpty()) {
        echo "   لا توجد فئات أصول\n";
    } else {
        foreach ($categories as $category) {
            echo "   - ID: {$category->id}, Name: {$category->name}, Active: " . 
                 ($category->is_active ? 'Yes' : 'No') . "\n";
        }
    }
    
    echo "\n🎉 تم إصلاح جدول asset_categories بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إصلاح الجدول: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}










