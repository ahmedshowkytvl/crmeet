# نظام إدارة الأصول (Assets Control System)

## نظرة عامة
نظام إدارة الأصول هو وحدة متكاملة تم تطويرها لإدارة أصول الشركة بشكل شامل وفعال. يوفر النظام إمكانية تتبع الأصول، إدارتها، تعيينها للموظفين، ومراقبة حالتها طوال دورة حياتها.

## المميزات الرئيسية

### 1. إدارة الأصول
- **تسجيل الأصول**: إضافة أصول جديدة مع تفاصيل شاملة
- **توليد الباركود**: توليد تلقائي للباركود الفريد لكل أصل
- **تتبع الحالة**: مراقبة حالة الأصول (نشط، صيانة، متقاعد)
- **الخصائص الديناميكية**: خصائص مخصصة لكل فئة أصول

### 2. تصنيف الأصول
- **فئات الأصول**: تنظيم الأصول في فئات مختلفة
- **الخصائص المخصصة**: إضافة خصائص ديناميكية لكل فئة
- **أنواع البيانات**: دعم أنواع مختلفة (نص، رقم، تاريخ، قائمة، منطقي، صورة)

### 3. إدارة المواقع
- **مواقع الأصول**: تتبع مواقع الأصول المختلفة
- **العناوين**: حفظ عناوين مفصلة لكل موقع

### 4. تعيين الأصول
- **تعيين للموظفين**: ربط الأصول بالموظفين
- **تاريخ التعيين**: تتبع تواريخ التعيين والإرجاع
- **ملاحظات**: إضافة ملاحظات للتعيينات

### 5. سجل الأنشطة
- **تتبع العمليات**: تسجيل جميع العمليات على الأصول
- **أنواع العمليات**: تعيين، إرجاع، نقل، إصلاح، تصرف
- **التواريخ والأوقات**: تتبع دقيق للتواريخ

### 6. لوحة التحكم
- **إحصائيات شاملة**: عرض إحصائيات الأصول
- **الرسوم البيانية**: تمثيل بصري للبيانات
- **التقارير**: تقارير مفصلة عن الأصول

## الهيكل التقني

### قاعدة البيانات
تم إنشاء 7 جداول رئيسية:

1. **asset_categories** - فئات الأصول
2. **asset_locations** - مواقع الأصول
3. **assets** - الأصول الرئيسية
4. **asset_assignments** - تعيينات الأصول
5. **asset_logs** - سجل الأنشطة
6. **asset_category_properties** - خصائص الفئات
7. **asset_property_values** - قيم الخصائص

### النماذج (Models)
- `AssetCategory` - فئات الأصول
- `AssetLocation` - مواقع الأصول
- `Asset` - الأصول الرئيسية
- `AssetAssignment` - تعيينات الأصول
- `AssetLog` - سجل الأنشطة
- `AssetCategoryProperty` - خصائص الفئات
- `AssetPropertyValue` - قيم الخصائص

### الكنترولرز (Controllers)
- `AssetController` - إدارة الأصول
- `AssetCategoryController` - إدارة الفئات
- `AssetLocationController` - إدارة المواقع
- `AssetAssignmentController` - إدارة التعيينات
- `AssetLogController` - إدارة السجلات
- `AssetDashboardController` - لوحة التحكم

### الخدمات (Services)
- `BarcodeService` - توليد الباركود
- `AssetService` - منطق الأعمال للأصول

## التثبيت والإعداد

### 1. تشغيل المايجريشنز
```bash
php artisan migrate
```

### 2. تشغيل الـ Seeders
```bash
php artisan db:seed --class=AssetSeeder
```

### 3. تثبيت المكتبات المطلوبة
```bash
composer install
```

## الاستخدام

### الوصول للنظام
1. قم بتسجيل الدخول للنظام
2. اذهب إلى "Assets Control" في القائمة الجانبية
3. اختر الوحدة المطلوبة

### إضافة أصل جديد
1. اذهب إلى "Assets" > "Create New"
2. اختر الفئة المناسبة
3. املأ البيانات الأساسية
4. املأ الخصائص المخصصة للفئة
5. احفظ الأصل

### تعيين أصل
1. اذهب إلى "Assignments" > "Create New"
2. اختر الأصل والموظف
3. أضف ملاحظات (اختياري)
4. احفظ التعيين

### طباعة الباركود
1. اذهب إلى صفحة تفاصيل الأصل
2. اضغط على "Print Barcode"
3. سيتم فتح نافذة طباعة جديدة

## الملفات المضافة

### المايجريشنز
- `2024_01_01_000001_create_asset_categories_table.php`
- `2024_01_01_000002_create_asset_locations_table.php`
- `2024_01_01_000003_create_assets_table.php`
- `2024_01_01_000004_create_asset_assignments_table.php`
- `2024_01_01_000005_create_asset_logs_table.php`
- `2024_01_01_000006_create_asset_category_properties_table.php`
- `2024_01_01_000007_create_asset_property_values_table.php`

### النماذج
- `app/Models/AssetCategory.php`
- `app/Models/AssetLocation.php`
- `app/Models/Asset.php`
- `app/Models/AssetAssignment.php`
- `app/Models/AssetLog.php`
- `app/Models/AssetCategoryProperty.php`
- `app/Models/AssetPropertyValue.php`

