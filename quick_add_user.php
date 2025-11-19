<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

echo "=== إضافة مستخدم سريع ===\n";
echo "=== Quick User Addition ===\n\n";

/**
 * قم بتعديل هذه البيانات لإضافة المستخدم المطلوب
 * Modify this data to add the required user
 */
$name = 'أحمد محمد علي'; // الاسم العربي
$nameEn = 'Ahmed Mohamed Ali'; // الاسم الإنجليزي
$email = 'ahmed.mohamed@stafftobia.com'; // البريد الإلكتروني
$password = 'password123'; // كلمة المرور
$roleSlug = 'software_developer'; // الدور (software_developer, ceo, head_manager, manager, team_leader, employee, supplier)
$departmentName = 'IT'; // القسم (IT, HR, Accounts, Operation, Admin, Marketing, etc.)
$phoneWork = '966112345678'; // هاتف العمل
$phonePersonal = '966501234567'; // الهاتف الشخصي
$jobTitle = 'Software Developer'; // المسمى الوظيفي بالإنجليزية
$jobTitleAr = 'مطور برمجيات'; // المسمى الوظيفي بالعربية
$managerEmail = null; // بريد المدير المباشر (اختياري)

echo "📋 بيانات المستخدم:\n";
echo "User Data:\n";
echo "  الاسم: {$name}\n";
echo "  Name: {$nameEn}\n";
echo "  البريد الإلكتروني: {$email}\n";
echo "  Email: {$email}\n";
echo "  الدور: {$roleSlug}\n";
echo "  Role: {$roleSlug}\n";
echo "  القسم: {$departmentName}\n";
echo "  Department: {$departmentName}\n";
echo "  المسمى الوظيفي: {$jobTitleAr}\n";
echo "  Job Title: {$jobTitle}\n\n";

try {
    // التحقق من وجود المستخدم
    $existingUser = User::where('email', $email)->first();
    if ($existingUser) {
        echo "⚠️  المستخدم موجود بالفعل!\n";
        echo "⚠️  User already exists!\n";
        echo "   ID: {$existingUser->id}\n";
        echo "   Name: {$existingUser->name_ar}\n";
        echo "   Email: {$existingUser->email}\n";
        return;
    }
    
    // الحصول على الدور
    $role = Role::where('slug', $roleSlug)->first();
    if (!$role) {
        echo "❌ الدور غير موجود: {$roleSlug}\n";
        echo "❌ Role not found: {$roleSlug}\n";
        echo "الأدوار المتاحة:\n";
        echo "Available roles:\n";
        $availableRoles = Role::active()->ordered()->get();
        foreach ($availableRoles as $availableRole) {
            echo "  - {$availableRole->slug} ({$availableRole->name_ar})\n";
        }
        return;
    }
    
    // الحصول على القسم
    $department = Department::where('name', 'LIKE', "%{$departmentName}%")
                           ->orWhere('name_ar', 'LIKE', "%{$departmentName}%")
                           ->orWhere('code', 'LIKE', "%{$departmentName}%")
                           ->first();
    
    if (!$department) {
        echo "❌ القسم غير موجود: {$departmentName}\n";
        echo "❌ Department not found: {$departmentName}\n";
        echo "الأقسام المتاحة:\n";
        echo "Available departments:\n";
        $availableDepartments = Department::active()->ordered()->get();
        foreach ($availableDepartments as $availableDept) {
            echo "  - {$availableDept->name_ar} ({$availableDept->code})\n";
        }
        return;
    }
    
    // الحصول على المدير (إذا تم تحديده)
    $manager = null;
    if ($managerEmail) {
        $manager = User::where('email', $managerEmail)->first();
        if (!$manager) {
            echo "⚠️  المدير غير موجود: {$managerEmail}\n";
            echo "⚠️  Manager not found: {$managerEmail}\n";
        }
    }
    
    // إنشاء المستخدم
    $user = User::create([
        'name' => $nameEn,
        'name_ar' => $name,
        'email' => $email,
        'password' => Hash::make($password),
        'role_id' => $role->id,
        'department_id' => $department->id,
        'manager_id' => $manager ? $manager->id : null,
        'phone_work' => $phoneWork,
        'phone_personal' => $phonePersonal,
        'work_email' => $email,
        'job_title' => $jobTitle,
        'position' => $jobTitle,
        'position_ar' => $jobTitleAr,
        'microsoft_teams_id' => $email,
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
}

echo "\n=== انتهاء العملية ===\n";
echo "=== End of Process ===\n";
