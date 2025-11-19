<?php

/**
 * سكريبت لإزالة صورة أحمد حمدي عبد الحميد والاحتفاظ فقط بعبد الحميد محمد عبد الحميد
 */

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "بدء عملية إزالة صورة أحمد حمدي عبد الحميد...\n";
    
    // البحث عن أحمد حمدي عبد الحميد
    $user = \App\Models\User::where('name', 'LIKE', '%Ahmed Hamdy%')
        ->orWhere('name_ar', 'LIKE', '%احمد حمدى%')
        ->first();
    
    if (!$user) {
        echo "لم يتم العثور على أحمد حمدي عبد الحميد\n";
        exit(0);
    }
    
    echo "تم العثور على: {$user->name_ar} ({$user->name})\n";
    
    if ($user->profile_picture) {
        try {
            // حذف ملف الصورة من التخزين
            $photoPath = storage_path('app/public/' . $user->profile_picture);
            if (file_exists($photoPath)) {
                unlink($photoPath);
                echo "تم حذف ملف الصورة: {$user->profile_picture}\n";
            }
            
            // إزالة مسار الصورة من قاعدة البيانات
            $user->profile_picture = null;
            $user->save();
            
            echo "تم إزالة صورة أحمد حمدي عبد الحميد بنجاح\n";
            
        } catch (Exception $e) {
            echo "خطأ في إزالة الصورة: " . $e->getMessage() . "\n";
        }
    } else {
        echo "الموظف لا يملك صورة\n";
    }
    
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

