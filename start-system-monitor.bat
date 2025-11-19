@echo off
echo ========================================
echo    بدء نظام مراقبة النظام
echo ========================================
echo.

echo [1/3] تشغيل خادم Laravel...
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3 /nobreak > nul

echo [2/3] تثبيت تبعيات WebSocket...
if not exist "node_modules" (
    echo تثبيت تبعيات Node.js...
    npm install --prefix . ws
) else (
    echo تبعيات Node.js موجودة بالفعل
)

echo [3/3] تشغيل خادم WebSocket...
start "WebSocket Server" cmd /k "node websocket-server.js"
timeout /t 2 /nobreak > nul

echo.
echo ========================================
echo    تم تشغيل النظام بنجاح!
echo ========================================
echo.
echo روابط الوصول:
echo - واجهة المراقب: http://localhost:8000/system-monitor
echo - خادم WebSocket: ws://localhost:8080/ws/system-monitor
echo.
echo ملاحظة: تأكد من أن المنافذ 8000 و 8080 متاحة
echo.
pause






