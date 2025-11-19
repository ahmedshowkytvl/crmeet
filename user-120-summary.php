<?php
/**
 * ملخص بيانات المستخدم رقم 120
 * User 120 data summary
 */

require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'crm';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "🎯 ملخص بيانات المستخدم رقم 120:\n";
    echo "==================================\n\n";

    // بيانات المستخدم
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([120]);
    $user = $stmt->fetch();

    if ($user) {
        echo "👤 معلومات شخصية:\n";
        echo "   الاسم: " . $user['name'] . "\n";
        echo "   الاسم (عربي): " . $user['name_ar'] . "\n";
        echo "   البريد الإلكتروني: " . $user['email'] . "\n";
        echo "   نوع المستخدم: " . $user['user_type'] . "\n";
        echo "   المسمى الوظيفي: " . $user['job_title'] . "\n";
        echo "   الشركة: " . $user['company'] . "\n";
        echo "   البريد الوظيفي: " . $user['work_email'] . "\n";
        echo "   هاتف العمل: " . $user['phone_work'] . "\n";
        echo "   الهاتف الشخصي: " . $user['phone_personal'] . "\n";
        echo "   Microsoft Teams: " . $user['microsoft_teams_id'] . "\n";
        echo "   تاريخ الإنشاء: " . $user['created_at'] . "\n";
        echo "   آخر تحديث: " . $user['updated_at'] . "\n\n";

        // معلومات القسم
        if ($user['department_id']) {
            $stmt = $pdo->prepare('SELECT * FROM departments WHERE id = ?');
            $stmt->execute([$user['department_id']]);
            $department = $stmt->fetch();
            if ($department) {
                echo "🏢 معلومات القسم:\n";
                echo "   القسم: " . $department['name'] . "\n";
                echo "   القسم (عربي): " . $department['name_ar'] . "\n";
                echo "   الوصف: " . $department['description'] . "\n\n";
            }
        }

        // معلومات الدور
        if ($user['role_id']) {
            $stmt = $pdo->prepare('SELECT * FROM roles WHERE id = ?');
            $stmt->execute([$user['role_id']]);
            $role = $stmt->fetch();
            if ($role) {
                echo "🔐 معلومات الدور:\n";
                echo "   الدور: " . $role['name'] . "\n";
                echo "   الدور (عربي): " . $role['name_ar'] . "\n";
                echo "   الوصف: " . $role['description'] . "\n\n";
            }
        }

        // إحصائيات
        echo "📊 إحصائيات:\n";
        
        // عدد الأصول المخصصة
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM asset_assignments WHERE user_id = ?');
        $stmt->execute([120]);
        $assetCount = $stmt->fetch()['count'];
        echo "   الأصول المخصصة: " . $assetCount . "\n";
        
        // عدد الطلبات
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM employee_requests WHERE user_id = ?');
        $stmt->execute([120]);
        $requestCount = $stmt->fetch()['count'];
        echo "   الطلبات: " . $requestCount . "\n";

        echo "\n✅ تم العثور على المستخدم رقم 120 بنجاح!\n";
        echo "📝 ملاحظة: هذا المستخدم يبدو أنه مستخدم اختبار تم إنشاؤه اليوم\n";

    } else {
        echo "❌ المستخدم رقم 120 غير موجود\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
