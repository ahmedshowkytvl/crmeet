@echo off
title نظام إدارة الموظفين - إعداد الشبكة الواسعة
color 0A

echo.
echo  ███████╗██╗   ██╗██████╗ ███████╗████████╗    ███████╗██╗   ██╗███████╗████████╗███████╗██████╗ 
echo  ██╔════╝██║   ██║██╔══██╗██╔════╝╚══██╔══╝    ██╔════╝██║   ██║██╔════╝╚══██╔══╝██╔════╝██╔══██╗
echo  ███████╗██║   ██║██████╔╝███████╗   ██║       ███████╗██║   ██║███████╗   ██║   █████╗  ██████╔╝
echo  ╚════██║██║   ██║██╔══██╗╚════██║   ██║       ╚════██║██║   ██║╚════██║   ██║   ██╔══╝  ██╔══██╗
echo  ███████║╚██████╔╝██████╔╝███████║   ██║       ███████║╚██████╔╝███████║   ██║   ███████╗██║  ██║
echo  ╚══════╝ ╚═════╝ ╚═════╝ ╚══════╝   ╚═╝       ╚══════╝ ╚═════╝ ╚══════╝   ╚═╝   ╚══════╝╚═╝  ╚═╝
echo.
echo  ========================================
echo     إعداد النظام للشبكة الواسعة (WAN)
echo  ========================================
echo.

REM فحص المتطلبات الأساسية
echo [فحص المتطلبات]...
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP غير مثبت أو غير موجود في PATH
    echo يرجى تثبيت XAMPP أولاً
    pause
    exit /b 1
)

where psql >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  PostgreSQL غير موجود في PATH
    echo سيتم تخطي إعداد قاعدة البيانات
    set SKIP_DB=1
)

echo ✅ تم فحص المتطلبات الأساسية
echo.

REM عرض قائمة الخيارات
:menu
echo اختر العملية المطلوبة:
echo.
echo [1] إعداد كامل للنظام (موصى به)
echo [2] إعداد قاعدة البيانات فقط
echo [3] تكوين جدار الحماية فقط
echo [4] بدء الخوادم فقط
echo [5] اختبار الاتصال
echo [6] إزالة إعدادات الشبكة الواسعة
echo [7] عرض معلومات النظام
echo [8] خروج
echo.
set /p choice="اختر رقم العملية (1-8): "

if "%choice%"=="1" goto full_setup
if "%choice%"=="2" goto db_setup
if "%choice%"=="3" goto firewall_setup
if "%choice%"=="4" goto start_servers
if "%choice%"=="5" goto test_connection
if "%choice%"=="6" goto remove_setup
if "%choice%"=="7" goto system_info
if "%choice%"=="8" goto exit
goto menu

:full_setup
echo.
echo ========================================
echo    إعداد كامل للنظام
echo ========================================
echo.

echo [1/4] تحديث إعدادات التطبيق...
php wan-setup-tool.php
if %errorlevel% neq 0 (
    echo ❌ فشل في تحديث إعدادات التطبيق
    pause
    goto menu
)

echo [2/4] تكوين قاعدة البيانات...
if not defined SKIP_DB (
    configure-database-wan.bat
    if %errorlevel% neq 0 (
        echo ❌ فشل في تكوين قاعدة البيانات
        pause
        goto menu
    )
) else (
    echo ⚠️  تم تخطي تكوين قاعدة البيانات
)

echo [3/4] تكوين جدار الحماية...
configure-firewall-wan.bat
if %errorlevel% neq 0 (
    echo ❌ فشل في تكوين جدار الحماية
    pause
    goto menu
)

echo [4/4] بدء الخوادم...
start-wan-servers.bat

echo.
echo ✅ تم إعداد النظام بالكامل!
echo النظام متاح الآن على الشبكة الواسعة
echo.
pause
goto menu

:db_setup
echo.
echo ========================================
echo    إعداد قاعدة البيانات
echo ========================================
echo.
configure-database-wan.bat
pause
goto menu

:firewall_setup
echo.
echo ========================================
echo    تكوين جدار الحماية
echo ========================================
echo.
configure-firewall-wan.bat
pause
goto menu

:start_servers
echo.
echo ========================================
echo    بدء الخوادم
echo ========================================
echo.
start-wan-servers.bat
pause
goto menu

:test_connection
echo.
echo ========================================
echo    اختبار الاتصال
echo ========================================
echo.

echo فحص المنافذ المفتوحة:
netstat -an | findstr ":8000"
netstat -an | findstr ":5432"
netstat -an | findstr ":8080"

echo.
echo عناوين IP المتاحة:
ipconfig | findstr "IPv4"

echo.
echo اختبار الاتصال المحلي:
curl -s -o nul -w "%%{http_code}" http://localhost:8000 2>nul
if %errorlevel% equ 0 (
    echo ✅ الخادم يعمل محلياً
) else (
    echo ❌ الخادم لا يعمل محلياً
)

pause
goto menu

:remove_setup
echo.
echo ========================================
echo    إزالة إعدادات الشبكة الواسعة
echo ========================================
echo.
echo ⚠️  تحذير: هذا سيحذف جميع إعدادات الشبكة الواسعة
set /p confirm="هل أنت متأكد؟ (y/n): "
if /i not "%confirm%"=="y" goto menu

echo إزالة قواعد جدار الحماية...
remove-firewall-wan.bat

echo استعادة الإعدادات الأصلية...
php wan-setup-tool.php restore

echo ✅ تم إزالة إعدادات الشبكة الواسعة
pause
goto menu

:system_info
echo.
echo ========================================
echo    معلومات النظام
echo ========================================
echo.

echo معلومات النظام:
systeminfo | findstr /C:"OS Name" /C:"OS Version" /C:"Total Physical Memory"

echo.
echo معلومات الشبكة:
ipconfig | findstr "IPv4"

echo.
echo الخدمات المطلوبة:
net start | findstr "postgresql"
net start | findstr "apache"

echo.
echo إصدارات البرامج:
php -v
if not defined SKIP_DB (
    psql --version
)

pause
goto menu

:exit
echo.
echo شكراً لاستخدام أداة إعداد الشبكة الواسعة
echo.
pause
exit /b 0





