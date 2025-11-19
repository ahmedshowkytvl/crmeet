# نظام إدارة الموظفين المتقدم
## Advanced Employee Management System

### نظرة عامة | Overview
تم إعادة هيكلة نظام إدارة الموظفين ليشمل نظام أدوار متقدم مع دعم كامل للغتين العربية والإنجليزية.

The employee management system has been restructured to include an advanced role-based system with full support for both Arabic and English languages.

---

## الأدوار المتاحة | Available Roles

### 1. مطور البرمجيات | Software Developer
- **الصلاحيات**: صلاحية كاملة لجميع ميزات النظام
- **Permissions**: Full access to all system features
- **الوصف**: يمكن الوصول لجميع أجزاء النظام وأدوات التطوير
- **Description**: Complete access to all system components and development tools

### 2. الرئيس التنفيذي | CEO
- **الصلاحيات**: صلاحية كاملة لجميع ميزات النظام وعمليات الشركة
- **Permissions**: Full access to all system features and company-wide operations
- **الوصف**: إدارة شاملة للشركة وجميع العمليات
- **Description**: Comprehensive company management and all operations

### 3. المدير الرئيسي | Head Manager
- **الصلاحيات**: إدارة الفريق والمهام وعمليات القسم
- **Permissions**: Team management, tasks, and department operations
- **الوصف**: إدارة كاملة للفريق والمهام المرتبطة بالقسم
- **Description**: Complete team and department task management

### 4. قائد الفريق | Team Leader
- **الصلاحيات**: عرض وإدارة أعضاء الفريق ومهامهم
- **Permissions**: View and manage team members and their tasks
- **الوصف**: إدارة فريق محدود مع صلاحيات محددة
- **Description**: Limited team management with specific permissions

### 5. الموظف | Employee
- **الصلاحيات**: الملف الشخصي ومعلومات الاتصال بالفريق والمهام المشتركة
- **Permissions**: Own profile, team contact info, and shared tasks
- **الوصف**: يمكن رؤية زملاء العمل وأرقام هواتفهم والمهام المشتركة
- **Description**: Can view colleagues, their work phones, and shared tasks

---

## الميزات الجديدة | New Features

### 1. الملف الشخصي للموظف | Employee Profile
- **صورة شخصية** | Profile Picture
- **رقم الموظف (HRID)** | Employee Number
- **تاريخ التعيين** | Hiring Date (default: today)
- **البريد الإلكتروني** | Email (uses department global email if empty)
- **معرف Microsoft Teams** | Microsoft Teams ID (default: employee email)
- **رقم AVAYA الداخلي** | AVAYA Extension
- **أرقام الهواتف** | Phone Numbers (WORK/PERSONAL/OTHER)
- **مستندات التعيين** | Hiring Documents
- **العنوان** | Address (EN/AR)
- **الوظيفة** | Position/Job Title (EN/AR)
- **القسم** | Department (foreign key)
- **الفرع** | Branch (foreign key)

### 2. إدارة الأقسام | Department Management
- **الاسم** | Name (EN/AR)
- **الوصف** | Description (EN/AR)
- **البريد الإلكتروني العام** | Global Email
- **المدير الرئيسي** | Head Manager (must belong to same department)
- **قادة الفرق** | Team Leaders (must belong to same department)
- **الهيكل الهرمي** | Hierarchy/Matrix Structure
- **الرقم الداخلي** | Extension

### 3. إدارة الفروع | Branch Management
- **الاسم** | Name (EN/AR)
- **الوصف** | Description (EN/AR)
- **رمز الفرع** | Branch Code
- **العنوان** | Address (EN/AR)
- **المدينة** | City (EN/AR)
- **الدولة** | Country (EN/AR)
- **الرمز البريدي** | Postal Code
- **الهاتف** | Phone
- **البريد الإلكتروني** | Email
- **اسم المدير** | Manager Name (EN/AR)

### 4. نظام الصلاحيات | Permission System
- **إدارة المستخدمين** | User Management
- **إدارة المهام** | Task Management
- **إدارة الأقسام** | Department Management
- **إدارة الطلبات** | Request Management
- **التقارير** | Reports
- **الإعدادات** | Settings

---

## قاعدة البيانات | Database Schema

### الجداول الجديدة | New Tables
1. **roles** - الأدوار
2. **branches** - الفروع
3. **phone_types** - أنواع أرقام الهواتف
4. **user_phones** - أرقام هواتف المستخدمين
5. **hiring_documents** - مستندات التعيين

