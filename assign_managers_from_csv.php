<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// تحليل ملف CSV وتحديد المديرين
function analyzeCSVAndAssignManagers() {
    $csvFile = 'Staff LIST 2025 edited.csv';
    
    if (!file_exists($csvFile)) {
        echo "ملف CSV غير موجود!\n";
        return;
    }
    
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        echo "لا يمكن فتح ملف CSV!\n";
        return;
    }
    
    // قراءة العنوان
    $header = fgetcsv($handle);
    echo "عناوين الأعمدة: " . implode(', ', $header) . "\n\n";
    
    $employees = [];
    $managers = [];
    $managerMapping = [];
    
    // قراءة البيانات
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) < 20) continue; // تجاهل الصفوف غير المكتملة
        
        $employeeCode = trim($data[1]);
        $arabicName = trim($data[2]);
        $englishName = trim($data[3]);
        $reportTo = trim($data[15]); // Report To is column 15 (0-indexed)
        $jobTitle = trim($data[16]); // Job is column 16
        $department = trim($data[14]); // Organization is column 14
        $workEmail = trim($data[12]); // Work Email is column 12
        
        if (empty($employeeCode) || $employeeCode === 'Egy Ball') continue;
        
        $employee = [
            'employee_code' => $employeeCode,
            'arabic_name' => $arabicName,
            'english_name' => $englishName,
            'report_to' => $reportTo,
            'job_title' => $jobTitle,
            'department' => $department,
            'work_email' => $workEmail
        ];
        
        $employees[] = $employee;
    }
    
    fclose($handle);
    
    echo "تم العثور على " . count($employees) . " موظف\n";
    
    // تحديد المديرين باستخدام الدالة المحسنة
    $managers = identifyManagers($employees);
    $managerMapping = [];
    
    foreach ($managers as $manager) {
        $managerMapping[$manager['employee_code']] = $manager;
    }
    
    echo "تم العثور على " . count($managers) . " مدير\n\n";
    
    // عرض المديرين
    echo "المديرون المحددون:\n";
    echo "==================\n";
    foreach ($managers as $manager) {
        echo "كود الموظف: {$manager['employee_code']}\n";
        echo "الاسم العربي: {$manager['arabic_name']}\n";
        echo "الاسم الإنجليزي: {$manager['english_name']}\n";
        echo "المسمى الوظيفي: {$manager['job_title']}\n";
        echo "القسم: {$manager['department']}\n";
        echo "البريد الإلكتروني: {$manager['work_email']}\n";
        echo "---\n";
    }
    
    return ['employees' => $employees, 'managers' => $managers, 'manager_mapping' => $managerMapping];
}

// تحديد ما إذا كان الموظف مديراً
function isManager($jobTitle, $reportTo) {
    $managerTitles = [
        'Manager', 'مدير', 'Head Of Department', 'رئيس قسم',
        'Group Chief Accountant', 'Chief Accountant', 'رئيس المحاسبين',
        'Sales Manager', 'مدير المبيعات', 'Visa Section Head', 'رئيس قسم التأشيرات',
        'Business Development Manager', 'مدير التطوير التجاري',
        'Operation Manager', 'مدير العمليات', 'Supervisor', 'مشرف',
        'IT Manager', 'مدير تقنية المعلومات', 'Assistant IT Manager', 'مساعد مدير تقنية المعلومات',
        'Tour Leader Supervisor', 'مشرف قادة الرحلات',
        'Assistant Tourism Manager', 'مساعد مدير السياحة',
        'Middle East Manager', 'مدير الشرق الأوسط',
        'Egypt Express Manager', 'مدير مصر إكسبريس',
        'Business Development Manager', 'مدير التطوير التجاري',
        'Internet Manager', 'مدير الإنترنت',
        'HR Personel', 'موظف موارد بشرية', 'HR Generalist', 'أخصائي موارد بشرية عام'
    ];
    
    // فحص المسمى الوظيفي
    foreach ($managerTitles as $title) {
        if (stripos($jobTitle, $title) !== false) {
            return true;
        }
    }
    
    // إذا كان "Report To" فارغ أو "Mr.Farouk" فهو مدير
    if (empty($reportTo) || $reportTo === 'Mr.Farouk') {
        return true;
    }
    
    return false;
}

