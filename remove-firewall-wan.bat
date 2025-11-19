@echo off
echo ========================================
echo    إزالة قواعد جدار الحماية للشبكة الواسعة
echo ========================================
echo.

echo [1/4] إزالة قواعد المنافذ...

REM إزالة قواعد Laravel
netsh advfirewall firewall delete rule name="Laravel App Port 8000"
netsh advfirewall firewall delete rule name="Laravel App Port 8000 Out"

REM إزالة قواعد Apache
netsh advfirewall firewall delete rule name="Apache HTTP Port 80"
netsh advfirewall firewall delete rule name="Apache HTTP Port 80 Out"

REM إزالة قواعد PostgreSQL
netsh advfirewall firewall delete rule name="PostgreSQL Port 5432"
netsh advfirewall firewall delete rule name="PostgreSQL Port 5432 Out"

REM إزالة قواعد WebSocket
netsh advfirewall firewall delete rule name="WebSocket Port 8080"
netsh advfirewall firewall delete rule name="WebSocket Port 8080 Out"

REM إزالة قواعد HTTPS
netsh advfirewall firewall delete rule name="HTTPS Port 443"
netsh advfirewall firewall delete rule name="HTTPS Port 443 Out"

echo [2/4] إزالة قواعد البرامج...
netsh advfirewall firewall delete rule name="PHP Artisan Serve"
netsh advfirewall firewall delete rule name="Node.js WebSocket"

echo [3/4] إزالة القواعد المتقدمة...
netsh advfirewall firewall delete rule name="Laravel Local Network"

echo [4/4] عرض حالة جدار الحماية...
netsh advfirewall show allprofiles state

echo.
echo ✅ تم إزالة جميع قواعد الشبكة الواسعة
echo النظام عاد إلى الإعدادات الأصلية
echo.
pause





