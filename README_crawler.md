# Laravel Error Crawler

سكريبت Python لفحص جميع الصفحات في موقع Laravel المحلي واكتشاف الأخطاء.

## المتطلبات

- Python 3.6 أو أحدث
- المكتبات المطلوبة في `requirements.txt`

## التثبيت

```bash
pip install -r requirements.txt
```

## الاستخدام

```bash
python laravel_error_crawler.py
```

## الميزات

- **الزحف التلقائي**: يفحص جميع الروابط داخل النطاق `http://127.0.0.1:8000/`
- **المصادقة**: يستخدم الكوكيز المحددة للوصول للصفحات المحمية
- **اكتشاف الأخطاء**: يكتشف أخطاء Laravel/PHP مثل:
  - ErrorException
  - Internal Server Error
  - Attempt to read property
  - Stack Trace
  - Fatal errors
  - Parse errors
  - Warnings و Notices

- **تقرير CSV**: يحفظ النتائج في ملف `laravel_error_report.csv`
- **تتبع التقدم**: يطبع حالة كل صفحة أثناء الفحص

## ملف النتائج

الملف `laravel_error_report.csv` يحتوي على الأعمدة التالية:
- **URL**: رابط الصفحة التي تحتوي على خطأ
- **Status Code**: كود حالة HTTP
- **Error Type**: نوع الخطأ المكتشف
- **Error Message**: رسالة الخطأ
- **Stack Trace Line**: أول سطر من Stack Trace إن وجد

## مثال على الاستخدام

```bash
$ python laravel_error_crawler.py

بدء فحص الموقع: http://127.0.0.1:8000/
==================================================
فحص http://127.0.0.1:8000/... OK
فحص http://127.0.0.1:8000/dashboard... OK
فحص http://127.0.0.1:8000/users... خطأ موجود!
فحص http://127.0.0.1:8000/assets... OK

تم حفظ النتائج في: laravel_error_report.csv

==================================================
ملخص الفحص:
عدد الصفحات المفحوصة: 25
عدد الأخطاء المكتشفة: 3
تم حفظ النتائج في: laravel_error_report.csv
==================================================
```

## ملاحظات

- السكريبت يتجنب الزحف للروابط الخارجية
- يتجاهل روابط JavaScript و mailto و tel
- يزيل التكرار في الروابط تلقائياً
- يتعامل مع الأخطاء بأمان ويستمر في الفحص حتى لو فشلت صفحة واحدة




