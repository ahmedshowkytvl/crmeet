<?php
/**
 * تحليل مفصل لملف Excel لفهم سبب عدم استيراد جميع الموظفين
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

echo "🔍 تحليل مفصل لملف Excel...\n\n";

$filePath = 'staff list 2025.xlsx';

if (!file_exists($filePath)) {
    echo "❌ الملف غير موجود: $filePath\n";
    exit(1);
}

try {
    // قراءة الملف
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // الحصول على البيانات
    $data = $worksheet->toArray();
    
    if (empty($data)) {
        echo "❌ الملف فارغ\n";
        exit(1);
    }
    
    echo "📊 إجمالي الصفوف في الملف: " . count($data) . "\n\n";
    
    // استخراج العناوين
    $headers = array_shift($data);
    echo "📋 العناوين: " . count($headers) . " عمود\n";
    
    // تحليل الصفوف
    $totalRows = count($data);
    $emptyRows = 0;
    $validRows = 0;
    $invalidRows = 0;
    $duplicateEmails = [];
    $missingEmail = 0;
    $missingName = 0;
    
    echo "\n🔄 تحليل الصفوف...\n";
    
    foreach ($data as $rowIndex => $row) {
        $rowNumber = $rowIndex + 2; // +2 لأننا بدأنا من الصف الثاني
        
        // تخطي الصفوف الفارغة تماماً
        if (empty(array_filter($row))) {
            $emptyRows++;
            continue;
        }
        
        // البحث عن الحقول المطلوبة
        $name = null;
        $email = null;
        
        foreach ($headers as $colIndex => $header) {
            $cleanHeader = trim($header);
            $value = trim($row[$colIndex] ?? '');
            
            if (in_array($cleanHeader, ['English Name/ الاسم بالانجليزية', 'Arabic Name/ الاسم بالعربية', 'name', 'Name'])) {
                if (!empty($value)) {
                    $name = $value;
                }
            }
            
            if (in_array($cleanHeader, ['Work Email / ايميل العمل', 'email', 'Email'])) {
                if (!empty($value)) {
                    $email = $value;
                }
            }
        }
        
        // تحليل الصف
        if (empty($name)) {
            $missingName++;
            $invalidRows++;
            if ($rowNumber <= 100) { // عرض أول 100 صف فقط
                echo "   ❌ الصف $rowNumber: لا يوجد اسم\n";
            }
        } elseif (empty($email)) {
            $missingEmail++;
            $invalidRows++;
            if ($rowNumber <= 100) { // عرض أول 100 صف فقط
                echo "   ❌ الصف $rowNumber: لا يوجد بريد إلكتروني - الاسم: $name\n";
            }
        } else {
            // فحص التكرار
            if (in_array($email, $duplicateEmails)) {
                $invalidRows++;
                if ($rowNumber <= 100) { // عرض أول 100 صف فقط
                    echo "   ⚠️  الصف $rowNumber: بريد إلكتروني مكرر - $email\n";
                }
            } else {
                $duplicateEmails[] = $email;
                $validRows++;
                if ($rowNumber <= 100) { // عرض أول 100 صف فقط
                    echo "   ✅ الصف $rowNumber: صالح - $name ($email)\n";
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📊 نتائج التحليل المفصل\n";
    echo str_repeat("=", 60) . "\n";
    echo "📝 إجمالي الصفوف: $totalRows\n";
    echo "⚪ صفوف فارغة: $emptyRows\n";
    echo "✅ صفوف صالحة: $validRows\n";
    echo "❌ صفوف غير صالحة: $invalidRows\n";
    echo "   - بدون اسم: $missingName\n";
    echo "   - بدون بريد إلكتروني: $missingEmail\n";
    echo "   - بريد إلكتروني مكرر: " . ($invalidRows - $missingName - $missingEmail) . "\n\n";
    
    echo "💡 التفسير:\n";
    echo "   - الملف يحتوي على " . number_format($totalRows) . " صف\n";
    echo "   - " . number_format($emptyRows) . " صف فارغ تماماً\n";
    echo "   - " . number_format($validRows) . " صف يحتوي على بيانات صالحة\n";
    echo "   - " . number_format($invalidRows) . " صف يحتوي على بيانات ناقصة أو مكررة\n\n";
    
    echo "🎯 النتيجة:\n";
    echo "   تم استيراد $validRows موظف من أصل $totalRows صف في الملف\n";
    echo "   معدل النجاح: " . round(($validRows / $totalRows) * 100, 2) . "%\n\n";
    
    if ($validRows < 50) {
        echo "🔍 تحليل إضافي للصفوف الأولى:\n";
        echo "   دعني أتحقق من أول 10 صفوف بالتفصيل...\n\n";
        
        for ($i = 0; $i < min(10, count($data)); $i++) {
            $row = $data[$i];
            $rowNumber = $i + 2;
            echo "--- الصف $rowNumber ---\n";
            foreach ($headers as $colIndex => $header) {
                $value = $row[$colIndex] ?? '';
                if (!empty($value)) {
                    echo "   " . trim($header) . ": " . trim($value) . "\n";
                }
            }
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ خطأ في تحليل الملف: " . $e->getMessage() . "\n";
    exit(1);
}
?>
