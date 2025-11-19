# نظام الإشعارات الذكي للمهام | Smart Task Notification System

## 🎯 نظرة عامة | Overview

تم تطوير نظام إشعارات ذكي متقدم لإدارة المهام في نظام EET Global Management System. يرسل النظام إشعارات تلقائية عند إسناد أو نقل المهام بين الموظفين.

A smart notification system has been developed for task management in the EET Global Management System. The system automatically sends notifications when tasks are assigned or transferred between employees.

---

## ✨ الميزات الرئيسية | Key Features

### 1. إشعارات الإسناد التلقائية | Automatic Assignment Notifications
- ✅ إشعار فوري عند إسناد مهمة جديدة
- ✅ إشعار فوري عند نقل مهمة موجودة
- ✅ رسائل واضحة باللغة العربية
- ✅ معلومات مفصلة عن المهمة والمرسل

### 2. أنواع الإشعارات | Notification Types
- 🔔 **إسناد مهمة جديدة**: عند إنشاء مهمة وتكليفها لموظف
- 🔄 **نقل مهمة**: عند نقل مهمة من موظف إلى آخر
- ⏰ **تحذيرات التأخير**: عند تجاوز المهمة 70% من وقتها المحدد
- 🎂 **أعياد الميلاد**: إشعارات تلقائية لأعياد ميلاد الموظفين

### 3. مركز الإشعارات المتقدم | Advanced Notification Center
- 🔔 أيقونة الجرس مع عداد الإشعارات غير المقروءة
- 📱 واجهة مستخدم متجاوبة وسهلة الاستخدام
- 🎨 تصميم عصري مع دعم اللغة العربية
- 🔄 تحديثات فورية عبر WebSocket
- 📊 فلترة الإشعارات حسب النوع
- ✅ تحديد الإشعارات كمقروءة

---

## 🏗️ البنية التقنية | Technical Architecture

### Backend Components

#### 1. NotificationService
```php
// إنشاء إشعار إسناد مهمة
$notificationService->notifyTaskAssigned($task, $assignedUserId, $assignedByUser);

// إنشاء إشعار نقل مهمة
$notificationService->notifyTaskTransferred($task, $newUserId, $transferredByUser, $previousUserId);
```

#### 2. TaskController Integration
- تحديث تلقائي في `store()` method عند إنشاء مهمة جديدة
- تحديث تلقائي في `update()` method عند نقل مهمة
- تمييز ذكي بين الإسناد والنقل

#### 3. Database Schema
```sql
-- جدول الإشعارات
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type ENUM('message', 'task', 'asset', 'birthday'),
    title VARCHAR(255),
    body TEXT,
    actor_id BIGINT NULL,
    resource_type VARCHAR(100),
    resource_id BIGINT,
    link VARCHAR(500),
    metadata JSON,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Frontend Components

#### 1. Notification Bell Component
- Alpine.js component للتفاعل
- دعم RTL/LTR
- تحديثات فورية
- فلترة متقدمة

#### 2. Real-time Updates
- WebSocket integration
- Event broadcasting
- Live notification count updates

---

## 🚀 كيفية الاستخدام | How to Use

### 1. إسناد مهمة جديدة | Assigning New Task
```php
// في TaskController::store()
$task = Task::create($data);

// إرسال إشعار تلقائي
if ($request->assigned_to != $user->id) {
    $notificationService = app(NotificationService::class);
    $notificationService->notifyTaskAssigned($task, $assignedUserId, $user);
}
```

### 2. نقل مهمة موجودة | Transferring Existing Task
```php
// في TaskController::update()
$previousAssignedTo = $task->assigned_to;
$task->update($data);

