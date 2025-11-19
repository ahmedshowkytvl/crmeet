#!/bin/bash

# تشغيل MCP مع MySQL
echo "🚀 تشغيل MySQL MCP..."

# إعداد متغيرات البيئة
export MYSQL_HOST=127.0.0.1
export MYSQL_PORT=3306
export MYSQL_USER=root
export MYSQL_PASSWORD=""
export MYSQL_DATABASE=crm
export MYSQL_SSL=false

echo "📋 إعدادات الاتصال:"
echo "Host: $MYSQL_HOST"
echo "Port: $MYSQL_PORT"
echo "Database: $MYSQL_DATABASE"
echo "User: $MYSQL_USER"
echo ""

echo "✅ تشغيل خادم MCP..."
echo "💡 يمكنك الآن استخدام MCP في Cursor"
echo "🛑 اضغط Ctrl+C لإيقاف الخادم"
echo ""

# تشغيل خادم MCP
npx -y @benborla29/mcp-server-mysql
