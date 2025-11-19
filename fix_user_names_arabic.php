<?php
/**
 * سكريبت إصلاح أخطاء الأسماء في جدول المستخدمين
 * - ترجمة الأسماء الإنجليزية إلى عربية
 * - إصلاح name_ar الذي يحتوي على أحرف إنجليزية
 * - إصلاح name الذي يحتوي على أحرف عربية
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 بدء إصلاح أخطاء الأسماء في جدول المستخدمين...\n\n";

/**
 * دالة لتحويل الأسماء الإنجليزية إلى العربية
 */
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
        'moustafa' => 'مصطفى',
        'khaled' => 'خالد',
        'ibrahim' => 'إبراهيم',
        'osama' => 'أسامة',
        'walid' => 'وليد',
        'waleed' => 'وليد',
        'sameh' => 'سامح',
        'rami' => 'رامي',
        'hany' => 'هاني',
        'adel' => 'عادل',
        'farouk' => 'فاروق',
        'hanan' => 'حنان',
        'heba' => 'هبة',
        'nour' => 'نور',
        'nourhan' => 'نورهان',
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
        'abdel' => 'عبد',
        'abd' => 'عبد',
        'el' => 'ال',
        'sayeed' => 'سيد',
        'sayed' => 'سيد',
        'soliman' => 'سليمان',
        'solaiman' => 'سليمان',
        'anwar' => 'أنور',
        'morsi' => 'مرسي',
        'nazmi' => 'نظمي',
        'naguib' => 'نجيب',
        'osman' => 'عثمان',
        'toukhy' => 'توقي',
        'fathy' => 'فتحي',
        'mohsen' => 'محسن',
        'ghany' => 'غني',
        'abdelghany' => 'عبد الغني',
        'maged' => 'ماجد',
        'ezzat' => 'عزت',
        'hal' => 'حال',
        'swilam' => 'سويلم',
        'saad' => 'سعد',
        'el-sayed' => 'السيد',
        'refai' => 'رفاعي',
        'elrefai' => 'الرفاعي',
        'madbouly' => 'مدبولي',
        'shafie' => 'شافعي',
        'labib' => 'لبيب',
        'fahmy' => 'فهمي',
        'motelab' => 'مطلب',
        'nazmi' => 'نظمي',
        'abd el' => 'عبد ال',
        'abd el mohsen' => 'عبد المحسن',
        'abd el gawad' => 'عبد الجواد',
        'yara' => 'يارا',
        'dalia' => 'داليا',
        'nada' => 'ندى',
        'belal' => 'بلال',
        'emam' => 'إمام',
        'manar' => 'منار',
        'rahma' => 'رحمة',
        'omnia' => 'أمنية',
        'sama' => 'سماح',
        'bavly' => 'بافلي',
        'samy' => 'سامي',
        'taha' => 'طه',
        'tamer' => 'تامر',
        'yehia' => 'يحيى',
        'aladdine' => 'علاء الدين',
        'shenouda' => 'شنودة',
        'magdy' => 'مجدى',
        'mansour' => 'منصور',
        'hamid' => 'حامد',
        'benali' => 'بنعلي',
        'dieb' => 'ديب',
        'refat' => 'رفعت',
        'tawfik' => 'توفيق',
        'bayomi' => 'بايومي',
        'nagy' => 'نجي',
        'kreem' => 'كريم',
        'rizk' => 'رضا',
        'ayad' => 'عياد',
        'ashor' => 'عاشور',
        'abdelrahman' => 'عبد الرحمن',
        'goda' => 'جودة',
        'menam' => 'منعم',
        'rabea' => 'ربيعة',
        'haidy' => 'هايدي',
        'awady' => 'عواضى',
        'abdou' => 'عبده',
        'jr' => '',
        'reservation' => 'حجز',
        'agent' => 'وكيل',
        'tour' => 'سياحة',
        'operator' => 'مشغل',
        'data' => 'بيانات',
        'entry' => 'إدخال',
        'clerk' => 'موظف',
        'executive' => 'تنفيذي',
        'accountant' => 'محاسب',
        'junior' => 'مساعد',
        'team' => 'فريق',
        'lead' => 'رئيس',
    ];
    
    // تنظيف الاسم
    $englishName = trim($englishName);
    
    // تقسيم الاسم إلى كلمات
    $words = explode(' ', $englishName);
    $arabicWords = [];
    
    foreach ($words as $word) {
        $wordLower = strtolower(trim($word));
        
        // إزالة النقاط والفواصل
        $wordLower = preg_replace('/[.,]/', '', $wordLower);
        
        if (empty($wordLower)) {
            continue;
        }
        
        $converted = false;
        
        // البحث عن تطابق كامل أولاً
        if (isset($conversions[$wordLower])) {
            $arabicWords[] = $conversions[$wordLower];
            $converted = true;
        } else {
            // البحث عن تطابق جزئي
            foreach ($conversions as $english => $arabic) {
                if (stripos($wordLower, $english) !== false || stripos($english, $wordLower) !== false) {
                    $arabicWords[] = str_ireplace($english, $arabic, $wordLower);
                    $converted = true;
                    break;
                }
            }
        }
        
        // إذا لم يتم التحويل، نترك الكلمة كما هي (قد تكون اسم عائلة أو كلمة غير معروفة)
        if (!$converted) {
            $arabicWords[] = $word;
        }
    }
    
    // دمج الكلمات
    $result = implode(' ', $arabicWords);
    
    return trim($result);
}

