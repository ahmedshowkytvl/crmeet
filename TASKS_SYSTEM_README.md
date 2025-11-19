# نظام إدارة المهام المتقدم | Advanced Tasks Management System

## نظرة عامة | Overview

تم تطوير نظام إدارة المهام ليشمل ميزات متقدمة تتيح للمستخدمين إنشاء وإدارة المهام بناءً على صلاحياتهم مع دعم المهام المتكررة.

The tasks management system has been enhanced with advanced features that allow users to create and manage tasks based on their permissions with support for recurring tasks.

---

## الميزات الرئيسية | Key Features

### 1. صلاحيات التكليف | Assignment Permissions

#### CEO / Super Admin
- ✅ يمكنه تكليف أي شخص في الشركة
- ✅ Can assign tasks to anyone in the company
- ✅ يرى جميع المهام في النظام
#### Manager / Admin
- ✅ يمكنه تكليف أشخاص في قسمه فقط
- ✅ Can assign tasks to people in their department only
- ✅ يرى مهام قسمه والمهام المكلف بها
- ✅ Can view department tasks and their assigned tasks

#### Employee (موظف عادي)
- ✅ يمكنه فقط تكليف نفسه
- ✅ Can only assign tasks to themselves
- ✅ يرى فقط المهام المكلف بها أو التي أنشأها
- ✅ Can only view tasks assigned to them or created by them

### 2. إدارة الحالة والأولوية | Status and Priority Management

#### إذا كانت المهمة للمستخدم نفسه | If task is for the user themselves:
- ✅ يمكن تحديد **الحالة** و **الأولوية**
- ✅ Can set both **status** and **priority**

#### إذا كانت المهمة لشخص آخر | If task is for someone else:
- ✅ المنشئ يحدد **الأولوية** فقط
- ✅ Creator sets **priority** only
- ✅ المكلف بالمهمة يحدد **الحالة**
- ✅ Assignee sets the **status**

### 3. أنواع التكرار | Repeat Types

#### أنواع المهام المتاحة | Available Task Types:
- 🔹 **مرة واحدة (One Time)**: تنفذ مرة واحدة فقط
- 🔹 **يومية (Daily)**: تتكرر كل يوم
- 🔹 **ربع سنوية (Quarterly)**: تتكرر كل 3 أشهر
- 🔹 **سنوية (Yearly)**: تتكرر كل سنة

### 4. حالات المهمة | Task Statuses

- 🟡 **قيد الانتظار (Pending)**: في انتظار البدء
- 🔵 **قيد التنفيذ (In Progress)**: جاري العمل عليها
- 🟢 **مكتملة (Completed)**: تم إنجازها
- ⚪ **معلقة (On Hold)**: موقفة مؤقتاً

### 5. مستويات الأولوية | Priority Levels

- 🔴 **عالية (High)**: ضرورية وعاجلة
- 🟡 **متوسطة (Medium)**: مهمة ولكن ليست عاجلة
- 🟢 **منخفضة (Low)**: يمكن تأجيلها

### 6. نظام SLA (Service Level Agreement)

#### طريقتان لتحديد موعد الاستحقاق:

**أ) تحديد تاريخ ووقت محدد:**
- اختر تاريخ محدد
- اختر وقت محدد (اختياري، الافتراضي 23:59)
- مثال: 15/10/2025 الساعة 14:30

**ب) تحديد عدد الساعات من الآن:**
- أدخل عدد الساعات (مثال: 24 ساعة)
- يتم حساب التاريخ والوقت تلقائياً
- مثال: إذا أدخلت 48 ساعة، سيتم حساب الموعد بعد يومين من الآن

#### ميزات SLA:
- ✅ حساب الوقت المتبقي تلقائياً
- ✅ تحذيرات عند اقتراب الموعد
- ✅ تحديد نسبة الوقت المستخدم
- ✅ إشعارات عند تجاوز SLA

### 7. إدارة الجدولة | Schedule Management

