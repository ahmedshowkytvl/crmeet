# واجهة البحث المتقدم في التذاكر (Zoho Desk Advanced Search)

## نظرة عامة

تم إنشاء صفحة جديدة متكاملة للبحث المتقدم في تذاكر Zoho Desk تسمح بالبحث في التذاكر بطرق متعددة وفعالة.

## الملفات المضافة

### 1. Controller
**الملف:** `app/Http/Controllers/ZohoAdvancedSearchController.php`
- `index()` - عرض صفحة البحث
- `searchByText()` - البحث النصي
- `searchByCustomField()` - البحث بالحقل المخصص
- `searchByTimeRange()` - البحث بمجال زمني

### 2. View
**الملف:** `resources/views/zoho/advanced-search.blade.php`
- واجهة البحث الفعلية مع 3 أنواع بحث
- عرض النتائج في جدول

### 3. Routes
**الملف:** `routes/web.php`
- `GET /zoho/advanced-search` - صفحة البحث
- `POST /zoho/advanced-search/text` - API للبحث النصي
- `POST /zoho/advanced-search/custom-field` - API للبحث بالحقل المخصص
- `POST /zoho/advanced-search/time-range` - API للبحث بمجال زمني

### 4. Service (ZohoApiClient)
**الملف:** `app/Services/ZohoApiClient.php`
تم إضافة 3 دوال جديدة:
- `advancedSearchByText()` - البحث النصي
- `advancedSearchByCustomField()` - البحث بالحقل المخصص
- `advancedSearchByTimeRange()` - البحث بمجال زمني

## الميزات

### 1. البحث النصي
- البحث في العناوين، الأوصاف، وأرقام التذاكر
- يعمل على كافة التذاكر بدون حد أقصى
- البحث case-insensitive

**الاستخدام:**
```javascript
performSearch('/api/zoho/advanced-search/text', { 
    search_text: 'نص البحث' 
});
```

### 2. البحث بالحقل المخصص (CF_Closed_by)
- البحث عن التذاكر التي أُغلقت بواسطة موظف معين
- يعمل على جميع التذاكر بدون حد أقصى

**الاستخدام:**
```javascript
performSearch('/api/zoho/advanced-search/custom-field', { 
    cf_closed_by: 'اسم الموظف' 
});
```

### 3. البحث بمجال زمني
ثلاثة أزرار للبحث السريع:
- **اليوم:** جميع التذاكر المُنشأة اليوم
- **هذا الشهر:** جميع التذاكر المُنشأة في الشهر الحالي
- **هذا العام:** جميع التذاكر المُنشأة في العام الحالي

**الاستخدام:**
```javascript
performSearch('/api/zoho/advanced-search/time-range', { 
    period: 'day|month|year' 
});
```

## واجهة النتائج

### الأعمدة المعروضة
1. **رقم التذكرة** - رقم التذكرة في Zoho
2. **العنوان** - عنوان التذكرة
3. **القسم** - القسم المسؤول
4. **تاريخ الإنشاء** - تاريخ ووقت إنشاء التذكرة
5. **الحالة** - حالة التذكرة (Open, Closed, Pending, In Progress)
6. **القناة** - قناة التذكرة (Email, Phone, etc.)
7. **البريد الإلكتروني** - بريد المستخدم
8. **تم الإغلاق بواسطة** - الموظف الذي أغلقت التذكرة

## آلية العمل

### البحث النصي
```php
public function advancedSearchByText($searchQuery, $limit = 1000)
{
    // 1. جلب التذاكر على صفحات (100 تذكرة في كل مرة)
    // 2. فلترة النتائج بناءً على نص البحث
    // 3. رجوع بجميع التذاكر المتطابقة
}
```

### البحث بالحقل المخصص
```php
public function advancedSearchByCustomField($fieldName, $fieldValue, $limit = 1000)
{
    // 1. جلب التذاكر على صفحات
    // 2. فلترة النتائج بناءً على قيمة الحقل المخصص
    // 3. رجوع بجميع التذاكر المتطابقة
}
```

### البحث بمجال زمني
```php
public function advancedSearchByTimeRange($startDate, $endDate, $limit = 1000)
{
    // 1. تحديد النطاق الزمني
    // 2. جلب التذاكر التي تم إنشاؤها ضمن هذا النطاق
    // 3. رجوع بجميع التذاكر
}
```

## الاتصال بـ Zoho API

يستخدم النظام المدمج `ZohoApiClient` مع:
- **OAuth 2.0** للتصديق الآمن
- **Access Token** التلقائي من `refresh_token`
- **Cache** للتقليل من استهلاك API
- **Headers المطلوبة:**
  ```
  Authorization: Zoho-oauthtoken {ACCESS_TOKEN}
  orgId: {ORG_ID}
  ```

## معالجة الأخطاء

### حالات الخطأ:
1. **لا يوجد نتائج:** رسالة واضحة للمستخدم
2. **خطأ الاتصال:** سجل في logs + رسالة للمستخدم
3. **خطأ تفويض:** تحديث تلقائي للـ token

### مثال على معالجة الأخطاء:
```javascript
.catch(error => {
    console.error('Error:', error);
    showError('حدث خطأ أثناء الاتصال بالخادم');
});
```

## الحدود والقيود

### API Limits
- الحد الأقصى: 100 تذكرة لكل طلب
- النظام يجلب على صفحات تلقائياً
- معدل البحث: يعتمد على سرعة API

### Performance
- البحث على آلاف التذاكر قد يستغرق وقتاً
- يتم عرض مؤشر تحميل أثناء البحث
- النتائج تُعرض فور توفرها

## الوصول إلى الصفحة

### عبر الـ Sidebar
تمت إضافة رابط "بحث متقدم في Zoho" في قسم Work في الـ Sidebar

### عبر الـ URL
```
http://localhost:8000/zoho/advanced-search
```

## ملاحظات أمنية

- ✅ جميع الـ Routes محمية بـ `auth` middleware
- ✅ CSRF protection مفعل
- ✅ Sensitive data يُسجل في logs فقط
- ✅ لا يتم عرض tokens في الواجهة

## اختبار الصفحة

### 1. اختبار البحث النصي
```bash
curl -X POST http://localhost:8000/zoho/advanced-search/text \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {token}" \
  -d '{"search_text": "test"}'
```

### 2. اختبار البحث بالحقل المخصص
```bash
curl -X POST http://localhost:8000/zoho/advanced-search/custom-field \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {token}" \
  -d '{"cf_closed_by": "اسم الموظف"}'
```

### 3. اختبار البحث الزمني
```bash
curl -X POST http://localhost:8000/zoho/advanced-search/time-range \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: {token}" \
  -d '{"period": "day"}'
```

## التحسينات المستقبلية

- [ ] إضافة تصدير النتائج إلى Excel
- [ ] إضافة فلترة إضافية (Status, Priority)
- [ ] إضافة Advanced Filtering
- [ ] إضافة Saved Searches
- [ ] إضافة Pagination للنتائج الكبيرة
- [ ] إضافة Charts للإحصائيات

## الدعم

للأسئلة أو المشاكل، يرجى التواصل مع فريق التطوير.

---
تم إنشاء هذه الصفحة بواسطة: AI Assistant
تاريخ الإنشاء: 2025-01-16

