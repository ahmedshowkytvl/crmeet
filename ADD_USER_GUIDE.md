# دليل إضافة المستخدمين الجدد
## Guide for Adding New Users

### نظرة عامة
هذا السكريبت يسمح لك بإضافة مستخدمين جدد إلى نظام إدارة الموظفين مع جميع المعلومات المطلوبة باللغة العربية والإنجليزية.

### كيفية الاستخدام

#### 1. تشغيل السكريبت
```bash
php add_user_script.php
```

#### 2. تخصيص بيانات المستخدم
قم بتعديل المتغير `$userData` في السكريبت لتشمل معلومات المستخدم المطلوب إضافته:

```php
$userData = [
    // المعلومات الأساسية
    'name' => 'أحمد محمد علي', // الاسم بالإنجليزية
    'name_ar' => 'أحمد محمد علي', // الاسم بالعربية
    'email' => 'ahmed.mohamed@stafftobia.com', // البريد الإلكتروني
    'password' => 'password123', // كلمة المرور
    
    // معلومات العمل
    'job_title' => 'Software Developer', // المسمى الوظيفي بالإنجليزية
    'position' => 'Software Developer', // المنصب بالإنجليزية
    'position_ar' => 'مطور برمجيات', // المنصب بالعربية
    
    // معلومات الاتصال
    'phone_work' => '966112345678', // هاتف العمل
    'phone_personal' => '966501234567', // الهاتف الشخصي
    'work_email' => 'ahmed.mohamed@stafftobia.com', // البريد الإلكتروني للعمل
    
    // معلومات إضافية
    'address' => 'Riyadh, Saudi Arabia', // العنوان بالإنجليزية
    'address_ar' => 'الرياض، المملكة العربية السعودية', // العنوان بالعربية
    'birth_date' => '1990-01-15', // تاريخ الميلاد
    'nationality' => 'Saudi', // الجنسية
    'city' => 'Riyadh', // المدينة
    'country' => 'Saudi Arabia', // البلد
    
    // معلومات النظام
    'role_slug' => 'software_developer', // دور المستخدم
    'department_name' => 'IT', // اسم القسم
    'manager_email' => null, // بريد المدير المباشر (اختياري)
    
    // معلومات Microsoft Teams
    'microsoft_teams_id' => 'ahmed.mohamed@stafftobia.com',
    
    // معلومات Zoho (اختيارية)
    'zoho_agent_name' => 'Ahmed Mohamed',
    'zoho_email' => 'ahmed.mohamed@stafftobia.com',
    'is_zoho_enabled' => true,
];
```

### الأدوار المتاحة
- `software_developer` - مطور برمجيات
- `ceo` - الرئيس التنفيذي
- `head_manager` - المدير العام
- `manager` - مدير
- `team_leader` - قائد الفريق
- `employee` - موظف
- `supplier` - مورد

### الأقسام المتاحة
- `IT` - تقنية المعلومات
- `HR` - الموارد البشرية
- `Accounts` - المحاسبة
- `Operation` - التشغيل
- `Admin` - الإدارة
- `Marketing` - التسويق
- `Commercial` - التجاري
- `Traffic` - المرور
- `Contracting Egypt` - المقاولات مصر
- `Contracting Middle East` - المقاولات الشرق الأوسط
- `Contracting International` - المقاولات الدولية
- `Internet` - الإنترنت

### الميزات
- ✅ التحقق من وجود المستخدم مسبقاً
- ✅ التحقق من صحة الدور والقسم
- ✅ ربط المستخدم بالمدير المباشر
- ✅ دعم كامل للغة العربية والإنجليزية
- ✅ تكامل مع Microsoft Teams
- ✅ تكامل مع نظام Zoho
- ✅ رسائل خطأ واضحة باللغة العربية

### مثال على الاستخدام

#### إضافة مطور برمجيات جديد:
```php
$userData = [
    'name' => 'سارة أحمد',
    'name_ar' => 'سارة أحمد',
    'email' => 'sara.ahmed@stafftobia.com',
    'password' => 'securePassword123',
    'job_title' => 'Senior Software Developer',
    'position' => 'Senior Software Developer',
    'position_ar' => 'مطور برمجيات أول',
    'phone_work' => '966112345679',
    'phone_personal' => '966501234568',
    'work_email' => 'sara.ahmed@stafftobia.com',
    'address' => 'Jeddah, Saudi Arabia',
    'address_ar' => 'جدة، المملكة العربية السعودية',
    'birth_date' => '1988-05-20',
    'nationality' => 'Saudi',
    'city' => 'Jeddah',
    'country' => 'Saudi Arabia',
    'role_slug' => 'software_developer',
    'department_name' => 'IT',
    'manager_email' => 'ahmed.mohamed@stafftobia.com', // المدير المباشر
    'microsoft_teams_id' => 'sara.ahmed@stafftobia.com',
    'zoho_agent_name' => 'Sara Ahmed',
    'zoho_email' => 'sara.ahmed@stafftobia.com',
    'is_zoho_enabled' => true,
];
```

### ملاحظات مهمة
1. تأكد من أن البريد الإلكتروني فريد ولا يستخدم من قبل
2. تأكد من أن الدور والقسم موجودان في النظام
3. إذا حددت مديراً مباشراً، تأكد من وجوده في النظام
4. كلمة المرور يجب أن تكون آمنة ومعقدة
5. جميع التواريخ يجب أن تكون بصيغة `YYYY-MM-DD`

### استكشاف الأخطاء
إذا واجهت أي مشاكل:
1. تأكد من أن قاعدة البيانات متصلة
2. تأكد من أن جميع الجداول المطلوبة موجودة
3. تحقق من رسائل الخطأ المعروضة
4. تأكد من صحة البيانات المدخلة

### الدعم
للمساعدة أو الاستفسارات، يرجى التواصل مع فريق تقنية المعلومات.
