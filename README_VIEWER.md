# أداة مشاهدة تذاكر Zoho

أداة Python لعرض بيانات جدول `zoho_tickets_cache` من قاعدة بيانات PostgreSQL.

## المتطلبات

- Python 3.7 أو أحدث
- PostgreSQL
- psycopg2-binary

## التثبيت

```bash
pip install -r requirements.txt
```

## الاستخدام

```bash
python zoho_tickets_viewer.py
```

## المميزات

- عرض جميع البيانات من جدول `zoho_tickets_cache`
- تحديث البيانات بضغطة زر
- عرض عدد السجلات
- إمكانية التمرير الأفقية والعمودية
- واجهة عربية

## إعدادات قاعدة البيانات

يمكن تعديل إعدادات الاتصال في متغير `db_config` داخل الكلاس:
- Host: 127.0.0.1
- Port: 5432
- Database: CRM_ALL
- User: postgres
- Password: (فارغ)

