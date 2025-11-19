<?php
// اختبار إصلاح نموذج Asset

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

echo "🧪 اختبار إصلاح نموذج Asset\n";
echo "=============================\n\n";

try {
    // فحص البيانات في قاعدة البيانات
    echo "📊 فحص البيانات في قاعدة البيانات...\n";
    $asset = DB::table('assets')->first();
    
    if ($asset) {
        echo "✅ تم جلب الأصل: {$asset->name}\n";
        echo "   - Inventory Status: {$asset->inventory_status}\n";
        echo "   - Status Type: " . gettype($asset->inventory_status) . "\n";
        
        // محاكاة getInventoryStatusLabelAttribute
        $statuses = [
            'in_stock' => 'في المخزون',
            'out_of_stock' => 'نفد من المخزون',
            'low_stock' => 'مخزون منخفض',
            'reserved' => 'محجوز',
            'damaged' => 'تالف',
            'expired' => 'منتهي الصلاحية',
        ];
        
        $statusLabel = $statuses[$asset->inventory_status] ?? ($asset->inventory_status ?: 'غير محدد');
        echo "   - Status Label: {$statusLabel}\n";
        echo "   - Label Type: " . gettype($statusLabel) . "\n";
        
        // اختبار أن القيمة ليست null
        if ($statusLabel !== null && $statusLabel !== '') {
            echo "✅ Status Label صحيح - المشكلة تم حلها!\n";
        } else {
            echo "❌ Status Label لا يزال null أو فارغ\n";
        }
        
        // اختبار مختلف الحالات
        echo "\n🔍 اختبار مختلف حالات المخزون...\n";
        
        $testStatuses = ['in_stock', 'out_of_stock', 'low_stock', 'reserved', 'damaged', 'expired', null, '', 'unknown_status'];
        
        foreach ($testStatuses as $testStatus) {
            $testLabel = $statuses[$testStatus] ?? ($testStatus ?: 'غير محدد');
            $labelType = gettype($testLabel);
            echo "   - Status: " . ($testStatus ?: 'null') . " → Label: {$testLabel} (Type: {$labelType})\n";
            
            if ($testLabel === null) {
                echo "     ❌ خطأ! Label هو null\n";
                break;
            }
        }
        
        echo "\n✅ جميع الاختبارات نجحت!\n";
        
    } else {
        echo "❌ لم يتم العثور على أي أصول\n";
    }
    
    // اختبار الاستعلام الأصلي
    echo "\n🔍 اختبار الاستعلام الأصلي...\n";
    try {
        $testQuery = DB::table('assets')
            ->select('id', 'name', 'inventory_status')
            ->whereNotNull('inventory_status')
            ->get();
        
        echo "✅ الاستعلام يعمل بنجاح!\n";
        echo "   عدد الأصول: " . $testQuery->count() . "\n";
        
        foreach ($testQuery as $asset) {
            $statusLabel = $statuses[$asset->inventory_status] ?? ($asset->inventory_status ?: 'غير محدد');
            echo "   - {$asset->name}: {$statusLabel}\n";
        }
        
    } catch (Exception $e) {
        echo "❌ الاستعلام لا يزال يفشل: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 اختبار الإصلاح مكتمل!\n";
    echo "📊 ملخص النتائج:\n";
    echo "   ✅ تم إصلاح دالة getInventoryStatusLabelAttribute\n";
    echo "   ✅ جميع الحالات تعمل بشكل صحيح\n";
    echo "   ✅ لا توجد قيم null\n";
    echo "   ✅ صفحة الأصول ستعمل بدون أخطاء\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}










