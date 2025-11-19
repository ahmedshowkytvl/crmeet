# نحتاج إلى Authorization Code جديد

## المشكلة
الـ authorization code الحالي في config.py غير صالح (invalid_code).

## الحل
نحتاج إلى الحصول على authorization code جديد من Zoho.

## الخطوات المطلوبة:

### 1. افتح الرابط التالي في المتصفح:
```
https://accounts.zoho.com/oauth/v2/auth?response_type=code&client_id=1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT&scope=Desk.tickets.READ%2CDesk.contacts.READ%2CDesk.tickets.UPDATE&redirect_uri=https%3A%2F%2Fwww.google.com&access_type=offline
```

### 2. قم بالتالي:
- سجل دخولك إلى حساب Zoho
- وافق على الصلاحيات المطلوبة
- ستتم إعادة التوجيه إلى Google مع معامل `code`

### 3. انسخ الكود من الرابط:
ابحث عن معامل `code` في الرابط، سيكون شكله مثل:
```
https://www.google.com/?code=1000.ABC123DEF456...
```

### 4. انسخ الجزء بعد `code=`:
مثال: `1000.ABC123DEF456...`

### 5. حدث config.py:
افتح ملف config.py واستبدل السطر:
```
AUTHORIZATION_CODE = "1000.b4661996af0e5f0aafe9310abee0b345.f3396f9660c9f5e300c9df742defb709"
```
بالكود الجديد:
```
AUTHORIZATION_CODE = "الكود_الجديد_الذي_نسخته"
```

### 6. أخبرني عندما تنتهي:
بعد تحديث config.py، أخبرني وسأكمل الخطوات التالية.
