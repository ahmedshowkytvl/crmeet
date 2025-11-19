# دليل التثبيت السريع - نظام الجدولة

## الخطوات الأساسية

### 1. تشغيل Migrations

```bash
cd /root/CRM
php artisan migrate
```

سيتم إنشاء الجداول التالية:
- `schedule_events`
- `meeting_rooms`
- `event_user`

### 2. تثبيت Dependencies

```bash
npm install
npm run build
```

### 3. إعداد Authentication Token

أضف token API في layout الرئيسي. افتح `resources/views/layouts/app.blade.php` وأضف:

```html
<meta name="api-token" content="{{ auth()->user() ? auth()->user()->createToken('api')->plainTextToken : '' }}">
```

أو استخدم Sanctum token من session:

```php
@auth
    <meta name="api-token" content="{{ session('api_token') ?? '' }}">
@endauth
```

### 4. الوصول للتقويم

افتح المتصفح وانتقل إلى:
```
http://your-domain/schedule
```

## إعداد الصلاحيات (اختياري)

إذا كنت تستخدم نظام الصلاحيات، أضف الصلاحيات التالية:

```sql
INSERT INTO permissions (name, slug, description) VALUES
('إدارة الأحداث', 'manage-events', 'القدرة على إدارة جميع الأحداث'),
('إدارة غرف الاجتماعات', 'manage-meeting-rooms', 'القدرة على إدارة غرف الاجتماعات');
```

## إنشاء غرفة اجتماع تجريبية

يمكنك إنشاء غرفة اجتماع عبر API أو مباشرة في قاعدة البيانات:

```sql
INSERT INTO meeting_rooms (name, name_ar, capacity, location, is_available, created_at, updated_at) VALUES
('قاعة الاجتماعات الرئيسية', 'قاعة الاجتماعات الرئيسية', 20, 'الطابق الأول', 1, NOW(), NOW());
```

## اختبار النظام

### 1. اختبار إنشاء حدث

```bash
curl -X POST http://your-domain/api/schedule/events \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "اختبار الحدث",
    "start_time": "2024-01-01T10:00:00Z",
    "end_time": "2024-01-01T11:00:00Z"
  }'
```

### 2. اختبار الحصول على الأحداث

```bash
curl -X GET "http://your-domain/api/schedule/events?start=2024-01-01&end=2024-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. اختبار الحصول على الغرف المتاحة

```bash
curl -X GET "http://your-domain/api/schedule/meeting-rooms/available?start_time=2024-01-01T10:00:00Z&end_time=2024-01-01T11:00:00Z" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## استكشاف الأخطاء

### المشكلة: خطأ 401 Unauthorized

**الحل**: تأكد من:
1. تسجيل الدخول
2. وجود API token صحيح
3. Token لم ينته صلاحيته

### المشكلة: التقويم فارغ

**الحل**: 
1. تحقق من console المتصفح للأخطاء
2. تأكد من تحميل FullCalendar CSS و JS
3. تحقق من صحة API endpoint

### المشكلة: لا يمكن إنشاء أحداث

**الحل**:
1. تحقق من صحة البيانات المرسلة
2. تحقق من validation rules
3. تحقق من logs Laravel

### المشكلة: لا تظهر غرف الاجتماعات

**الحل**:
1. تأكد من وجود غرف في قاعدة البيانات
2. تحقق من أن `is_available = 1`
3. تحقق من API response

## الخطوات التالية

1. **تخصيص الألوان**: عدل ألوان الأحداث في `ScheduleEventController`
2. **إضافة أنواع أحداث**: عدل enum `event_type` في migration
3. **إضافة مرافق للغرف**: عدل حقل `amenities` في JSON
4. **تخصيص التذكيرات**: أضف نظام إشعارات البريد الإلكتروني

## الدعم

للحصول على المساعدة، راجع ملف `SCHEDULE_SYSTEM_README.md` للتوثيق الكامل.

---

**ملاحظة**: تأكد من عمل backup لقاعدة البيانات قبل تشغيل migrations.

