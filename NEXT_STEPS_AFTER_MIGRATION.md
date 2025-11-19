# ✅ Migrations نجحت! الخطوات التالية

## 🎯 الخطوات المطلوبة

### 1. تأكد من بيانات Zoho في `.env`

افتح `.env` وتأكد من وجود:

```env
ZOHO_CLIENT_ID=1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT
ZOHO_CLIENT_SECRET=30624b06180b20ab5252fc8e6145ad175762a367a0
ZOHO_REFRESH_TOKEN=1000.52819ce62c5efadf103da41c39462664.026dbfb73e2747e9b0b09a714e0fa0ee
ZOHO_ORG_ID=786481962
ZOHO_SYNC_ENABLED=true
```

### 2. ربط المستخدمين (Auto-Map)

```bash
php artisan zoho:auto-map
```

سيربط المستخدمين تلقائياً بناءً على تطابق البريد الإلكتروني.

### 3. جلب التذاكر (أول مرة - اختبار)

```bash
# ابدأ بـ 50 تذكرة للتجربة
php artisan zoho:sync-tickets --limit=50
```

### 4. حساب الإحصائيات

```bash
# حساب إحصائيات الشهر الحالي
php artisan zoho:calculate-stats --period=monthly
```

### 5. تفعيل Scheduler (للمزامنة التلقائية)

**في Development:**
```bash
php artisan schedule:work
```

**في Production (crontab):**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🌐 الصفحات المتاحة

بعد تنفيذ الخطوات أعلاه، زور:

- **Dashboard الشخصي**: `http://localhost/zoho/my-stats`
- **لوحة الإدارة**: `http://localhost/zoho/admin`
- **التقارير**: `http://localhost/zoho/reports`
- **Leaderboard**: `http://localhost/zoho/leaderboard`

---

## 🔑 الـ Permissions (مهم!)

أضف الـ permissions في قاعدة البيانات:

```sql
INSERT INTO permissions (name, slug, created_at, updated_at) VALUES 
('View Zoho Reports', 'view-zoho-reports', NOW(), NOW()),
('Manage Zoho', 'manage-zoho', NOW(), NOW());
```

ثم اربطها بالأدوار المناسبة في جدول `role_permissions`.

---

## 📊 للتحقق من النتائج

بعد المزامنة:

```bash
# شوف عدد التذاكر المخزنة
php artisan tinker
>>> \App\Models\ZohoTicketCache::count()

# شوف المستخدمين المربوطين
>>> \App\Models\User::zohoEnabled()->count()

# شوف الإحصائيات المحسوبة
>>> \App\Models\UserZohoStat::count()
```

---

## 🚀 كل شيء جاهز!

بعد تنفيذ الخطوات، النظام سيعمل تلقائياً:
- ✅ مزامنة كل 10 دقائق
- ✅ حساب إحصائيات كل ساعة
- ✅ Dashboard جميل وسريع
- ✅ API endpoints جاهزة

**مبروك! 🎉**