### الكنترولرز
- `app/Http/Controllers/AssetController.php`
- `app/Http/Controllers/AssetCategoryController.php`
- `app/Http/Controllers/AssetLocationController.php`
- `app/Http/Controllers/AssetAssignmentController.php`
- `app/Http/Controllers/AssetLogController.php`
- `app/Http/Controllers/AssetDashboardController.php`

### الخدمات
- `app/Services/BarcodeService.php`
- `app/Services/AssetService.php`

### ملفات Blade
- `resources/views/assets/dashboard.blade.php`
- `resources/views/assets/index.blade.php`
- `resources/views/assets/create.blade.php`
- `resources/views/assets/show.blade.php`
- `resources/views/assets/categories/index.blade.php`
- `resources/views/assets/categories/create.blade.php`
- `resources/views/assets/categories/show.blade.php`
- `resources/views/assets/locations/index.blade.php`
- `resources/views/assets/locations/create.blade.php`
- `resources/views/assets/assignments/index.blade.php`
- `resources/views/assets/assignments/create.blade.php`
- `resources/views/assets/logs/index.blade.php`
- `resources/views/assets/print-barcode.blade.php`

### ملفات CSS و JavaScript
- `public/css/assets.css`
- `public/js/assets.js`

### ملفات الترجمة
- `lang/ar/assets.php`
- `lang/en/assets.php`

### الـ Seeders
- `database/seeders/AssetSeeder.php`

## الروتس المضافة

```php
// Assets Control System Routes
Route::middleware(['auth'])->prefix('assets')->name('assets.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AssetDashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [AssetDashboardController::class, 'statistics'])->name('statistics');

    // Assets
    Route::resource('assets', AssetController::class);
    Route::get('assets/{asset}/print-barcode', [AssetController::class, 'printBarcode'])->name('assets.print-barcode');
    Route::get('assets/{asset}/download-barcode', [AssetController::class, 'downloadBarcode'])->name('assets.download-barcode');

    // Categories
    Route::resource('categories', AssetCategoryController::class);
    Route::patch('categories/{category}/toggle-status', [AssetCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::post('categories/{category}/properties', [AssetCategoryController::class, 'storeProperty'])->name('categories.store-property');
    Route::put('properties/{property}', [AssetCategoryController::class, 'updateProperty'])->name('properties.update');
    Route::delete('properties/{property}', [AssetCategoryController::class, 'destroyProperty'])->name('properties.destroy');

    // Locations
    Route::resource('locations', AssetLocationController::class);
    Route::patch('locations/{location}/toggle-status', [AssetLocationController::class, 'toggle-status'])->name('locations.toggle-status');

    // Assignments
    Route::resource('assignments', AssetAssignmentController::class);
    Route::get('assignments/{assignment}/return', [AssetAssignmentController::class, 'showReturnForm'])->name('assignments.return');
    Route::post('assignments/{assignment}/return', [AssetAssignmentController::class, 'return'])->name('assignments.return.store');
    Route::get('assignments/assets', [AssetAssignmentController::class, 'getAssets'])->name('assignments.assets');

    // Logs
    Route::get('logs', [AssetLogController::class, 'index'])->name('logs.index');
    Route::get('logs/asset/{asset}', [AssetLogController::class, 'asset'])->name('logs.asset');
    Route::get('logs/export', [AssetLogController::class, 'export'])->name('logs.export');
});
```

## المكتبات المستخدمة

### picqer/php-barcode-generator
مكتبة لتوليد الباركودات بصيغة PNG
```bash
composer require picqer/php-barcode-generator
```

## الدعم والمساعدة

### استكشاف الأخطاء
1. تأكد من تشغيل المايجريشنز
2. تأكد من تشغيل الـ Seeders
3. تأكد من تثبيت المكتبات المطلوبة
4. تحقق من صلاحيات الملفات

### المشاكل الشائعة
1. **خطأ في العلاقات**: تأكد من صحة أسماء الأعمدة في العلاقات
2. **خطأ في الباركود**: تأكد من تثبيت مكتبة الباركود
3. **خطأ في الترجمة**: تأكد من وجود ملفات الترجمة

## التطوير المستقبلي

### المميزات المقترحة
1. **التقارير المتقدمة**: تقارير أكثر تفصيلاً
2. **الإشعارات**: إشعارات للصيانة الدورية
3. **التكامل**: تكامل مع أنظمة أخرى
4. **التطبيق المحمول**: تطبيق للهواتف الذكية
5. **الذكاء الاصطناعي**: تحليل البيانات والتنبؤات

### التحسينات المقترحة
1. **الأداء**: تحسين سرعة الاستعلامات
2. **الأمان**: تحسين الأمان والحماية
3. **واجهة المستخدم**: تحسين التصميم والتفاعل
4. **الاستجابة**: تحسين الاستجابة للأجهزة المختلفة

## الخلاصة

نظام إدارة الأصول يوفر حلاً شاملاً وفعالاً لإدارة أصول الشركة. مع المميزات المتقدمة والتصميم المرن، يمكن للنظام أن يتكيف مع احتياجات الشركات المختلفة ويوفر إدارة فعالة للأصول.

---

**تم التطوير بواسطة**: فريق التطوير  
**تاريخ الإنشاء**: 2024  
**الإصدار**: 1.0.0