// تحديد المديرين من خلال تحليل أفضل
function identifyManagers($employees) {
    $managers = [];
    $managerNames = [];
    
    // قائمة بأسماء المديرين المعروفين
    $knownManagers = [
        'Mr.Farouk' => 'المدير العام',
        'Abdel Hamid Mohamed' => 'مدير الحسابات',
        'Ashraf Shafie' => 'مدير المبيعات',
        'Heba Mohamed Ezzat' => 'مدير التطوير التجاري',
        'Ahmed Elsayed' => 'مدير العمليات',
        'Mohamed Anwar Awad' => 'مدير تقنية المعلومات',
        'Mousad Soliman' => 'مدير التعاقدات الدولية',
        'Wafaa Mohamed Naguib Osman' => 'مدير التعاقدات المحلية',
        'Mohamed Fathy Mohamed' => 'مدير التعاقدات الشرق أوسطية',
        'Karim Mohamed Ali' => 'مدير الإنترنت',
        'Nadia Saeed' => 'مدير التسويق',
        'Khaled Ahmed' => 'مدير الموارد البشرية'
    ];
    
    foreach ($employees as $employee) {
        $isManager = false;
        
        // فحص المسمى الوظيفي
        if (isManager($employee['job_title'], $employee['report_to'])) {
            $isManager = true;
        }
        
        // فحص إذا كان اسمه في قائمة المديرين المعروفين
        foreach ($knownManagers as $managerName => $title) {
            if (stripos($employee['arabic_name'], $managerName) !== false || 
                stripos($employee['english_name'], $managerName) !== false) {
                $isManager = true;
                break;
            }
        }
        
        // فحص إذا كان "Report To" فارغ أو "Mr.Farouk"
        if (empty($employee['report_to']) || $employee['report_to'] === 'Mr.Farouk') {
            $isManager = true;
        }
        
        if ($isManager) {
            $managers[] = $employee;
            $managerNames[] = $employee['arabic_name'];
        }
    }
    
    return $managers;
}

// إنشاء سكريبت Laravel لتعيين المديرين
function generateLaravelScript($analysis) {
    $employees = $analysis['employees'];
    $managers = $analysis['managers'];
    $managerMapping = $analysis['manager_mapping'];
    
    $script = "<?php\n\n";
    $script .= "use Illuminate\\Support\\Facades\\DB;\n";
    $script .= "use App\\Models\\User;\n\n";
    $script .= "// سكريبت تعيين المديرين للموظفين\n";
    $script .= "function assignManagersToEmployees() {\n";
    $script .= "    echo \"بدء تعيين المديرين للموظفين...\\n\";\n\n";
    
    // إنشاء mapping للمديرين
    $script .= "    // mapping للمديرين\n";
    $script .= "    \$managerMapping = [\n";
    foreach ($managers as $manager) {
        $script .= "        '{$manager['employee_code']}' => '{$manager['arabic_name']}',\n";
    }
    $script .= "    ];\n\n";
    
    // إنشاء mapping للعلاقات
    $script .= "    // mapping للعلاقات بين المديرين والموظفين\n";
    $script .= "    \$reportingRelations = [\n";
    
    $reportingRelations = [];
    foreach ($employees as $employee) {
        if (!empty($employee['report_to']) && $employee['report_to'] !== 'Mr.Farouk') {
            // البحث عن كود المدير
            $managerCode = findManagerCodeByReportTo($employee['report_to'], $managers);
            if ($managerCode) {
                $reportingRelations[$employee['employee_code']] = $managerCode;
                $script .= "        '{$employee['employee_code']}' => '{$managerCode}', // {$employee['arabic_name']} -> {$employee['report_to']}\n";
            }
        }
    }
    
    $script .= "    ];\n\n";
    
    $script .= "    // تعيين المديرين\n";
    $script .= "    foreach (\$reportingRelations as \$employeeCode => \$managerCode) {\n";
    $script .= "        \$employee = User::where('EmployeeCode', \$employeeCode)->first();\n";
    $script .= "        \$manager = User::where('EmployeeCode', \$managerCode)->first();\n";
    $script .= "        \n";
    $script .= "        if (\$employee && \$manager) {\n";
    $script .= "            \$employee->update(['manager_id' => \$manager->id]);\n";
    $script .= "            echo \"تم تعيين المدير {\$manager->name} للموظف {\$employee->name}\\n\";\n";
    $script .= "        } else {\n";
    $script .= "            echo \"لم يتم العثور على الموظف {\$employeeCode} أو المدير {\$managerCode}\\n\";\n";
    $script .= "        }\n";
    $script .= "    }\n";
    $script .= "    \n";
    $script .= "    echo \"تم الانتهاء من تعيين المديرين!\\n\";\n";
    $script .= "}\n\n";
    
    $script .= "// تشغيل السكريبت\n";
    $script .= "assignManagersToEmployees();\n";
    
    return $script;
}

