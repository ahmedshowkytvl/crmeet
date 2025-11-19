# إعداد MCP (Model Context Protocol) للتطبيق

## نظرة عامة
هذا الدليل يوضح كيفية إعداد MCP للعمل مع قاعدة البيانات MySQL الخاصة بتطبيق Laravel.

## الملفات المطلوبة

### 1. ملف إعداد MySQL MCP
تم إنشاء `mcp-mysql-config.json` مع الإعدادات التالية:
- Host: 127.0.0.1
- Port: 3306
- Database: crm
- Username: root
- Password: (فارغ)

### 2. ملف إعداد PostgreSQL MCP (اختياري)
تم إنشاء `mcp-postgres-config.json` كبديل.

## خطوات الإعداد

### الخطوة 1: تثبيت MCP Server
```bash
npm install -g @benborla29/mcp-server-mysql
```

### الخطوة 2: إضافة الإعداد إلى Cursor
1. افتح Cursor
2. اذهب إلى Settings > Extensions > MCP
3. أضف الملف `mcp-final-config.json` إلى إعدادات MCP

### الخطوة 3: اختبار الاتصال
بعد إضافة الإعداد، يمكنك اختبار الاتصال باستخدام الأوامر التالية في Cursor:

```sql
-- عرض جميع الجداول
SHOW TABLES;

-- عرض هيكل جدول معين
DESCRIBE users;

-- استعلام بسيط
SELECT * FROM users LIMIT 5;
```

## إعدادات قاعدة البيانات الحالية

من ملف `.env`:
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=crm
- DB_USERNAME=root
- DB_PASSWORD=

## استكشاف الأخطاء

### مشكلة الاتصال
إذا لم يعمل الاتصال، تأكد من:
1. أن خادم MySQL يعمل
2. أن قاعدة البيانات `crm` موجودة
3. أن المستخدم `root` لديه صلاحيات الوصول

### اختبار الاتصال من Laravel
```bash
php artisan tinker
DB::connection()->getPdo();
```

## الميزات المتاحة مع MCP

1. **استعلامات SQL مباشرة**: يمكنك تنفيذ استعلامات SQL مباشرة من Cursor
2. **تحليل قاعدة البيانات**: عرض هيكل الجداول والعلاقات
3. **تحسين الاستعلامات**: اقتراحات لتحسين أداء الاستعلامات
4. **إدارة البيانات**: إدراج، تحديث، وحذف البيانات

## ملاحظات مهمة

- تأكد من عمل خادم MySQL قبل استخدام MCP
- استخدم MCP بحذر عند تعديل البيانات الحساسة
- احتفظ بنسخة احتياطية من قاعدة البيانات قبل إجراء تغييرات كبيرة
