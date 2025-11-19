@echo off
title MCP MySQL Server
color 0A

echo.
echo ========================================
echo    🚀 خادم MCP مع MySQL
echo ========================================
echo.

echo 📋 إعدادات الاتصال:
echo Host: 127.0.0.1
echo Port: 3306
echo Database: crm
echo User: root
echo.

echo ✅ تشغيل خادم MCP...
echo 💡 يمكنك الآن استخدام MCP في Cursor
echo 🛑 اضغط Ctrl+C لإيقاف الخادم
echo.

set MYSQL_HOST=127.0.0.1
set MYSQL_PORT=3306
set MYSQL_USER=root
set MYSQL_PASSWORD=
set MYSQL_DATABASE=crm
set MYSQL_SSL=false
set MYSQL_CHARSET=utf8mb4
set MYSQL_COLLATION=utf8mb4_unicode_ci

npx -y @benborla29/mcp-server-mysql

pause
