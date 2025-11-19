<?php

/**
 * سكريبت لإزالة جميع الصور من الموظفين باستثناء عبد الحميد
 */

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "بدء عملية إزالة الصور من جميع الموظفين باستثناء عبد الحميد...\n";
    
    // الحصول على جميع الموظفين
    $users = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->whereNotNull('profile_picture')->get();
    
    echo "تم العثور على " . $users->count() . " موظف لديهم صور\n";
    
    $removedCount = 0;
    $keptCount = 0;
    
    foreach ($users as $user) {
        // التحقق من أن الموظف ليس عبد الحميد
        $isAbdelHamid = (
            stripos($user->name, 'Abdel Hamid') !== false ||
            stripos($user->name, 'عبد الحميد') !== false ||
            stripos($user->name_ar, 'عبد الحميد') !== false
        );
        
        if ($isAbdelHamid) {
            echo "تم الاحتفاظ بصورة عبد الحميد: {$user->name_ar}\n";
            $keptCount++;
            continue;
        }
        
        try {
            // حذف ملف الصورة من التخزين
            if ($user->profile_picture) {
                $photoPath = storage_path('app/public/' . $user->profile_picture);
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                    echo "تم حذف ملف الصورة: {$user->profile_picture}\n";
                }
            }
            
            // إزالة مسار الصورة من قاعدة البيانات
            $user->profile_picture = null;
            $user->save();
            
            $removedCount++;
            echo "تم إزالة صورة الموظف: {$user->name_ar} ({$user->name})\n";
            
        } catch (Exception $e) {
            echo "خطأ في إزالة صورة {$user->name_ar}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== ملخص النتائج ===\n";
    echo "عدد الصور المحذوفة: $removedCount\n";
    echo "عدد الصور المحتفظ بها: $keptCount\n";
    
    // التحقق من النتائج النهائية
    echo "\n=== التحقق من النتائج ===\n";
    $usersWithPhotos = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->whereNotNull('profile_picture')->get();
    
    echo "عدد الموظفين المتبقين مع صور: " . $usersWithPhotos->count() . "\n";
    
    foreach ($usersWithPhotos as $user) {
        echo "- {$user->name_ar} ({$user->name}) - {$user->profile_picture}\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nتم الانتهاء من العملية بنجاح!\n";

