<?php
/**
 * ملخص بيانات المستخدم رقم 114
 * User 114 data summary
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

    echo "🎯 ملخص بيانات المستخدم رقم 114:\n";
    echo "==================================\n\n";

    // بيانات المستخدم
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([114]);
    $user = $stmt->fetch();

    if ($user) {
        echo "👤 المعلومات الشخصية:\n";
        echo "   الاسم: " . $user['name'] . "\n";
        echo "   الاسم (عربي): " . $user['name_ar'] . "\n";
        echo "   البريد الإلكتروني: " . $user['email'] . "\n";
        echo "   نوع المستخدم: " . $user['user_type'] . "\n";
        echo "   المسمى الوظيفي: " . $user['job_title'] . "\n";
        echo "   البريد الوظيفي: " . $user['work_email'] . "\n";
        echo "   كود الموظف: " . $user['EmployeeCode'] . "\n";
        echo "   تاريخ التوظيف: " . $user['hire_date'] . "\n";
        echo "   تاريخ الميلاد: " . $user['birth_date'] . "\n";
        echo "   صورة الملف الشخصي: " . $user['profile_picture'] . "\n";
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

        // البحث عن المهام
        echo "📋 المهام:\n";
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? OR assigned_to = ?');
        $stmt->execute([114, 114]);
        $tasks = $stmt->fetchAll();
        
        if ($tasks) {
            foreach ($tasks as $task) {
                echo "   - Task ID: " . $task['id'] . " | " . $task['title'] . " | Status: " . $task['status'] . "\n";
            }
        } else {
            echo "   لا توجد مهام مرتبطة\n";
        }

        echo "\n";

        // البحث عن الأصول المخصصة
        echo "💼 الأصول المخصصة:\n";
        $stmt = $pdo->prepare('SELECT aa.*, a.name as asset_name 
                              FROM asset_assignments aa 
                              JOIN assets a ON aa.asset_id = a.id 
                              WHERE aa.user_id = ?');
        $stmt->execute([114]);
        $assignments = $stmt->fetchAll();
        
        if ($assignments) {
            foreach ($assignments as $assignment) {
                echo "   - Asset: " . $assignment['asset_name'] . " | Status: " . $assignment['status'] . "\n";
            }
        } else {
            echo "   لا توجد أصول مخصصة\n";
        }

        echo "\n";

        // البحث عن الطلبات
        echo "📝 الطلبات:\n";
        $stmt = $pdo->prepare('SELECT * FROM employee_requests WHERE user_id = ?');
        $stmt->execute([114]);
        $requests = $stmt->fetchAll();
        
        if ($requests) {
            foreach ($requests as $request) {
                echo "   - Request ID: " . $request['id'] . " | Type: " . $request['request_type'] . " | Status: " . $request['status'] . "\n";
            }
        } else {
            echo "   لا توجد طلبات\n";
        }

        echo "\n";

        // إحصائيات
        echo "📊 إحصائيات:\n";
        $stats = [];
        
        // عدد المهام
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE user_id = ? OR assigned_to = ?');
        $stmt->execute([114, 114]);
        $stats['tasks'] = $stmt->fetch()['count'];
        
        // عدد الأصول المخصصة
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM asset_assignments WHERE user_id = ?');
        $stmt->execute([114]);
        $stats['assets'] = $stmt->fetch()['count'];
        
        // عدد الطلبات
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM employee_requests WHERE user_id = ?');
        $stmt->execute([114]);
        $stats['requests'] = $stmt->fetch()['count'];
        
        echo "   المهام: " . $stats['tasks'] . "\n";
        echo "   الأصول المخصصة: " . $stats['assets'] . "\n";
        echo "   الطلبات: " . $stats['requests'] . "\n";

        echo "\n✅ تم العثور على المستخدم رقم 114 بنجاح!\n";

    } else {
        echo "❌ المستخدم رقم 114 غير موجود\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
