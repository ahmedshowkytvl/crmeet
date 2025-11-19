<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// إعدادات قاعدة البيانات
$host = '127.0.0.1';
$port = '5432';
$dbname = 'CRM_ALL';
$username = 'postgres';
$password = '';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "تم الاتصال بقاعدة البيانات بنجاح\n";
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n");
}

// قراءة ملف Excel
$excelFile = 'Egyball 2025.xlsx';

if (!file_exists($excelFile)) {
    die("ملف Excel غير موجود: $excelFile\n");
}

try {
    $spreadsheet = IOFactory::load($excelFile);
    
    // فحص جميع الأوراق
    $sheetNames = $spreadsheet->getSheetNames();
    echo "أوراق العمل الموجودة: " . implode(', ', $sheetNames) . "\n\n";
    
    // قراءة كل ورقة عمل
    foreach ($sheetNames as $sheetName) {
        echo "=== ورقة العمل: $sheetName ===\n";
        $worksheet = $spreadsheet->getSheetByName($sheetName);
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        echo "عدد الصفوف: $highestRow\n";
        echo "عدد الأعمدة: $highestColumn\n";
        
        // قراءة العناوين
        $headers = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $header = $worksheet->getCell($col . '1')->getValue();
            if (!empty($header)) {
                $headers[$col] = $header;
            }
        }
        
        echo "عناوين الأعمدة:\n";
        foreach ($headers as $col => $header) {
            echo "$col: $header\n";
        }
        echo "\n";
        
        // قراءة البيانات
        $users = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $user = [];
            $hasData = false;
            
            foreach ($headers as $col => $header) {
                $value = $worksheet->getCell($col . $row)->getValue();
                if (!empty($value)) {
                    $hasData = true;
                }
                $user[$header] = $value;
            }
            
            if ($hasData) {
                $users[] = $user;
            }
        }
        
        echo "تم قراءة " . count($users) . " مستخدم من ورقة العمل: $sheetName\n\n";
        
        // عرض أول 3 مستخدمين كمثال
        if (count($users) > 0) {
            echo "عينة من البيانات:\n";
            for ($i = 0; $i < min(3, count($users)); $i++) {
                echo "المستخدم " . ($i + 1) . ":\n";
                foreach ($users[$i] as $key => $value) {
                    if (!empty($value)) {
                        echo "  $key: $value\n";
                    }
                }
                echo "\n";
            }
        }
        
        // حفظ البيانات في ملف JSON للتحليل
        if (count($users) > 0) {
            $filename = "excel_users_data_$sheetName.json";
            file_put_contents($filename, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            echo "تم حفظ البيانات في ملف $filename\n";
        }
        
        echo "\n" . str_repeat("=", 50) . "\n\n";
    }
    
} catch (Exception $e) {
    die("خطأ في قراءة ملف Excel: " . $e->getMessage() . "\n");
}
?>
