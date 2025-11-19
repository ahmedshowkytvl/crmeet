<?php

/**
 * السكريبت النهائي لإضافة الصور الشخصية للموظفين
 * يستخدم خريطة مباشرة محسنة للأسماء
 */

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// خريطة محسنة للأسماء والصور
$nameToImageMap = [
    // أسماء ذكور
    'أحمد محمد' => 'ahmed-mohamed.png',
    'عماد سعد السيد الرفاعي' => 'emad-saad.png',
    'أحمد علاء علي محمد' => 'ahmed-alaa.png',
    'السيد محمد محمد ديف' => 'el-sayed.png',
    'يوسف رزق عياد' => 'yousef.png',
    'أشرف شافي محمد محمود' => 'mr ashraf shafay.png',
    'أحمد ماهر سعد فهمي' => 'ahmed-maher.png',
    'أحمد محمد أحمد ذيب' => 'ahmed-mohamed.png',
    'هبة محمد عزت هال' => 'heba-yehia-2.png',
    'أحمد السيد عبد الرحيم محمد' => 'ahmed-elsayed-(2).png',
    'إسلام إيهاب أحمد محمد' => 'saeed.png',
    'أميرة حمدي السيد أحمد' => 'Amira-Ibrahim.png',
    'رامي سيد علي حسن' => 'ramy.png',
    'عصام إبراهيم' => 'ekram2.png',
    'أحمد عادل عبد الحميد' => 'ahmed-desoky.png',
    'مصطفى مجدي عبد الرحمن الغرب' => 'am-magdy.png',
    'خالد أحمد محمد' => 'ahmed-khaled.png',
    'زياد مصطفى علي' => 'aly.png',
    'لمياء حسين علي' => 'lamiaa.png',
    'نيرة أحمد مبروك حامد' => 'nour2.png',
    'عبد الرحمن محمد محمد خالد' => 'abdelrhman2-.png',
    'محمد فتحي محمد الطوخي' => 'mr mohamed fathyy.png',
    'رحام مجدي عبده سويلم' => 'reham medhatt.png',
    'ندى محمود إبراهيم الحسيني' => 'nada.png',
    'ميادة عادل محمد بركات' => 'mayarr.png',
    'أميرة إبراهيم محمد رشاد' => 'Amira-Ibrahim2.png',
    'رانيا محمد سيف الدين علي' => 'mrs-rania.png',
    'مى زيادة السيد زيادة' => 'mai.png',
    'حبيبة علاء علي عبد العزيز' => 'Aliaa-Tarek.png',
    'أحمد السيد أحمد السيد' => 'ahmed-elsayed-(2).png',
    
    // أسماء إناث
    'هند' => 'hind.png',
    'فاطمة صابر' => 'fatma saber.png',
    'هاجر' => 'hager.png',
    'آية' => 'aya.png',
    'سلمى' => 'salma.png',
    'راضية' => 'radwa.png',
    'سارة' => 'sarah_.png',
    'إيمان' => 'eman.png',
    'روان' => 'rawann.png',
    'علياء' => 'Aliaa-Tarek.png',
    'تسنيم' => 'tasnem.png',
    'جوستينا يعقوب' => 'Joustina-jacoub.png',
    'نوران' => 'nour.png',
    'رحام' => 'reham contractingg.png',
    
    // أسماء أخرى
    'أحمد سوبحي' => 'ahmed sobhy.png',
    'أحمد جمال' => 'ahmed gamal.png',
    'أحمد الحسين' => 'ahmed-huessin.png',
    'أحمد خالد' => 'ahmed-khaled.png',
    'علاء عبد العزيز' => 'alaa-abdelaziz.png',
    'علاء جمال' => 'alaa-gamal.png',
    'عمرو أحمد' => 'amr ahmed.png',
    'عمرو يحيى' => 'amr-yehia.png',
    'أيمن أشرف' => 'ayman ashraf.png',
    'إبراهيم عبد الغني' => 'ibrahim-abdelghany.png',
    'محمد رمضان' => 'Mohamed--Ramadan.png',
    'محمد غريب' => 'mr mohamed ghareb.png',
    'أحمد ماهر 2' => 'mr-ahmed-maher-2.png',
    'ضياء' => 'mr-diaa.png',
    'محمد فتحي 2' => 'mr-mohamed-fathyy2.png',
    'محمد فاروق' => 'mr-mohammed-farouk.png',
    'محمد فاروق 2' => 'mr-mohammed-farouk2.png',
    'أكرم' => 'mr akram.png',
    'عبد الرحمن 2' => 'abdelrhman2-.png',
    'علي' => 'aly.png',
    'أم جمال' => 'am-gamal.png',
    'عكرام' => 'ekram2.png',
    'سامح' => 'sameh.png',
    'وليد' => 'walid.png',
];

$imagesPath = 'D:\ett\old';

try {
    echo "بدء العملية النهائية لربط الصور...\n";
    
    $matches = 0;
    $processed = 0;
    
    foreach ($nameToImageMap as $arabicName => $imageFile) {
        $processed++;
        
        // البحث عن الموظف بالاسم العربي
        $user = \App\Models\User::where('name_ar', 'LIKE', "%$arabicName%")
            ->orWhere('name', 'LIKE', "%$arabicName%")
            ->first();
        
        if (!$user) {
            echo "لم يتم العثور على موظف باسم: $arabicName\n";
            continue;
        }
        
        // تخطي إذا كان لديه صورة بالفعل
        if ($user->profile_picture) {
            echo "الموظف {$user->name_ar} لديه صورة بالفعل\n";
            continue;
        }
        
        $imagePath = $imagesPath . '\\' . $imageFile;
        
        if (!file_exists($imagePath)) {
            echo "الصورة غير موجودة: $imageFile\n";
            continue;
        }
        
        try {
            // نسخ الصورة إلى مجلد التخزين
            $filename = time() . '_' . $imageFile;
            $destinationPath = storage_path('app/public/profile_pictures/' . $filename);
            
            // إنشاء المجلد إذا لم يكن موجوداً
            if (!is_dir(dirname($destinationPath))) {
                mkdir(dirname($destinationPath), 0755, true);
            }
            
            // نسخ الملف
            if (copy($imagePath, $destinationPath)) {
                // تحديث قاعدة البيانات
                $user->profile_picture = 'profile_pictures/' . $filename;
                $user->save();
                
                $matches++;
                echo "تم ربط الصورة {$imageFile} للموظف {$user->name_ar}\n";
            } else {
                echo "فشل في نسخ الصورة: $imageFile\n";
            }
        } catch (Exception $e) {
            echo "خطأ في معالجة الصورة $imageFile: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== ملخص النتائج ===\n";
    echo "عدد الصور المعالجة: $processed\n";
    echo "عدد الصور المربوطة: $matches\n";
    echo "نسبة النجاح: " . round(($matches / $processed) * 100, 2) . "%\n";
    
    // عرض الموظفين الذين لم يتم ربط صور لهم
    echo "\n=== الموظفون الذين يحتاجون صور يدوية ===\n";
    $usersWithoutPhotos = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->whereNull('profile_picture')->get();
    
    foreach ($usersWithoutPhotos as $user) {
        echo "- {$user->name_ar} ({$user->name})\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nتم الانتهاء من العملية النهائية!\n";