#### تواريخ البداية والنهاية:
- **تاريخ ووقت البداية المخطط**: متى تريد بدء المهمة
- **تاريخ ووقت النهاية المخطط**: متى تريد إنهاء المهمة
- **تاريخ ووقت البدء الفعلي**: متى بدأت المهمة فعلياً (يتم تسجيله تلقائياً)
- **تاريخ ووقت الانتهاء الفعلي**: متى انتهت المهمة فعلياً (يتم تسجيله تلقائياً)

#### الميزات:
- ✅ مقارنة بين المخطط والفعلي
- ✅ حساب مدة المهمة بالساعات
- ✅ تتبع دقة الجدولة
- ✅ عرض التواريخ في جدول المهام

---

## البنية التقنية | Technical Structure

### قاعدة البيانات | Database

#### حقول جدول المهام | Tasks Table Fields

```sql
- id: bigint (primary key)
- title: varchar(255) - العنوان بالإنجليزية
- title_ar: varchar(255) - العنوان بالعربية
- description: text - الوصف بالإنجليزية
- description_ar: text - الوصف بالعربية
- assigned_to: bigint (foreign key to users)
- created_by: bigint (foreign key to users)
- department_id: bigint (foreign key to departments)
- priority: enum('low', 'medium', 'high')
- status: enum('pending', 'in_progress', 'completed', 'on_hold')
- creator_can_update_status: boolean
- category: varchar(100)
- repeat_type: enum('one_time', 'daily', 'quarterly', 'yearly')
- due_date: date - تاريخ الاستحقاق
- due_time: time - وقت الاستحقاق
- due_datetime: timestamp - التاريخ والوقت الكامل للاستحقاق
- sla_hours: integer - عدد الساعات المتوقعة للإنجاز (SLA)
- start_datetime: timestamp - تاريخ ووقت البداية المخطط
- end_datetime: timestamp - تاريخ ووقت النهاية المخطط
- actual_start_datetime: timestamp - تاريخ ووقت البدء الفعلي
- actual_end_datetime: timestamp - تاريخ ووقت الانتهاء الفعلي
- last_repeated_at: timestamp
- next_repeat_at: timestamp
- is_repeat_active: boolean
- created_at: timestamp
- updated_at: timestamp
```

### الملفات الرئيسية | Main Files

#### النموذج | Model
- `app/Models/Task.php` - نموذج المهام مع العلاقات والدوال المساعدة

#### المتحكم | Controller
- `app/Http/Controllers/TaskController.php` - منطق إدارة المهام والصلاحيات

#### العروض | Views
- `resources/views/tasks/index.blade.php` - قائمة المهام مع الفلاتر
- `resources/views/tasks/create.blade.php` - إنشاء مهمة جديدة
- `resources/views/tasks/edit.blade.php` - تعديل مهمة
- `resources/views/tasks/show.blade.php` - عرض تفاصيل المهمة

#### المايجريشن | Migration
- `database/migrations/2025_10_12_150000_update_tasks_table_for_advanced_features.php`

#### الترجمات | Translations
- `lang/ar/messages.php` - الترجمات العربية
- `lang/en/messages.php` - الترجمات الإنجليزية

---

## الراوتس | Routes

```php
// Tasks Routes
Route::resource('tasks', TaskController::class);
Route::patch('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
```

### الراوتس المتاحة | Available Routes

- `GET /tasks` - عرض قائمة المهام | View tasks list
- `GET /tasks/create` - صفحة إنشاء مهمة | Create task page
- `POST /tasks` - حفظ مهمة جديدة | Store new task
- `GET /tasks/{task}` - عرض تفاصيل مهمة | Show task details
- `GET /tasks/{task}/edit` - صفحة تعديل مهمة | Edit task page
- `PUT /tasks/{task}` - تحديث مهمة | Update task
- `DELETE /tasks/{task}` - حذف مهمة | Delete task
- `PATCH /tasks/{task}/update-status` - تحديث حالة المهمة | Update task status

---

## دوال مساعدة في النموذج | Helper Methods in Model

### التحقق من الصلاحيات | Permission Checks

