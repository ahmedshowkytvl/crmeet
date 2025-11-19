<?php

/**
 * سكريبت سريع لإضافة الصور الشخصية للموظفين
 * يستخدم خريطة مباشرة للأسماء
 */

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// خريطة مباشرة للأسماء والصور
$nameToImageMap = [
    'أحمد محمد عبد الحميد' => 'ahmed mahmoud.png',
    'حنان محمد علي إبراهيم' => 'hanan.png',
    'عماد سعد السيد الرفاعي' => 'emad-saad.png',
    'أحمد علاء علي محمد' => 'ahmed-alaa.png',
    'السيد محمد محمد ديف' => 'el-sayed.png',
    'محمد كامل صالح عبد العزيز' => 'mohamed-anwar.png',
    'ياسمين مراد مختار جابر' => 'yasmin.png',
    'محمد رأفت دسوقي دسوقي' => 'mohamed-mohmoud.png',
    'عمر عصام حسن أحمد' => 'osama hamedd.png',
    'إسلام محمد كامل محمد' => 'saeed.png',
    'أبانوب سعد لطفي توفيق' => 'anan.png',
    'كريم سعيد حسن محمد' => 'mr karim.png',
    'محمد أشرف الدسوقي طولبة' => 'mohamed-anwar.png',
    'محمود عاطف بيومي' => 'amr atef.png',
    'يوسف رزق عياد' => 'yousef.png',
    'أشرف شافي محمد محمود' => 'ashraf shafay.png',
    'أحمد ماهر سعد فهمي' => 'ahmed-maher.png',
    'أحمد محمد أحمد ذيب' => 'ahmed-mohamed.png',
    'هبة محمد عزت هال' => 'heba-yehia-2.png',
    'هند' => 'hind.png',
    'فاطمة صابر' => 'fatma saber.png',
    'هاجر' => 'hager.png',
    'نور' => 'nour.png',
    'مي' => 'mai.png',
    'آية' => 'aya.png',
    'سلمى' => 'salma.png',
    'راضية' => 'radwa.png',
    'مريم' => 'mariam.png',
    'سارة' => 'sarah_.png',
    'إيمان' => 'eman.png',
    'روان' => 'rawann.png',
    'ياسمين' => 'yasmin.png',
    'أميرة إبراهيم' => 'Amira-Ibrahim.png',
    'علياء' => 'Aliaa-Tarek.png',
    'هاجر' => 'hager.png',
    'تسنيم' => 'tasnem.png',
    'شيماء' => 'shiamaa.png',
    'جوستينا يعقوب' => 'Joustina-jacoub.png',
    'وفاء' => 'ms-wafaa.png',
    'رانيا' => 'mrs-rania.png',
    'نوران' => 'nour2.png',
    'رحام' => 'reham medhatt.png',
    'أحمد سوبحي' => 'ahmed sobhy.png',
    'أحمد السيد' => 'ahmed-elsayed-(2).png',
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
    'محمد فتحي' => 'mr mohamed fathyy.png',
    'محمد غريب' => 'mr mohamed ghareb.png',
    'مسعد' => 'mr mosaad.png',
    'رامي' => 'ramy.png',
    'أحمد ماهر 2' => 'mr-ahmed-maher-2.png',
    'ضياء' => 'mr-diaa.png',
    'محمد فتحي 2' => 'mr-mohamed-fathyy2.png',
    'محمد فاروق' => 'mr-mohammed-farouk.png',
    'محمد فاروق 2' => 'mr-mohammed-farouk2.png',
    'طارق' => 'mr-tarek.png',
    'حنان محمد' => 'mrs-Hanan-Mohamed.png',
    'رانيا' => 'mrs-rania2.png',
    'أكرم' => 'mr akram.png',
    'عبد الرحمن' => 'abdelrahman.png',
    'عبد الرحمن 2' => 'abdelrhman2-.png',
    'علي' => 'aly.png',
    'أم جمال' => 'am-gamal.png',
    'أم مجدي' => 'am-magdy.png',
    'عكرام' => 'ekram2.png',
    'سامح' => 'sameh.png',
    'وليد' => 'walid.png',
];

$imagesPath = 'D:\ett\old';

try {
    echo "بدء عملية ربط الصور السريعة...\n";
    
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
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nتم الانتهاء من العملية بنجاح!\n";
