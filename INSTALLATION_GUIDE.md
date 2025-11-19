# دليل التثبيت - نظام مراقبة النظام

## 📋 المتطلبات

### متطلبات النظام
- **PHP**: 8.2 أو أحدث
- **Laravel**: 12.x
- **Node.js**: 16.x أو أحدث
- **قاعدة البيانات**: MySQL 5.7+ أو MariaDB 10.3+
- **نظام التشغيل**: Windows, Linux, macOS

### متطلبات إضافية
- **WebSocket**: لاتصال الوقت الفعلي
- **OPcache**: لتحسين الأداء (اختياري)
- **Redis**: للكاش المتقدم (اختياري)

## 🚀 التثبيت خطوة بخطوة

### الخطوة 1: إعداد البيئة

```bash
# نسخ ملف الإعدادات
cp .env.system-monitor.example .env

# تثبيت تبعيات PHP
composer install

# تثبيت تبعيات Node.js
npm install ws
```

### الخطوة 2: إعداد قاعدة البيانات

```bash
# إنشاء قاعدة البيانات
php artisan migrate

# إنشاء المستخدمين (اختياري)
php artisan db:seed
```

### الخطوة 3: إعداد الإعدادات

افتح ملف `.env` وأضف الإعدادات التالية:

```env
# تفعيل نظام المراقبة
SYSTEM_MONITOR_ENABLED=true

# إعدادات WebSocket
SYSTEM_MONITOR_WEBSOCKET_PORT=8080

# حدود التنبيهات
SYSTEM_MONITOR_MEMORY_THRESHOLD=90
SYSTEM_MONITOR_DISK_THRESHOLD=90

# الأمان (اختياري)
SYSTEM_MONITOR_ALLOWED_IPS=192.168.0.0/16
SYSTEM_MONITOR_REQUIRE_AUTH=false
```

### الخطوة 4: تشغيل النظام

#### الطريقة السريعة (مستحسنة)
```bash
# Windows
start-system-monitor.bat

# Linux/Mac
./start-system-monitor.sh
```

#### الطريقة اليدوية
```bash
# Terminal 1: خادم Laravel
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: خادم WebSocket
node websocket-server.js
```

### الخطوة 5: التحقق من التثبيت

1. افتح المتصفح
2. اذهب إلى `http://localhost:8000/system-monitor`
3. يجب أن ترى واجهة المراقب

## 🔧 التكوين المتقدم

### إعدادات الأمان

#### تقييد الوصول بـ IP
```env
SYSTEM_MONITOR_ALLOWED_IPS=192.168.1.0/24,10.0.0.0/8
```

#### تفعيل المصادقة
```env
SYSTEM_MONITOR_REQUIRE_AUTH=true
```

#### استخدام API Key
```env
SYSTEM_MONITOR_API_KEY=your-secret-key-here
```

### إعدادات الأداء

#### تحسين فترات التحديث
```env
# تحديث أسرع (3 ثوان)
SYSTEM_MONITOR_REFRESH_INTERVAL=3000

# تحديث أبطأ (10 ثوان)
SYSTEM_MONITOR_REFRESH_INTERVAL=10000
```

#### تحسين الرسوم البيانية
```env
# عدد أكبر من النقاط (50 نقطة)
SYSTEM_MONITOR_MAX_DATA_POINTS=50

# تحديث أسرع للرسوم
SYSTEM_MONITOR_CHART_UPDATE_INTERVAL=2000
```

### إعدادات التنبيهات

#### حدود التنبيهات
```env
# تنبيه عند استخدام 80% من الذاكرة
SYSTEM_MONITOR_MEMORY_THRESHOLD=80

# تنبيه عند استخدام 85% من القرص
SYSTEM_MONITOR_DISK_THRESHOLD=85

# تنبيه عند بطء الاستجابة (1.5 ثانية)
SYSTEM_MONITOR_RESPONSE_THRESHOLD=1500
```

## 🌐 الوصول من الشبكة

### معرفة عنوان IP

#### Windows
```cmd
ipconfig
```

#### Linux/Mac
```bash
ifconfig
# أو
ip addr show
```

### مثال
إذا كان عنوان IP هو `192.168.1.100`:

```
http://192.168.1.100:8000/system-monitor
```

### إعدادات الجدار الناري

#### Windows (Windows Defender)
1. افتح Windows Defender Firewall
2. اضغط "Advanced settings"
3. اضغط "Inbound Rules" → "New Rule"
4. اختر "Port" → "TCP" → "8000,8080"
5. اختر "Allow the connection"

#### Linux (UFW)
```bash
sudo ufw allow 8000
sudo ufw allow 8080
```

#### Linux (iptables)
```bash
sudo iptables -A INPUT -p tcp --dport 8000 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 8080 -j ACCEPT
```

## 🔍 استكشاف الأخطاء

### مشاكل شائعة

#### 1. WebSocket لا يعمل
```bash
# تحقق من تثبيت ws
npm list ws

# إعادة تثبيت
npm install ws

# تشغيل يدوي
node websocket-server.js
```

#### 2. Laravel لا يعمل
```bash
# تحقق من PHP
php --version

# تحقق من Laravel
php artisan --version

# مسح الكاش
php artisan config:clear
php artisan cache:clear
```

#### 3. مشاكل الشبكة
```bash
# تحقق من المنافذ
netstat -an | grep 8000
netstat -an | grep 8080

# اختبار الاتصال
telnet localhost 8000
telnet localhost 8080
```

#### 4. مشاكل قاعدة البيانات
```bash
# اختبار الاتصال
php artisan tinker
>>> DB::connection()->getPdo();

# تشغيل المايجريشن
php artisan migrate:status
```

### سجلات النظام

#### سجلات Laravel
```bash
tail -f storage/logs/laravel.log
```

#### سجلات WebSocket
تظهر في console الخادم

#### سجلات النظام
```bash
# Linux
tail -f /var/log/syslog

# Windows
# Event Viewer → Windows Logs → System
```

## 📊 مراقبة الأداء

### مقاييس مهمة
- **استخدام الذاكرة**: يجب أن يكون أقل من 90%
- **استخدام القرص**: يجب أن يكون أقل من 90%
- **وقت الاستجابة**: يجب أن يكون أقل من 2 ثانية
- **اتصالات قاعدة البيانات**: يجب أن تكون أقل من 80% من الحد الأقصى

### تحسين الأداء
```env
# تفعيل OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000

# تحسين MySQL
innodb_buffer_pool_size=1G
query_cache_size=64M
```

## 🛡️ الأمان

### إعدادات الأمان الأساسية
```env
# تقييد IP
SYSTEM_MONITOR_ALLOWED_IPS=192.168.0.0/16

# تفعيل المصادقة
SYSTEM_MONITOR_REQUIRE_AUTH=true

# استخدام HTTPS
APP_URL=https://your-domain.com
```

### إعدادات الأمان المتقدمة
```env
# API Key قوي
SYSTEM_MONITOR_API_KEY=your-very-secure-api-key-here

# تقييد أكثر
SYSTEM_MONITOR_ALLOWED_IPS=192.168.1.100,192.168.1.101
```

## 📞 الدعم

### الحصول على المساعدة
1. راجع هذا الدليل
2. تحقق من `SYSTEM_MONITOR_README.md`
3. راجع سجلات النظام
4. تأكد من المتطلبات

### التطوير
- أضف ميزات جديدة
- حسّن الواجهة
- أضف تنبيهات جديدة

---

**ملاحظة**: احفظ هذا الدليل للرجوع إليه لاحقاً! 📚