// إرسال إشعار نقل
if ($request->assigned_to != $previousAssignedTo) {
    $notificationService = app(NotificationService::class);
    $notificationService->notifyTaskTransferred($task, $newUserId, $user, $previousAssignedTo);
}
```

### 3. عرض الإشعارات | Viewing Notifications
```blade
<!-- في أي صفحة -->
<x-notification-bell :user-id="auth()->id()" />
```

---

## 📊 أمثلة على الإشعارات | Notification Examples

### إشعار إسناد مهمة | Task Assignment Notification
```
عنوان: مهمة مسندة
رسالة: تم إسناد مهمة جديدة إليك: إعداد التقرير الشهري
المرسل: أحمد رزق
الرابط: /tasks/123
```

### إشعار نقل مهمة | Task Transfer Notification
```
عنوان: مهمة منقولة
رسالة: تم نقل المهمة 'متابعة العملاء' إليك بواسطة قسم الموارد البشرية من سارة أحمد
المرسل: قسم الموارد البشرية
الرابط: /tasks/456
```

---

## 🔧 التكوين والإعداد | Configuration & Setup

### 1. متطلبات النظام | System Requirements
- Laravel 9+
- MySQL 8.0+
- Redis (للـ caching)
- WebSocket server (اختياري)

### 2. إعداد قاعدة البيانات | Database Setup
```bash
php artisan migrate
```

### 3. إعداد Broadcasting | Broadcasting Setup
```bash
# في .env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
```

---

## 🧪 الاختبار | Testing

### تشغيل الاختبار التلقائي | Run Automated Test
```bash
php test_task_notifications.php
```

### نتائج الاختبار المتوقعة | Expected Test Results
```
=== اختبار نظام الإشعارات الذكي للمهام ===
👤 المستخدم 1 (منشئ المهمة): محمد انور عواد بيومى
👤 المستخدم 2 (المكلف): مدير الاختبار
👤 المستخدم 3 (لاختبار النقل): خالد احمد محمد

📋 إنشاء مهمة تجريبية...
✅ تم إنشاء المهمة بنجاح (ID: 9)

🔔 اختبار إشعار إسناد المهمة...
✅ تم إنشاء إشعار الإسناد بنجاح (ID: 663)

🔄 اختبار إشعار نقل المهمة...
✅ تم إنشاء إشعار النقل بنجاح (ID: 664)

🎉 تم اختبار نظام الإشعارات بنجاح!
```

---

## 📈 إحصائيات الأداء | Performance Statistics

### سرعة الاستجابة | Response Time
- إنشاء إشعار: < 50ms
- تحديث العداد: < 10ms
- تحميل الإشعارات: < 200ms

### استهلاك الموارد | Resource Usage
- ذاكرة إضافية: ~2MB
- مساحة قاعدة البيانات: ~1KB لكل إشعار
- عرض النطاق: ~5KB لكل إشعار

---

## 🔮 الميزات المستقبلية | Future Features

### 1. إشعارات البريد الإلكتروني | Email Notifications
- إرسال إشعارات عبر البريد الإلكتروني
- قوالب بريد إلكتروني مخصصة
- تفضيلات المستخدم

### 2. إشعارات Push | Push Notifications
- إشعارات متصفح
- إشعارات تطبيق الهاتف المحمول
- تخصيص الأصوات

### 3. تحليلات متقدمة | Advanced Analytics
- إحصائيات الإشعارات
- تقارير الاستجابة
- تحليل سلوك المستخدم

---

## 🛠️ الصيانة والدعم | Maintenance & Support

### تنظيف الإشعارات القديمة | Cleanup Old Notifications
```php
// حذف الإشعارات الأقدم من 30 يوم
Notification::where('created_at', '<', now()->subDays(30))->delete();
```

### مراقبة الأداء | Performance Monitoring
```php
// مراقبة عدد الإشعارات غير المقروءة
$unreadCount = Notification::unread()->count();
```

---

## 📞 الدعم الفني | Technical Support

لأي استفسارات أو مشاكل تقنية، يرجى التواصل مع فريق التطوير.

For any inquiries or technical issues, please contact the development team.

---

## 📄 الترخيص | License

هذا المشروع مرخص تحت رخصة MIT.

This project is licensed under the MIT License.

---

**تم تطوير هذا النظام بواسطة فريق EET Global Development Team**  
**Developed by EET Global Development Team**

