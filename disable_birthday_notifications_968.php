<?php
/**
 * سكريبت لتعطيل إشعارات أعياد الميلاد للمستخدم برقم الموظف 968
 * Script to disable birthday notifications for user with employee ID 968
 */

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
    echo "Connected to database successfully\n\n";
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات / Database connection error: " . $e->getMessage() . "\n");
}

// تعطيل إشعارات أعياد الميلاد للمستخدم
$employeeId = '968';

try {
    // تحديث حقل receive_birthday_notifications إلى false
    $sql = "UPDATE users SET receive_birthday_notifications = false WHERE employee_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ تم تعطيل إشعارات أعياد الميلاد للمستخدم بنجاح\n";
        echo "✅ Birthday notifications disabled successfully for user\n";
        echo "Employee ID: $employeeId\n\n";
    } else {
        echo "⚠️  لم يتم العثور على مستخدم برقم الموظف: $employeeId\n";
        echo "⚠️  User with employee ID not found: $employeeId\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ في تحديث إعدادات المستخدم / Error updating user settings: " . $e->getMessage() . "\n";
}

// عرض تفاصيل المستخدم
try {
    $sql = "SELECT u.id, u.name, u.email, u.employee_id, u.receive_birthday_notifications, 
                   u.job_title, d.name_ar as department_name, r.name_ar as role_name 
            FROM users u 
            LEFT JOIN departments d ON u.department_id = d.id 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.employee_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employeeId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "=== تفاصيل المستخدم / User Details ===\n";
        echo "ID: {$user['id']}\n";
        echo "الاسم / Name: {$user['name']}\n";
        echo "البريد الإلكتروني / Email: {$user['email']}\n";
        echo "رقم الموظف / Employee ID: {$user['employee_id']}\n";
        echo "المسمى الوظيفي / Job Title: {$user['job_title']}\n";
        echo "القسم / Department: {$user['department_name']}\n";
        echo "الدور / Role: {$user['role_name']}\n";
        $status = $user['receive_birthday_notifications'] ? 'مفعّل / Enabled' : 'معطّل / Disabled';
        echo "إشعارات أعياد الميلاد / Birthday Notifications: $status\n";
    } else {
        echo "\nلم يتم العثور على تفاصيل المستخدم / User details not found\n";
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على تفاصيل المستخدم / Error fetching user details: " . $e->getMessage() . "\n";
}
?>

