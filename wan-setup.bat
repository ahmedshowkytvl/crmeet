@echo off
echo ========================================
echo    إعداد النظام للعمل على الشبكة الواسعة
echo ========================================
echo.

REM فحص إعدادات الشبكة الحالية
echo [1/5] فحص إعدادات الشبكة...
ipconfig | findstr "IPv4"
echo.

REM إيقاف جدار الحماية مؤقتاً للاختبار
echo [2/5] تكوين جدار الحماية...
netsh advfirewall firewall add rule name="Laravel App Port 8000" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall add rule name="Laravel App Port 80" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="PostgreSQL Port 5432" dir=in action=allow protocol=TCP localport=5432
netsh advfirewall firewall add rule name="WebSocket Port 8080" dir=in action=allow protocol=TCP localport=8080
echo تم تكوين جدار الحماية بنجاح
echo.

REM تحديث ملف .env للشبكة الواسعة
echo [3/5] تحديث إعدادات التطبيق...
copy .env .env.backup
echo تم إنشاء نسخة احتياطية من ملف .env
echo.

REM بدء خادم Laravel على جميع الواجهات
echo [4/5] بدء خادم Laravel...
echo النظام متاح الآن على:
echo - http://192.168.15.29:8000
echo - http://192.168.99.1:8000  
echo - http://192.168.172.1:8000
echo - http://192.168.254.1:8000
echo.
echo للوصول من خارج الشبكة المحلية، استخدم IP العام لجهازك
echo.

REM بدء الخادم
php artisan serve --host=0.0.0.0 --port=8000

pause