```php
// التحقق من صلاحية تحديث الحالة
$task->canUserUpdateStatus($userId): bool

// التحقق من صلاحية تحديث الأولوية
$task->canUserUpdatePriority($userId): bool
```

### حساب التكرار | Repeat Calculation

```php
// حساب تاريخ التكرار التالي
$task->calculateNextRepeatDate(): Carbon|null

// Scopes للمهام المتكررة
Task::activeRepeating()->get();
Task::dueForRepeat()->get();
```

---

## الفلاتر المتاحة | Available Filters

في صفحة القائمة يمكن الفلترة حسب:

- الحالة (Status)
- الأولوية (Priority)  
- نوع التكرار (Repeat Type)
- القسم (Department)
- المكلف بالمهمة (Assigned To)

---

## أمثلة الاستخدام | Usage Examples

### مثال 1: إنشاء مهمة لنفسي | Example 1: Create task for myself
```
1. اذهب إلى: المهام > إضافة مهمة جديدة
2. املأ العنوان والوصف
3. اختر نفسك في "المكلف بالمهمة"
4. حدد الحالة (سيظهر الحقل تلقائياً)
5. حدد الأولوية
6. اختر نوع التكرار
7. احفظ
```

### مثال 2: مدير يكلف موظف في قسمه | Example 2: Manager assigns task to department member
```
1. اذهب إلى: المهام > إضافة مهمة جديدة
2. املأ العنوان والوصف
3. اختر موظف من قسمك في "المكلف بالمهمة"
4. حدد الأولوية (حقل الحالة لن يظهر)
5. اختر نوع التكرار
6. احفظ
7. الموظف سيستطيع تحديث الحالة لاحقاً
```

### مثال 3: CEO يكلف أي موظف | Example 3: CEO assigns task to any employee
```
1. اذهب إلى: المهام > إضافة مهمة جديدة
2. املأ العنوان والوصف
3. اختر أي موظف في "المكلف بالمهمة"
4. حدد الأولوية
5. اختر نوع التكرار
6. احفظ
```

---

## ملاحظات مهمة | Important Notes

### الصلاحيات | Permissions
- ✅ يجب أن يكون المستخدم مسجل دخول
- ✅ User must be authenticated
- ✅ الصلاحيات تطبق تلقائياً حسب الدور
- ✅ Permissions are applied automatically based on role

### التكرار | Repeating
- 🔄 المهام المتكررة تحسب التاريخ التالي تلقائياً
- 🔄 Recurring tasks calculate next date automatically
- 🔄 يمكن إيقاف/تفعيل التكرار
- 🔄 Repeat can be paused/activated

### الحذف | Deletion
- ⚠️ فقط منشئ المهمة أو CEO يمكنه الحذف
- ⚠️ Only creator or CEO can delete tasks
- ⚠️ التعليقات المرتبطة بالمهمة تحذف تلقائياً
- ⚠️ Related comments are deleted automatically

---

## التطوير المستقبلي | Future Enhancements

### اقتراحات للتطوير | Suggested Improvements

1. **إشعارات تلقائية** عند اقتراب موعد المهمة
2. **Automatic notifications** when task due date approaches
3. **تصدير المهام** إلى Excel/PDF
4. **Export tasks** to Excel/PDF
5. **لوحة تحكم للمهام** مع إحصائيات
6. **Dashboard** with task statistics
7. **مرفقات للمهام** (صور، ملفات)
8. **Task attachments** (images, files)
9. **تكامل مع التقويم** (Google Calendar, Outlook)
10. **Calendar integration** (Google Calendar, Outlook)

---

## المساعدة والدعم | Help & Support

للمزيد من المساعدة:
- 📧 راسل فريق التطوير | Contact development team
- 📖 راجع التوثيق الفني | Review technical documentation
- 🐛 أبلغ عن الأخطاء | Report bugs

---

تم التطوير بواسطة: AI Assistant with Claude Sonnet 4.5
تاريخ: 12 أكتوبر 2025

Developed by: AI Assistant with Claude Sonnet 4.5
Date: October 12, 2025

