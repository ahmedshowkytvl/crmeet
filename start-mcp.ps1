# سكريبت تشغيل خادم MCP مع MySQL
# MCP Server startup script with MySQL

Write-Host "🚀 بدء تشغيل خادم MCP مع MySQL..." -ForegroundColor Green
Write-Host ""

# إعداد متغيرات البيئة
$env:MYSQL_HOST = "127.0.0.1"
$env:MYSQL_PORT = "3306"
$env:MYSQL_USER = "root"
$env:MYSQL_PASSWORD = ""
$env:MYSQL_DATABASE = "crm"
$env:MYSQL_SSL = "false"
$env:MYSQL_CHARSET = "utf8mb4"
$env:MYSQL_COLLATION = "utf8mb4_unicode_ci"

Write-Host "📋 إعدادات الاتصال:" -ForegroundColor Yellow
Write-Host "Host: $env:MYSQL_HOST"
Write-Host "Port: $env:MYSQL_PORT"
Write-Host "Database: $env:MYSQL_DATABASE"
Write-Host "User: $env:MYSQL_USER"
Write-Host ""

Write-Host "✅ تشغيل خادم MCP..." -ForegroundColor Green
Write-Host "💡 يمكنك الآن استخدام MCP في Cursor" -ForegroundColor Cyan
Write-Host "🛑 اضغط Ctrl+C لإيقاف الخادم" -ForegroundColor Red
Write-Host ""

# تشغيل خادم MCP
npx -y @benborla29/mcp-server-mysql
