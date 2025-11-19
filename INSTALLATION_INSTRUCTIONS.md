# تعليمات التثبيت - ميزة إضافة الموظفين بالدفعة

## المتطلبات

### 1. تثبيت مكتبة PhpSpreadsheet
قبل استخدام ميزة إضافة الموظفين بالدفعة، يجب تثبيت مكتبة PhpSpreadsheet:

```bash
# في مجلد المشروع
composer require phpoffice/phpspreadsheet
```

أو إذا كان لديك مشاكل مع composer:

```bash
# تأكد من أن PHP متاح في PATH
php composer.phar require phpoffice/phpspreadsheet
```

### 2. تحديث autoload
بعد تثبيت المكتبة:

```bash
composer dump-autoload
```

## الملفات المطلوبة

تأكد من وجود الملفات التالية:

1. `app/Http/Controllers/BatchEmployeeController.php`
2. `resources/views/users/batch-create.blade.php`
3. تحديث `routes/web.php`
4. تحديث `resources/views/users/index.blade.php`
5. تحديث `lang/ar/messages.php`
6. تحديث `composer.json`

## اختبار الميزة

### 1. الوصول للميزة
- اذهب إلى صفحة "إدارة المستخدمين"
- اضغط على زر "إضافة الموظفين بالدفعة"

### 2. تحميل قالب Excel
- اضغط على "تحميل قالب Excel"
- املأ البيانات في الملف

### 3. رفع الملف
- اختر ملف Excel المملوء بالبيانات
- اختر القسم والمنصب الافتراضي
- قم بربط الأعمدة مع الحقول
- اضغط على "إضافة الموظفين"

## استكشاف الأخطاء

### خطأ "Class not found"
إذا ظهر خطأ "Class not found" لـ PhpSpreadsheet:

1. تأكد من تثبيت المكتبة:
   ```bash
   composer show phpoffice/phpspreadsheet
   ```

2. إذا لم تكن مثبتة:
   ```bash
   composer require phpoffice/phpspreadsheet
   ```

3. امسح cache Laravel:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### خطأ في الصلاحيات
تأكد من أن المستخدم لديه صلاحية `users.create`:

1. اذهب إلى إدارة الأدوار
2. تأكد من أن الدور يحتوي على صلاحية "إنشاء مستخدمين"

### خطأ في رفع الملف
- تأكد من أن الملف بصيغة .xlsx أو .xls
- تأكد من أن حجم الملف أقل من 10 ميجابايت
- تأكد من أن الملف يحتوي على صف رؤوس

## الدعم

إذا واجهت أي مشاكل، يرجى:

1. التحقق من ملف `storage/logs/laravel.log`
2. التأكد من تثبيت جميع المتطلبات
3. التواصل مع فريق التطوير
