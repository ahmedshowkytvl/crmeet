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

// تحديث كلمة مرور المستخدم
$employeeId = '968';
$newPassword = 'password';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $sql = "UPDATE users SET password = ? WHERE employee_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashedPassword, $employeeId]);
    
    if ($stmt->rowCount() > 0) {
        echo "تم تحديث كلمة مرور المستخدم بنجاح\n";
        echo "Employee ID: $employeeId\n";
        echo "كلمة المرور: $newPassword\n";
    } else {
        echo "لم يتم العثور على مستخدم برقم الموظف: $employeeId\n";
    }
    
} catch (PDOException $e) {
    echo "خطأ في تحديث كلمة مرور المستخدم: " . $e->getMessage() . "\n";
}

// عرض تفاصيل المستخدم
try {
    $sql = "SELECT u.id, u.name, u.email, u.employee_id, u.job_title, d.name_ar as department_name, r.name_ar as role_name 
            FROM users u 
            LEFT JOIN departments d ON u.department_id = d.id 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.employee_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\n=== تفاصيل المستخدم ===\n";
        echo "ID: {$user['id']}\n";
        echo "الاسم: {$user['name']}\n";
        echo "البريد الإلكتروني: {$user['email']}\n";
        echo "رقم الموظف: {$user['employee_id']}\n";
        echo "المسمى الوظيفي: {$user['job_title']}\n";
        echo "القسم: {$user['department_name']}\n";
        echo "الدور: {$user['role_name']}\n";
    } else {
        echo "\nلم يتم العثور على تفاصيل المستخدم\n";
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على تفاصيل المستخدم: " . $e->getMessage() . "\n";
}
?>

