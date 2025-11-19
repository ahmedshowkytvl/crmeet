@echo off
echo 🚀 بدء تشغيل خادم MCP مع MySQL...
echo.

set MYSQL_HOST=127.0.0.1
set MYSQL_PORT=3306
set MYSQL_USER=root
set MYSQL_PASSWORD=
set MYSQL_DATABASE=crm
set MYSQL_SSL=false
set MYSQL_CHARSET=utf8mb4
set MYSQL_COLLATION=utf8mb4_unicode_ci

echo 📋 إعدادات الاتصال:
echo Host: %MYSQL_HOST%
echo Port: %MYSQL_PORT%
echo Database: %MYSQL_DATABASE%
echo User: %MYSQL_USER%
echo.

echo ✅ تشغيل خادم MCP...
npx -y @benborla29/mcp-server-mysql

pause
