<?php
/**
 * البحث عن جميع البيانات المتعلقة بالمستخدم رقم 120 (محدث)
 * Search for all data related to user ID 120 (updated)
 */

require_once 'vendor/autoload.php';

// تحميل متغيرات البيئة
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // إعدادات الاتصال
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'crm';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    // إنشاء الاتصال
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "🔍 البحث عن جميع البيانات المتعلقة بالمستخدم رقم 120:\n";
    echo "====================================================\n\n";

    // 1. بيانات المستخدم الأساسية
    echo "1️⃣ بيانات المستخدم الأساسية:\n";
    echo "============================\n";
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([120]);
    $user = $stmt->fetch();

    if ($user) {
        echo "✅ تم العثور على المستخدم:\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "   Name: " . $user['name'] . "\n";
        echo "   Name (AR): " . $user['name_ar'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   User Type: " . $user['user_type'] . "\n";
        echo "   Department ID: " . $user['department_id'] . "\n";
        echo "   Role ID: " . $user['role_id'] . "\n";
        echo "   Job Title: " . $user['job_title'] . "\n";
        echo "   Company: " . $user['company'] . "\n";
        echo "   Work Email: " . $user['work_email'] . "\n";
        echo "   Phone Work: " . $user['phone_work'] . "\n";
        echo "   Phone Personal: " . $user['phone_personal'] . "\n";
        echo "   Microsoft Teams ID: " . $user['microsoft_teams_id'] . "\n";
        echo "   Created At: " . $user['created_at'] . "\n";
        echo "   Updated At: " . $user['updated_at'] . "\n";
    } else {
        echo "❌ المستخدم رقم 120 غير موجود\n";
        exit;
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
    $stmt->execute([120]);
    $phones = $stmt->fetchAll();
    
    if ($phones) {
        foreach ($phones as $phone) {
            echo "   Phone ID: " . $phone['id'] . "\n";
            echo "   Phone: " . $phone['phone'] . "\n";
            echo "   Type: " . $phone['phone_type'] . "\n";
            echo "   Created: " . $phone['created_at'] . "\n";
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
    $stmt->execute([120, 120]);
    $contacts = $stmt->fetchAll();
    
    if ($contacts) {
        foreach ($contacts as $contact) {
            echo "   Contact ID: " . $contact['id'] . "\n";
            echo "   Name: " . $contact['name'] . "\n";
            echo "   Email: " . $contact['email'] . "\n";
            echo "   Phone: " . $contact['phone'] . "\n";
            echo "   Created: " . $contact['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد جهات اتصال مرتبطة\n";
    }

    echo "\n";

    // 6. الأصول المخصصة
    echo "6️⃣ الأصول المخصصة:\n";
    echo "==================\n";
    $stmt = $pdo->prepare('SELECT aa.*, a.name as asset_name, a.description as asset_description 
                          FROM asset_assignments aa 
                          JOIN assets a ON aa.asset_id = a.id 
                          WHERE aa.user_id = ?');
    $stmt->execute([120]);
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
        echo "   لا توجد أصول مخصصة\n";
    }

    echo "\n";

    // 7. طلبات الموظفين
    echo "7️⃣ طلبات الموظفين:\n";
    echo "==================\n";
    $stmt = $pdo->prepare('SELECT * FROM employee_requests WHERE user_id = ?');
    $stmt->execute([120]);
    $requests = $stmt->fetchAll();
    
    if ($requests) {
        foreach ($requests as $request) {
            echo "   Request ID: " . $request['id'] . "\n";
            echo "   Type: " . $request['request_type'] . "\n";
            echo "   Description: " . $request['description'] . "\n";
            echo "   Status: " . $request['status'] . "\n";
            echo "   Created: " . $request['created_at'] . "\n";
            echo "   ---\n";
        }
    } else {
        echo "   لا توجد طلبات\n";
    }

    echo "\n";

    // 8. إحصائيات عامة
    echo "8️⃣ إحصائيات عامة:\n";
    echo "==================\n";
    
    $stats = [];
    
    // عدد جهات الاتصال
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM contacts WHERE user_id = ? OR created_by = ?');
    $stmt->execute([120, 120]);
    $stats['contacts'] = $stmt->fetch()['count'];
    
    // عدد الأصول المخصصة
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM asset_assignments WHERE user_id = ?');
    $stmt->execute([120]);
    $stats['assets'] = $stmt->fetch()['count'];
    
    // عدد الطلبات
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM employee_requests WHERE user_id = ?');
    $stmt->execute([120]);
    $stats['requests'] = $stmt->fetch()['count'];
    
    echo "   جهات الاتصال: " . $stats['contacts'] . "\n";
    echo "   الأصول المخصصة: " . $stats['assets'] . "\n";
    echo "   الطلبات: " . $stats['requests'] . "\n";

    echo "\n✅ تم الانتهاء من البحث!\n";

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات:\n";
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ خطأ عام:\n";
    echo "Error: " . $e->getMessage() . "\n";
}
