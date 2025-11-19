# كيفية تشغيل Laravel Reverb

## المشكلة

إذا كنت تواجه خطأ 500 عند إرسال الرسائل، فغالباً ما يكون السبب هو أن Reverb غير متصل.

## الحل

### 1. تشغيل Reverb يدوياً (للاختبار)

افتح terminal جديد وقم بتشغيل:

```bash
cd /root/CRM
php artisan reverb:start
```

يجب أن ترى رسالة مثل:
```
Reverb server started on 0.0.0.0:8080
```

### 2. التحقق من أن Reverb يعمل

افتح terminal آخر وتحقق:

```bash
# تحقق من أن Port 8080 مستخدم
netstat -tulpn | grep 8080

# أو
lsof -i :8080
```

### 3. اختبار إرسال الرسائل

بعد تشغيل Reverb:
1. افتح صفحة الدردشة
2. حاول إرسال رسالة
3. يجب أن تعمل بدون خطأ 500

## ملاحظات مهمة

- **Reverb يجب أن يعمل بشكل مستمر**: إذا أغلقت Terminal، سيتوقف Reverb
- **للبرودكشن**: استخدم Supervisor (راجع `docs/REVERB_SETUP.md`)
- **الرسائل تُحفظ حتى لو فشل البث**: تم إضافة error handling للسماح بحفظ الرسائل حتى لو لم يكن Reverb متصل

## إذا لم يعمل Reverb

### التحقق من الإعدادات

```bash
# تحقق من .env
cat .env | grep REVERB

# يجب أن يكون:
REVERB_APP_ID=379520
REVERB_APP_KEY=rgnhswole5ru5j1pr1sx
REVERB_APP_SECRET=nqdtkjumsvcajlff6umr
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### التحقق من Port 8080

```bash
# تحقق من أن Port 8080 متاح
netstat -tulpn | grep 8080

# إذا كان Port مستخدم من قبل تطبيق آخر، غير PORT في .env
```

### تشغيل Reverb مع Debug

```bash
php artisan reverb:start --debug
```

## بعد تشغيل Reverb

1. ✅ الرسائل تُحفظ في قاعدة البيانات
2. ✅ الرسائل تُبث للمستخدمين الآخرين عبر WebSocket
3. ✅ الرسائل تظهر فوراً بدون polling

---

**ملاحظة**: الرسائل الآن تُحفظ حتى لو فشل البث (تم إضافة error handling). لكن للاستفادة الكاملة من الميزات الفورية، يجب أن يكون Reverb متصل.

