# Zoho API - البحث بالتاريخ والموظف

## 🎯 **المشكلة:**
عايز نجيب كل التذاكر اللي `closed_by` فيها `Yaraa Khaled` من تاريخ معين لحد دلوقتي

## ✅ **الحلول المضافة:**

### 1. **دوال جديدة في ZohoApiClient:**

#### `getTicketsByDateRangeAndAgent($agentName, $fromDate, $toDate, $limit)`
```php
// جلب تذاكر موظف معين في فترة زمنية
$response = $apiClient->getTicketsByDateRangeAndAgent(
    'Yaraa Khaled',
    '2024-01-01',  // من تاريخ
    '2024-12-31', // إلى تاريخ
    1000          // حد أقصى للتذاكر
);
```

#### `getTicketsByCustomField($fieldName, $fieldValue, $fromDate, $toDate, $limit)`
```php
// جلب تذاكر حسب custom field معين
$response = $apiClient->getTicketsByCustomField(
    'cf_closed_by',  // اسم الحقل
    'Yaraa Khaled',  // قيمة الحقل
    '2024-01-01',    // من تاريخ
    '2024-12-31',    // إلى تاريخ
    1000            // حد أقصى للتذاكر
);
```

### 2. **دوال جديدة في ZohoSyncService:**

#### `syncTicketsByCustomField($fieldName, $fieldValue, $fromDate, $toDate)`
```php
// تزامن تذاكر حسب custom field
$result = $syncService->syncTicketsByCustomField(
    'cf_closed_by',
    'Yaraa Khaled',
    '2024-01-01',
    '2024-12-31'
);
```

### 3. **Command جديد: `ZohoSyncByAgent`**

```bash
# جلب تذاكر Yaraa Khaled من آخر 30 يوم
php artisan zoho:sync-by-agent "Yaraa Khaled" --from=2024-01-01 --to=2024-12-31

# جلب تذاكر Yaraa Khaled من آخر 7 أيام
php artisan zoho:sync-by-agent "Yaraa Khaled" --from=2024-12-01 --to=2024-12-31

# جلب تذاكر Yaraa Khaled بدون تحديد تاريخ (كل التذاكر)
php artisan zoho:sync-by-agent "Yaraa Khaled"

# جلب تذاكر موظف آخر
php artisan zoho:sync-by-agent "Nada Magdy" --from=2024-01-01 --to=2024-12-31
```

## 🚀 **طرق الاستخدام:**

### **الطريقة 1: Command Line**
```bash
# جلب تذاكر Yaraa Khaled من آخر 30 يوم
php artisan zoho:sync-by-agent "Yaraa Khaled" --from=2024-01-01 --to=2024-12-31

# جلب تذاكر Yaraa Khaled من آخر 7 أيام
php artisan zoho:sync-by-agent "Yaraa Khaled" --from=2024-12-01 --to=2024-12-31

# جلب تذاكر Yaraa Khaled بدون تحديد تاريخ (كل التذاكر)
php artisan zoho:sync-by-agent "Yaraa Khaled"
```

### **الطريقة 2: Tinker**
```bash
php artisan tinker

>>> $apiClient = new \App\Services\ZohoApiClient();
>>> $response = $apiClient->getTicketsByDateRangeAndAgent('Yaraa Khaled', '2024-01-01', '2024-12-31', 100);
>>> $response['count']; // عدد التذاكر
>>> $response['data']; // بيانات التذاكر
```

### **الطريقة 3: في الكود**
```php
use App\Services\ZohoApiClient;
use App\Services\ZohoSyncService;

// جلب التذاكر مباشرة
$apiClient = new ZohoApiClient();
$tickets = $apiClient->getTicketsByDateRangeAndAgent(
    'Yaraa Khaled',
    '2024-01-01',
    '2024-12-31',
    1000
);

// أو تزامن التذاكر
$syncService = new ZohoSyncService($apiClient);
$result = $syncService->syncTicketsByCustomField(
    'cf_closed_by',
    'Yaraa Khaled',
    '2024-01-01',
    '2024-12-31'
);
```

## 📊 **النتائج المتوقعة:**

### **بعد تشغيل التزامن:**
```bash
php artisan zoho:sync-by-agent "Yaraa Khaled" --from=2024-01-01 --to=2024-12-31
```

**ستحصل على:**
```
🔄 Starting Zoho tickets sync for agent: Yaraa Khaled
📋 Search Parameters:
   Agent: Yaraa Khaled
   Field: cf_closed_by
   From Date: 2024-01-01
   To Date: 2024-12-31
   Limit: 1000

✅ Synced 150 tickets for cf_closed_by = Yaraa Khaled
📊 Synced: 150 tickets

📈 Statistics:
   Total Tickets: 150
   Closed Tickets: 120
   Open Tickets: 30
   Avg Response Time: 45.2 minutes
```

## 🔍 **فحص النتائج:**

```bash
php artisan tinker

# عدد تذاكر Yaraa Khaled
>>> \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->count();

# تذاكر Yaraa Khaled المغلقة
>>> \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->where('status', 'Closed')->count();

# آخر 10 تذاكر لـ Yaraa Khaled
>>> \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->orderBy('closed_at_zoho', 'desc')->limit(10)->get();
```

## ⚡ **المميزات:**

1. **البحث بالتاريخ:** تحديد فترة زمنية محددة
2. **البحث بالموظف:** جلب تذاكر موظف معين فقط
3. **البحث بـ Custom Field:** مرونة في البحث
4. **الإحصائيات:** عرض إحصائيات بعد التزامن
5. **الحد الأقصى:** تجنب جلب تذاكر كثيرة جداً

## 🎯 **الاستخدام الأمثل:**

```bash
# جلب تذاكر Yaraa Khaled من آخر 30 يوم
php artisan zoho:sync-by-agent "Yaraa Khaled" --from=2024-01-01 --to=2024-12-31

# ثم فحص النتائج
php artisan tinker
>>> \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->count();
```

**الآن يمكنك جلب تذاكر Yaraa Khaled بالتاريخ المحدد!** 🚀
