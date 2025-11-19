<?php
/**
 * البحث عن المستخدم رقم 120
 * Search for user ID 120
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

    echo "🔍 البحث عن المستخدم رقم 120...\n";
    echo "Host: $host\n";
    echo "Database: $database\n\n";

    // إنشاء الاتصال
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // البحث عن المستخدم رقم 120
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([120]);
    $user = $stmt->fetch();

    if ($user) {
        echo "✅ تم العثور على المستخدم رقم 120:\n";
        echo "=====================================\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Full Name: " . ($user['full_name'] ?? 'غير محدد') . "\n";
        echo "Display Name: " . ($user['display_name'] ?? 'غير محدد') . "\n";
        echo "Role: " . ($user['role'] ?? 'غير محدد') . "\n";
        echo "Status: " . ($user['status'] ?? 'غير محدد') . "\n";
        echo "Phone: " . ($user['phone'] ?? 'غير محدد') . "\n";
        echo "Created At: " . ($user['created_at'] ?? 'غير محدد') . "\n";
        echo "Last Login: " . ($user['last_login'] ?? 'غير محدد') . "\n";
    } else {
        echo "❌ المستخدم رقم 120 غير موجود\n\n";
        
        // عرض إحصائيات قاعدة البيانات
        $stmt = $pdo->query('SELECT MIN(id) as min_id, MAX(id) as max_id, COUNT(*) as total FROM users');
        $stats = $stmt->fetch();
        
        echo "📊 إحصائيات قاعدة البيانات:\n";
        echo "أقل معرف: " . $stats['min_id'] . "\n";
        echo "أعلى معرف: " . $stats['max_id'] . "\n";
        echo "إجمالي المستخدمين: " . $stats['total'] . "\n\n";
        
        // عرض المستخدمين بالقرب من الرقم 120
        echo "🔍 المستخدمين بالقرب من الرقم 120:\n";
        $stmt = $pdo->query('SELECT id, username, email, full_name FROM users WHERE id >= 115 AND id <= 125 ORDER BY id');
        $nearbyUsers = $stmt->fetchAll();
        
        if (empty($nearbyUsers)) {
            echo "لا يوجد مستخدمين في هذا النطاق\n";
        } else {
            foreach ($nearbyUsers as $nearbyUser) {
                echo "- ID: " . $nearbyUser['id'] . " | Username: " . $nearbyUser['username'] . " | Email: " . $nearbyUser['email'] . "\n";
            }
        }
    }

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات:\n";
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ خطأ عام:\n";
    echo "Error: " . $e->getMessage() . "\n";
}
