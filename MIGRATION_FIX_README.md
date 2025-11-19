# حل مشكلة Migration - حقول بطاقة الاتصال الشاملة

## المشكلة
يحدث خطأ `Column not found: 1054 Unknown column 'phone_mobile'` لأن الحقول الجديدة لم يتم إضافتها إلى قاعدة البيانات بعد.

## الحلول المتاحة

### الحل الأول: تشغيل Migration يدوياً
1. افتح phpMyAdmin أو أي أداة إدارة قاعدة البيانات
2. اختر قاعدة البيانات الخاصة بالمشروع
3. شغل ملف SQL التالي: `database/add_contact_fields.sql`

### الحل الثاني: تشغيل Migration عبر PHP
1. تأكد من إعدادات قاعدة البيانات في ملف `run_migration.php`
2. شغل الملف من المتصفح: `http://localhost:8000/run_migration.php`

### الحل الثالث: تشغيل Migration عبر Artisan
```bash
cd stafftobia
php artisan migrate
```

## الحقول المضافة

### حقول الاتصال
- `phone_mobile` - الهاتف المحمول
- `phone_emergency` - هاتف الطوارئ
- `whatsapp` - واتساب
- `telegram` - تيليجرام
- `skype` - سكايب
- `facebook` - فيسبوك
- `instagram` - إنستجرام

### حقول العمل
- `employee_id` - رقم الموظف
- `hire_date` - تاريخ التعيين
- `work_location` - موقع العمل
- `office_room` - المكتب
- `extension` - التحويلة

### حقول شخصية
- `birth_date` - تاريخ الميلاد
- `nationality` - الجنسية
- `address` - العنوان
- `city` - المدينة
- `country` - البلد
- `postal_code` - الرمز البريدي

### حقول إضافية
- `skills` - المهارات
- `interests` - الاهتمامات
- `languages` - اللغات

### إعدادات الخصوصية
- `show_phone_work` - إظهار هاتف العمل
- `show_phone_personal` - إظهار الهاتف الشخصي
- `show_phone_mobile` - إظهار الهاتف المحمول
- `show_email` - إظهار الإيميل
- `show_address` - إظهار العنوان
- `show_social_media` - إظهار وسائل التواصل الاجتماعي

## التحقق من نجاح العملية
بعد تشغيل migration، يجب أن تعمل صفحات جهات الاتصال وبطاقات الاتصال بدون أخطاء.

## ملاحظات مهمة
- تم تعديل الكود ليتعامل مع الحقول غير الموجودة
- استخدام `isset()` للتحقق من وجود الحقول
- استخدام `Schema::hasColumn()` للتحقق من وجود الأعمدة في قاعدة البيانات
