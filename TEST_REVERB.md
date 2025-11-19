# اختبار Laravel Reverb

## خطوات الاختبار السريع

### 1. تحديث .env

```bash
# أضف هذه المتغيرات إلى .env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=test-app-id-1234567890123456
REVERB_APP_KEY=test-app-key-1234567890123456
REVERB_APP_SECRET=test-app-secret-1234567890123456
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 2. تشغيل Reverb

في terminal منفصل:

```bash
cd /root/CRM
php artisan reverb:start
```

يجب أن ترى:
```
Reverb server started on 0.0.0.0:8080
```

### 3. اختبار الاتصال

1. افتح صفحة الدردشة في المتصفح
2. افتح Developer Tools (F12)
3. اذهب إلى Console tab
4. يجب أن ترى: `Echo connected` أو رسالة مشابهة
5. اذهب إلى Network tab → WS (WebSocket)
6. يجب أن ترى اتصال WebSocket ناجح

### 4. اختبار إرسال الرسائل

1. افتح صفحة الدردشة في متصفحين مختلفين
2. سجل الدخول بحسابين مختلفين
3. افتح نفس المحادثة في كلا المتصفحين
4. أرسل رسالة من المتصفح الأول
5. يجب أن تظهر الرسالة فوراً في المتصفح الثاني

### 5. التحقق من السجلات

```bash
# سجلات Laravel
tail -f storage/logs/laravel.log

# سجلات Reverb (إذا تم إعداد Supervisor)
tail -f storage/logs/reverb.log
```

## المشاكل الشائعة

### WebSocket connection failed

**الحل:**
1. تأكد من أن Reverb يعمل: `php artisan reverb:start`
2. تحقق من Port 8080: `netstat -tulpn | grep 8080`
3. تحقق من إعدادات .env

### الرسائل لا تصل

**الحل:**
1. تحقق من Console في المتصفح
2. تحقق من أن Event يتم إرساله في Laravel logs
3. تحقق من قنوات البث في `routes/channels.php`

### Reverb لا يعمل

**الحل:**
```bash
# تحقق من PHP version
php -v  # يجب أن يكون 8.2 أو أحدث

# تحقق من الإعدادات
php artisan config:clear
php artisan cache:clear

# حاول تشغيل Reverb مع debug
php artisan reverb:start --debug
```

---

**إذا واجهت مشاكل، راجع `REVERB_SETUP_INSTRUCTIONS.md`**

