<?php
/**
 * البيانات الكاملة للمستخدم رقم 114
 * Complete data for user ID 114
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

    echo "🎯 البيانات الكاملة للمستخدم رقم 114:\n";
    echo "====================================\n\n";

    // 1. البيانات الأساسية
    echo "1️⃣ البيانات الأساسية:\n";
    echo "====================\n";
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([114]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ المستخدم رقم 114 غير موجود\n";
        exit;
    }

    echo "✅ تم العثور على المستخدم:\n";
    echo "   ID: " . $user['id'] . "\n";
    echo "   Name: " . $user['name'] . "\n";
    echo "   Name (AR): " . $user['name_ar'] . "\n";
    echo "   Email: " . $user['email'] . "\n";
    echo "   User Type: " . $user['user_type'] . "\n";
    echo "   Job Title: " . $user['job_title'] . "\n";
    echo "   Work Email: " . $user['work_email'] . "\n";
    echo "   Employee Code: " . $user['EmployeeCode'] . "\n";
    echo "   Hire Date: " . $user['hire_date'] . "\n";
    echo "   Birth Date: " . $user['birth_date'] . "\n";
    echo "   Profile Picture: " . $user['profile_picture'] . "\n";
    echo "   Created At: " . $user['created_at'] . "\n";
    echo "   Updated At: " . $user['updated_at'] . "\n\n";

    // 2. معلومات القسم
    echo "2️⃣ معلومات القسم:\n";
    echo "==================\n";
    if ($user['department_id']) {
        $stmt = $pdo->prepare('SELECT * FROM departments WHERE id = ?');
        $stmt->execute([$user['department_id']]);
        $department = $stmt->fetch();
        if ($department) {
            echo "   Department: " . $department['name'] . "\n";
            echo "   Department (AR): " . $department['name_ar'] . "\n";
            echo "   Description: " . $department['description'] . "\n";
        }
    }
    echo "\n";

    // 3. معلومات الدور
    echo "3️⃣ معلومات الدور:\n";
    echo "==================\n";
    if ($user['role_id']) {
        $stmt = $pdo->prepare('SELECT * FROM roles WHERE id = ?');
        $stmt->execute([$user['role_id']]);
        $role = $stmt->fetch();
        if ($role) {
            echo "   Role: " . $role['name'] . "\n";
            echo "   Role (AR): " . $role['name_ar'] . "\n";
            echo "   Description: " . $role['description'] . "\n";
        }
    }
    echo "\n";

    // 4. المهام المخصصة له
    echo "4️⃣ المهام المخصصة له:\n";
    echo "=====================\n";
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE assigned_to = ?');
    $stmt->execute([114]);
    $tasks = $stmt->fetchAll();
    
    if ($tasks) {
        foreach ($tasks as $task) {
            echo "   Task ID: " . $task['id'] . "\n";
            echo "   Title: " . $task['title'] . "\n";
            echo "   Description: " . $task['description'] . "\n";
            echo "   Status: " . $task['status'] . "\n";
            echo "   Priority: " . $task['priority'] . "\n";
            echo "   Category: " . $task['category'] . "\n";
            echo "   Due Date: " . $task['due_date'] . "\n";
            echo "   Created At: " . $task['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد مهام مخصصة له\n";
    }

    echo "\n";

    // 5. المهام التي أنشأها
    echo "5️⃣ المهام التي أنشأها:\n";
    echo "=====================\n";
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE assigned_by = ?');
    $stmt->execute([114]);
    $createdTasks = $stmt->fetchAll();
    
    if ($createdTasks) {
        foreach ($createdTasks as $task) {
            echo "   Task ID: " . $task['id'] . "\n";
            echo "   Title: " . $task['title'] . "\n";
            echo "   Status: " . $task['status'] . "\n";
            echo "   Priority: " . $task['priority'] . "\n";
            echo "   Created At: " . $task['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لم ينشئ أي مهام\n";
    }

    echo "\n";

    // 6. الأصول المخصصة له
    echo "6️⃣ الأصول المخصصة له:\n";
    echo "=====================\n";
    $stmt = $pdo->prepare('SELECT aa.*, a.name as asset_name, a.description as asset_description 
                          FROM asset_assignments aa 
                          JOIN assets a ON aa.asset_id = a.id 
                          WHERE aa.user_id = ?');
    $stmt->execute([114]);
    $assignments = $stmt->fetchAll();
    
    if ($assignments) {
        foreach ($assignments as $assignment) {
            echo "   Assignment ID: " . $assignment['id'] . "\n";
            echo "   Asset Name: " . $assignment['asset_name'] . "\n";
            echo "   Asset Description: " . $assignment['asset_description'] . "\n";
            echo "   Assigned Date: " . $assignment['assigned_date'] . "\n";
            echo "   Status: " . $assignment['status'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد أصول مخصصة له\n";
    }

    echo "\n";

    // 7. الطلبات
    echo "7️⃣ الطلبات:\n";
    echo "============\n";
    $stmt = $pdo->prepare('SELECT * FROM employee_requests WHERE employee_id = ? OR requested_by = ?');
    $stmt->execute([114, 114]);
    $requests = $stmt->fetchAll();
    
    if ($requests) {
        foreach ($requests as $request) {
            echo "   Request ID: " . $request['id'] . "\n";
            echo "   Title: " . $request['title'] . "\n";
            echo "   Description: " . $request['description'] . "\n";
            echo "   Status: " . $request['status'] . "\n";
            echo "   Created At: " . $request['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد طلبات\n";
    }

    echo "\n";

    // 8. جهات الاتصال المرتبطة
    echo "8️⃣ جهات الاتصال المرتبطة:\n";
    echo "==========================\n";
    $stmt = $pdo->prepare('SELECT * FROM contacts WHERE department_id = ?');
    $stmt->execute([$user['department_id']]);
    $contacts = $stmt->fetchAll();
    
    if ($contacts) {
        echo "   جهات الاتصال في نفس القسم (" . count($contacts) . " جهة اتصال):\n";
        foreach ($contacts as $contact) {
            echo "   - " . $contact['name'] . " (" . $contact['contact_type'] . ") - " . $contact['company'] . "\n";
        }
    } else {
        echo "   لا توجد جهات اتصال في نفس القسم\n";
    }

    echo "\n";

    // 9. إحصائيات شاملة
    echo "9️⃣ إحصائيات شاملة:\n";
    echo "==================\n";
    
    $stats = [];
    
    // عدد المهام المخصصة له
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?');
    $stmt->execute([114]);
    $stats['assigned_tasks'] = $stmt->fetch()['count'];
    
    // عدد المهام التي أنشأها
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tasks WHERE assigned_by = ?');
    $stmt->execute([114]);
    $stats['created_tasks'] = $stmt->fetch()['count'];
    
    // عدد الأصول المخصصة
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM asset_assignments WHERE user_id = ?');
    $stmt->execute([114]);
    $stats['assets'] = $stmt->fetch()['count'];
    
    // عدد الطلبات
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM employee_requests WHERE employee_id = ? OR requested_by = ?');
    $stmt->execute([114, 114]);
    $stats['requests'] = $stmt->fetch()['count'];
    
    // عدد جهات الاتصال في نفس القسم
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM contacts WHERE department_id = ?');
    $stmt->execute([$user['department_id']]);
    $stats['department_contacts'] = $stmt->fetch()['count'];
    
    echo "   المهام المخصصة له: " . $stats['assigned_tasks'] . "\n";
    echo "   المهام التي أنشأها: " . $stats['created_tasks'] . "\n";
    echo "   الأصول المخصصة: " . $stats['assets'] . "\n";
    echo "   الطلبات: " . $stats['requests'] . "\n";
    echo "   جهات الاتصال في نفس القسم: " . $stats['department_contacts'] . "\n";

    echo "\n✅ تم الانتهاء من البحث الشامل للمستخدم رقم 114!\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
