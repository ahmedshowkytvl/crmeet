# دليل البدء السريع لـ MCP مع MySQL

## 🚀 الإعداد السريع

### 1. تثبيت MCP Server
```bash
npm install -g @benborla29/mcp-server-mysql
```

### 2. إضافة الإعداد إلى Cursor
1. افتح Cursor
2. اذهب إلى Settings > Extensions > MCP
3. أضف ملف `mcp-final-config.json`

### 3. اختبار الاتصال
```bash
php test-db-connection.php
```

## 📊 الجداول المتاحة في قاعدة البيانات

- **users** - المستخدمين
- **departments** - الأقسام
- **branches** - الفروع
- **assets** - الأصول
- **asset_categories** - فئات الأصول
- **asset_assignments** - تعيينات الأصول
- **contacts** - جهات الاتصال
- **tasks** - المهام
- **warehouses** - المستودعات
- **inventory** - المخزون

## 🔧 أوامر MCP مفيدة

### عرض هيكل الجداول
```sql
DESCRIBE users;
SHOW CREATE TABLE users;
```

### استعلامات مفيدة
```sql
-- عدد المستخدمين
SELECT COUNT(*) FROM users;

-- المستخدمين مع أقسامهم
SELECT u.name, d.name as department 
FROM users u 
LEFT JOIN departments d ON u.department_id = d.id;

-- الأصول المتاحة
SELECT a.name, ac.name as category 
FROM assets a 
LEFT JOIN asset_categories ac ON a.category_id = ac.id;
```

## ⚠️ ملاحظات مهمة

- تأكد من تشغيل MySQL قبل استخدام MCP
- استخدم MCP بحذر عند تعديل البيانات
- احتفظ بنسخة احتياطية من قاعدة البيانات

## 🆘 استكشاف الأخطاء

إذا لم يعمل MCP:
1. تحقق من تشغيل MySQL
2. تأكد من صحة إعدادات الاتصال
3. جرب إعادة تشغيل Cursor
