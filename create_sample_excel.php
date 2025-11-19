<?php
/**
 * إنشاء ملف Excel نموذجي لاختبار الاستيراد
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

echo "📝 إنشاء ملف Excel نموذجي...\n";

// إنشاء جدول بيانات جديد
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// إضافة العناوين
$headers = [
    'name' => 'الاسم',
    'name_ar' => 'الاسم بالعربية', 
    'email' => 'البريد الإلكتروني',
    'work_email' => 'البريد الوظيفي',
    'phone_work' => 'هاتف العمل',
    'phone_personal' => 'الهاتف الشخصي',
    'job_title' => 'المسمى الوظيفي',
    'position' => 'المنصب',
    'position_ar' => 'المنصب بالعربية',
    'department' => 'القسم',
    'role' => 'الدور',
    'manager' => 'المدير',
    'address' => 'العنوان',
    'address_ar' => 'العنوان بالعربية',
    'birth_date' => 'تاريخ الميلاد',
    'bio' => 'نبذة شخصية',
    'notes' => 'ملاحظات',
    'nationality' => 'الجنسية',
    'city' => 'المدينة',
    'country' => 'البلد'
];

// كتابة العناوين
$col = 1;
foreach ($headers as $key => $header) {
    $sheet->setCellValue([$col, 1], $header);
    $col++;
}

// إضافة بيانات نموذجية
$sampleData = [
    [
        'name' => 'أحمد محمد علي',
        'name_ar' => 'أحمد محمد علي',
        'email' => 'ahmed.mohamed@company.com',
        'work_email' => 'ahmed.mohamed@company.com',
        'phone_work' => '+201234567890',
        'phone_personal' => '+201987654321',
        'job_title' => 'مطور برمجيات',
        'position' => 'Software Developer',
        'position_ar' => 'مطور برمجيات',
        'department' => 'IT Department',
        'role' => 'software_developer',
        'manager' => 'System Administrator',
        'address' => 'القاهرة، مصر',
        'address_ar' => 'القاهرة، مصر',
        'birth_date' => '1990-05-15',
        'bio' => 'مطور برمجيات متخصص في Laravel و PHP',
        'notes' => 'موظف ممتاز',
        'nationality' => 'مصري',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    [
        'name' => 'فاطمة أحمد حسن',
        'name_ar' => 'فاطمة أحمد حسن',
        'email' => 'fatma.ahmed@company.com',
        'work_email' => 'fatma.ahmed@company.com',
        'phone_work' => '+201234567891',
        'phone_personal' => '+201987654322',
        'job_title' => 'مدير مشروع',
        'position' => 'Project Manager',
        'position_ar' => 'مدير مشروع',
        'department' => 'IT Department',
        'role' => 'manager',
        'manager' => 'System Administrator',
        'address' => 'الإسكندرية، مصر',
        'address_ar' => 'الإسكندرية، مصر',
        'birth_date' => '1985-08-20',
        'bio' => 'مدير مشروع ذو خبرة واسعة',
        'notes' => 'قائد فريق ممتاز',
        'nationality' => 'مصري',
        'city' => 'الإسكندرية',
        'country' => 'مصر'
    ],
    [
        'name' => 'محمد عبد الرحمن',
        'name_ar' => 'محمد عبد الرحمن',
        'email' => 'mohamed.abdelrahman@company.com',
        'work_email' => 'mohamed.abdelrahman@company.com',
        'phone_work' => '+201234567892',
        'phone_personal' => '+201987654323',
        'job_title' => 'محاسب',
        'position' => 'Accountant',
        'position_ar' => 'محاسب',
        'department' => 'Finance Department',
        'role' => 'employee',
        'manager' => 'فاطمة أحمد حسن',
        'address' => 'الجيزة، مصر',
        'address_ar' => 'الجيزة، مصر',
        'birth_date' => '1992-12-10',
        'bio' => 'محاسب متخصص في المحاسبة المالية',
        'notes' => 'دقيق في العمل',
        'nationality' => 'مصري',
        'city' => 'الجيزة',
        'country' => 'مصر'
    ]
];

// كتابة البيانات
$row = 2;
foreach ($sampleData as $data) {
    $col = 1;
    foreach ($headers as $key => $header) {
        $value = $data[$key] ?? '';
        $sheet->setCellValue([$col, $row], $value);
        $col++;
    }
    $row++;
}

// حفظ الملف
$filename = 'sample_staff_data.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "✅ تم إنشاء الملف: $filename\n";
echo "📊 عدد الصفوف: " . (count($sampleData) + 1) . " (بما في ذلك العنوان)\n";
echo "📋 عدد الأعمدة: " . count($headers) . "\n\n";

echo "💡 يمكنك الآن اختبار الاستيراد باستخدام:\n";
echo "   php test_excel_import.php '$filename'\n";
echo "   php import_staff_excel.php '$filename'\n";
?>
