# نظام التكامل مع Snipe-IT

## نظرة عامة

نظام التكامل مع Snipe-IT يوفر مزامنة شاملة بين نظام إدارة الأصول المحلي ونظام Snipe-IT لإدارة الأصول. يتيح النظام مزامنة الأصول والمستخدمين والفئات والمواقع والنماذج والموردين بشكل تلقائي أو يدوي.

## المميزات الرئيسية

### 🔄 المزامنة التلقائية
- مزامنة دورية للأصول والمستخدمين والفئات
- مزامنة تدريجية أو كاملة حسب الحاجة
- جدولة المزامنة التلقائية
- تسجيل مفصل لعمليات المزامنة

### 🔌 واجهة برمجة التطبيقات (API)
- تكامل كامل مع Snipe-IT API
- دعم جميع عمليات CRUD للأصول
- اختبار الاتصال والتحقق من الصحة
- معالجة الأخطاء وإعادة المحاولة

### 📊 لوحة تحكم شاملة
- إحصائيات المزامنة في الوقت الفعلي
- عرض آخر عمليات المزامنة
- إدارة إعدادات التكامل
- مراقبة حالة الاتصال

### ⚙️ إعدادات مرنة
- تكوين رابط API ورمز API
- تخصيص خيارات المزامنة
- إعدادات المزامنة التلقائية
- تكوين Webhook للاستقبال

## التثبيت والإعداد

### 1. تشغيل Migrations

```bash
php artisan migrate
```

### 2. إعداد متغيرات البيئة

أضف المتغيرات التالية إلى ملف `.env`:

```env
# Snipe-IT API Configuration
SNIPEIT_API_URL=http://127.0.0.1
SNIPEIT_API_TOKEN=your_api_token_here
SNIPEIT_TIMEOUT=30

# Auto Sync Configuration
SNIPEIT_AUTO_SYNC_ENABLED=false
SNIPEIT_SYNC_INTERVAL=60

# Sync Options
SNIPEIT_SYNC_ASSETS=true
SNIPEIT_SYNC_USERS=true
SNIPEIT_SYNC_CATEGORIES=true
SNIPEIT_SYNC_LOCATIONS=true
SNIPEIT_SYNC_MODELS=true
SNIPEIT_SYNC_SUPPLIERS=true

# Webhook Configuration
SNIPEIT_WEBHOOK_ENABLED=false
SNIPEIT_WEBHOOK_URL=
SNIPEIT_WEBHOOK_SECRET=

# Cache Configuration
SNIPEIT_CACHE_TTL=3600
SNIPEIT_CACHE_PREFIX=snipeit_

# Pagination Configuration
SNIPEIT_PER_PAGE=100
SNIPEIT_MAX_PER_PAGE=500

# Error Handling Configuration
SNIPEIT_RETRY_ATTEMPTS=3
SNIPEIT_RETRY_DELAY=1000

# Logging Configuration
SNIPEIT_LOG_LEVEL=info
SNIPEIT_LOG_CHANNEL=daily
```

### 3. إعداد الصلاحيات

تأكد من وجود صلاحية `manage-assets` للمستخدمين الذين يحتاجون للوصول لنظام التكامل.

## الاستخدام

### الوصول للنظام

1. انتقل إلى **التكامل مع Snipe-IT** من الشريط الجانبي
2. ستظهر لوحة التحكم الرئيسية مع الإحصائيات

### اختبار الاتصال

1. انقر على **اختبار الاتصال**
2. تأكد من صحة الإعدادات قبل المتابعة

### المزامنة اليدوية

#### مزامنة الأصول
1. انقر على **مزامنة الأصول**
2. اختر نوع المزامنة:
   - **مزامنة تدريجية**: مزامنة الأصول المحدثة فقط
   - **مزامنة كاملة**: مزامنة جميع الأصول
3. (اختياري) أدخل معرفات أصول محددة
4. انقر على **تأكيد المزامنة**

#### مزامنة المستخدمين
1. انقر على **مزامنة المستخدمين**
2. سيتم مزامنة جميع المستخدمين من Snipe-IT

