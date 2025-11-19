# 🚀 تشغيل MySQL MCP باستخدام Shell Script

## ✅ تم إنشاء ملف Shell Script

### تشغيل MCP:
```bash
./start-mysql-mcp.sh
```

## 📋 محتوى الملف

```bash
#!/bin/bash

echo "🚀 خادم MySQL MCP"
echo "📋 إعدادات الاتصال:"
echo "Host: 127.0.0.1"
echo "Port: 3306"
echo "Database: crm"
echo "User: root"

# إعداد متغيرات البيئة
export MYSQL_HOST=127.0.0.1
export MYSQL_PORT=3306
export MYSQL_USER=root
export MYSQL_PASSWORD=""
export MYSQL_DATABASE=crm
export MYSQL_SSL=false

# تشغيل خادم MCP
npx -y @benborla29/mcp-server-mysql
```

## 🔧 إضافة MCP إلى Cursor

1. **افتح Cursor**
2. **اذهب إلى Settings > Extensions > MCP**
3. **أضف ملف `cursor-mcp-config.json`**

## 📊 إعدادات MCP

```json
{
  "mcpServers": {
    "mysql-crm": {
      "command": "npx",
      "args": ["-y", "@benborla29/mcp-server-mysql"],
      "env": {
        "MYSQL_HOST": "127.0.0.1",
        "MYSQL_PORT": "3306",
        "MYSQL_USER": "root",
        "MYSQL_PASSWORD": "",
        "MYSQL_DATABASE": "crm"
      }
    }
  }
}
```

## 🎯 اختبار MCP

بعد إضافة الإعداد إلى Cursor، يمكنك:

### عرض الجداول
```sql
SHOW TABLES;
```

### البحث عن مستخدم
```sql
SELECT * FROM users WHERE id = 120;
```

### عرض جميع المستخدمين
```sql
SELECT id, username, email, full_name FROM users;
```

## 🆘 استكشاف الأخطاء

### إذا لم يعمل MCP:
1. تأكد من تشغيل MySQL
2. تحقق من إعدادات Cursor
3. جرب إعادة تشغيل MCP

### إذا لم تظهر البيانات:
1. تأكد من وجود البيانات
2. تحقق من صحة الاستعلام
3. جرب استعلام بسيط أولاً

## 🎉 جاهز للاستخدام!

الآن يمكنك:
- تشغيل MCP باستخدام `./start-mysql-mcp.sh`
- إضافة الإعداد إلى Cursor
- استخدام MCP للتفاعل مع قاعدة البيانات
