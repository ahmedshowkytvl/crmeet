# إعداد Laravel Reverb للبرودكشن

## المتطلبات
- PHP 8.2 أو أحدث
- Supervisor مثبت
- Nginx أو Apache مع دعم WebSocket proxy
- SSL/HTTPS (مطلوب للبرودكشن)

## الخطوات

### 1. إعداد ملف .env

أضف المتغيرات التالية إلى ملف `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 2. توليد المفاتيح

قم بتشغيل الأوامر التالية لتوليد المفاتيح:

```bash
php artisan reverb:install
```

أو قم بتوليد المفاتيح يدوياً:

```bash
# REVERB_APP_ID
echo $(openssl rand -hex 16)

# REVERB_APP_KEY
echo $(openssl rand -hex 16)

# REVERB_APP_SECRET
echo $(openssl rand -hex 16)
```

### 3. إعداد Supervisor

انسخ ملف `supervisor/reverb.conf` إلى `/etc/supervisor/conf.d/`:

```bash
sudo cp /root/CRM/supervisor/reverb.conf /etc/supervisor/conf.d/reverb.conf
```

ثم قم بتحديث Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb:*
```

للتحقق من الحالة:

```bash
sudo supervisorctl status reverb:*
```

### 4. إعداد Nginx

أضف التكوين التالي إلى ملف Nginx الخاص بك:

```nginx
# WebSocket proxy for Reverb
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
}
```

### 5. إعداد Apache

إذا كنت تستخدم Apache، أضف التكوين التالي:

```apache
<Location "/app/">
    ProxyPass ws://127.0.0.1:8080/app/
    ProxyPassReverse ws://127.0.0.1:8080/app/
</Location>
```

تأكد من تفعيل mod_proxy_wstunnel:

```bash
sudo a2enmod proxy_wstunnel
sudo systemctl restart apache2
```

### 6. تشغيل Reverb

للاختبار المحلي:

```bash
php artisan reverb:start
```

للبرودكشن، استخدم Supervisor (تم إعداده في الخطوة 3).

### 7. التحقق من العمل

افتح Developer Tools في المتصفح وتحقق من:
- اتصال WebSocket ناجح
- عدم وجود أخطاء في Console
- وصول الرسائل فوراً

### 8. استكشاف الأخطاء

#### Reverb لا يعمل
```bash
# تحقق من السجلات
tail -f /root/CRM/storage/logs/reverb.log

# تحقق من Supervisor
sudo supervisorctl status reverb:*
```

#### WebSocket connection failed
- تأكد من أن Reverb يعمل على Port 8080
- تحقق من إعدادات Nginx/Apache
- تأكد من أن SSL/HTTPS يعمل بشكل صحيح

#### الرسائل لا تصل
- تحقق من إعدادات BROADCAST_CONNECTION في .env
- تأكد من أن Event يتم إرساله بشكل صحيح
- تحقق من قنوات البث في routes/channels.php

## ملاحظات مهمة

1. **Port 8080**: تأكد من أن Port 8080 غير مستخدم من قبل تطبيق آخر
2. **Firewall**: تأكد من فتح Port 8080 في Firewall (للبرودكشن)
3. **SSL**: HTTPS مطلوب للبرودكشن
4. **Memory**: Reverb قد يستهلك ذاكرة، راقب الاستخدام

## الأوامر المفيدة

```bash
# إعادة تشغيل Reverb
sudo supervisorctl restart reverb:*

# إيقاف Reverb
sudo supervisorctl stop reverb:*

# عرض السجلات
tail -f /root/CRM/storage/logs/reverb.log

# اختبار الاتصال
php artisan reverb:start --debug
```

