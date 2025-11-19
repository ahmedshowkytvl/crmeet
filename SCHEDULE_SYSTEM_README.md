# نظام الجدولة والتقويم - Schedule System

## نظرة عامة

نظام جدولة شامل يوفر للمستخدمين إدارة تقويماتهم الشخصية، إنشاء الأحداث والاجتماعات، وحجز غرف الاجتماعات. يدعم النظام أحداث متكررة، مناطق زمنية، ودمج مع التقويمات الخارجية.

## المميزات

### ✅ المميزات الأساسية

- **تقويم شخصي**: كل مستخدم لديه تقويمه الخاص
- **إنشاء وإدارة الأحداث**: إنشاء، تعديل، وحذف الأحداث والاجتماعات
- **حجز غرف الاجتماعات**: نظام شامل لإدارة غرف الاجتماعات وحجزها
- **منع الحجز المزدوج**: التأكد من عدم ازدحام الغرف
- **عروض متعددة**: عرض شهري، أسبوعي، يومي، وقائمة
- **أحداث متكررة**: دعم الأحداث اليومية، الأسبوعية، الشهرية، والسنوية
- **دعم المناطق الزمنية**: إدارة الأحداث بمناطق زمنية مختلفة
- **إدارة المشاركين**: دعوة مستخدمين متعددين للاجتماعات
- **حالة RSVP**: تتبع قبول/رفض الدعوات
- **التصريح والصلاحيات**: نظام أذونات محكم

### ✅ المميزات الإضافية (اختيارية)

- دعم Google Calendar / Outlook Sync (جاهز للتنفيذ)
- إشعارات وتذكيرات عبر البريد الإلكتروني (جاهز للتنفيذ)

## هيكل قاعدة البيانات

### جدول `schedule_events`

```sql
- id: Primary Key
- title: عنوان الحدث
- description: وصف الحدث
- start_time: وقت البداية
- end_time: وقت النهاية
- timezone: المنطقة الزمنية
- location: المكان
- event_type: نوع الحدث (meeting, event, reminder, task)
- status: الحالة (scheduled, confirmed, cancelled, completed)
- priority: الأولوية (low, medium, high)
- is_recurring: هل الحدث متكرر
- recurring_pattern: نمط التكرار (daily, weekly, monthly, yearly, custom)
- recurring_rules: قواعد التكرار (JSON)
- recurring_end_date: تاريخ انتهاء التكرار
- parent_event_id: معرف الحدث الأصلي (للأحداث المتكررة)
- meeting_room_id: معرف غرفة الاجتماع
- user_id: معرف المستخدم (منشئ الحدث)
- color: لون الحدث في التقويم
- reminders: التذكيرات (JSON)
- external_calendar_id: معرف التقويم الخارجي
- external_calendar_type: نوع التقويم الخارجي (google, outlook)
- last_synced_at: آخر وقت مزامنة
- created_at, updated_at, deleted_at
```

### جدول `meeting_rooms`

```sql
- id: Primary Key
- name: اسم الغرفة
- name_ar: اسم الغرفة بالعربية
- description: الوصف
- description_ar: الوصف بالعربية
- capacity: السعة
- location: الموقع
- location_ar: الموقع بالعربية
- amenities: المرافق (JSON)
- is_available: هل الغرفة متاحة
- availability_schedule: جدول الأوقات المتاحة (JSON)
- image: صورة الغرفة
- hourly_rate: السعر بالساعة
- created_by: منشئ الغرفة
- created_at, updated_at, deleted_at
```

### جدول `event_user` (Pivot Table)

```sql
- id: Primary Key
- event_id: معرف الحدث
- user_id: معرف المستخدم
- role: الدور (attendee, organizer, optional)
- rsvp_status: حالة RSVP (pending, accepted, declined, tentative)
- responded_at: وقت الرد
- response_note: ملاحظة الرد
- created_at, updated_at
```

## التثبيت والإعداد

### 1. تشغيل Migrations

```bash
php artisan migrate
```

### 2. تثبيت Dependencies

```bash
npm install
npm run build
```

### 3. إعداد الصلاحيات

أضف الصلاحيات التالية في قاعدة البيانات:

- `manage-events`: إدارة الأحداث
- `manage-meeting-rooms`: إدارة غرف الاجتماعات

