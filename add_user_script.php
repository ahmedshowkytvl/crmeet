<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

echo "=== إضافة مستخدم جديد ===\n";
echo "=== Add New User ===\n\n";

/**
 * بيانات المستخدم المطلوب إضافته
 * User data to be added
 */
$userData = [
    // المعلومات الأساسية - Basic Information
    'name' => 'أحمد محمد علي', // الاسم بالإنجليزية
    'name_ar' => 'أحمد محمد علي', // الاسم بالعربية
    'email' => 'ahmed.mohamed@stafftobia.com', // البريد الإلكتروني
    'password' => 'password123', // كلمة المرور
    
    // معلومات العمل - Work Information
    'job_title' => 'Software Developer', // المسمى الوظيفي بالإنجليزية
    'position' => 'Software Developer', // المنصب بالإنجليزية
    'position_ar' => 'مطور برمجيات', // المنصب بالعربية
    
    // معلومات الاتصال - Contact Information
    'phone_work' => '966112345678', // هاتف العمل
    'phone_personal' => '966501234567', // الهاتف الشخصي
    'work_email' => 'ahmed.mohamed@stafftobia.com', // البريد الإلكتروني للعمل
    
    // معلومات إضافية - Additional Information
    'address' => 'Riyadh, Saudi Arabia', // العنوان بالإنجليزية
    'address_ar' => 'الرياض، المملكة العربية السعودية', // العنوان بالعربية
    'birth_date' => '1990-01-15', // تاريخ الميلاد
    'nationality' => 'Saudi', // الجنسية
    'city' => 'Riyadh', // المدينة
    'country' => 'Saudi Arabia', // البلد
    
    // معلومات النظام - System Information
    'role_slug' => 'software_developer', // دور المستخدم (software_developer, ceo, head_manager, manager, team_leader, employee, supplier)
    'department_name' => 'IT', // اسم القسم (IT, HR, Accounts, Operation, Admin, etc.)
    'manager_email' => null, // بريد المدير المباشر (اختياري)
    
    // معلومات Microsoft Teams
    'microsoft_teams_id' => 'ahmed.mohamed@stafftobia.com',
    
    // معلومات Zoho (اختيارية)
    'zoho_agent_name' => 'Ahmed Mohamed',
    'zoho_email' => 'ahmed.mohamed@stafftobia.com',
    'is_zoho_enabled' => true,
];

echo "📋 بيانات المستخدم:\n";
echo "User Data:\n";
echo "  الاسم: {$userData['name']}\n";
echo "  Name: {$userData['name']}\n";
echo "  البريد الإلكتروني: {$userData['email']}\n";
echo "  Email: {$userData['email']}\n";
echo "  المسمى الوظيفي: {$userData['position_ar']}\n";
echo "  Job Title: {$userData['position']}\n";
echo "  الدور: {$userData['role_slug']}\n";
echo "  Role: {$userData['role_slug']}\n";
echo "  القسم: {$userData['department_name']}\n";
echo "  Department: {$userData['department_name']}\n\n";

