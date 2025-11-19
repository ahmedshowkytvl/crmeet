# إعداد النظام للعمل على الشبكة الواسعة (WAN)

## نظرة عامة
هذا الدليل يوضح كيفية إعداد نظام إدارة الموظفين للعمل على الشبكة الواسعة حتى يتمكن زملاؤك من الوصول إليه من خارج الشبكة المحلية.

## المتطلبات الأساسية
- XAMPP مثبت ومُشغل
- Laravel Framework
- PostgreSQL Database
- اتصال إنترنت مستقر
- معرفة عنوان IP العام لجهازك

## الخطوات المطلوبة

### 1. فحص إعدادات الشبكة الحالية
```bash
# عرض عناوين IP الحالية
ipconfig
```

### 2. تكوين جدار الحماية (Windows Firewall)
```bash
# فتح المنافذ المطلوبة
netsh advfirewall firewall add rule name="Laravel App Port 8000" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall add rule name="Laravel App Port 80" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="PostgreSQL Port 5432" dir=in action=allow protocol=TCP localport=5432
netsh advfirewall firewall add rule name="WebSocket Port 8080" dir=in action=allow protocol=TCP localport=8080
```

### 3. تحديث إعدادات Laravel
تحديث ملف `.env`:
```env
APP_URL=http://YOUR_PUBLIC_IP:8000
DB_HOST=0.0.0.0
```

### 4. تكوين Apache للوصول الخارجي
تحديث ملف `httpd.conf` في XAMPP:
```apache
# تغيير من
Listen 127.0.0.1:80
# إلى
Listen 0.0.0.0:80

# إضافة أو تعديل
<Directory "D:/xampp/htdocs/crm/stafftobia/public">
    AllowOverride All
    Require all granted
</Directory>
```

### 5. تكوين قاعدة البيانات PostgreSQL
تحديث ملف `postgresql.conf`:
```conf
listen_addresses = '*'
port = 5432
```

تحديث ملف `pg_hba.conf`:
```conf
# إضافة سطر للسماح بالاتصالات الخارجية
host    all             all             0.0.0.0/0               md5
```

### 6. بدء الخوادم
```bash
# بدء خادم Laravel على جميع الواجهات
php artisan serve --host=0.0.0.0 --port=8000

# بدء خادم WebSocket
node websocket-server.js
```

## الوصول للنظام

### من الشبكة المحلية
- http://192.168.15.29:8000
- http://192.168.99.1:8000
- http://192.168.172.1:8000
- http://192.168.254.1:8000

### من خارج الشبكة المحلية
- http://YOUR_PUBLIC_IP:8000

## نصائح الأمان

### 1. استخدام HTTPS
```bash
# تثبيت شهادة SSL
# أو استخدام Cloudflare للـ SSL المجاني
```

### 2. تقييد الوصول
```apache
# في Apache، يمكن تقييد الوصول حسب IP
<RequireAll>
    Require ip 192.168.15.0/24
    Require ip YOUR_COLLEGE_IP_RANGE
</RequireAll>
```

### 3. تغيير المنافذ الافتراضية
```bash
# استخدام منافذ غير افتراضية للأمان
php artisan serve --host=0.0.0.0 --port=8080
```

## استكشاف الأخطاء

### مشكلة: لا يمكن الوصول من الخارج
**الحل:**
1. تأكد من فتح المنافذ في جدار الحماية
2. تحقق من إعدادات الراوتر (Port Forwarding)
3. تأكد من أن ISP لا يحجب المنافذ

### مشكلة: قاعدة البيانات غير متاحة
**الحل:**
1. تأكد من إعدادات PostgreSQL
2. تحقق من ملف pg_hba.conf
3. أعد تشغيل خدمة PostgreSQL

### مشكلة: بطء في الاستجابة
**الحل:**
1. استخدم خادم إنتاج بدلاً من Development Server
2. فعّل التخزين المؤقت (Caching)
3. استخدم CDN للملفات الثابتة

## خادم الإنتاج (اختياري)

### استخدام Apache مع Laravel
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot "D:/xampp/htdocs/crm/stafftobia/public"
    
    <Directory "D:/xampp/htdocs/crm/stafftobia/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/laravel_error.log"
    CustomLog "logs/laravel_access.log" common
</VirtualHost>
```

### تحسين الأداء
```bash
# تحسين Laravel للإنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## المراقبة والصيانة

### مراقبة النظام
- استخدم WebSocket Server المدمج للمراقبة
- راقب استخدام الذاكرة والمعالج
- تحقق من سجلات الأخطاء بانتظام

### النسخ الاحتياطي
```bash
# نسخ احتياطي لقاعدة البيانات
pg_dump -h localhost -U postgres CRM_ALL > backup_$(date +%Y%m%d).sql

# نسخ احتياطي للملفات
tar -czf files_backup_$(date +%Y%m%d).tar.gz storage/ public/
```

## الدعم الفني

في حالة مواجهة مشاكل:
1. تحقق من سجلات الأخطاء
2. تأكد من إعدادات الشبكة
3. اختبر الاتصال محلياً أولاً
4. استخدم أدوات التشخيص مثل `netstat` و `telnet`

---
**ملاحظة:** تأكد من الحصول على إذن من مدير الشبكة قبل فتح المنافذ للوصول الخارجي.





