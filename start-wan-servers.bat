@echo off
echo ========================================
echo    تشغيل النظام على الشبكة الواسعة
echo ========================================
echo.

echo بدء خادم Laravel...
start "Laravel Server" php artisan serve --host=0.0.0.0 --port=8000

echo بدء خادم WebSocket...
start "WebSocket Server" node websocket-server.js

echo.
echo ✅ تم بدء جميع الخوادم
echo النظام متاح الآن على الشبكة الواسعة
echo.
echo اضغط أي مفتاح للخروج...
pause > nul