### 4. إعداد Authentication

تأكد من أن النظام يستخدم Laravel Sanctum للـ API authentication. أضف token في meta tag أو localStorage:

```html
<meta name="api-token" content="{{ auth()->user()->createToken('api')->plainTextToken }}">
```

## API Endpoints

### Events Endpoints

#### الحصول على الأحداث
```
GET /api/schedule/events
Query Parameters:
  - start: تاريخ البداية (ISO 8601)
  - end: تاريخ النهاية (ISO 8601)
  - event_type: نوع الحدث
  - status: الحالة
```

#### إنشاء حدث جديد
```
POST /api/schedule/events
Body:
{
  "title": "عنوان الحدث",
  "description": "الوصف",
  "start_time": "2024-01-01T10:00:00Z",
  "end_time": "2024-01-01T11:00:00Z",
  "timezone": "Asia/Riyadh",
  "location": "المكان",
  "event_type": "meeting",
  "priority": "high",
  "color": "#3788d8",
  "meeting_room_id": 1,
  "attendee_ids": [1, 2, 3],
  "is_recurring": true,
  "recurring_pattern": "weekly",
  "recurring_end_date": "2024-12-31",
  "reminders": [{"type": "email", "minutes": 15}]
}
```

#### تحديث حدث
```
PUT /api/schedule/events/{id}
Body: (نفس body الإنشاء)
```

#### حذف حدث
```
DELETE /api/schedule/events/{id}
```

#### RSVP لحدث
```
POST /api/schedule/events/{id}/rsvp
Body:
{
  "rsvp_status": "accepted",
  "response_note": "ملاحظة"
}
```

### Meeting Rooms Endpoints

#### الحصول على غرف الاجتماعات
```
GET /api/schedule/meeting-rooms
Query Parameters:
  - available: true/false
  - location: الموقع
  - min_capacity: الحد الأدنى للسعة
```

#### الحصول على الغرف المتاحة
```
GET /api/schedule/meeting-rooms/available
Query Parameters:
  - start_time: وقت البداية
  - end_time: وقت النهاية
  - capacity: السعة
```

#### الحصول على الأوقات المتاحة لغرفة
```
GET /api/schedule/meeting-rooms/{id}/available-time-slots
Query Parameters:
  - date: التاريخ
  - duration: المدة بالدقائق
```

#### إنشاء غرفة جديدة
```
POST /api/schedule/meeting-rooms
Body:
{
  "name": "اسم الغرفة",
  "name_ar": "اسم الغرفة بالعربية",
  "description": "الوصف",
  "capacity": 10,
  "location": "الموقع",
  "amenities": ["projector", "whiteboard"],
  "is_available": true,
  "hourly_rate": 100.00
}
```

#### تحديث غرفة
```
PUT /api/schedule/meeting-rooms/{id}
Body: (نفس body الإنشاء)
```

#### حذف غرفة
```
DELETE /api/schedule/meeting-rooms/{id}
```

## استخدام الواجهة

### الوصول للتقويم

```
/schedule
```

### إنشاء حدث جديد

1. انقر على التاريخ المطلوب في التقويم
2. أو انقر على زر "إنشاء حدث جديد"
3. املأ البيانات المطلوبة
4. اختر غرفة الاجتماع (اختياري)
5. اختر المشاركين (اختياري)
6. اضغط "إنشاء"

### حجز غرفة اجتماع

1. انقر على "حجز غرفة اجتماع"
2. اختر التاريخ والوقت
3. اختر الغرفة المتاحة
4. املأ بيانات الاجتماع
5. اضغط "حجز"

### إدارة الأحداث

- **عرض التفاصيل**: انقر على الحدث في التقويم
- **تعديل**: انقر على "تعديل" في نافذة التفاصيل
- **حذف**: انقر على "حذف" في نافذة التفاصيل
- **سحب وإفلات**: اسحب الحدث لتغيير الوقت
- **تغيير الحجم**: اسحب حواف الحدث لتغيير المدة

## أمثلة الاستخدام

### مثال: إنشاء حدث متكرر أسبوعي

