<?php
/**
 * سكريبت فحص أخطاء الأسماء في جدول المستخدمين
 * - البحث عن أسماء مكررة
 * - التحقق من الترجمة العربية
 * - البحث عن أسماء غير عربية في الحقول العربية
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 بدء فحص أخطاء الأسماء في جدول المستخدمين...\n\n";

$errors = [];
$warnings = [];

// 1. البحث عن أسماء مكررة
echo "📋 1. فحص الأسماء المكررة...\n";
$duplicateNames = DB::table('users')
    ->select('name', DB::raw('COUNT(*) as count'))
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->groupBy('name')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($duplicateNames->count() > 0) {
    echo "   ⚠️  وجدت " . $duplicateNames->count() . " أسماء مكررة:\n";
    foreach ($duplicateNames as $dup) {
        $users = DB::table('users')
            ->where('name', $dup->name)
            ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);
        
        echo "   - الاسم: '{$dup->name}' (مكرر {$dup->count} مرات)\n";
        foreach ($users as $user) {
            echo "     → ID: {$user->id}, Employee ID: " . ($user->employee_id ?: 'N/A') . ", Email: {$user->email}\n";
        }
        echo "\n";
    }
    $warnings[] = "الأسماء المكررة: " . $duplicateNames->count();
} else {
    echo "   ✅ لا توجد أسماء مكررة\n";
}

// 2. البحث عن أسماء عربية مكررة
echo "\n📋 2. فحص الأسماء العربية المكررة...\n";
$duplicateNamesAr = DB::table('users')
    ->select('name_ar', DB::raw('COUNT(*) as count'))
    ->whereNotNull('name_ar')
    ->where('name_ar', '!=', '')
    ->groupBy('name_ar')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($duplicateNamesAr->count() > 0) {
    echo "   ⚠️  وجدت " . $duplicateNamesAr->count() . " أسماء عربية مكررة:\n";
    foreach ($duplicateNamesAr as $dup) {
        $users = DB::table('users')
            ->where('name_ar', $dup->name_ar)
            ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);
        
        echo "   - الاسم العربي: '{$dup->name_ar}' (مكرر {$dup->count} مرات)\n";
        foreach ($users as $user) {
            echo "     → ID: {$user->id}, Employee ID: " . ($user->employee_id ?: 'N/A') . ", Email: {$user->email}\n";
        }
        echo "\n";
    }
    $warnings[] = "الأسماء العربية المكررة: " . $duplicateNamesAr->count();
} else {
    echo "   ✅ لا توجد أسماء عربية مكررة\n";
}

// 3. فحص المستخدمين الذين لديهم name_ar فارغ أو نفس name
echo "\n📋 3. فحص المستخدمين بدون ترجمة عربية أو بأسماء متطابقة...\n";
$usersWithoutArabic = DB::table('users')
    ->where(function($query) {
        $query->whereNull('name_ar')
              ->orWhere('name_ar', '')
              ->orWhereColumn('name_ar', 'name');
    })
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);

if ($usersWithoutArabic->count() > 0) {
    echo "   ⚠️  وجدت " . $usersWithoutArabic->count() . " مستخدمين بدون ترجمة عربية صحيحة:\n";
    foreach ($usersWithoutArabic as $user) {
        $status = '';
        if (empty($user->name_ar)) {
            $status = 'فارغ';
        } elseif ($user->name_ar === $user->name) {
            $status = 'متطابق مع الاسم الإنجليزي';
        }
        
        echo "   - ID: {$user->id}, Name: '{$user->name}', Name_AR: '{$user->name_ar}' ({$status})\n";
        echo "     Email: {$user->email}, Employee ID: " . ($user->employee_id ?: 'N/A') . "\n";
    }
    $warnings[] = "مستخدمين بدون ترجمة عربية: " . $usersWithoutArabic->count();
} else {
    echo "   ✅ جميع المستخدمين لديهم ترجمة عربية صحيحة\n";
}

// 4. فحص الأسماء التي تحتوي على أحرف إنجليزية في name_ar
echo "\n📋 4. فحص الأسماء العربية التي تحتوي على أحرف إنجليزية...\n";
$allUsers = DB::table('users')
    ->whereNotNull('name_ar')
    ->where('name_ar', '!=', '')
    ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);

$usersWithEnglishInArabic = [];
foreach ($allUsers as $user) {
    // التحقق من وجود أحرف إنجليزية (a-z, A-Z) في name_ar
    if (preg_match('/[a-zA-Z]/', $user->name_ar)) {
        // لكن نتجاهل الأسماء التي تحتوي على أرقام أو رموز فقط
        // أو الأسماء التي هي نفسها name (تم فحصها سابقاً)
        if ($user->name_ar !== $user->name) {
            $usersWithEnglishInArabic[] = $user;
        }
    }
}

if (count($usersWithEnglishInArabic) > 0) {
    echo "   ⚠️  وجدت " . count($usersWithEnglishInArabic) . " أسماء عربية تحتوي على أحرف إنجليزية:\n";
    foreach ($usersWithEnglishInArabic as $user) {
        echo "   - ID: {$user->id}, Name_AR: '{$user->name_ar}', Name: '{$user->name}'\n";
        echo "     Email: {$user->email}, Employee ID: " . ($user->employee_id ?: 'N/A') . "\n";
    }
    $warnings[] = "أسماء عربية تحتوي على إنجليزية: " . count($usersWithEnglishInArabic);
} else {
    echo "   ✅ جميع الأسماء العربية نظيفة\n";
}

// 5. فحص الأسماء التي تحتوي على أحرف عربية في name (يجب أن تكون إنجليزية)
echo "\n📋 5. فحص الأسماء الإنجليزية التي تحتوي على أحرف عربية...\n";
$usersWithArabicInEnglish = DB::table('users')
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);

$usersWithArabic = [];
foreach ($usersWithArabicInEnglish as $user) {
    // التحقق من وجود أحرف عربية في name
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $user->name)) {
        $usersWithArabic[] = $user;
    }
}

if (count($usersWithArabic) > 0) {
    echo "   ⚠️  وجدت " . count($usersWithArabic) . " أسماء إنجليزية تحتوي على أحرف عربية:\n";
    foreach ($usersWithArabic as $user) {
        echo "   - ID: {$user->id}, Name: '{$user->name}', Name_AR: '{$user->name_ar}'\n";
        echo "     Email: {$user->email}, Employee ID: " . ($user->employee_id ?: 'N/A') . "\n";
    }
    $warnings[] = "أسماء إنجليزية تحتوي على عربية: " . count($usersWithArabic);
} else {
    echo "   ✅ جميع الأسماء الإنجليزية نظيفة\n";
}

// 6. فحص الأسماء الفارغة
echo "\n📋 6. فحص الأسماء الفارغة...\n";
$usersWithEmptyNames = DB::table('users')
    ->where(function($query) {
        $query->whereNull('name')
              ->orWhere('name', '');
    })
    ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);

if ($usersWithEmptyNames->count() > 0) {
    echo "   ⚠️  وجدت " . $usersWithEmptyNames->count() . " مستخدمين بأسماء فارغة:\n";
    foreach ($usersWithEmptyNames as $user) {
        echo "   - ID: {$user->id}, Name: '" . ($user->name ?: 'NULL') . "', Name_AR: '" . ($user->name_ar ?: 'NULL') . "'\n";
        echo "     Email: {$user->email}, Employee ID: " . ($user->employee_id ?: 'N/A') . "\n";
    }
    $errors[] = "مستخدمين بأسماء فارغة: " . $usersWithEmptyNames->count();
} else {
    echo "   ✅ لا توجد أسماء فارغة\n";
}

// 7. فحص الأسماء التي تم استيرادها من Egyball
echo "\n📋 7. فحص الأسماء المستوردة من Egyball (emp_XXX)...\n";
$egyballUsers = DB::table('users')
    ->where('employee_id', 'LIKE', 'emp_%')
    ->get(['id', 'name', 'name_ar', 'email', 'employee_id']);

$egyballIssues = [];
foreach ($egyballUsers as $user) {
    $hasIssue = false;
    $issue = [];
    
    // التحقق من أن name_ar = name
    if ($user->name_ar === $user->name) {
        $hasIssue = true;
        $issue[] = 'name_ar = name (لا يوجد ترجمة عربية)';
    }
    
    // التحقق من وجود أحرف عربية في name
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $user->name)) {
        $hasIssue = true;
        $issue[] = 'name يحتوي على أحرف عربية';
    }
    
    if ($hasIssue) {
        $egyballIssues[] = [
            'user' => $user,
            'issues' => $issue
        ];
    }
}

if (count($egyballIssues) > 0) {
    echo "   ⚠️  وجدت " . count($egyballIssues) . " مستخدمين من Egyball بمشاكل:\n";
    foreach ($egyballIssues as $issue) {
        $user = $issue['user'];
        echo "   - Employee ID: {$user->employee_id}, Name: '{$user->name}', Name_AR: '{$user->name_ar}'\n";
        echo "     Email: {$user->email}, Issues: " . implode(', ', $issue['issues']) . "\n";
    }
    $warnings[] = "مستخدمين من Egyball بمشاكل: " . count($egyballIssues);
} else {
    echo "   ✅ جميع المستخدمين من Egyball صحيحين\n";
}

// عرض الملخص النهائي
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 ملخص النتائج\n";
echo str_repeat("=", 60) . "\n";

if (count($errors) > 0) {
    echo "❌ الأخطاء:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  التحذيرات:\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
    echo "\n";
}

if (count($errors) == 0 && count($warnings) == 0) {
    echo "✅ لا توجد أخطاء أو تحذيرات!\n";
}

echo "\n🎉 تم الانتهاء من الفحص!\n";
?>

