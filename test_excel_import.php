<?php
/**
 * سكريبت اختبار استيراد بيانات الموظفين من Excel
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

echo "🔍 اختبار قراءة ملف Excel...\n\n";

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
    
    echo "✅ تم قراءة الملف بنجاح\n";
    echo "📊 عدد الصفوف: " . count($data) . "\n\n";
    
    // عرض العناوين
    $headers = array_shift($data);
    echo "📋 العناوين المكتشفة:\n";
    foreach ($headers as $index => $header) {
        $cleanHeader = trim($header);
        $encoding = mb_detect_encoding($cleanHeader);
        echo "   " . ($index + 1) . ". '$cleanHeader' (ترميز: $encoding)\n";
    }
    
    echo "\n📝 عينة من البيانات (الصفوف الأولى):\n";
    $sampleRows = array_slice($data, 0, 3);
    
    foreach ($sampleRows as $rowIndex => $row) {
        echo "\n--- الصف " . ($rowIndex + 2) . " ---\n";
        foreach ($headers as $colIndex => $header) {
            $value = $row[$colIndex] ?? '';
            $cleanValue = trim($value);
            $encoding = mb_detect_encoding($cleanValue);
            echo "   " . trim($header) . ": '$cleanValue' (ترميز: $encoding)\n";
        }
    }
    
    echo "\n✅ اختبار القراءة مكتمل!\n";
    echo "💡 يمكنك الآن تشغيل: php import_staff_excel.php '$filePath'\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في قراءة الملف: " . $e->getMessage() . "\n";
    exit(1);
}
?>