$fixedCount = 0;
$skippedCount = 0;

// 1. إصلاح المستخدمين من Egyball الذين لديهم name_ar = name
echo "📋 1. إصلاح المستخدمين من Egyball (name_ar = name)...\n";
$egyballUsers = DB::table('users')
    ->where('employee_id', 'LIKE', 'emp_%')
    ->whereColumn('name_ar', 'name')
    ->get(['id', 'name', 'name_ar', 'employee_id']);

foreach ($egyballUsers as $user) {
    $arabicName = convertEnglishToArabic($user->name);
    
    if ($arabicName !== $user->name) {
        DB::table('users')
            ->where('id', $user->id)
            ->update(['name_ar' => $arabicName]);
        
        echo "   ✅ ID {$user->id} ({$user->employee_id}): '{$user->name}' → '{$arabicName}'\n";
        $fixedCount++;
    } else {
        echo "   ⚠️  ID {$user->id} ({$user->employee_id}): لا يمكن ترجمة '{$user->name}'\n";
        $skippedCount++;
    }
}

// 2. إصلاح name_ar الذي يحتوي على أحرف إنجليزية (مثل 'Admin', 'Btb', 'Trans')
echo "\n📋 2. إصلاح name_ar الذي يحتوي على أحرف إنجليزية...\n";
$usersWithEnglishInArabic = DB::table('users')
    ->whereNotNull('name_ar')
    ->where('name_ar', '!=', '')
    ->get(['id', 'name', 'name_ar', 'employee_id']);

foreach ($usersWithEnglishInArabic as $user) {
    // التحقق من وجود أحرف إنجليزية في name_ar
    if (preg_match('/[a-zA-Z]/', $user->name_ar)) {
        // إذا كان name_ar مختلف عن name، نستخدم name لإنشاء ترجمة عربية
        if ($user->name_ar !== $user->name && !empty($user->name)) {
            $arabicName = convertEnglishToArabic($user->name);
            
            if ($arabicName !== $user->name && $arabicName !== $user->name_ar) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['name_ar' => $arabicName]);
                
                echo "   ✅ ID {$user->id} ({$user->employee_id}): '{$user->name_ar}' → '{$arabicName}'\n";
                $fixedCount++;
            }
        }
    }
}

// 3. إصلاح name الذي يحتوي على أحرف عربية
echo "\n📋 3. إصلاح name الذي يحتوي على أحرف عربية...\n";
$usersWithArabicInEnglish = DB::table('users')
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->get(['id', 'name', 'name_ar', 'employee_id']);

foreach ($usersWithArabicInEnglish as $user) {
    // التحقق من وجود أحرف عربية في name
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $user->name)) {
        // إذا كان name يحتوي على عربية، ننقلها إلى name_ar ونستخدم ترجمة إنجليزية
        if (empty($user->name_ar) || $user->name_ar === $user->name) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name_ar' => $user->name,
                    'name' => 'System Admin' // ترجمة تقريبية
                ]);
            
            echo "   ✅ ID {$user->id} ({$user->employee_id}): نقل الاسم العربي إلى name_ar\n";
            $fixedCount++;
        }
    }
}

// 4. إصلاح المستخدمين الذين لديهم name_ar فارغ أو متطابق مع name
echo "\n📋 4. إصلاح المستخدمين بدون ترجمة عربية صحيحة...\n";
$usersWithoutArabic = DB::table('users')
    ->where(function($query) {
        $query->whereNull('name_ar')
              ->orWhere('name_ar', '')
              ->orWhereColumn('name_ar', 'name');
    })
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->get(['id', 'name', 'name_ar', 'employee_id']);

foreach ($usersWithoutArabic as $user) {
    // تخطي الأسماء التي هي أسماء أنظمة (Admin, Btb, etc.)
    $systemNames = ['admin', 'btb', 'trans', 'account', 'internetdept', 'contracting', 'cotracting'];
    $nameLower = strtolower($user->name);
    
    $isSystemName = false;
    foreach ($systemNames as $sysName) {
        if (stripos($nameLower, $sysName) !== false) {
            $isSystemName = true;
            break;
        }
    }
    
    if ($isSystemName) {
        // للأسماء النظامية، نستخدم نفس الاسم
        DB::table('users')
            ->where('id', $user->id)
            ->update(['name_ar' => $user->name]);
        
        echo "   ✅ ID {$user->id} ({$user->employee_id}): اسم نظامي '{$user->name}' - تم الحفاظ عليه\n";
        $fixedCount++;
    } else {
        // للأسماء العادية، نحاول الترجمة
        $arabicName = convertEnglishToArabic($user->name);
        
        if ($arabicName !== $user->name) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['name_ar' => $arabicName]);
            
            echo "   ✅ ID {$user->id} ({$user->employee_id}): '{$user->name}' → '{$arabicName}'\n";
            $fixedCount++;
        } else {
            echo "   ⚠️  ID {$user->id} ({$user->employee_id}): لا يمكن ترجمة '{$user->name}'\n";
            $skippedCount++;
        }
    }
}

// عرض الملخص
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 ملخص الإصلاحات\n";
echo str_repeat("=", 60) . "\n";
echo "✅ تم إصلاح: $fixedCount مستخدم\n";
echo "⚠️  تم التخطي: $skippedCount مستخدم\n";
echo "\n🎉 تم الانتهاء من الإصلاح!\n";
?>

