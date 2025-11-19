<?php

/**
 * سكريبت للتحقق من حالة ربط الصور بالموظفين
 */

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== تقرير حالة الصور الشخصية للموظفين ===\n\n";
    
    // الحصول على جميع الموظفين
    $users = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->get();
    
    $totalUsers = $users->count();
    $usersWithPhotos = $users->where('profile_picture', '!=', null)->count();
    $usersWithoutPhotos = $totalUsers - $usersWithPhotos;
    
    echo "إجمالي الموظفين: $totalUsers\n";
    echo "الموظفون مع الصور: $usersWithPhotos\n";
    echo "الموظفون بدون صور: $usersWithoutPhotos\n";
    echo "نسبة التغطية: " . round(($usersWithPhotos / $totalUsers) * 100, 2) . "%\n\n";
    
    // عرض الموظفين مع الصور
    echo "=== الموظفون مع الصور ===\n";
    $usersWithPhotosList = $users->where('profile_picture', '!=', null);
    foreach ($usersWithPhotosList as $user) {
        $photoPath = storage_path('app/public/' . $user->profile_picture);
        $photoExists = file_exists($photoPath) ? '✓' : '✗';
        echo "- {$user->name_ar} ({$user->name}) - {$user->profile_picture} $photoExists\n";
    }
    
    echo "\n=== الموظفون بدون صور ===\n";
    $usersWithoutPhotosList = $users->where('profile_picture', null);
    foreach ($usersWithoutPhotosList as $user) {
        echo "- {$user->name_ar} ({$user->name})\n";
    }
    
    // فحص الصور المفقودة
    echo "\n=== فحص الصور المفقودة ===\n";
    $missingPhotos = 0;
    foreach ($usersWithPhotosList as $user) {
        $photoPath = storage_path('app/public/' . $user->profile_picture);
        if (!file_exists($photoPath)) {
            echo "صورة مفقودة: {$user->name_ar} - {$user->profile_picture}\n";
            $missingPhotos++;
        }
    }
    
    if ($missingPhotos === 0) {
        echo "جميع الصور موجودة ✓\n";
    } else {
        echo "عدد الصور المفقودة: $missingPhotos\n";
    }
    
    // إحصائيات إضافية
    echo "\n=== إحصائيات إضافية ===\n";
    
    // الصور حسب القسم
    $photosByDepartment = [];
    foreach ($usersWithPhotosList as $user) {
        $deptName = $user->department ? $user->department->name : 'غير محدد';
        if (!isset($photosByDepartment[$deptName])) {
            $photosByDepartment[$deptName] = 0;
        }
        $photosByDepartment[$deptName]++;
    }
    
    echo "الصور حسب القسم:\n";
    foreach ($photosByDepartment as $dept => $count) {
        echo "- $dept: $count صورة\n";
    }
    
    // حجم الصور
    $totalSize = 0;
    $photoCount = 0;
    foreach ($usersWithPhotosList as $user) {
        $photoPath = storage_path('app/public/' . $user->profile_picture);
        if (file_exists($photoPath)) {
            $totalSize += filesize($photoPath);
            $photoCount++;
        }
    }
    
    if ($photoCount > 0) {
        $avgSize = $totalSize / $photoCount;
        echo "\nإجمالي حجم الصور: " . round($totalSize / 1024 / 1024, 2) . " MB\n";
        echo "متوسط حجم الصورة: " . round($avgSize / 1024, 2) . " KB\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nتم الانتهاء من الفحص!\n";