try {
    // التحقق من وجود المستخدم
    echo "🔍 التحقق من وجود المستخدم...\n";
    echo "Checking if user exists...\n";
    
    $existingUser = User::where('email', $userData['email'])->first();
    if ($existingUser) {
        echo "⚠️  المستخدم موجود بالفعل!\n";
        echo "⚠️  User already exists!\n";
        echo "   ID: {$existingUser->id}\n";
        echo "   Name: {$existingUser->name}\n";
        echo "   Email: {$existingUser->email}\n";
        return;
    }
    
    echo "✅ المستخدم غير موجود، سيتم إنشاؤه...\n";
    echo "✅ User doesn't exist, will be created...\n\n";
    
    // الحصول على الدور
    echo "🔍 البحث عن الدور...\n";
    echo "Looking for role...\n";
    
    $role = Role::where('slug', $userData['role_slug'])->first();
    if (!$role) {
        echo "❌ الدور غير موجود: {$userData['role_slug']}\n";
        echo "❌ Role not found: {$userData['role_slug']}\n";
        echo "الأدوار المتاحة:\n";
        echo "Available roles:\n";
        $availableRoles = Role::active()->ordered()->get();
        foreach ($availableRoles as $availableRole) {
            echo "  - {$availableRole->slug} ({$availableRole->name_ar})\n";
        }
        return;
    }
    
    echo "✅ تم العثور على الدور: {$role->name_ar} (ID: {$role->id})\n";
    echo "✅ Role found: {$role->name_ar} (ID: {$role->id})\n\n";
    
    // الحصول على القسم
    echo "🔍 البحث عن القسم...\n";
    echo "Looking for department...\n";
    
    $department = Department::where('name', 'LIKE', "%{$userData['department_name']}%")
                           ->orWhere('name_ar', 'LIKE', "%{$userData['department_name']}%")
                           ->orWhere('code', 'LIKE', "%{$userData['department_name']}%")
                           ->first();
    
    if (!$department) {
        echo "❌ القسم غير موجود: {$userData['department_name']}\n";
        echo "❌ Department not found: {$userData['department_name']}\n";
        echo "الأقسام المتاحة:\n";
        echo "Available departments:\n";
        $availableDepartments = Department::active()->ordered()->get();
        foreach ($availableDepartments as $availableDept) {
            echo "  - {$availableDept->name_ar} ({$availableDept->code})\n";
        }
        return;
    }
    
    echo "✅ تم العثور على القسم: {$department->name_ar} (ID: {$department->id})\n";
    echo "✅ Department found: {$department->name_ar} (ID: {$department->id})\n\n";
    
    // الحصول على المدير (إذا تم تحديده)
    $manager = null;
    if ($userData['manager_email']) {
        echo "🔍 البحث عن المدير...\n";
        echo "Looking for manager...\n";
        
        $manager = User::where('email', $userData['manager_email'])->first();
        if ($manager) {
            echo "✅ تم العثور على المدير: {$manager->name_ar} (ID: {$manager->id})\n";
            echo "✅ Manager found: {$manager->name_ar} (ID: {$manager->id})\n\n";
        } else {
            echo "⚠️  المدير غير موجود: {$userData['manager_email']}\n";
            echo "⚠️  Manager not found: {$userData['manager_email']}\n\n";
        }
    }
    
    // إنشاء المستخدم
    echo "👤 إنشاء المستخدم...\n";
    echo "Creating user...\n";
    
    $user = User::create([
        'name' => $userData['name'],
        'name_ar' => $userData['name_ar'],
        'email' => $userData['email'],
        'password' => Hash::make($userData['password']),
        'role_id' => $role->id,
        'department_id' => $department->id,
        'manager_id' => $manager ? $manager->id : null,
        'phone_work' => $userData['phone_work'],
        'phone_personal' => $userData['phone_personal'],
        'work_email' => $userData['work_email'],
        'job_title' => $userData['job_title'],
        'position' => $userData['position'],
        'position_ar' => $userData['position_ar'],
        'address' => $userData['address'],
        'address_ar' => $userData['address_ar'],
        'birth_date' => $userData['birth_date'],
        'nationality' => $userData['nationality'],
        'city' => $userData['city'],
        'country' => $userData['country'],
        'microsoft_teams_id' => $userData['microsoft_teams_id'],
        'zoho_agent_name' => $userData['zoho_agent_name'],
        'zoho_email' => $userData['zoho_email'],
        'is_zoho_enabled' => $userData['is_zoho_enabled'],
        'zoho_linked_at' => $userData['is_zoho_enabled'] ? now() : null,
        'created_by' => 1, // System Administrator
        'is_active' => true,
    ]);
    
    echo "✅ تم إنشاء المستخدم بنجاح!\n";
    echo "✅ User created successfully!\n";
    echo "   ID: {$user->id}\n";
    echo "   Name: {$user->name_ar}\n";
    echo "   Email: {$user->email}\n";
    echo "   Role: {$role->name_ar}\n";
    echo "   Department: {$department->name_ar}\n";
    if ($manager) {
        echo "   Manager: {$manager->name_ar}\n";
    }
    echo "   Created: {$user->created_at}\n\n";
    
    echo "🎉 تمت العملية بنجاح!\n";
    echo "🎉 Operation completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء المستخدم:\n";
    echo "❌ Error creating user:\n";
    echo "   {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}\n";
    echo "   Line: {$e->getLine()}\n";
}

echo "\n=== انتهاء العملية ===\n";
echo "=== End of Process ===\n";
