# Ticket Web Viewer - Filtering by cf_closed_by

تم تحديث `ticket_web_viewer.py` لدعم تصفية التذاكر حسب `cf_closed_by` مع إمكانيات إضافية.

## المميزات الجديدة

### 1. تصفية التذاكر حسب cf_closed_by
- تصفية التذاكر حسب من أغلقها
- بحث جزئي (partial search) - يمكن البحث بأجزاء من الاسم
- تصفية غير حساسة لحالة الأحرف (case-insensitive)

### 2. تصفية إضافية حسب الحالة
- تصفية التذاكر حسب الحالة (Open, Closed, Pending, etc.)
- مطابقة دقيقة للحالة

### 3. تحكم في عدد التذاكر
- إمكانية تحديد عدد التذاكر المراد جلبها
- الحد الافتراضي: 20 تذكرة

## كيفية الاستخدام

### 1. جلب جميع التذاكر (بدون تصفية)
```
GET /api/tickets
```

### 2. تصفية حسب cf_closed_by
```
GET /api/tickets?cf_closed_by=أحمد محمد
GET /api/tickets?cf_closed_by=Auto Close
GET /api/tickets?cf_closed_by=محمد
```

### 3. تصفية حسب الحالة
```
GET /api/tickets?status=Closed
GET /api/tickets?status=Open
```

### 4. تصفية مركبة
```
GET /api/tickets?cf_closed_by=أحمد&status=Closed&limit=50
```

### 5. الحصول على قائمة خيارات cf_closed_by
```
GET /api/filters/cf_closed_by
```

## أمثلة على الاستخدام

### مثال 1: جلب التذاكر المغلقة بواسطة شخص معين
```bash
curl "http://localhost:5000/api/tickets?cf_closed_by=أحمد محمد&status=Closed"
```

### مثال 2: جلب التذاكر المغلقة تلقائياً
```bash
curl "http://localhost:5000/api/tickets?cf_closed_by=Auto Close"
```

### مثال 3: جلب التذاكر المغلقة يدوياً (غير Auto Close)
```bash
curl "http://localhost:5000/api/tickets?status=Closed&limit=100"
```
ثم تصفية النتائج في الكود لاستبعاد Auto Close

### مثال 4: الحصول على قائمة الأشخاص الذين أغلقوا تذاكر
```bash
curl "http://localhost:5000/api/filters/cf_closed_by"
```

## استجابة API

### استجابة جلب التذاكر
```json
{
  "success": true,
  "tickets": [
    {
      "id": "123456789",
      "ticketNumber": "TKT-001",
      "subject": "مشكلة في النظام",
      "status": "Closed",
      "createdTime": "01/15/2024 10:30:00 AM",
      "closedTime": "01/15/2024 11:00:00 AM",
      "email": "customer@example.com",
      "cf_closed_by": "أحمد محمد",
      "threadCount": 3,
      "channel": "Email"
    }
  ],
  "count": 1,
  "total_fetched": 20,
  "filters_applied": {
    "cf_closed_by": "أحمد محمد",
    "status": "Closed",
    "limit": 20
  }
}
```

### استجابة خيارات cf_closed_by
```json
{
  "success": true,
  "cf_closed_by_options": [
    "أحمد محمد",
    "Auto Close",
    "فاطمة علي",
    "محمد حسن",
    "سارة أحمد"
  ],
  "count": 5
}
```

## استخدامات متقدمة

### 1. إنشاء واجهة تصفية
يمكن استخدام endpoint `/api/filters/cf_closed_by` لإنشاء قائمة منسدلة في الواجهة الأمامية.

### 2. تحليل الأداء
يمكن استخدام التصفية لتحليل أداء الموظفين:
- من أغلق أكثر التذاكر؟
- كم تذكرة أغلقها كل موظف؟
- مقارنة التذاكر المغلقة يدوياً مقابل تلقائياً

### 3. التقارير المخصصة
يمكن إنشاء تقارير مخصصة حسب:
- الموظف
- الفترة الزمنية
- نوع الإغلاق (يدوي/تلقائي)

## أمثلة على JavaScript

### جلب التذاكر مع تصفية
```javascript
// جلب التذاكر المغلقة بواسطة شخص معين
fetch('/api/tickets?cf_closed_by=أحمد محمد&status=Closed')
  .then(response => response.json())
  .then(data => {
    console.log(`Found ${data.count} tickets`);
    data.tickets.forEach(ticket => {
      console.log(`Ticket ${ticket.ticketNumber}: ${ticket.subject}`);
    });
  });

// جلب خيارات cf_closed_by
fetch('/api/filters/cf_closed_by')
  .then(response => response.json())
  .then(data => {
    console.log('Available cf_closed_by options:', data.cf_closed_by_options);
  });
```

### إنشاء واجهة تصفية
```javascript
// إنشاء قائمة منسدلة للتصفية
function createFilterDropdown() {
  fetch('/api/filters/cf_closed_by')
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById('cf_closed_by_filter');
      data.cf_closed_by_options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option;
        optionElement.textContent = option;
        select.appendChild(optionElement);
      });
    });
}

// تطبيق التصفية
function applyFilter() {
  const cfClosedBy = document.getElementById('cf_closed_by_filter').value;
  const status = document.getElementById('status_filter').value;
  const limit = document.getElementById('limit_input').value;
  
  const url = `/api/tickets?cf_closed_by=${cfClosedBy}&status=${status}&limit=${limit}`;
  
  fetch(url)
    .then(response => response.json())
    .then(data => {
      displayTickets(data.tickets);
    });
}
```

## ملاحظات مهمة

1. **البحث الجزئي**: التصفية تستخدم بحث جزئي، لذا يمكن البحث بـ "أحمد" للعثور على "أحمد محمد"

2. **غير حساس للأحرف**: البحث غير حساس لحالة الأحرف

3. **الأداء**: كلما زاد عدد التذاكر المطلوبة، كلما زاد وقت الاستجابة

4. **الحدود**: لا توجد حدود صارمة، لكن يُنصح بعدم تجاوز 200 تذكرة في الطلب الواحد

5. **التحديث**: البيانات تُحدث من Zoho Desk في الوقت الفعلي

## استكشاف الأخطاء

### لا توجد نتائج
- تأكد من صحة القيمة في `cf_closed_by`
- تحقق من وجود تذاكر بهذه القيمة
- جرب البحث الجزئي (جزء من الاسم)

### خطأ في الخادم
- تأكد من تشغيل الخادم
- تحقق من اتصال API
- راجع رسائل الخطأ في وحدة التحكم

---

**الآن يمكنك تصفية التذاكر بسهولة حسب من أغلقها!** 🎯
