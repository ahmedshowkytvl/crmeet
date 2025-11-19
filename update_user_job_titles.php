<?php
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

// خريطة المسميات الوظيفية الحالية إلى المسميات الجديدة
$jobTitleMapping = [
    'مدير تقنية المعلومات' => 'مدير تقنية المعلومات',
    'مطور برمجيات' => 'مطور برمجيات',
    'محلل أنظمة' => 'محلل أنظمة',
    'مدير الموارد البشرية' => 'مدير الموارد البشرية',
    'أخصائي توظيف' => 'أخصائي توظيف',
    'مدير المبيعات' => 'مدير المبيعات',
    'مندوب مبيعات' => 'مندوب مبيعات',
    'مدير المحاسبة' => 'مدير المحاسبة',
    'محاسب' => 'موظف محاسبة',
    'مدير التسويق' => 'مدير التسويق',
    'أخصائي تسويق رقمي' => 'أخصائي تسويق رقمي'
];

// تحديث المسميات الوظيفية للمستخدمين
$updateCount = 0;
$errorCount = 0;

foreach ($jobTitleMapping as $oldTitle => $newTitle) {
    try {
        // البحث عن المسمى الوظيفي الجديد
        $stmt = $pdo->prepare("SELECT id FROM job_titles WHERE name_ar = ?");
        $stmt->execute([$newTitle]);
        $jobTitle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($jobTitle) {
            // تحديث المستخدمين الذين لديهم المسمى الوظيفي القديم
            $updateSql = "UPDATE users SET job_title = ? WHERE job_title = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$newTitle, $oldTitle]);
            
            $affectedRows = $updateStmt->rowCount();
            if ($affectedRows > 0) {
                $updateCount += $affectedRows;
                echo "تم تحديث $affectedRows مستخدم من '$oldTitle' إلى '$newTitle'\n";
            }
        } else {
            echo "لم يتم العثور على المسمى الوظيفي: $newTitle\n";
        }
        
    } catch (PDOException $e) {
        $errorCount++;
        echo "خطأ في تحديث المسمى الوظيفي '$oldTitle': " . $e->getMessage() . "\n";
    }
}