#### مزامنة الفئات
1. انقر على **مزامنة الفئات**
2. سيتم مزامنة جميع الفئات من Snipe-IT

### إدارة الإعدادات

1. انتقل إلى **إعدادات التكامل مع Snipe-IT**
2. قم بتعديل الإعدادات حسب الحاجة:
   - رابط API ورمز API
   - خيارات المزامنة التلقائية
   - خيارات المزامنة
   - إعدادات Webhook
3. انقر على **حفظ الإعدادات**

## المزامنة التلقائية

### إعداد المزامنة التلقائية

1. في إعدادات التكامل، فعّل **المزامنة التلقائية**
2. حدد فترة المزامنة (بالدقائق)
3. اختر ما تريد مزامنته تلقائياً

### تشغيل المزامنة التلقائية

```bash
# مزامنة تدريجية لجميع العناصر
php artisan snipeit:sync

# مزامنة كاملة للأصول فقط
php artisan snipeit:sync --type=full --assets

# مزامنة المستخدمين والفئات فقط
php artisan snipeit:sync --users --categories
```

### جدولة المزامنة التلقائية

أضف المهمة التالية إلى `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // مزامنة كل ساعة
    $schedule->command('snipeit:sync')
             ->hourly()
             ->when(function () {
                 return config('snipeit.auto_sync_enabled', false);
             });
}
```

## واجهة برمجة التطبيقات (API)

### اختبار الاتصال

```http
POST /api/snipe-it/test-connection
```

### مزامنة الأصول

```http
POST /api/snipe-it/sync/assets
Content-Type: application/json

{
    "sync_type": "incremental",
    "asset_ids": [1, 2, 3]
}
```

### مزامنة المستخدمين

```http
POST /api/snipe-it/sync/users
```

### مزامنة الفئات

```http
POST /api/snipe-it/sync/categories
```

### جلب تفاصيل أصل

```http
GET /api/snipe-it/assets/{assetId}
```

### تحديث أصل

```http
PUT /api/snipe-it/assets/{assetId}
Content-Type: application/json

{
    "name": "Updated Asset Name",
    "asset_tag": "TAG001",
    "model_id": 1,
    "status_id": 1
}
```

### إنشاء أصل جديد

```http
POST /api/snipe-it/assets
Content-Type: application/json

{
    "name": "New Asset",
    "asset_tag": "TAG002",
    "model_id": 1,
    "status_id": 1
}
```

### حذف أصل

```http
DELETE /api/snipe-it/assets/{assetId}
```

### جلب الإحصائيات

```http
GET /api/snipe-it/stats
```

### جلب سجل المزامنة

```http
GET /api/snipe-it/sync-logs?page=1&per_page=15
```

## هيكل قاعدة البيانات

### جدول `snipeit_sync_logs`

