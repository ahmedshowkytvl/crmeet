<?php

/**
 * سكريبت لإضافة الصور الشخصية تلقائياً للموظفين
 * يربط الصور من مجلد D:\ett\old بأسماء الموظفين في قاعدة البيانات
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// مسار مجلد الصور
$imagesPath = 'D:\ett\old';

// دالة لتنظيف اسم الملف
function cleanFileName($filename) {
    // إزالة الامتداد
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // إزالة الأرقام والرموز الخاصة
    $name = preg_replace('/\d+/', '', $name);
    $name = preg_replace('/[^a-zA-Z\p{Arabic}\s]/u', '', $name);
    
    // إزالة المسافات الزائدة
    $name = trim($name);
    
    return $name;
}

// دالة لتحويل الأسماء الإنجليزية إلى العربية
function convertEnglishToArabic($englishName) {
    $conversions = [
        'ahmed' => 'أحمد',
        'mohamed' => 'محمد',
        'mahmoud' => 'محمود',
        'ali' => 'علي',
        'hassan' => 'حسن',
        'hussain' => 'حسين',
        'saeed' => 'سعيد',
        'omar' => 'عمر',
        'youssef' => 'يوسف',
        'karim' => 'كريم',
        'amr' => 'عمرو',
        'yasser' => 'ياسر',
        'ashraf' => 'أشرف',
        'emad' => 'عماد',
        'salah' => 'صلاح',
        'tarek' => 'طارق',
        'gamal' => 'جمال',
        'alaa' => 'علاء',
        'hani' => 'هاني',
        'wael' => 'وائل',
        'nader' => 'نادر',
        'mostafa' => 'مصطفى',
        'khaled' => 'خالد',
        'ibrahim' => 'إبراهيم',
        'osama' => 'أسامة',
        'walid' => 'وليد',
        'sameh' => 'سامح',
        'rami' => 'رامي',
        'hany' => 'هاني',
        'adel' => 'عادل',
        'farouk' => 'فاروق',
        'hanan' => 'حنان',
        'heba' => 'هبة',
        'nour' => 'نور',
        'mai' => 'مي',
        'aya' => 'آية',
        'salma' => 'سلمى',
        'radwa' => 'راضية',
        'mariam' => 'مريم',
        'sarah' => 'سارة',
        'fatma' => 'فاطمة',
        'eman' => 'إيمان',
        'hind' => 'هند',
        'rawan' => 'رawan',
        'yasmin' => 'ياسمين',
        'amira' => 'أميرة',
        'alia' => 'علياء',
        'hager' => 'هاجر',
        'tasneem' => 'تسنيم',
        'shaimaa' => 'شيماء',
        'joustina' => 'جوستينا',
        'wafaa' => 'وفاء',
        'rania' => 'رانيا',
        'nouran' => 'نوران',
        'reham' => 'رحام',
        'miss' => '',
        'mr' => '',
        'mrs' => '',
        'ms' => ''
    ];
    
    $lowerName = strtolower($englishName);
    
    foreach ($conversions as $english => $arabic) {
        $lowerName = str_replace($english, $arabic, $lowerName);
    }
    
    return trim($lowerName);
}

// دالة لمقارنة الأسماء
function isNameMatch($imageName, $userName, $userNameAr) {
    // تنظيف الأسماء
    $cleanImageName = cleanFileName($imageName);
    $cleanUserName = cleanFileName($userName);
    $cleanUserNameAr = cleanFileName($userNameAr ?? '');
    
    // تحويل الاسم الإنجليزي إلى عربي
    $convertedImageName = convertEnglishToArabic($cleanImageName);
    
    // مقارنة مباشرة
    if (stripos($cleanUserNameAr, $cleanImageName) !== false || 
        stripos($cleanImageName, $cleanUserNameAr) !== false ||
        stripos($convertedImageName, $cleanUserNameAr) !== false ||
        stripos($cleanUserNameAr, $convertedImageName) !== false) {
        return true;
    }
    
    // مقارنة مع الاسم الإنجليزي
    if (stripos($cleanUserName, $cleanImageName) !== false || 
        stripos($cleanImageName, $cleanUserName) !== false) {
        return true;
    }
    
    // مقارنة كلمات منفصلة
    $imageWords = explode(' ', $cleanImageName);
    $userWords = explode(' ', $cleanUserNameAr);
    
    $matches = 0;
    foreach ($imageWords as $imageWord) {
        if (strlen($imageWord) > 2) {
            foreach ($userWords as $userWord) {
                if (strlen($userWord) > 2 && 
                    (stripos($userWord, $imageWord) !== false || 
                     stripos($imageWord, $userWord) !== false)) {
                    $matches++;
                    break;
                }
            }
        }
    }
    
    return $matches >= 2;
}

try {
    echo "بدء عملية ربط الصور بالموظفين...\n";
    
    // الحصول على جميع الملفات في مجلد الصور
    if (!is_dir($imagesPath)) {
        throw new Exception("مجلد الصور غير موجود: $imagesPath");
    }
    
    $imageFiles = glob($imagesPath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    if (empty($imageFiles)) {
        throw new Exception("لا توجد ملفات صور في المجلد");
    }
    
    echo "تم العثور على " . count($imageFiles) . " صورة\n";
    
    // الحصول على جميع الموظفين
    $users = \App\Models\User::where('user_type', 'employee')
        ->orWhereNull('user_type')
        ->get();
    
    echo "تم العثور على " . $users->count() . " موظف\n";
    
    $matches = 0;
    $processed = 0;
    
    foreach ($users as $user) {
        $processed++;
        echo "معالجة الموظف: {$user->name} ({$user->name_ar}) - ";
        
        // تخطي إذا كان لديه صورة بالفعل
        if ($user->profile_picture) {
            echo "لديه صورة بالفعل\n";
            continue;
        }
        
        $bestMatch = null;
        $bestScore = 0;
        
        // البحث عن أفضل تطابق
        foreach ($imageFiles as $imageFile) {
            $imageName = basename($imageFile);
            
            if (isNameMatch($imageName, $user->name, $user->name_ar)) {
                // حساب نقاط التطابق
                $score = 0;
                $cleanImageName = cleanFileName($imageName);
                $cleanUserNameAr = cleanFileName($user->name_ar ?? '');
                
                if (stripos($cleanUserNameAr, $cleanImageName) !== false) {
                    $score += 10;
                }
                
                if (stripos($cleanImageName, $cleanUserNameAr) !== false) {
                    $score += 10;
                }
                
                // إضافة نقاط إضافية للتطابق الكامل
                if (strtolower($cleanImageName) === strtolower($cleanUserNameAr)) {
                    $score += 20;
                }
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $imageFile;
                }
            }
        }
        
        if ($bestMatch && $bestScore >= 5) {
            try {
                // نسخ الصورة إلى مجلد التخزين
                $filename = time() . '_' . basename($bestMatch);
                $destinationPath = storage_path('app/public/profile_pictures/' . $filename);
                
                // إنشاء المجلد إذا لم يكن موجوداً
                if (!is_dir(dirname($destinationPath))) {
                    mkdir(dirname($destinationPath), 0755, true);
                }
                
                // نسخ الملف
                if (copy($bestMatch, $destinationPath)) {
                    // تحديث قاعدة البيانات
                    $user->profile_picture = 'profile_pictures/' . $filename;
                    $user->save();
                    
                    $matches++;
                    echo "تم ربط الصورة: " . basename($bestMatch) . " (نقاط: $bestScore)\n";
                } else {
                    echo "فشل في نسخ الصورة\n";
                }
            } catch (Exception $e) {
                echo "خطأ في معالجة الصورة: " . $e->getMessage() . "\n";
            }
        } else {
            echo "لم يتم العثور على تطابق مناسب\n";
        }
    }
    
    echo "\n=== ملخص النتائج ===\n";
    echo "عدد الموظفين المعالجين: $processed\n";
    echo "عدد الصور المربوطة: $matches\n";
    echo "نسبة النجاح: " . round(($matches / $processed) * 100, 2) . "%\n";
    
    // عرض الموظفين الذين لم يتم ربط صور لهم
    echo "\n=== الموظفون بدون صور ===\n";
    $usersWithoutPhotos = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->whereNull('profile_picture')->get();
    
    foreach ($usersWithoutPhotos as $user) {
        echo "- {$user->name} ({$user->name_ar})\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nتم الانتهاء من العملية بنجاح!\n";
