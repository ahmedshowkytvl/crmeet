@echo off
echo ========================================
echo    تكوين جدار الحماية للشبكة الواسعة
echo ========================================
echo.

echo [1/6] فحص حالة جدار الحماية...
netsh advfirewall show allprofiles state

echo.
echo [2/6] فتح المنافذ المطلوبة...

REM منفذ Laravel Development Server
netsh advfirewall firewall add rule name="Laravel App Port 8000" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall add rule name="Laravel App Port 8000 Out" dir=out action=allow protocol=TCP localport=8000

REM منفذ Apache HTTP
netsh advfirewall firewall add rule name="Apache HTTP Port 80" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="Apache HTTP Port 80 Out" dir=out action=allow protocol=TCP localport=80

REM منفذ PostgreSQL
netsh advfirewall firewall add rule name="PostgreSQL Port 5432" dir=in action=allow protocol=TCP localport=5432
netsh advfirewall firewall add rule name="PostgreSQL Port 5432 Out" dir=out action=allow protocol=TCP localport=5432

REM منفذ WebSocket
netsh advfirewall firewall add rule name="WebSocket Port 8080" dir=in action=allow protocol=TCP localport=8080
netsh advfirewall firewall add rule name="WebSocket Port 8080 Out" dir=out action=allow protocol=TCP localport=8080

REM منفذ HTTPS (اختياري)
netsh advfirewall firewall add rule name="HTTPS Port 443" dir=in action=allow protocol=TCP localport=443
netsh advfirewall firewall add rule name="HTTPS Port 443 Out" dir=out action=allow protocol=TCP localport=443

echo [3/6] إضافة قواعد للبرامج...
netsh advfirewall firewall add rule name="PHP Artisan Serve" dir=in action=allow program="php.exe"
netsh advfirewall firewall add rule name="Node.js WebSocket" dir=in action=allow program="node.exe"

echo [4/6] تكوين قواعد متقدمة للأمان...
REM السماح بالاتصالات من الشبكة المحلية فقط (اختياري)
netsh advfirewall firewall add rule name="Laravel Local Network" dir=in action=allow protocol=TCP localport=8000 remoteip=192.168.0.0/16,10.0.0.0/8,172.16.0.0/12

echo [5/6] عرض القواعد المضافة...
echo.
echo قواعد جدار الحماية المضافة:
netsh advfirewall firewall show rule name="Laravel App Port 8000"
netsh advfirewall firewall show rule name="PostgreSQL Port 5432"
netsh advfirewall firewall show rule name="WebSocket Port 8080"

echo [6/6] اختبار المنافذ...
echo.
echo اختبار المنافذ المفتوحة:
netstat -an | findstr ":8000"
netstat -an | findstr ":5432"
netstat -an | findstr ":8080"

echo.
echo ✅ تم تكوين جدار الحماية بنجاح
echo.
echo المنافذ المفتوحة:
echo   - 8000: Laravel Development Server
echo   - 80: Apache HTTP Server
echo   - 5432: PostgreSQL Database
echo   - 8080: WebSocket Server
echo   - 443: HTTPS (اختياري)
echo.
echo ⚠️  تحذير: تأكد من تحديث كلمات المرور الافتراضية
echo.
pause





