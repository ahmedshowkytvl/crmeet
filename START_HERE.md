# كيفية تشغيل CRM

## المتطلبات الأساسية

1. **PHP 8.2 أو أحدث**
2. **Composer** - لإدارة حزم PHP
3. **Node.js و NPM** - لتشغيل Vite
4. **قاعدة بيانات MySQL** - يجب أن تكون قيد التشغيل

## خطوات التشغيل

### الطريقة 1: استخدام Composer (موصى بها)

```bash
cd CRM/CRM
composer run dev
```

هذا الأمر سيقوم بتشغيل:
- خادم Laravel على `http://localhost:8000`
- قائمة الانتظار (Queue)
- Vite للواجهة الأمامية
- سجلات التطبيق (Pail)

### الطريقة 2: استخدام ملفات التشغيل

#### على Windows:
```cmd
run.bat
```

أو باستخدام PowerShell:
```powershell
.\run.ps1
```

### الطريقة 3: التشغيل اليدوي

1. **تثبيت المتطلبات:**
```bash
composer install
npm install
```

2. **إعداد ملف البيئة:**
```bash
# إذا لم يكن ملف .env موجوداً
copy .env.example .env
php artisan key:generate
```

3. **تشغيل قاعدة البيانات:**
```bash
php artisan migrate
```

4. **تشغيل الخادم:**
```bash
php artisan serve
```

5. **في نافذة منفصلة، تشغيل Vite:**
```bash
npm run dev
```

## الوصول للتطبيق

بعد التشغيل، يمكنك الوصول للتطبيق على:
- **الخادم المحلي:** http://localhost:8000
- **الخادم المحدد في .env:** http://192.168.15.29/crm/stafftobia/public

## ملاحظات مهمة

- تأكد من أن قاعدة البيانات MySQL تعمل وأن بيانات الاتصال في ملف `.env` صحيحة
- تأكد من أن المنفذ 8000 غير مستخدم
- للتطوير، استخدم `composer run dev` لتشغيل جميع الخدمات معاً