// إضافة مسميات وظيفية جديدة للمستخدمين الذين لا يملكون مسمى وظيفي
try {
    // الحصول على المستخدمين الذين لا يملكون مسمى وظيفي
    $stmt = $pdo->query("SELECT id, name, department_id FROM users WHERE job_title IS NULL OR job_title = ''");
    $usersWithoutTitles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=== المستخدمين بدون مسمى وظيفي ===\n";
    echo "عدد المستخدمين: " . count($usersWithoutTitles) . "\n";
    
    // ربط المستخدمين بالمسميات الوظيفية المناسبة حسب القسم
    $departmentJobTitles = [
        3 => 'موظف محاسبة', // Accounts
        4 => 'مندوب مبيعات', // BTC - Sales
        5 => 'موظف تجاري', // Commercial
        6 => 'موظف عمليات', // Operation
        7 => 'موظف تقنية معلومات', // IT
        8 => 'موظف إداري', // Admin
        9 => 'موظف موارد بشرية', // HR
        10 => 'موظف مرور', // Traffic
        11 => 'موظف مقاولات', // Contracting Egypt
        12 => 'موظف مقاولات', // Contracting International
        13 => 'موظف مقاولات', // Contracting Middle East
        14 => 'موظف إنترنت', // Internet
        15 => 'موظف تسويق' // Marketing
    ];
    
    foreach ($usersWithoutTitles as $user) {
        $departmentId = $user['department_id'];
        $defaultTitle = $departmentJobTitles[$departmentId] ?? 'موظف';
        
        try {
            $updateSql = "UPDATE users SET job_title = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$defaultTitle, $user['id']]);
            
            echo "تم تعيين المسمى الوظيفي '$defaultTitle' للمستخدم: {$user['name']}\n";
            $updateCount++;
            
        } catch (PDOException $e) {
            echo "خطأ في تعيين المسمى الوظيفي للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على المستخدمين: " . $e->getMessage() . "\n";
}

// تحديث المسميات الوظيفية للمستخدمين الجدد
try {
    $newUsers = [
        ['id' => 124, 'name' => 'أحمد محمد علي', 'job_title' => 'مدير تقنية المعلومات'],
        ['id' => 125, 'name' => 'فاطمة أحمد حسن', 'job_title' => 'مطور برمجيات'],
        ['id' => 126, 'name' => 'محمد إبراهيم سعد', 'job_title' => 'محلل أنظمة'],
        ['id' => 127, 'name' => 'نور الدين محمود', 'job_title' => 'مدير الموارد البشرية'],
        ['id' => 128, 'name' => 'سارة محمد عبدالله', 'job_title' => 'أخصائي توظيف'],
        ['id' => 129, 'name' => 'خالد أحمد مصطفى', 'job_title' => 'مدير المبيعات'],
        ['id' => 130, 'name' => 'مريم علي حسن', 'job_title' => 'مندوب مبيعات'],
        ['id' => 131, 'name' => 'عبدالرحمن محمد إبراهيم', 'job_title' => 'مدير المحاسبة'],
        ['id' => 132, 'name' => 'هند محمد السيد', 'job_title' => 'موظف محاسبة'],
        ['id' => 133, 'name' => 'ياسمين أحمد فؤاد', 'job_title' => 'مدير التسويق'],
        ['id' => 134, 'name' => 'أحمد محمود رشاد', 'job_title' => 'أخصائي تسويق رقمي']
    ];
    
    echo "\n=== تحديث المسميات الوظيفية للمستخدمين الجدد ===\n";
    
    foreach ($newUsers as $user) {
        try {
            $updateSql = "UPDATE users SET job_title = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$user['job_title'], $user['id']]);
            
            echo "تم تحديث المسمى الوظيفي للمستخدم {$user['name']}: {$user['job_title']}\n";
            $updateCount++;
            
        } catch (PDOException $e) {
            echo "خطأ في تحديث المسمى الوظيفي للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
} catch (Exception $e) {
    echo "خطأ في تحديث المستخدمين الجدد: " . $e->getMessage() . "\n";
}

echo "\n=== ملخص النتائج ===\n";
echo "تم تحديث $updateCount مسمى وظيفي\n";
echo "فشل في تحديث $errorCount مسمى وظيفي\n";

// عرض إحصائيات المسميات الوظيفية
$stmt = $pdo->query("
    SELECT job_title, COUNT(*) as user_count 
    FROM users 
    WHERE job_title IS NOT NULL AND job_title != '' 
    GROUP BY job_title 
    ORDER BY user_count DESC 
    LIMIT 20
");
$jobTitleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== أكثر المسميات الوظيفية شيوعاً ===\n";
foreach ($jobTitleStats as $stat) {
    echo "{$stat['job_title']}: {$stat['user_count']} مستخدم\n";
}

// عرض المستخدمين حسب القسم والمسمى الوظيفي
$stmt = $pdo->query("
    SELECT d.name_ar as department_name, u.job_title, COUNT(*) as user_count 
    FROM departments d 
    LEFT JOIN users u ON d.id = u.department_id 
    WHERE u.job_title IS NOT NULL AND u.job_title != ''
    GROUP BY d.id, d.name_ar, u.job_title 
    ORDER BY d.name_ar, user_count DESC
");
$departmentJobStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== المستخدمين حسب القسم والمسمى الوظيفي ===\n";
$currentDepartment = '';
foreach ($departmentJobStats as $stat) {
    if ($currentDepartment != $stat['department_name']) {
        $currentDepartment = $stat['department_name'];
        echo "\n--- $currentDepartment ---\n";
    }
    echo "  {$stat['job_title']}: {$stat['user_count']} مستخدم\n";
}
?>
