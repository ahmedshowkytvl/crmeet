<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== فحص المستخدمين الذين لا يزالون بدون تاريخ ميلاد ===\n\n";

try {
    // جلب المستخدمين بدون تاريخ ميلاد
    $usersWithoutBirthday = DB::table('users')
        ->whereNull('birthday')
        ->whereNull('birth_date')
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->select('id', 'name', 'email', 'employee_id', 'EmployeeCode', 'created_at')
        ->orderBy('id')
        ->get();
    
    echo "عدد المستخدمين بدون تاريخ ميلاد: " . $usersWithoutBirthday->count() . "\n\n";
    
    if ($usersWithoutBirthday->count() > 0) {
        echo "=== تفاصيل المستخدمين بدون تاريخ ميلاد ===\n";
        echo str_pad("ID", 5) . " | " . 
             str_pad("الاسم", 40) . " | " . 
             str_pad("الإيميل", 35) . " | " . 
             str_pad("كود الموظف", 15) . " | " . 
             str_pad("تاريخ الإنشاء", 20) . "\n";
        echo str_repeat("-", 120) . "\n";
        
        foreach ($usersWithoutBirthday as $user) {
            echo str_pad($user->id, 5) . " | " . 
                 str_pad(substr($user->name, 0, 40), 40) . " | " . 
                 str_pad(substr($user->email, 0, 35), 35) . " | " . 
                 str_pad($user->EmployeeCode ?? $user->employee_id ?? 'N/A', 15) . " | " . 
                 str_pad($user->created_at, 20) . "\n";
        }
        
        echo "\n";
        
        // إحصائيات إضافية
        echo "=== إحصائيات إضافية ===\n";
        
        // المستخدمين حسب نوع المستخدم
        $byUserType = DB::table('users')
            ->whereNull('birthday')
            ->whereNull('birth_date')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->select('user_type', DB::raw('count(*) as count'))
            ->groupBy('user_type')
            ->get();
        
        echo "حسب نوع المستخدم:\n";
        foreach ($byUserType as $type) {
            echo "- {$type->user_type}: {$type->count} مستخدم\n";
        }
        
        echo "\n";
        
        // المستخدمين حسب القسم
        $byDepartment = DB::table('users')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->whereNull('users.birthday')
            ->whereNull('users.birth_date')
            ->whereNotNull('users.email')
            ->where('users.email', '!=', '')
            ->select('departments.name as department_name', DB::raw('count(*) as count'))
            ->groupBy('departments.name')
            ->orderBy('count', 'desc')
            ->get();
        
        echo "حسب القسم:\n";
        foreach ($byDepartment as $dept) {
            $deptName = $dept->department_name ?: 'غير محدد';
            echo "- {$deptName}: {$dept->count} مستخدم\n";
        }
        
        echo "\n";
        
        // المستخدمين حسب تاريخ الإنشاء
        $byCreationDate = DB::table('users')
            ->whereNull('birthday')
            ->whereNull('birth_date')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->select(DB::raw('DATE(created_at) as creation_date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('creation_date', 'desc')
            ->get();
        
        echo "حسب تاريخ الإنشاء:\n";
        foreach ($byCreationDate as $date) {
            echo "- {$date->creation_date}: {$date->count} مستخدم\n";
        }
        
    } else {
        echo "🎉 جميع المستخدمين لديهم تواريخ ميلاد!\n";
    }
    
    echo "\n=== انتهى الفحص ===\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "الملف: " . $e->getFile() . "\n";
    echo "السطر: " . $e->getLine() . "\n";
    exit(1);
}

