#!/bin/bash

# سكريبت تشغيل خادم MySQL MCP
# MySQL MCP Server startup script

echo ""
echo "========================================"
echo "    🚀 خادم MySQL MCP"
echo "========================================"
echo ""

echo "📋 إعدادات الاتصال:"
echo "Host: 127.0.0.1"
echo "Port: 3306"
echo "Database: crm"
echo "User: root"
echo ""

echo "✅ تشغيل خادم MCP..."
echo "💡 يمكنك الآن استخدام MCP في Cursor"
echo "🛑 اضغط Ctrl+C لإيقاف الخادم"
echo ""

# إعداد متغيرات البيئة
export MYSQL_HOST=127.0.0.1
export MYSQL_PORT=3306
export MYSQL_USER=root
export MYSQL_PASSWORD=""
export MYSQL_DATABASE=crm
export MYSQL_SSL=false

# تشغيل خادم MCP
npx -y @benborla29/mcp-server-mysql
