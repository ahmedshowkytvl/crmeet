# دليل Zoho Integration

## نظرة عامة

نظام متكامل لربط المستخدمين في Laravel مع Zoho Desk وعرض إحصائيات أدائهم في حل التذاكر بشكل real-time.

## المميزات

- ✅ ربط اختياري - مش كل الموظفين لازم يكونوا على Zoho
- ✅ مزامنة تلقائية كل 10 دقائق
- ✅ حساب TPH (Tickets Per Hour)
- ✅ حساب متوسط وقت الرد
- ✅ نظام نقاط الأداء (Performance Score)
- ✅ Dashboard شخصي لكل موظف
- ✅ Leaderboard ولوحة المتصدرين
- ✅ تقارير شاملة للإدارة
- ✅ نظام الإنجازات (Achievements) - قادم قريباً
- ✅ API endpoints كاملة

## التثبيت والإعداد

### 1. إضافة المتغيرات في `.env`

```env
# Zoho API Credentials
ZOHO_CLIENT_ID=your_client_id_here
ZOHO_CLIENT_SECRET=your_client_secret_here
ZOHO_REFRESH_TOKEN=your_refresh_token_here
ZOHO_ORG_ID=your_org_id_here

# Zoho Sync Settings
ZOHO_SYNC_ENABLED=true
```

### 2. تشغيل الـ Migrations

```bash
php artisan migrate
```

سيتم إنشاء الجداول التالية:
- `users` - إضافة حقول Zoho
- `user_zoho_stats` - إحصائيات الموظفين
- `zoho_tickets_cache` - كاش التذاكر
- `user_achievements` - الإنجازات

### 3. ربط المستخدمين مع Zoho Agents

#### الطريقة الأولى: الربط التلقائي (Auto-map)

```bash
php artisan zoho:auto-map
```

سيتم ربط المستخدمين تلقائياً بناءً على تطابق البريد الإلكتروني.

#### الطريقة الثانية: الربط اليدوي

1. اذهب إلى صفحة الإدارة: `/zoho/admin`
2. اضغط على زر "تعديل" بجوار المستخدم
3. أدخل `Zoho Agent Name` (كما يظهر في `cf_closed_by`)
4. احفظ التغييرات

### 4. تفعيل الـ Scheduler

في production، تأكد من إضافة Cron Job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

الـ scheduler سيقوم بـ:
- مزامنة التذاكر كل 10 دقائق
- حساب الإحصائيات كل ساعة

## الاستخدام

### Commands

```bash
# مزامنة التذاكر
php artisan zoho:sync-tickets

# مزامنة تذاكر مستخدم معين
php artisan zoho:sync-tickets --user=1

# مزامنة تذاكر من تاريخ معين
php artisan zoho:sync-tickets --from=2024-01-01 --to=2024-01-31

# حساب الإحصائيات
php artisan zoho:calculate-stats

# حساب إحصائيات مستخدم معين
php artisan zoho:calculate-stats --user=1

# حساب إحصائيات يومية/أسبوعية/شهرية
php artisan zoho:calculate-stats --period=daily
php artisan zoho:calculate-stats --period=weekly
php artisan zoho:calculate-stats --period=monthly

# الربط التلقائي
php artisan zoho:auto-map
```

### Routes

#### Web Routes (للمستخدمين)

- `/zoho/my-stats` - Dashboard الشخصي
- `/zoho/reports` - التقارير (للمدراء)
- `/zoho/leaderboard` - لوحة المتصدرين
- `/zoho/admin` - لوحة الإدارة (للمطورين)

#### API Routes

```
GET  /api/zoho/user/{userId}/stats       - جلب إحصائيات مستخدم
GET  /api/zoho/user/{userId}/tickets     - جلب تذاكر مستخدم
GET  /api/zoho/leaderboard               - جلب المتصدرين
POST /api/zoho/sync/trigger              - تشغيل المزامنة يدوياً (admin only)
```

## كيفية عمل النظام

### 1. المزامنة

```
Zoho Desk API -> ZohoSyncService -> ZohoTicketCache (Database)
```

- يجلب التذاكر من Zoho API
- يستثني التذاكر `Auto Close`
- يربط كل تذكرة بالمستخدم المقابل حسب `cf_closed_by`
- يخزن البيانات في جدول `zoho_tickets_cache`

### 2. حساب الإحصائيات

```
ZohoTicketCache -> ZohoStatsService -> UserZohoStat (Database)
```

- يحسب عدد التذاكر المغلقة
- يحسب متوسط وقت الرد
- يحسب TPH (Tickets Per Hour) من الـ threads
- يحسب نقاط الأداء (Performance Score)
- يخزن النتائج في `user_zoho_stats`

### 3. حساب TPH

```php
TPH = 60 / (متوسط الدقائق بين كل thread outgoing والتالي)
```

الخطوات:
1. جلب كل threads للتذكرة
2. فلترة الـ `outgoing` threads فقط
3. حساب الوقت بين كل thread والذي يليه
4. حساب المتوسط
5. تحويله لـ tickets/hour

### 4. حساب Performance Score

الوزن الافتراضي (يمكن تغييره في `config/zoho.php`):

- **40%** - عدد التذاكر (كلما أكثر كلما أفضل)
- **40%** - سرعة الرد (كلما أقل كلما أفضل)
- **20%** - TPH (كلما أعلى كلما أفضل)

النتيجة النهائية من 0 إلى 100.

## الـ Permissions المطلوبة

تأكد من إضافة الـ permissions التالية للأدوار المناسبة:

- `view-zoho-reports` - للمدراء لعرض التقارير
- `manage-zoho` - للمطورين/الإدارة لإدارة الربط

يمكن إضافتهم في جدول `permissions` أو عبر seeder.

## الاختبار

### اختبار الاتصال بـ Zoho

```php
use App\Services\ZohoApiClient;

$client = new ZohoApiClient();
$isConnected = $client->testConnection();
```

### اختبار المزامنة

```bash
# مزامنة 10 تذاكر فقط للاختبار
php artisan zoho:sync-tickets --limit=10
```

### اختبار الإحصائيات

```bash
# حساب إحصائيات اليوم لمستخدم معين
php artisan zoho:calculate-stats --user=1 --period=daily
```

## الأخطاء الشائعة وحلولها

### 1. "Failed to refresh Zoho access token"

**السبب**: بيانات الاتصال غير صحيحة أو منتهية

**الحل**:
- تحقق من `ZOHO_CLIENT_ID`, `ZOHO_CLIENT_SECRET`, `ZOHO_REFRESH_TOKEN`
- تأكد أن الـ refresh token ما زال صالحاً
- جدد الـ token من Zoho Developer Console

### 2. "No Zoho-enabled users to sync"

**السبب**: لا يوجد مستخدمين مربوطين مع Zoho

**الحل**:
```bash
php artisan zoho:auto-map
```
أو ربط يدوي من `/zoho/admin`

### 3. "User not found or Zoho not enabled"

**السبب**: المستخدم غير مفعّل على Zoho

**الحل**:
- اذهب لـ `/zoho/admin`
- تأكد من `is_zoho_enabled = true`
- تأكد من وجود `zoho_agent_name`

## التطوير المستقبلي

- [ ] نظام Achievements كامل
- [ ] إشعارات عند كسب Achievement
- [ ] Export التقارير لـ Excel/PDF
- [ ] Dashboard للمدير المباشر
- [ ] مقارنة الأداء بين الأقسام
- [ ] Gamification (Badges, Levels)
- [ ] Integration مع نظام المكافآت

## الدعم

للمساعدة أو الاستفسارات، تواصل مع فريق التطوير.

