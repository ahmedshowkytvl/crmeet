<?php
/**
 * البحث الشامل عن المستخدم رقم 114
 * Complete search for user ID 114
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

    echo "🔍 البحث الشامل عن المستخدم رقم 114:\n";
    echo "====================================\n\n";

    // 1. البيانات الأساسية للمستخدم
    echo "1️⃣ البيانات الأساسية:\n";
    echo "====================\n";
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([114]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ المستخدم رقم 114 غير موجود في قاعدة البيانات\n";
        exit;
    }

    echo "✅ تم العثور على المستخدم:\n";
    foreach ($user as $key => $value) {
        if ($value !== null && $value !== '') {
            echo "   $key: $value\n";
        }
    }

    echo "\n";

    // 2. معلومات القسم
    echo "2️⃣ معلومات القسم:\n";
    echo "==================\n";
    if ($user['department_id']) {
        $stmt = $pdo->prepare('SELECT * FROM departments WHERE id = ?');
        $stmt->execute([$user['department_id']]);
        $department = $stmt->fetch();
        if ($department) {
            echo "   Department ID: " . $department['id'] . "\n";
            echo "   Department Name: " . $department['name'] . "\n";
            echo "   Department Name (AR): " . $department['name_ar'] . "\n";
            echo "   Description: " . $department['description'] . "\n";
            echo "   Created At: " . $department['created_at'] . "\n";
            echo "   Updated At: " . $department['updated_at'] . "\n";
        } else {
            echo "   القسم غير موجود\n";
        }
    } else {
        echo "   لا يوجد قسم مخصص\n";
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
            echo "   Role ID: " . $role['id'] . "\n";
            echo "   Role Name: " . $role['name'] . "\n";
            echo "   Role Name (AR): " . $role['name_ar'] . "\n";
            echo "   Description: " . $role['description'] . "\n";
            echo "   Created At: " . $role['created_at'] . "\n";
            echo "   Updated At: " . $role['updated_at'] . "\n";
        } else {
            echo "   الدور غير موجود\n";
        }
    } else {
        echo "   لا يوجد دور مخصص\n";
    }

    echo "\n";

    // 4. أرقام الهاتف
    echo "4️⃣ أرقام الهاتف:\n";
    echo "================\n";
    $stmt = $pdo->prepare('SELECT * FROM user_phones WHERE user_id = ?');
    $stmt->execute([114]);
    $phones = $stmt->fetchAll();
    
    if ($phones) {
        foreach ($phones as $phone) {
            echo "   Phone ID: " . $phone['id'] . "\n";
            echo "   Phone: " . $phone['phone'] . "\n";
            echo "   Type: " . $phone['phone_type'] . "\n";
            echo "   Created At: " . $phone['created_at'] . "\n";
            echo "   Updated At: " . $phone['updated_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد أرقام هواتف مسجلة\n";
    }

    echo "\n";

    // 5. جهات الاتصال
    echo "5️⃣ جهات الاتصال:\n";
    echo "================\n";
    $stmt = $pdo->prepare('SELECT * FROM contacts WHERE user_id = ? OR created_by = ?');
    $stmt->execute([114, 114]);
    $contacts = $stmt->fetchAll();
    
    if ($contacts) {
        foreach ($contacts as $contact) {
            echo "   Contact ID: " . $contact['id'] . "\n";
            echo "   Name: " . $contact['name'] . "\n";
            echo "   Email: " . $contact['email'] . "\n";
            echo "   Phone: " . $contact['phone'] . "\n";
            echo "   Company: " . $contact['company'] . "\n";
            echo "   Position: " . $contact['position'] . "\n";
            echo "   Created At: " . $contact['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد جهات اتصال مرتبطة\n";
    }

    echo "\n";

    // 6. المهام
    echo "6️⃣ المهام:\n";
    echo "==========\n";
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? OR assigned_to = ?');
    $stmt->execute([114, 114]);
    $tasks = $stmt->fetchAll();
    
    if ($tasks) {
        foreach ($tasks as $task) {
            echo "   Task ID: " . $task['id'] . "\n";
            echo "   Title: " . $task['title'] . "\n";
            echo "   Description: " . $task['description'] . "\n";
            echo "   Status: " . $task['status'] . "\n";
            echo "   Priority: " . $task['priority'] . "\n";
            echo "   Due Date: " . $task['due_date'] . "\n";
            echo "   Created At: " . $task['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد مهام مرتبطة\n";
    }

    echo "\n";

    // 7. الأصول المخصصة
    echo "7️⃣ الأصول المخصصة:\n";
    echo "==================\n";
    $stmt = $pdo->prepare('SELECT aa.*, a.name as asset_name, a.description as asset_description, a.serial_number 
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
            echo "   Serial Number: " . $assignment['serial_number'] . "\n";
            echo "   Assigned Date: " . $assignment['assigned_date'] . "\n";
            echo "   Status: " . $assignment['status'] . "\n";
            echo "   Notes: " . $assignment['notes'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد أصول مخصصة\n";
    }

    echo "\n";

    // 8. طلبات الموظفين
    echo "8️⃣ طلبات الموظفين:\n";
    echo "==================\n";
    $stmt = $pdo->prepare('SELECT * FROM employee_requests WHERE user_id = ?');
    $stmt->execute([114]);
    $requests = $stmt->fetchAll();
    
    if ($requests) {
        foreach ($requests as $request) {
            echo "   Request ID: " . $request['id'] . "\n";
            echo "   Type: " . $request['request_type'] . "\n";
            echo "   Description: " . $request['description'] . "\n";
            echo "   Status: " . $request['status'] . "\n";
            echo "   Priority: " . $request['priority'] . "\n";
            echo "   Created At: " . $request['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد طلبات\n";
    }

    echo "\n";

    // 9. التعليقات
    echo "9️⃣ التعليقات:\n";
    echo "==============\n";
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE user_id = ?');
    $stmt->execute([114]);
    $comments = $stmt->fetchAll();
    
    if ($comments) {
        foreach ($comments as $comment) {
            echo "   Comment ID: " . $comment['id'] . "\n";
            echo "   Content: " . $comment['content'] . "\n";
            echo "   Type: " . $comment['commentable_type'] . "\n";
            echo "   Created At: " . $comment['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد تعليقات\n";
    }

    echo "\n";

    // 10. إحصائيات عامة
    echo "🔟 إحصائيات عامة:\n";
    echo "==================\n";
    
    $stats = [];
    
    // عدد أرقام الهاتف
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM user_phones WHERE user_id = ?');
    $stmt->execute([114]);
    $stats['phones'] = $stmt->fetch()['count'];
    
    // عدد جهات الاتصال
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM contacts WHERE user_id = ? OR created_by = ?');
    $stmt->execute([114, 114]);
    $stats['contacts'] = $stmt->fetch()['count'];
    
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
    
    // عدد التعليقات
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM comments WHERE user_id = ?');
    $stmt->execute([114]);
    $stats['comments'] = $stmt->fetch()['count'];
    
    echo "   أرقام الهاتف: " . $stats['phones'] . "\n";
    echo "   جهات الاتصال: " . $stats['contacts'] . "\n";
    echo "   المهام: " . $stats['tasks'] . "\n";
    echo "   الأصول المخصصة: " . $stats['assets'] . "\n";
    echo "   الطلبات: " . $stats['requests'] . "\n";
    echo "   التعليقات: " . $stats['comments'] . "\n";

    echo "\n✅ تم الانتهاء من البحث الشامل!\n";

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات:\n";
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ خطأ عام:\n";
    echo "Error: " . $e->getMessage() . "\n";
}
