<?php
/**
 * ربط الصور بالمستخدمين في نظام CRM
 * Assign Photos to Users in CRM System
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

class PhotoUserMatcher
{
    private $photosFolder;
    private $users;
    private $matches = [];
    private $unmatchedUsers = [];
    private $unmatchedPhotos = [];

    public function __construct($photosFolder = 'D:/ett/new')
    {
        $this->photosFolder = $photosFolder;
    }

    /**
     * تحميل بيانات المستخدمين من قاعدة البيانات
     */
    public function loadUsers()
    {
        echo "🔄 تحميل بيانات المستخدمين من قاعدة البيانات...\n";
        
        $this->users = DB::table('users')
            ->select('id', 'name', 'name_ar', 'email', 'profile_picture')
            ->where('user_type', 'employee')
            ->get()
            ->toArray();
        
        echo "✅ تم تحميل " . count($this->users) . " مستخدم من قاعدة البيانات\n";
        return true;
    }

    /**
     * تحميل قائمة الصور من المجلد
     */
    public function loadPhotos()
    {
        echo "🔄 تحميل قائمة الصور من المجلد...\n";
        
        $imageExtensions = ['png', 'jpg', 'jpeg', 'bmp', 'tiff', 'webp'];
        $this->unmatchedPhotos = [];
        
        if (is_dir($this->photosFolder)) {
            $files = scandir($this->photosFolder);
            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $imageExtensions)) {
                    $this->unmatchedPhotos[] = $file;
                }
            }
        }
        
        echo "✅ تم العثور على " . count($this->unmatchedPhotos) . " صورة في المجلد\n";
        return count($this->unmatchedPhotos) > 0;
    }

    /**
     * تطبيع الاسم للمقارنة
     */
    private function normalizeName($name)
    {
        if (empty($name)) return "";
        
        // تحويل إلى أحرف صغيرة
        $name = strtolower(trim($name));
        
        // إزالة الأرقام والرموز الخاصة
        $name = preg_replace('/[0-9\-_\(\)\[\]\.]/', '', $name);
        
        // إزالة المسافات الزائدة
        $name = preg_replace('/\s+/', ' ', $name);
        
        // إزالة كلمات شائعة
        $commonWords = ['mr', 'mrs', 'miss', 'dr', 'prof', 'eng', 'ahmed', 'mohamed', 'ali', 'hassan'];
        $words = explode(' ', $name);
        $words = array_filter($words, function($word) use ($commonWords) {
            return !in_array($word, $commonWords);
        });
        
        return implode(' ', $words);
    }

    /**
     * حساب درجة التشابه بين اسمين
     */
    private function calculateSimilarity($name1, $name2)
    {
        if (empty($name1) || empty($name2)) return 0.0;
        
        // تطبيع الأسماء
        $normName1 = $this->normalizeName($name1);
        $normName2 = $this->normalizeName($name2);
        
        if (empty($normName1) || empty($normName2)) return 0.0;
        
        // حساب التشابه
        similar_text($normName1, $normName2, $similarity);
        $similarity = $similarity / 100;
        
        // مكافأة للكلمات المشتركة
        $words1 = explode(' ', $normName1);
        $words2 = explode(' ', $normName2);
        $commonWords = array_intersect($words1, $words2);
        
        if (!empty($commonWords)) {
            $wordBonus = count($commonWords) / max(count($words1), count($words2));
            $similarity = max($similarity, $wordBonus);
        }
        
        return $similarity;
    }

    /**
     * البحث عن أفضل مطابقة صورة للمستخدم
     */
    private function findBestPhotoMatch($user)
    {
        $userName = $user->name ?? $user->name_ar ?? '';
        
        if (empty($userName)) return [null, 0.0];
        
        $bestMatch = null;
        $bestSimilarity = 0.0;
        
        foreach ($this->unmatchedPhotos as $photoFile) {
            // استخراج الاسم من اسم الملف
            $photoName = pathinfo($photoFile, PATHINFO_FILENAME);
            
            // حساب التشابه
            $similarity = $this->calculateSimilarity($userName, $photoName);
            
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $bestMatch = $photoFile;
            }
        }
        
        return [$bestMatch, $bestSimilarity];
    }

    /**
     * ربط المستخدمين بالصور
     */
    public function matchUsersWithPhotos($minSimilarity = 0.3)
    {
        echo "🔄 بدء عملية ربط المستخدمين بالصور...\n";
        
        $this->matches = [];
        $this->unmatchedUsers = [];
        
        foreach ($this->users as $user) {
            $userName = $user->name ?? $user->name_ar ?? '';
            
            // البحث عن أفضل مطابقة
            [$bestPhoto, $similarity] = $this->findBestPhotoMatch($user);
            
            if ($bestPhoto && $similarity >= $minSimilarity) {
                // إضافة المطابقة
                $match = [
                    'user_id' => $user->id,
                    'user_name' => $userName,
                    'user_email' => $user->email,
                    'photo_file' => $bestPhoto,
                    'similarity' => $similarity,
                    'match_quality' => $this->getMatchQuality($similarity)
                ];
                $this->matches[] = $match;
                
                // إزالة الصورة من القائمة غير المطابقة
                $key = array_search($bestPhoto, $this->unmatchedPhotos);
                if ($key !== false) {
                    unset($this->unmatchedPhotos[$key]);
                }
                
                echo "✅ مطابقة: {$userName} -> {$bestPhoto} (تشابه: " . number_format($similarity, 2) . ")\n";
            } else {
                // إضافة المستخدم للقائمة غير المطابقة
                $this->unmatchedUsers[] = [
                    'user_id' => $user->id,
                    'user_name' => $userName,
                    'user_email' => $user->email,
                    'reason' => $bestPhoto ? 'تشابه منخفض: ' . number_format($similarity, 2) : 'لا توجد صورة مناسبة'
                ];
                echo "❌ لا توجد مطابقة: {$userName}\n";
            }
        }
        
        echo "\n📊 نتائج المطابقة:\n";
        echo "✅ مطابقات ناجحة: " . count($this->matches) . "\n";
        echo "❌ مستخدمون بدون صور: " . count($this->unmatchedUsers) . "\n";
        echo "🖼️ صور بدون مطابقة: " . count($this->unmatchedPhotos) . "\n";
    }

    /**
     * تحديد جودة المطابقة
     */
    private function getMatchQuality($similarity)
    {
        if ($similarity >= 0.8) return "ممتازة";
        elseif ($similarity >= 0.6) return "جيدة";
        elseif ($similarity >= 0.4) return "متوسطة";
        else return "ضعيفة";
    }

    /**
     * نسخ الصور إلى مجلد النظام
     */
    public function copyPhotosToSystem()
    {
        echo "🔄 نسخ الصور إلى مجلد النظام...\n";
        
        $targetDir = public_path('images/users/');
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $copiedCount = 0;
        
        foreach ($this->matches as $match) {
            $sourceFile = $this->photosFolder . '/' . $match['photo_file'];
            $targetFile = $targetDir . 'user_' . $match['user_id'] . '_' . $match['photo_file'];
            
            if (file_exists($sourceFile)) {
                if (copy($sourceFile, $targetFile)) {
                    $copiedCount++;
                    echo "✅ تم نسخ: {$match['user_name']} -> user_{$match['user_id']}_{$match['photo_file']}\n";
                } else {
                    echo "❌ فشل في نسخ: {$match['user_name']}\n";
                }
            }
        }
        
        echo "📁 تم نسخ {$copiedCount} صورة إلى مجلد النظام\n";
    }

    /**
     * تحديث قاعدة البيانات بروابط الصور
     */
    public function updateDatabase()
    {
        echo "🔄 تحديث قاعدة البيانات بروابط الصور...\n";
        
        $updatedCount = 0;
        
        foreach ($this->matches as $match) {
            $photoPath = 'images/users/user_' . $match['user_id'] . '_' . $match['photo_file'];
            
            $result = DB::table('users')
                ->where('id', $match['user_id'])
                ->update([
                    'profile_picture' => $photoPath,
                    'updated_at' => now()
                ]);
            
            if ($result) {
                $updatedCount++;
                echo "✅ تم تحديث: {$match['user_name']} -> {$photoPath}\n";
            } else {
                echo "❌ فشل في تحديث: {$match['user_name']}\n";
            }
        }
        
        echo "💾 تم تحديث {$updatedCount} مستخدم في قاعدة البيانات\n";
    }

    /**
     * حفظ النتائج في ملف
     */
    public function saveResults($outputFile = 'photo_assignment_results.json')
    {
        $results = [
            'summary' => [
                'total_users' => count($this->users),
                'total_photos' => count($this->unmatchedPhotos) + count($this->matches),
                'successful_matches' => count($this->matches),
                'unmatched_users' => count($this->unmatchedUsers),
                'unmatched_photos' => count($this->unmatchedPhotos)
            ],
            'matches' => $this->matches,
            'unmatched_users' => $this->unmatchedUsers,
            'unmatched_photos' => array_values($this->unmatchedPhotos)
        ];
        
        file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "💾 تم حفظ النتائج في: {$outputFile}\n";
    }

    /**
     * طباعة ملخص النتائج
     */
    public function printSummary()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 ملخص ربط المستخدمين بالصور\n";
        echo str_repeat("=", 60) . "\n";
        echo "👥 إجمالي المستخدمين: " . count($this->users) . "\n";
        echo "🖼️ إجمالي الصور: " . (count($this->unmatchedPhotos) + count($this->matches)) . "\n";
        echo "✅ مطابقات ناجحة: " . count($this->matches) . "\n";
        echo "❌ مستخدمون بدون صور: " . count($this->unmatchedUsers) . "\n";
        echo "🖼️ صور بدون مطابقة: " . count($this->unmatchedPhotos) . "\n";
        
        if (!empty($this->matches)) {
            echo "\n🎯 أفضل المطابقات:\n";
            usort($this->matches, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            foreach (array_slice($this->matches, 0, 5) as $match) {
                echo "  • {$match['user_name']} -> {$match['photo_file']} ({$match['similarity']})\n";
            }
        }
    }
}

// تشغيل السكريبت
echo "🔗 ربط الصور بالمستخدمين في نظام CRM\n";
echo str_repeat("=", 50) . "\n";

try {
    $matcher = new PhotoUserMatcher();
    
    // تحميل البيانات
    if (!$matcher->loadUsers()) {
        throw new Exception("فشل في تحميل بيانات المستخدمين");
    }
    
    if (!$matcher->loadPhotos()) {
        throw new Exception("لم يتم العثور على صور في المجلد");
    }
    
    // تحديد الحد الأدنى للتشابه
    $minSimilarity = 0.3;
    echo "استخدام الحد الأدنى للتشابه: {$minSimilarity}\n\n";
    
    // بدء عملية المطابقة
    $matcher->matchUsersWithPhotos($minSimilarity);
    
    // نسخ الصور إلى النظام
    $matcher->copyPhotosToSystem();
    
    // تحديث قاعدة البيانات
    $matcher->updateDatabase();
    
    // حفظ النتائج
    $matcher->saveResults();
    
    // طباعة الملخص
    $matcher->printSummary();
    
    echo "\n✅ تم الانتهاء من ربط الصور بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