```javascript
const eventData = {
    title: "اجتماع أسبوعي",
    description: "اجتماع الفريق الأسبوعي",
    start_time: "2024-01-01T10:00:00Z",
    end_time: "2024-01-01T11:00:00Z",
    event_type: "meeting",
    is_recurring: true,
    recurring_pattern: "weekly",
    recurring_end_date: "2024-12-31",
    attendee_ids: [1, 2, 3, 4],
    meeting_room_id: 1
};

const response = await axios.post('/api/schedule/events', eventData);
```

### مثال: التحقق من توفر غرفة

```javascript
const availableRooms = await axios.get('/api/schedule/meeting-rooms/available', {
    params: {
        start_time: '2024-01-01T10:00:00Z',
        end_time: '2024-01-01T11:00:00Z',
        capacity: 10
    }
});
```

### مثال: الحصول على الأوقات المتاحة

```javascript
const timeSlots = await axios.get('/api/schedule/meeting-rooms/1/available-time-slots', {
    params: {
        date: '2024-01-01',
        duration: 60
    }
});
```

## التخصيص والتطوير

### إضافة أنواع أحداث جديدة

عدل enum `event_type` في migration:

```php
$table->enum('event_type', ['meeting', 'event', 'reminder', 'task', 'custom']);
```

### إضافة مرافق جديدة للغرف

عدل حقل `amenities` في JSON:

```json
{
  "projector": true,
  "whiteboard": true,
  "video_conference": true,
  "wifi": true,
  "air_conditioning": true
}
```

### تخصيص الألوان

يمكن تخصيص ألوان الأحداث حسب النوع أو الأولوية في `ScheduleEventController`:

```php
'color' => $request->color ?? $this->getDefaultColor($request->event_type, $request->priority)
```

## الأمان والصلاحيات

### Policies

- **ScheduleEventPolicy**: يتحكم في صلاحيات الوصول للأحداث
  - المستخدمون يمكنهم رؤية الأحداث الخاصة بهم والأحداث المدعوين إليها
  - فقط مالك الحدث أو المنظم يمكنه التعديل
  - فقط مالك الحدث يمكنه الحذف

- **MeetingRoomPolicy**: يتحكم في صلاحيات إدارة الغرف
  - جميع المستخدمين يمكنهم رؤية الغرف
  - فقط المستخدمون ذوو الصلاحية `manage-meeting-rooms` يمكنهم إنشاء/تعديل/حذف الغرف

## الاختبار

### اختبار API

```bash
php artisan test --filter ScheduleEventTest
php artisan test --filter MeetingRoomTest
```

### اختبار الواجهة

1. افتح `/schedule`
2. جرب إنشاء حدث جديد
3. جرب حجز غرفة
4. جرب تعديل وحذف الأحداث

## استكشاف الأخطاء

### مشكلة: التقويم لا يظهر

- تأكد من تحميل FullCalendar CSS و JS
- تحقق من console للأخطاء
- تأكد من صحة API token

### مشكلة: لا يمكن حجز غرفة

- تحقق من أن الغرفة متاحة
- تحقق من عدم وجود تعارض في الوقت
- تحقق من الصلاحيات

### مشكلة: الأحداث المتكررة لا تظهر

- تأكد من أن `is_recurring` = true
- تحقق من `recurring_pattern` و `recurring_end_date`
- تأكد من تشغيل job لإنشاء الأحداث المتكررة (إن وجد)

## الدعم والتطوير المستقبلي

### الميزات المخطط لها

- [ ] دمج Google Calendar
- [ ] دمج Outlook Calendar
- [ ] إشعارات البريد الإلكتروني
- [ ] إشعارات Push
- [ ] تصدير الأحداث (ICS)
- [ ] استيراد الأحداث (ICS)
- [ ] تذكيرات SMS
- [ ] تقارير الجدولة
- [ ] تحليلات الاستخدام

## المساهمة

للمساهمة في تطوير النظام:

1. Fork المشروع
2. أنشئ branch للميزة الجديدة
3. Commit التغييرات
4. Push إلى branch
5. أنشئ Pull Request

## الترخيص

هذا المشروع مرخص تحت MIT License.

## الاتصال

للدعم أو الاستفسارات، يرجى الاتصال بفريق التطوير.

---

**آخر تحديث**: نوفمبر 2024
**الإصدار**: 1.0.0

