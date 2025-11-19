#!/bin/bash

echo "========================================"
echo "   بدء نظام مراقبة النظام"
echo "========================================"
echo

echo "[1/3] تشغيل خادم Laravel..."
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!
sleep 3

echo "[2/3] تثبيت تبعيات WebSocket..."
if [ ! -d "node_modules" ]; then
    echo "تثبيت تبعيات Node.js..."
    npm install ws
else
    echo "تبعيات Node.js موجودة بالفعل"
fi

echo "[3/3] تشغيل خادم WebSocket..."
node websocket-server.js &
WEBSOCKET_PID=$!
sleep 2

echo
echo "========================================"
echo "   تم تشغيل النظام بنجاح!"
echo "========================================"
echo
echo "روابط الوصول:"
echo "- واجهة المراقب: http://localhost:8000/system-monitor"
echo "- خادم WebSocket: ws://localhost:8080/ws/system-monitor"
echo
echo "ملاحظة: تأكد من أن المنافذ 8000 و 8080 متاحة"
echo
echo "لإيقاف النظام، اضغط Ctrl+C"

# دالة لإيقاف العمليات عند إنهاء السكريبت
cleanup() {
    echo
    echo "إيقاف النظام..."
    kill $LARAVEL_PID 2>/dev/null
    kill $WEBSOCKET_PID 2>/dev/null
    exit 0
}

# ربط دالة التنظيف بإشارة الإنهاء
trap cleanup SIGINT SIGTERM

# انتظار حتى يتم إيقاف السكريبت
wait






