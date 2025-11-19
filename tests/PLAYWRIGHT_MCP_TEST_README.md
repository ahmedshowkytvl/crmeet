# اختبار الشات باستخدام Playwright MCP

## نظرة عامة

هذا الاختبار يستخدم Playwright MCP لاختبار نظام الشات على `192.168.15.216:8000`. يقوم الاختبار بـ:

1. ✅ تسجيل الدخول بحساب Madonna (847)
2. ✅ فتح الشات وإرسال رسالة
3. ✅ تسجيل الخروج وتسجيل الدخول بحساب Test User
4. ✅ فتح الشات والرد على الرسالة
5. ✅ جمع جميع الأخطاء من console logs و network errors

## المتطلبات

- Node.js 18+
- Playwright
- Chromium browser

## التثبيت

```bash
# تثبيت Node.js (إذا لم يكن مثبتاً)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"
nvm install 18
nvm use 18

# تثبيت Playwright
cd /root/CRM
npm install playwright --save-dev
npx playwright install chromium
```

## الاستخدام

```bash
cd /root/CRM
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"
mkdir -p /tmp/playwright_videos
node tests/chat_playwright_mcp_test.js
```

## الإعدادات

### URLs
- **Base URL:** `http://192.168.15.216:8000`
- **Chat URL:** `http://192.168.15.216:8000/chat/static?conversation=78`

### بيانات تسجيل الدخول

**Madonna (847):**
- Email: `marketing@egyptexpresstvl.com`
- Password: `password`
- User ID: 120

**Test User:**
- Email: `test.chat.user@example.com`
- Password: `password123`
- User ID: 146

## ما يتم جمعه

### 1. Console Logs
- جميع رسائل console (info, warn, error)
- مواقع الأخطاء في الكود
- Timestamps

### 2. Network Errors
- Failed requests
- HTTP errors (status >= 400)
- Response bodies للأخطاء

### 3. Page Errors
- JavaScript errors
- Stack traces

### 4. Screenshots
- عند كل خطوة مهمة
- عند حدوث أخطاء
- Screenshots كاملة للصفحة

### 5. Videos (اختياري)
- تسجيل فيديو للجلسة الكاملة

## الملفات المُنشأة

### 1. تقرير JSON
**الموقع:** `/tmp/playwright_chat_test_report.json`

**المحتوى:**
```json
{
  "timestamp": "2025-11-10T10:00:00.000Z",
  "baseUrl": "http://192.168.15.216:8000",
  "summary": {
    "totalErrors": 5,
    "consoleErrors": 3,
    "networkErrors": 2,
    "screenshots": 6
  },
  "errors": [...],
  "consoleLogs": [...],
  "networkErrors": [...],
  "screenshots": [...]
}
```

### 2. Screenshots
**الموقع:** `/tmp/playwright_*.png`

- `playwright_madonna_logged_in_*.png`
- `playwright_chat_loaded_*.png`
- `playwright_madonna_message_sent_*.png`
- `playwright_test_logged_in_*.png`
- `playwright_test_reply_sent_*.png`
- `playwright_error_final_*.png`

### 3. Videos (إذا تم تفعيلها)
**الموقع:** `/tmp/playwright_videos/`

## أنواع الأخطاء المُكتشفة

### 1. CONSOLE_ERROR
أخطاء JavaScript في console:
```javascript
{
  "type": "CONSOLE_ERROR",
  "message": "Uncaught TypeError: ...",
  "details": {
    "location": {...}
  }
}
```

### 2. NETWORK_ERROR
فشل في طلبات الشبكة:
```javascript
{
  "type": "NETWORK_ERROR",
  "message": "Request failed: POST /chat/static/send",
  "details": {
    "url": "...",
    "method": "POST",
    "failure": "net::ERR_CONNECTION_REFUSED"
  }
}
```

### 3. HTTP_ERROR
أخطاء HTTP (status >= 400):
```javascript
{
  "type": "HTTP_ERROR",
  "message": "HTTP 500: /chat/static/send",
  "details": {
    "url": "...",
    "status": 500,
    "statusText": "Internal Server Error",
    "body": "..."
  }
}
```

### 4. JSON_ERROR
استجابات ليست JSON:
```javascript
{
  "type": "JSON_ERROR",
  "message": "Response is not JSON: /chat/static/send",
  "details": {
    "contentType": "text/html",
    "status": 200
  }
}
```

### 5. PAGE_ERROR
أخطاء JavaScript في الصفحة:
```javascript
{
  "type": "PAGE_ERROR",
  "message": "Uncaught ReferenceError: ...",
  "details": {
    "stack": "..."
  }
}
```

### 6. MESSAGE_NOT_FOUND
الرسالة لم تظهر في الشات:
```javascript
{
  "type": "MESSAGE_NOT_FOUND",
  "message": "الرسالة لم تظهر في الشات",
  "details": {
    "expected": "...",
    "found": [...]
  }
}
```

## تحليل النتائج

### قراءة التقرير
```bash
cat /tmp/playwright_chat_test_report.json | jq '.summary'
```

### عرض الأخطاء فقط
```bash
cat /tmp/playwright_chat_test_report.json | jq '.errors'
```

### عرض Console Errors
```bash
cat /tmp/playwright_chat_test_report.json | jq '.consoleLogs[] | select(.type == "error")'
```

### عرض Network Errors
```bash
cat /tmp/playwright_chat_test_report.json | jq '.networkErrors'
```

## استكشاف الأخطاء

### مشكلة: Connection Refused
**السبب:** السيرفر غير متاح على `192.168.15.216:8000`
**الحل:** تأكد من أن السيرفر يعمل

### مشكلة: Login Failed
**السبب:** بيانات تسجيل الدخول غير صحيحة
**الحل:** تحقق من Email و Password

### مشكلة: Chat Not Found
**السبب:** الشات (ID: 78) غير موجود
**الحل:** تأكد من وجود الشات أو غيّر CHAT_URL

### مشكلة: Message Not Sent
**السبب:** مشكلة في API أو CSRF token
**الحل:** تحقق من console logs و network errors

## التحسينات المستقبلية

- [ ] إضافة اختبارات للرسائل الطويلة
- [ ] إضافة اختبارات للملفات المرفقة
- [ ] إضافة اختبارات للرسائل الجماعية
- [ ] إضافة اختبارات للأداء (Performance)
- [ ] إضافة اختبارات للـ Real-time updates

## الدعم

للمساعدة أو الإبلاغ عن مشاكل، يرجى فتح issue في المشروع.


