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

echo "=== تقرير التحقق من صحة البيانات المستوردة ===\n\n";

// 1. إحصائيات عامة
echo "=== الإحصائيات العامة ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
echo "إجمالي المستخدمين: $totalUsers\n";

$stmt = $pdo->query("SELECT COUNT(*) as total_departments FROM departments");
$totalDepartments = $stmt->fetch(PDO::FETCH_ASSOC)['total_departments'];
echo "إجمالي الأقسام: $totalDepartments\n";

$stmt = $pdo->query("SELECT COUNT(*) as total_roles FROM roles");
$totalRoles = $stmt->fetch(PDO::FETCH_ASSOC)['total_roles'];
echo "إجمالي الأدوار: $totalRoles\n";

$stmt = $pdo->query("SELECT COUNT(*) as total_job_titles FROM job_titles");
$totalJobTitles = $stmt->fetch(PDO::FETCH_ASSOC)['total_job_titles'];
echo "إجمالي المسميات الوظيفية: $totalJobTitles\n\n";

// 2. التحقق من المستخدمين الجدد
echo "=== المستخدمين الجدد (آخر 11 مستخدم) ===\n";
$stmt = $pdo->query("
    SELECT u.id, u.name, u.name_ar, u.email, u.job_title, u.employee_id, u.\"EmployeeCode\", 
           d.name_ar as department_name, r.name_ar as role_name, u.created_at
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.id DESC
    LIMIT 11
");
$newUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($newUsers as $user) {
    echo "ID: {$user['id']} | الاسم: {$user['name_ar']} | البريد: {$user['email']} | المسمى: {$user['job_title']} | القسم: {$user['department_name']} | الدور: {$user['role_name']} | تاريخ الإنشاء: {$user['created_at']}\n";
}
echo "\n";

// 3. التحقق من صحة البيانات
echo "=== التحقق من صحة البيانات ===\n";

// المستخدمين بدون بريد إلكتروني
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email IS NULL OR email = ''");
$noEmail = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون بريد إلكتروني: $noEmail\n";

// المستخدمين بدون مسمى وظيفي
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE job_title IS NULL OR job_title = ''");
$noJobTitle = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون مسمى وظيفي: $noJobTitle\n";

// المستخدمين بدون قسم
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE department_id IS NULL");
$noDepartment = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون قسم: $noDepartment\n";

// المستخدمين بدون دور
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role_id IS NULL");
$noRole = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون دور: $noRole\n";

// المستخدمين بدون كود موظف
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE employee_id IS NULL OR employee_id = ''");
$noEmployeeId = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون كود موظف: $noEmployeeId\n";

// المستخدمين بدون تاريخ تعيين
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE hire_date IS NULL");
$noHireDate = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون تاريخ تعيين: $noHireDate\n\n";

// 4. إحصائيات الأقسام
echo "=== إحصائيات الأقسام ===\n";
$stmt = $pdo->query("
    SELECT d.name_ar as department_name, COUNT(u.id) as user_count,
           COUNT(CASE WHEN u.job_title IS NOT NULL AND u.job_title != '' THEN 1 END) as with_job_title,
           COUNT(CASE WHEN u.role_id IS NOT NULL THEN 1 END) as with_role
    FROM departments d
    LEFT JOIN users u ON d.id = u.department_id
    GROUP BY d.id, d.name_ar
    ORDER BY user_count DESC
");
$departmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($departmentStats as $stat) {
    echo "{$stat['department_name']}: {$stat['user_count']} مستخدم (مسمى وظيفي: {$stat['with_job_title']}, دور: {$stat['with_role']})\n";
}
echo "\n";

// 5. إحصائيات الأدوار
echo "=== إحصائيات الأدوار ===\n";
$stmt = $pdo->query("
    SELECT r.name_ar as role_name, COUNT(u.id) as user_count
    FROM roles r
    LEFT JOIN users u ON r.id = u.role_id
    GROUP BY r.id, r.name_ar
    ORDER BY user_count DESC
");
$roleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($roleStats as $stat) {
    echo "{$stat['role_name']}: {$stat['user_count']} مستخدم\n";
}
echo "\n";

// 6. إحصائيات المسميات الوظيفية
echo "=== إحصائيات المسميات الوظيفية (أكثر 20 مسمى) ===\n";
$stmt = $pdo->query("
    SELECT job_title, COUNT(*) as user_count
    FROM users
    WHERE job_title IS NOT NULL AND job_title != ''
    GROUP BY job_title
    ORDER BY user_count DESC
    LIMIT 20
");
$jobTitleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($jobTitleStats as $stat) {
    echo "{$stat['job_title']}: {$stat['user_count']} مستخدم\n";
}
echo "\n";

// 7. التحقق من التكرار
echo "=== التحقق من التكرار ===\n";

// البريد الإلكتروني المكرر
$stmt = $pdo->query("
    SELECT email, COUNT(*) as count
    FROM users
    WHERE email IS NOT NULL AND email != ''
    GROUP BY email
    HAVING COUNT(*) > 1
");
$duplicateEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($duplicateEmails) > 0) {
    echo "البريد الإلكتروني المكرر:\n";
    foreach ($duplicateEmails as $email) {
        echo "  {$email['email']}: {$email['count']} مرة\n";
    }
} else {
    echo "لا توجد بريد إلكتروني مكرر\n";
}

// كود الموظف المكرر
$stmt = $pdo->query("
    SELECT employee_id, COUNT(*) as count
    FROM users
    WHERE employee_id IS NOT NULL AND employee_id != ''
    GROUP BY employee_id
    HAVING COUNT(*) > 1
");
$duplicateEmployeeIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($duplicateEmployeeIds) > 0) {
    echo "كود الموظف المكرر:\n";
    foreach ($duplicateEmployeeIds as $empId) {
        echo "  {$empId['employee_id']}: {$empId['count']} مرة\n";
    }
} else {
    echo "لا توجد أكواد موظف مكررة\n";
}

// EmployeeCode المكرر
$stmt = $pdo->query("
    SELECT \"EmployeeCode\", COUNT(*) as count
    FROM users
    WHERE \"EmployeeCode\" IS NOT NULL
    GROUP BY \"EmployeeCode\"
    HAVING COUNT(*) > 1
");
$duplicateEmployeeCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($duplicateEmployeeCodes) > 0) {
    echo "EmployeeCode المكرر:\n";
    foreach ($duplicateEmployeeCodes as $empCode) {
        echo "  {$empCode['EmployeeCode']}: {$empCode['count']} مرة\n";
    }
} else {
    echo "لا توجد EmployeeCode مكررة\n";
}
echo "\n";

// 8. التحقق من صحة الروابط
echo "=== التحقق من صحة الروابط ===\n";

// المستخدمين مع أقسام غير موجودة
$stmt = $pdo->query("
    SELECT COUNT(*) as count
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE u.department_id IS NOT NULL AND d.id IS NULL
");
$invalidDepartments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين مع أقسام غير موجودة: $invalidDepartments\n";

// المستخدمين مع أدوار غير موجودة
$stmt = $pdo->query("
    SELECT COUNT(*) as count
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.role_id IS NOT NULL AND r.id IS NULL
");
$invalidRoles = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين مع أدوار غير موجودة: $invalidRoles\n";

// المستخدمين مع مدراء غير موجودين
$stmt = $pdo->query("
    SELECT COUNT(*) as count
    FROM users u
    LEFT JOIN users m ON u.manager_id = m.id
    WHERE u.manager_id IS NOT NULL AND m.id IS NULL
");
$invalidManagers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين مع مدراء غير موجودين: $invalidManagers\n\n";

// 9. ملخص التوصيات
echo "=== ملخص التوصيات ===\n";

$issues = [];
if ($noEmail > 0) $issues[] = "إضافة بريد إلكتروني لـ $noEmail مستخدم";
if ($noJobTitle > 0) $issues[] = "إضافة مسمى وظيفي لـ $noJobTitle مستخدم";
if ($noDepartment > 0) $issues[] = "ربط $noDepartment مستخدم بقسم";
if ($noRole > 0) $issues[] = "ربط $noRole مستخدم بدور";
if ($noEmployeeId > 0) $issues[] = "إضافة كود موظف لـ $noEmployeeId مستخدم";
if ($noHireDate > 0) $issues[] = "إضافة تاريخ تعيين لـ $noHireDate مستخدم";
if ($invalidDepartments > 0) $issues[] = "إصلاح $invalidDepartments رابط قسم غير صحيح";
if ($invalidRoles > 0) $issues[] = "إصلاح $invalidRoles رابط دور غير صحيح";
if ($invalidManagers > 0) $issues[] = "إصلاح $invalidManagers رابط مدير غير صحيح";

if (count($issues) > 0) {
    echo "يجب معالجة المشاكل التالية:\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". $issue\n";
    }
} else {
    echo "جميع البيانات صحيحة ولا تحتاج إلى معالجة\n";
}

echo "\n=== انتهاء التقرير ===\n";
?>