### الجداول المحدثة | Updated Tables
1. **users** - المستخدمون (مع حقول جديدة)
2. **departments** - الأقسام (مع دعم اللغات المتعددة)
3. **permissions** - الصلاحيات (مع الترجمة العربية)
4. **role_permissions** - صلاحيات الأدوار (محدث)

---

## نظام اللغات | Language System

### اللغة الأساسية | Primary Language
- **الإنجليزية** | English
- جميع أسماء الحقول والجداول باللغة الإنجليزية
- All field names and table names in English

### اللغة الثانوية | Secondary Language
- **العربية** | Arabic
- جميع المحتوى والواجهات مدعومة بالعربية
- All content and interfaces supported in Arabic

---

## قواعد النظام | System Rules

### 1. تتبع البيانات | Data Tracking
- **من أنشأها** | Created By (user_id)
- **تاريخ الإنشاء** | Created At (date)
- **الوقت بالتحديد** | Exact Timestamp

### 2. العلاقات المنطقية | Logical Relationships
- **المدير الرئيسي** يجب أن يكون من نفس القسم
- **Head Manager** must belong to same department
- **قادة الفرق** يجب أن يكونوا من نفس القسم
- **Team Leaders** must belong to same department

### 3. نظام الصلاحيات | Permission System
- **لا تعارض** بين صلاحيات الأدوار المختلفة
- **No conflicts** between different role permissions
- **صلاحيات متسقة** ومنطقية
- **Consistent and logical** permissions

---

## الاستخدام | Usage

### 1. إدارة الأدوار | Role Management
```php
// إنشاء دور جديد
$role = Role::create([
    'name' => 'Software Developer',
    'name_ar' => 'مطور برمجيات',
    'slug' => 'software_developer',
    'description' => 'Full access to all system features',
    'description_ar' => 'صلاحية كاملة لجميع ميزات النظام'
]);

// تعيين صلاحيات
$role->permissions()->attach($permissionIds);
```

### 2. إدارة الملف الشخصي | Profile Management
```php
// تحديث الملف الشخصي
$user->update([
    'hrid' => 'EMP001',
    'position' => 'Senior Developer',
    'position_ar' => 'مطور أول',
    'microsoft_teams_id' => 'john.doe@company.com'
]);

// إضافة رقم هاتف
UserPhone::create([
    'user_id' => $user->id,
    'phone_type_id' => $workPhoneType->id,
    'phone_number' => '1234567890',
    'country_code' => '+966',
    'is_primary' => true
]);
```

### 3. التحقق من الصلاحيات | Permission Checking
```php
// التحقق من صلاحية
if ($user->hasPermission('users.create')) {
    // يمكن إنشاء مستخدمين
}

// التحقق من إدارة مستخدم
if ($user->canManageUser($targetUser)) {
    // يمكن إدارة هذا المستخدم
}
```

---

## الملفات المهمة | Important Files

### المتحكمات | Controllers
- `RoleController.php` - إدارة الأدوار
- `EmployeeProfileController.php` - إدارة الملف الشخصي

### النماذج | Models
- `Role.php` - نموذج الدور
- `Branch.php` - نموذج الفرع
- `PhoneType.php` - نموذج نوع الهاتف
- `UserPhone.php` - نموذج هاتف المستخدم
- `HiringDocument.php` - نموذج مستند التعيين

### المايجريشنز | Migrations
- `2025_01_16_100000_create_roles_table.php`
- `2025_01_16_100100_create_branches_table.php`
- `2025_01_16_100200_create_phone_types_table.php`
- `2025_01_16_100300_create_hiring_documents_table.php`
- `2025_01_16_100400_create_user_phones_table.php`
- `2025_01_16_100500_update_departments_table.php`
- `2025_01_16_100600_update_users_table_for_employee_profile.php`
- `2025_01_16_101100_fix_roles_system.php`
- `2025_01_16_101200_assign_permissions_to_new_roles.php`

### ملفات الترجمة | Translation Files
- `lang/en/messages.php` - الترجمة الإنجليزية
- `lang/ar/messages.php` - الترجمة العربية

---

## التثبيت | Installation

### 1. تشغيل المايجريشنز | Run Migrations
```bash
php artisan migrate
```

### 2. تشغيل البذور | Run Seeders
```bash
php artisan db:seed
```

### 3. مسح الكاش | Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## الدعم | Support

للمساعدة أو الاستفسارات، يرجى التواصل مع فريق التطوير.

For help or inquiries, please contact the development team.

---

**تم التطوير بواسطة** | **Developed by**: فريق تطوير نظام إدارة الموظفين  
**تاريخ التحديث** | **Last Updated**: 2025-01-16  
**الإصدار** | **Version**: 2.0.0
