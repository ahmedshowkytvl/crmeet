<?php
// اختبار نظام الأصول - إنشاء أصل وتعيينه لموظف ثم استرداده وتعيينه لموظف آخر

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

echo "🔍 اختبار نظام الأصول - EET Global Management System\n";
echo "====================================================\n\n";

try {
    // 1. فحص المستخدمين المتاحين
    echo "📋 فحص المستخدمين المتاحين...\n";
    $users = DB::table('users')->select('id', 'name', 'email')->limit(5)->get();
    
    if ($users->isEmpty()) {
        echo "❌ لا توجد مستخدمين في النظام\n";
        exit;
    }
    
    echo "✅ المستخدمون المتاحون:\n";
    foreach ($users as $user) {
        echo "   - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
    }
    
    // 2. فحص فئات الأصول
    echo "\n📂 فحص فئات الأصول...\n";
    $categories = DB::table('asset_categories')->select('id', 'name', 'name_ar')->get();
    
    if ($categories->isEmpty()) {
        echo "⚠️ لا توجد فئات أصول. إنشاء فئة افتراضية...\n";
        $categoryId = DB::table('asset_categories')->insertGetId([
            'name' => 'Computers',
            'name_ar' => 'أجهزة كمبيوتر',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ تم إنشاء فئة أصول: Computers (ID: $categoryId)\n";
    } else {
        $categoryId = $categories->first()->id;
        echo "✅ فئة الأصول المتاحة: {$categories->first()->name}\n";
    }
    
    // 3. فحص مواقع الأصول
    echo "\n📍 فحص مواقع الأصول...\n";
    $locations = DB::table('asset_locations')->select('id', 'name', 'name_ar')->get();
    
    if ($locations->isEmpty()) {
        echo "⚠️ لا توجد مواقع أصول. إنشاء موقع افتراضي...\n";
        $locationId = DB::table('asset_locations')->insertGetId([
            'name' => 'Main Office',
            'name_ar' => 'المكتب الرئيسي',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ تم إنشاء موقع أصول: Main Office (ID: $locationId)\n";
    } else {
        $locationId = $locations->first()->id;
        echo "✅ موقع الأصول المتاح: {$locations->first()->name}\n";
    }
    
    // 4. إنشاء أصل جديد
    echo "\n🖥️ إنشاء أصل جديد...\n";
    $assetId = DB::table('assets')->insertGetId([
        'name' => 'Dell Laptop - Test Asset',
        'name_ar' => 'لابتوب ديل - أصل تجريبي',
        'serial_number' => 'DL-TEST-' . time(),
        'model' => 'Latitude 5520',
        'brand' => 'Dell',
        'description' => 'Test laptop for asset management system',
        'description_ar' => 'لابتوب تجريبي لنظام إدارة الأصول',
        'purchase_price' => 25000,
        'purchase_date' => '2024-01-01',
        'warranty_expiry' => '2026-01-01',
        'status' => 'active',
        'category_id' => $categoryId,
        'location_id' => $locationId,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ تم إنشاء الأصل بنجاح:\n";
    echo "   - ID: $assetId\n";
    echo "   - Name: Dell Laptop - Test Asset\n";
    echo "   - Serial: DL-TEST-" . time() . "\n";
    echo "   - Status: active\n";
    echo "   - Assigned to: None (غير معين)\n";
    
    // 5. تعيين الأصل للمستخدم الأول
    $firstUser = $users->first();
    echo "\n👤 تعيين الأصل للمستخدم الأول...\n";
    echo "   - المستخدم: {$firstUser->name} (ID: {$firstUser->id})\n";
    
    // تحديث الأصل
    DB::table('assets')->where('id', $assetId)->update([
        'assigned_to' => $firstUser->id,
        'updated_at' => now()
    ]);
    
    // إنشاء سجل التعيين
    $assignmentId1 = DB::table('asset_assignments')->insertGetId([
        'asset_id' => $assetId,
        'assigned_to' => $firstUser->id,
        'assigned_by' => $firstUser->id, // في الواقع، يجب أن يكون مدير
        'assigned_date' => now()->toDateString(),
        'notes' => 'تعيين أولي للأصل التجريبي',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // إنشاء سجل في اللوج
    DB::table('asset_logs')->insert([
        'asset_id' => $assetId,
        'user_id' => $firstUser->id,
        'action' => 'assigned',
        'description' => "Asset assigned to {$firstUser->name}",
        'metadata' => json_encode([
            'assigned_to' => $firstUser->id,
            'assigned_by' => $firstUser->id,
            'assignment_id' => $assignmentId1
        ]),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ تم تعيين الأصل بنجاح:\n";
    echo "   - Assignment ID: $assignmentId1\n";
    echo "   - Assigned Date: " . now()->toDateString() . "\n";
    echo "   - Notes: تعيين أولي للأصل التجريبي\n";
    
    // 6. انتظار قليل (محاكاة الوقت الفعلي)
    echo "\n⏳ انتظار 2 ثانية...\n";
    sleep(2);
    
    // 7. استرداد الأصل من المستخدم الأول
    echo "\n🔄 استرداد الأصل من المستخدم الأول...\n";
    
    // تحديث الأصل - إزالة التعيين
    DB::table('assets')->where('id', $assetId)->update([
        'assigned_to' => null,
        'updated_at' => now()
    ]);
    
    // تحديث سجل التعيين - إضافة تاريخ الاسترداد
    DB::table('asset_assignments')->where('id', $assignmentId1)->update([
        'return_date' => now()->toDateString(),
        'updated_at' => now()
    ]);
    
    // إنشاء سجل في اللوج للاسترداد
    DB::table('asset_logs')->insert([
        'asset_id' => $assetId,
        'user_id' => $firstUser->id,
        'action' => 'returned',
        'description' => "Asset returned from {$firstUser->name}",
        'metadata' => json_encode([
            'returned_by' => $firstUser->id,
            'assignment_id' => $assignmentId1
        ]),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ تم استرداد الأصل بنجاح:\n";
    echo "   - Return Date: " . now()->toDateString() . "\n";
    echo "   - Status: غير معين\n";
    
    // 8. تعيين الأصل للمستخدم الثاني
    $secondUser = $users->skip(1)->first();
    if (!$secondUser) {
        $secondUser = $users->first(); // استخدام نفس المستخدم إذا لم يوجد آخر
    }
    
    echo "\n👤 تعيين الأصل للمستخدم الثاني...\n";
    echo "   - المستخدم: {$secondUser->name} (ID: {$secondUser->id})\n";
    
    // تحديث الأصل
    DB::table('assets')->where('id', $assetId)->update([
        'assigned_to' => $secondUser->id,
        'updated_at' => now()
    ]);
    
    // إنشاء سجل تعيين جديد
    $assignmentId2 = DB::table('asset_assignments')->insertGetId([
        'asset_id' => $assetId,
        'assigned_to' => $secondUser->id,
        'assigned_by' => $firstUser->id, // في الواقع، يجب أن يكون مدير
        'assigned_date' => now()->toDateString(),
        'notes' => 'تعيين ثانوي للأصل التجريبي - نقل من مستخدم آخر',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    // إنشاء سجل في اللوج
    DB::table('asset_logs')->insert([
        'asset_id' => $assetId,
        'user_id' => $secondUser->id,
        'action' => 'assigned',
        'description' => "Asset reassigned from {$firstUser->name} to {$secondUser->name}",
        'metadata' => json_encode([
            'assigned_to' => $secondUser->id,
            'assigned_by' => $firstUser->id,
            'assignment_id' => $assignmentId2,
            'previous_assignment' => $assignmentId1
        ]),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ تم تعيين الأصل للمستخدم الثاني بنجاح:\n";
    echo "   - Assignment ID: $assignmentId2\n";
    echo "   - Assigned Date: " . now()->toDateString() . "\n";
    echo "   - Notes: تعيين ثانوي للأصل التجريبي - نقل من مستخدم آخر\n";
    
    // 9. عرض ملخص العملية
    echo "\n📊 ملخص العملية:\n";
    echo "==================\n";
    
    // فحص حالة الأصل النهائية
    $finalAsset = DB::table('assets')->where('id', $assetId)->first();
    echo "🖥️ حالة الأصل النهائية:\n";
    echo "   - ID: {$finalAsset->id}\n";
    echo "   - Name: {$finalAsset->name}\n";
    echo "   - Serial: {$finalAsset->serial_number}\n";
    echo "   - Status: {$finalAsset->status}\n";
    echo "   - Assigned to: " . ($finalAsset->assigned_to ? $secondUser->name : 'غير معين') . "\n";
    
    // فحص سجلات التعيين
    $assignments = DB::table('asset_assignments')
        ->where('asset_id', $assetId)
        ->orderBy('created_at')
        ->get();
    
    echo "\n📋 سجلات التعيين:\n";
    foreach ($assignments as $assignment) {
        $user = DB::table('users')->where('id', $assignment->assigned_to)->first();
        echo "   - Assignment ID: {$assignment->id}\n";
        echo "     User: {$user->name}\n";
        echo "     Assigned Date: {$assignment->assigned_date}\n";
        echo "     Return Date: " . ($assignment->return_date ?: 'لم يتم الاسترداد') . "\n";
        echo "     Notes: {$assignment->notes}\n";
        echo "     Status: " . ($assignment->return_date ? 'تم الاسترداد' : 'نشط') . "\n\n";
    }
    
    // فحص سجلات اللوج
    $logs = DB::table('asset_logs')
        ->where('asset_id', $assetId)
        ->orderBy('created_at')
        ->get();
    
    echo "📝 سجلات النشاط:\n";
    foreach ($logs as $log) {
        $user = DB::table('users')->where('id', $log->user_id)->first();
        echo "   - {$log->action} - {$user->name}\n";
        echo "     Description: {$log->description}\n";
        echo "     Time: {$log->created_at}\n\n";
    }
    
    echo "🎉 تم الانتهاء من اختبار نظام الأصول بنجاح!\n";
    echo "\n📈 النتائج:\n";
    echo "✅ تم إنشاء أصل جديد\n";
    echo "✅ تم تعيينه للمستخدم الأول\n";
    echo "✅ تم استرداده من المستخدم الأول\n";
    echo "✅ تم تعيينه للمستخدم الثاني\n";
    echo "✅ تم تسجيل جميع العمليات في السجلات\n";
    echo "✅ النظام يعمل بشكل صحيح ومتتبع للعمليات\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في اختبار نظام الأصول: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}










