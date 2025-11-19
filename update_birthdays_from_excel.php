<?php

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== بدء عملية تحديث تواريخ الميلاد من ملف Excel ===\n\n";

try {
    // 1. تحميل ملف Excel
    echo "1. تحميل ملف Excel...\n";
    $spreadsheet = IOFactory::load('staff list 2025.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    $data = $worksheet->toArray();
    
    // إزالة الصف الأول (العناوين)
    $headers = array_shift($data);
    
    // العثور على فهارس الأعمدة المطلوبة
    $nameIndex = array_search('English Name/ الاسم بالانجليزية', $headers);
    $emailIndex = array_search('Work Email / ايميل العمل', $headers);
    $birthdayIndex = array_search('Birth Date / تاريخ الميلاد  ', $headers);
    
    if ($nameIndex === false || $emailIndex === false || $birthdayIndex === false) {
        throw new Exception("لم يتم العثور على الأعمدة المطلوبة في ملف Excel");
    }
    
    echo "تم العثور على الأعمدة:\n";
    echo "- الاسم: العمود " . ($nameIndex + 1) . "\n";
    echo "- الإيميل: العمود " . ($emailIndex + 1) . "\n";
    echo "- تاريخ الميلاد: العمود " . ($birthdayIndex + 1) . "\n\n";
    
    // 2. معالجة البيانات من Excel
    echo "2. معالجة البيانات من Excel...\n";
    $excelData = [];
    $processedCount = 0;
    
    foreach ($data as $rowIndex => $row) {
        if (empty($row[$emailIndex]) || empty($row[$birthdayIndex])) {
            continue;
        }
        
        $email = trim($row[$emailIndex]);
        $name = trim($row[$nameIndex]);
        $birthday = $row[$birthdayIndex];
        
        // تحويل تاريخ الميلاد إلى تنسيق صحيح
        if ($birthday instanceof DateTime) {
            $birthday = $birthday->format('Y-m-d');
        } elseif (is_string($birthday)) {
            // محاولة تحويل التاريخ
            $date = DateTime::createFromFormat('Y-m-d', $birthday);
            if (!$date) {
                $date = DateTime::createFromFormat('d/m/Y', $birthday);
            }
            if (!$date) {
                $date = DateTime::createFromFormat('m/d/Y', $birthday);
            }
            if ($date) {
                $birthday = $date->format('Y-m-d');
            } else {
                continue; // تخطي التواريخ غير الصحيحة
            }
        } else {
            continue;
        }
        
        $excelData[] = [
            'email' => $email,
            'name' => $name,
            'birthday' => $birthday,
            'row' => $rowIndex + 2 // +2 لأننا أزلنا الصف الأول ونبدأ العد من 1
        ];
        
        $processedCount++;
    }
    
    echo "تم معالجة " . $processedCount . " سجل من ملف Excel\n\n";
    
    // 3. الحصول على بيانات المستخدمين من قاعدة البيانات
    echo "3. جلب بيانات المستخدمين من قاعدة البيانات...\n";
    $users = DB::table('users')
        ->select('id', 'name', 'email', 'birthday', 'birth_date')
        ->get()
        ->keyBy('email');
    
    echo "تم جلب " . $users->count() . " مستخدم من قاعدة البيانات\n\n";
    
    // 4. مطابقة السجلات وتحديثها
    echo "4. بدء عملية المطابقة والتحديث...\n";
    
    $updatedCount = 0;
    $notFoundEmails = [];
    $alreadyHaveBirthday = 0;
    $errors = [];
    
    DB::beginTransaction();
    
    try {
        foreach ($excelData as $excelRow) {
            $email = $excelRow['email'];
            $birthday = $excelRow['birthday'];
            $name = $excelRow['name'];
            
            if (isset($users[$email])) {
                $user = $users[$email];
                
                // التحقق من وجود تاريخ ميلاد بالفعل
                if (!empty($user->birthday) || !empty($user->birth_date)) {
                    $alreadyHaveBirthday++;
                    continue;
                }
                
                // تحديث تاريخ الميلاد
                $updateResult = DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'birthday' => $birthday,
                        'birth_date' => $birthday,
                        'updated_at' => now()
                    ]);
                
                if ($updateResult) {
                    $updatedCount++;
                    echo "تم تحديث: {$name} ({$email}) - {$birthday}\n";
                } else {
                    $errors[] = "فشل في تحديث: {$name} ({$email})";
                }
            } else {
                $notFoundEmails[] = [
                    'email' => $email,
                    'name' => $name,
                    'birthday' => $birthday
                ];
            }
        }
        
        DB::commit();
        echo "\nتم حفظ التحديثات بنجاح!\n\n";
        
    } catch (Exception $e) {
        DB::rollback();
        throw $e;
    }
    
    // 5. إنشاء التقرير
    echo "=== تقرير النتائج ===\n\n";
    echo "إجمالي السجلات المعالجة من Excel: " . $processedCount . "\n";
    echo "عدد المستخدمين المحدثين: " . $updatedCount . "\n";
    echo "عدد المستخدمين الذين لديهم تاريخ ميلاد بالفعل: " . $alreadyHaveBirthday . "\n";
    echo "عدد الإيميلات غير الموجودة في قاعدة البيانات: " . count($notFoundEmails) . "\n";
    echo "عدد الأخطاء: " . count($errors) . "\n\n";
    
    // عرض الإيميلات غير الموجودة
    if (!empty($notFoundEmails)) {
        echo "=== الإيميلات غير الموجودة في قاعدة البيانات ===\n";
        foreach ($notFoundEmails as $item) {
            echo "- {$item['name']} ({$item['email']}) - {$item['birthday']}\n";
        }
        echo "\n";
    }
    
    // عرض الأخطاء
    if (!empty($errors)) {
        echo "=== الأخطاء ===\n";
        foreach ($errors as $error) {
            echo "- {$error}\n";
        }
        echo "\n";
    }
    
    // فحص المستخدمين الذين لا يزالون بدون تاريخ ميلاد
    echo "=== المستخدمين الذين لا يزالون بدون تاريخ ميلاد ===\n";
    $usersWithoutBirthday = DB::table('users')
        ->whereNull('birthday')
        ->whereNull('birth_date')
        ->whereNotNull('email')
        ->select('id', 'name', 'email')
        ->get();
    
    echo "عدد المستخدمين بدون تاريخ ميلاد: " . $usersWithoutBirthday->count() . "\n";
    if ($usersWithoutBirthday->count() > 0) {
        echo "أول 10 مستخدمين:\n";
        foreach ($usersWithoutBirthday->take(10) as $user) {
            echo "- {$user->name} ({$user->email})\n";
        }
    }
    
    echo "\n=== انتهت العملية بنجاح ===\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "الملف: " . $e->getFile() . "\n";
    echo "السطر: " . $e->getLine() . "\n";
    exit(1);
}

