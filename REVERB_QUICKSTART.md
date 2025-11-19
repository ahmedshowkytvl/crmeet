# دليل الإعداد السريع لـ Laravel Reverb

## الإعداد السريع للبيئة المحلية

### 1. تحديث ملف .env

أضف المتغيرات التالية إلى ملف `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id-here
REVERB_APP_KEY=your-app-key-here
REVERB_APP_SECRET=your-app-secret-here
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 2. توليد المفاتيح

```bash
# REVERB_APP_ID
openssl rand -hex 16

# REVERB_APP_KEY  
openssl rand -hex 16

# REVERB_APP_SECRET
openssl rand -hex 16
```

أو استخدم أي مولد عشوائي لـ 32 حرف hex.

### 3. تشغيل Reverb

في terminal منفصل:

```bash
php artisan reverb:start
```

أو للتطوير:

```bash
php artisan reverb:start --debug
```

### 4. اختبار النظام

1. افتح المتصفح واذهب إلى صفحة الدردشة
2. افتح Developer Tools (F12)
3. تحقق من Console - يجب أن ترى اتصال WebSocket ناجح
4. أرسل رسالة وتحقق من وصولها فوراً

## الإعداد للبرودكشن

راجع ملف `docs/REVERB_SETUP.md` للتفاصيل الكاملة.

### الخطوات الأساسية:

1. **تحديث .env للبرودكشن:**
```env
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

2. **إعداد Supervisor:**
```bash
sudo cp supervisor/reverb.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
```

3. **إعداد Nginx:**
أضف التكوين من ملف `nginx-reverb.conf` إلى إعدادات Nginx الخاصة بك.

4. **إعادة تشغيل Nginx:**
```bash
sudo nginx -t
sudo systemctl restart nginx
```

## استكشاف الأخطاء

### Reverb لا يعمل
```bash
# تحقق من السجلات
tail -f storage/logs/reverb.log

# تحقق من Port 8080
netstat -tulpn | grep 8080
```

### WebSocket connection failed
- تأكد من أن Reverb يعمل: `php artisan reverb:start`
- تحقق من إعدادات REVERB_HOST و REVERB_PORT في .env
- تأكد من أن المفاتيح صحيحة

### الرسائل لا تصل
- تحقق من Console في المتصفح
- تأكد من أن Event يتم إرساله: `broadcast(new MessageSent($message))`
- تحقق من قنوات البث في `routes/channels.php`

## ملاحظات مهمة

- Reverb يحتاج أن يعمل بشكل منفصل (Supervisor في البرودكشن)
- Port 8080 يجب أن يكون متاحاً
- في البرودكشن، استخدم HTTPS و Port 443
- تأكد من أن Firewall يسمح بالاتصال على Port المستخدم