// البحث عن كود المدير بناءً على "Report To"
function findManagerCodeByReportTo($reportTo, $managers) {
    // قائمة بأسماء المديرين المعروفين ومرادفاتها
    $managerAliases = [
        'Mr.Farouk' => ['Mr.Farouk', 'فاروق', 'Farouk'],
        'Abdel Hamid Mohamed' => ['Abdel Hamid Mohamed', 'عبد الحميد محمد', 'Abdel Hamid'],
        'Ashraf Shafie' => ['Ashraf Shafie', 'أشرف شافعي', 'Ashraf'],
        'Heba Mohamed Ezzat' => ['Heba Mohamed Ezzat', 'هبة محمد عزت', 'Heba'],
        'Ahmed Elsayed' => ['Ahmed Elsayed', 'أحمد السيد', 'Ahmed Elsayed'],
        'Mohamed Anwar Awad' => ['Mohamed Anwar Awad', 'محمد أنور عوض', 'Mohamed Anwar'],
        'Mousad Soliman' => ['Mousad Soliman', 'موسى سليمان', 'Mousad'],
        'Wafaa Mohamed Naguib Osman' => ['Wafaa Mohamed Naguib Osman', 'وفاء محمد نجيب عثمان', 'Wafaa'],
        'Mohamed Fathy Mohamed' => ['Mohamed Fathy Mohamed', 'محمد فتحي محمد', 'Mohamed Fathy'],
        'Karim Mohamed Ali' => ['Karim Mohamed Ali', 'كريم محمد علي', 'Karim'],
        'Nadia Saeed' => ['Nadia Saeed', 'نادية سعيد', 'Nadia'],
        'Khaled Ahmed' => ['Khaled Ahmed', 'خالد أحمد', 'Khaled']
    ];
    
    // البحث في المرادفات
    foreach ($managerAliases as $managerName => $aliases) {
        foreach ($aliases as $alias) {
            if (stripos($reportTo, $alias) !== false) {
                // البحث عن المدير في القائمة
                foreach ($managers as $manager) {
                    if (stripos($manager['arabic_name'], $managerName) !== false || 
                        stripos($manager['english_name'], $managerName) !== false) {
                        return $manager['employee_code'];
                    }
                }
            }
        }
    }
    
    // البحث المباشر في أسماء المديرين
    foreach ($managers as $manager) {
        if (stripos($manager['arabic_name'], $reportTo) !== false || 
            stripos($manager['english_name'], $reportTo) !== false) {
            return $manager['employee_code'];
        }
    }
    
    return null;
}

// تشغيل التحليل
echo "بدء تحليل ملف CSV...\n";
echo "====================\n\n";

$analysis = analyzeCSVAndAssignManagers();

if ($analysis) {
    echo "\nإنشاء سكريبت Laravel...\n";
    echo "======================\n";
    
    $laravelScript = generateLaravelScript($analysis);
    
    // حفظ السكريبت
    file_put_contents('assign_managers_script.php', $laravelScript);
    echo "تم حفظ السكريبت في assign_managers_script.php\n";
    
    // عرض السكريبت
    echo "\nالسكريبت المُنشأ:\n";
    echo "================\n";
    echo $laravelScript;
}

?>
