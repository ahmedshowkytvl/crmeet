<?php

/**
 * سكريبت محسن لإضافة الصور الشخصية تلقائياً للموظفين
 * يستخدم خوارزمية مطابقة ذكية ومحسنة
 */

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// مسار مجلد الصور
$imagesPath = 'D:\ett\old';

// دالة لتنظيف اسم الملف
function cleanFileName($filename) {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // إزالة الأرقام والرموز الخاصة
    $name = preg_replace('/\d+/', '', $name);
    $name = preg_replace('/[^a-zA-Z\p{Arabic}\s]/u', '', $name);
    $name = preg_replace('/\b(mr|mrs|ms|miss)\b/i', '', $name);
    
    // إزالة المسافات الزائدة
    $name = trim(preg_replace('/\s+/', ' ', $name));
    
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
        'rawan' => 'روان',
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
        'mousad' => 'مسعد',
        'essam' => 'عصام',
        'moustafa' => 'مصطفى',
        'zied' => 'زياد',
        'lamiaa' => 'لمياء',
        'yara' => 'يارا',
        'nayra' => 'نيرة',
        'fathy' => 'فتحي',
        'nada' => 'ندى',
        'mayada' => 'ميادة',
        'habiba' => 'حبيبة',
        'el-sayed' => 'السيد',
        'abdel' => 'عبد',
        'rahman' => 'الرحمن',
        'hamid' => 'الحميد',
        'refai' => 'الرفاعي',
        'deif' => 'ضيف',
        'dieb' => 'ذيب',
        'hal' => 'حال',
        'baioumy' => 'بيومي',
        'hassan' => 'حسن',
        'swilam' => 'سويلم',
        'kassem' => 'قاسم',
        'gawad' => 'الجواد',
        'zeyada' => 'زيادة',
        'aziz' => 'العزيز',
        'tolpa' => 'طولبة',
        'garges' => 'جرجس',
        'talaat' => 'تلات',
        'mokadem' => 'المقدم',
        'awady' => 'العوادي',
        'abbas' => 'عباس',
        'magady' => 'مجدي',
        'khalifa' => 'خليفة',
        'ramadan' => 'رمضان',
        'gomaa' => 'جمعة',
        'riad' => 'رياض',
        'haleem' => 'حليم',
        'amal' => 'أمل',
        'saleh' => 'صالح',
        'fargaly' => 'فرجلي',
        'noha' => 'نهى',
        'el-sayed' => 'السيد',
        'gad' => 'جاد',
        'labib' => 'لبيب',
        'khalaf' => 'خلف',
        'mosallhy' => 'مسلحي',
        'zalat' => 'زلات',
        'morsi' => 'مرسي',
        'madbouly' => 'مدبولي',
        'nagy' => 'نجي',
        'rizk' => 'رزق',
        'louay' => 'لؤي',
        'atef' => 'عاطف',
        'sharif' => 'شريف',
        'matwaly' => 'متولي',
        'monica' => 'مونيكا',
        'ezzat' => 'عزت',
        'gerges' => 'جرجس',
        'aisha' => 'عائشة',
        'hesham' => 'هشام',
        'asmaa' => 'أسماء',
        'ismail' => 'إسماعيل',
        'swiefy' => 'سويفي',
        'hamdy' => 'حمدي',
        'bahloul' => 'بهلول',
        'lotfy' => 'لطفي',
        'madonna' => 'مادونا',
        'nashaat' => 'نشأت',
        'anwer' => 'أنور',
        'seha' => 'سها',
        'shawky' => 'شوقي',
        'dahy' => 'داهي'
    ];
    
    $lowerName = strtolower($englishName);
    
    foreach ($conversions as $english => $arabic) {
        $lowerName = str_replace($english, $arabic, $lowerName);
    }
    
    return trim($lowerName);
}

