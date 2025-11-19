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

echo "=== اختبار إصلاح مشكلة Tasks ===\n\n";

// اختبار استعلام المهام للمستخدم 36
try {
    $sql = "SELECT * FROM tasks WHERE assigned_to = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([36]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "تم العثور على " . count($tasks) . " مهمة للمستخدم 36\n";
    
    if (count($tasks) > 0) {
        echo "عينة من المهام:\n";
        foreach (array_slice($tasks, 0, 3) as $task) {
            echo "- {$task['title']} (الحالة: {$task['status']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في استعلام المهام: " . $e->getMessage() . "\n";
}

// اختبار استعلام المهام التي أنشأها المستخدم 36
try {
    $sql = "SELECT * FROM tasks WHERE created_by = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([36]);
    $createdTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nتم العثور على " . count($createdTasks) . " مهمة أنشأها المستخدم 36\n";
    
    if (count($createdTasks) > 0) {
        echo "عينة من المهام المنشأة:\n";
        foreach (array_slice($createdTasks, 0, 3) as $task) {
            echo "- {$task['title']} (الحالة: {$task['status']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في استعلام المهام المنشأة: " . $e->getMessage() . "\n";
}

// اختبار إحصائيات المهام
try {
    $sql = "SELECT COUNT(*) as total_tasks FROM tasks";
    $stmt = $pdo->query($sql);
    $totalTasks = $stmt->fetch(PDO::FETCH_ASSOC)['total_tasks'];
    echo "\nإجمالي المهام في النظام: $totalTasks\n";
    
    $sql = "SELECT status, COUNT(*) as count FROM tasks GROUP BY status";
    $stmt = $pdo->query($sql);
    $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "إحصائيات المهام حسب الحالة:\n";
    foreach ($statusStats as $stat) {
        echo "- {$stat['status']}: {$stat['count']} مهمة\n";
    }
    
} catch (PDOException $e) {
    echo "خطأ في إحصائيات المهام: " . $e->getMessage() . "\n";
}

echo "\n=== انتهاء الاختبار ===\n";
?>