```sql
CREATE TABLE snipeit_sync_logs (
    id BIGINT PRIMARY KEY,
    type VARCHAR(255),
    sync_type VARCHAR(255),
    status ENUM('running', 'completed', 'failed'),
    started_at TIMESTAMP,
    completed_at TIMESTAMP NULL,
    user_id BIGINT NULL,
    synced_count INT DEFAULT 0,
    created_count INT DEFAULT 0,
    updated_count INT DEFAULT 0,
    errors JSON NULL,
    duration INT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### تحديث جدول `assets`

```sql
ALTER TABLE assets ADD COLUMN snipeit_id BIGINT UNIQUE NULL;
ALTER TABLE assets ADD COLUMN asset_tag VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN serial VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN model_id BIGINT NULL;
ALTER TABLE assets ADD COLUMN model_name VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN status_id BIGINT NULL;
ALTER TABLE assets ADD COLUMN status_name VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN assigned_to BIGINT NULL;
ALTER TABLE assets ADD COLUMN location_id BIGINT NULL;
ALTER TABLE assets ADD COLUMN location_name VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN notes TEXT NULL;
ALTER TABLE assets ADD COLUMN purchase_date DATE NULL;
ALTER TABLE assets ADD COLUMN purchase_cost DECIMAL(10,2) NULL;
ALTER TABLE assets ADD COLUMN supplier_id BIGINT NULL;
ALTER TABLE assets ADD COLUMN supplier_name VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN order_number VARCHAR(255) NULL;
ALTER TABLE assets ADD COLUMN warranty_months INT NULL;
ALTER TABLE assets ADD COLUMN requestable BOOLEAN DEFAULT FALSE;
ALTER TABLE assets ADD COLUMN last_checkout TIMESTAMP NULL;
ALTER TABLE assets ADD COLUMN last_checkin TIMESTAMP NULL;
ALTER TABLE assets ADD COLUMN expected_checkin TIMESTAMP NULL;
ALTER TABLE assets ADD COLUMN snipeit_updated_at TIMESTAMP NULL;
```

### تحديث جدول `users`

```sql
ALTER TABLE users ADD COLUMN snipeit_id BIGINT UNIQUE NULL;
ALTER TABLE users ADD COLUMN employee_num VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN notes TEXT NULL;
ALTER TABLE users ADD COLUMN activated BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN snipeit_updated_at TIMESTAMP NULL;
```

## استكشاف الأخطاء

### مشاكل الاتصال

1. **خطأ في الاتصال**: تحقق من صحة رابط API ورمز API
2. **انتهاء مهلة الاتصال**: زيادة قيمة `SNIPEIT_TIMEOUT`
3. **مشاكل الشبكة**: تحقق من إعدادات الجدار الناري

### مشاكل المزامنة

1. **فشل في المزامنة**: تحقق من سجل الأخطاء
2. **بيانات مفقودة**: تأكد من وجود البيانات في Snipe-IT
3. **تضارب في البيانات**: تحقق من معرفات Snipe-IT الفريدة

### مشاكل الأداء

1. **بطء في المزامنة**: قلل من قيمة `SNIPEIT_PER_PAGE`
2. **استهلاك ذاكرة عالي**: فعّل المزامنة التدريجية
3. **مشاكل قاعدة البيانات**: تحقق من الفهارس

## الأمان

### حماية API Token

- احتفظ برمز API في متغيرات البيئة
- لا تشارك رمز API مع أشخاص غير مخولين
- غيّر رمز API بانتظام

### التحكم في الوصول

- استخدم صلاحيات مناسبة للمستخدمين
- راقب عمليات المزامنة
- احتفظ بسجل مفصل للعمليات

## الصيانة

### تنظيف السجلات القديمة

```bash
# حذف سجلات المزامنة الأقدم من 30 يوم
php artisan snipeit:cleanup-logs --days=30
```

### مراقبة الأداء

- راقب حجم قاعدة البيانات
- تحقق من أداء المزامنة
- راقب استخدام الذاكرة

### النسخ الاحتياطي

- احتفظ بنسخة احتياطية من قاعدة البيانات
- احتفظ بنسخة احتياطية من إعدادات التكامل
- اختبر استعادة البيانات بانتظام

## الدعم والمساعدة

### الوثائق

- راجع وثائق Snipe-IT API
- تحقق من سجل الأخطاء في Laravel
- راجع سجل المزامنة في النظام

### التواصل

- تواصل مع فريق التطوير للمساعدة
- قدم تقارير مفصلة عن المشاكل
- شارك سجلات الأخطاء عند الحاجة

## التطوير المستقبلي

### المميزات المخططة

- [ ] مزامنة ثنائية الاتجاه
- [ ] دعم المزيد من أنواع الأصول
- [ ] تحسينات في الأداء
- [ ] واجهة مستخدم محسنة
- [ ] تقارير مفصلة
- [ ] تنبيهات ذكية

### المساهمة

- قدم اقتراحات للمميزات الجديدة
- شارك في تطوير النظام
- ساعد في تحسين الوثائق
- اختبر النظام وقدم ملاحظاتك

---

**ملاحظة**: هذا النظام مصمم للعمل مع Snipe-IT v6.0 أو أحدث. تأكد من تحديث Snipe-IT للحصول على أفضل تجربة.
