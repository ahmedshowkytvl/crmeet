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

// تحديث كلمة مرور المدير
$adminEmail = 'admin@stafftobia.com';
$newPassword = 'admin123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashedPassword, $adminEmail]);
    
    if ($stmt->rowCount() > 0) {
        echo "تم تحديث كلمة مرور المدير بنجاح\n";
        echo "البريد الإلكتروني: $adminEmail\n";
        echo "كلمة المرور: $newPassword\n";
    } else {
        echo "لم يتم العثور على حساب المدير\n";
    }
    
} catch (PDOException $e) {
    echo "خطأ في تحديث كلمة مرور المدير: " . $e->getMessage() . "\n";
}

// عرض تفاصيل حساب المدير
try {
    $sql = "SELECT u.id, u.name, u.email, u.job_title, d.name_ar as department_name, r.name_ar as role_name 
            FROM users u 
            LEFT JOIN departments d ON u.department_id = d.id 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$adminEmail]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "\n=== تفاصيل حساب المدير ===\n";
        echo "ID: {$admin['id']}\n";
        echo "الاسم: {$admin['name']}\n";
        echo "البريد الإلكتروني: {$admin['email']}\n";
        echo "المسمى الوظيفي: {$admin['job_title']}\n";
        echo "القسم: {$admin['department_name']}\n";
        echo "الدور: {$admin['role_name']}\n";
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على تفاصيل المدير: " . $e->getMessage() . "\n";
}
?>