// دالة لمقارنة الأسماء مع تحسينات
function isNameMatch($imageName, $userName, $userNameAr, $threshold = 0.6) {
    $cleanImageName = cleanFileName($imageName);
    $cleanUserName = cleanFileName($userName);
    $cleanUserNameAr = cleanFileName($userNameAr ?? '');
    
    // تحويل الاسم الإنجليزي إلى عربي
    $convertedImageName = convertEnglishToArabic($cleanImageName);
    
    // حساب نقاط التطابق
    $score = 0;
    $maxScore = 0;
    
    // تطابق مباشر مع الاسم العربي
    if (!empty($cleanUserNameAr)) {
        $maxScore += 20;
        if (stripos($cleanUserNameAr, $cleanImageName) !== false || 
            stripos($cleanImageName, $cleanUserNameAr) !== false) {
            $score += 20;
        } elseif (stripos($convertedImageName, $cleanUserNameAr) !== false ||
                  stripos($cleanUserNameAr, $convertedImageName) !== false) {
            $score += 15;
        }
    }
    
    // تطابق مع الاسم الإنجليزي
    if (!empty($cleanUserName)) {
        $maxScore += 10;
        if (stripos($cleanUserName, $cleanImageName) !== false || 
            stripos($cleanImageName, $cleanUserName) !== false) {
            $score += 10;
        }
    }
    
    // تطابق الكلمات المنفصلة
    $imageWords = array_filter(explode(' ', $cleanImageName), function($word) {
        return strlen($word) > 2;
    });
    $userWords = array_filter(explode(' ', $cleanUserNameAr), function($word) {
        return strlen($word) > 2;
    });
    
    $maxScore += count($imageWords) * 5;
    foreach ($imageWords as $imageWord) {
        foreach ($userWords as $userWord) {
            if (stripos($userWord, $imageWord) !== false || 
                stripos($imageWord, $userWord) !== false) {
                $score += 5;
                break;
            }
        }
    }
    
    // تطابق الكلمات المحولة
    $convertedWords = array_filter(explode(' ', $convertedImageName), function($word) {
        return strlen($word) > 2;
    });
    $maxScore += count($convertedWords) * 3;
    foreach ($convertedWords as $convertedWord) {
        foreach ($userWords as $userWord) {
            if (stripos($userWord, $convertedWord) !== false || 
                stripos($convertedWord, $userWord) !== false) {
                $score += 3;
                break;
            }
        }
    }
    
    // حساب النسبة
    $ratio = $maxScore > 0 ? $score / $maxScore : 0;
    
    return $ratio >= $threshold;
}

// دالة لحساب نقاط التطابق
function calculateMatchScore($imageName, $userName, $userNameAr) {
    $cleanImageName = cleanFileName($imageName);
    $cleanUserName = cleanFileName($userName);
    $cleanUserNameAr = cleanFileName($userNameAr ?? '');
    $convertedImageName = convertEnglishToArabic($cleanImageName);
    
    $score = 0;
    
    // تطابق مباشر مع الاسم العربي
    if (!empty($cleanUserNameAr)) {
        if (stripos($cleanUserNameAr, $cleanImageName) !== false || 
            stripos($cleanImageName, $cleanUserNameAr) !== false) {
            $score += 20;
        } elseif (stripos($convertedImageName, $cleanUserNameAr) !== false ||
                  stripos($cleanUserNameAr, $convertedImageName) !== false) {
            $score += 15;
        }
    }
    
    // تطابق مع الاسم الإنجليزي
    if (!empty($cleanUserName)) {
        if (stripos($cleanUserName, $cleanImageName) !== false || 
            stripos($cleanImageName, $cleanUserName) !== false) {
            $score += 10;
        }
    }
    
    // تطابق الكلمات المنفصلة
    $imageWords = array_filter(explode(' ', $cleanImageName), function($word) {
        return strlen($word) > 2;
    });
    $userWords = array_filter(explode(' ', $cleanUserNameAr), function($word) {
        return strlen($word) > 2;
    });
    
    foreach ($imageWords as $imageWord) {
        foreach ($userWords as $userWord) {
            if (stripos($userWord, $imageWord) !== false || 
                stripos($imageWord, $userWord) !== false) {
                $score += 5;
                break;
            }
        }
    }
    
    return $score;
}

try {
    echo "بدء عملية ربط الصور المحسنة...\n";
    
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
    $users = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->get();
    
    echo "تم العثور على " . $users->count() . " موظف\n";
    
    $matches = 0;
    $processed = 0;
    $usedImages = [];
    
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
            
            // تخطي الصور المستخدمة بالفعل
            if (in_array($imageName, $usedImages)) {
                continue;
            }
            
            if (isNameMatch($imageName, $user->name, $user->name_ar, 0.3)) {
                $score = calculateMatchScore($imageName, $user->name, $user->name_ar);
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $imageFile;
                }
            }
        }
        
        if ($bestMatch && $bestScore >= 10) {
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
                    
                    // إضافة الصورة للقائمة المستخدمة
                    $usedImages[] = basename($bestMatch);
                    
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
    echo "عدد الصور المستخدمة: " . count($usedImages) . "\n";
    
    // عرض الموظفين الذين لم يتم ربط صور لهم
    echo "\n=== الموظفون بدون صور ===\n";
    $usersWithoutPhotos = \App\Models\User::where(function($query) {
        $query->where('user_type', 'employee')->orWhereNull('user_type');
    })->whereNull('profile_picture')->get();
    
    foreach ($usersWithoutPhotos as $user) {
        echo "- {$user->name} ({$user->name_ar})\n";
    }
    
    // عرض الصور غير المستخدمة
    echo "\n=== الصور غير المستخدمة ===\n";
    $unusedImages = [];
    foreach ($imageFiles as $imageFile) {
        $imageName = basename($imageFile);
        if (!in_array($imageName, $usedImages)) {
            $unusedImages[] = $imageName;
        }
    }
    
    foreach ($unusedImages as $image) {
        echo "- $image\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nتم الانتهاء من العملية بنجاح!\n";
