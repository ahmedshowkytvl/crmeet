<?php
/**
 * اختبار السماح بتكرار البريد الإلكتروني
 * Test email duplicate allowance
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

    echo "🧪 اختبار السماح بتكرار البريد الإلكتروني:\n";
    echo "==========================================\n\n";

    // 1. فحص المستخدمين الحاليين بنفس البريد الإلكتروني
    echo "1️⃣ فحص المستخدمين الحاليين بنفس البريد الإلكتروني:\n";
    echo "==================================================\n";
    
    $stmt = $pdo->query("
        SELECT email, COUNT(*) as count 
        FROM users 
        GROUP BY email 
        HAVING COUNT(*) > 1 
        ORDER BY count DESC
    ");
    $duplicates = $stmt->fetchAll();
    
    if ($duplicates) {
        echo "✅ تم العثور على بريد إلكتروني مكرر:\n";
        foreach ($duplicates as $duplicate) {
            echo "   البريد: " . $duplicate['email'] . " - العدد: " . $duplicate['count'] . "\n";
        }
    } else {
        echo "   لا توجد بريد إلكتروني مكرر حالياً\n";
    }

    echo "\n";

    // 2. اختبار إضافة مستخدم بنفس البريد الإلكتروني
    echo "2️⃣ اختبار إضافة مستخدم بنفس البريد الإلكتروني:\n";
    echo "==============================================\n";
    
    $testEmail = 'test.duplicate@example.com';
    
    // حذف المستخدم التجريبي إذا كان موجوداً
    $stmt = $pdo->prepare('DELETE FROM users WHERE email = ?');
    $stmt->execute([$testEmail]);
    
    // إضافة المستخدم الأول
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, user_type, created_at, updated_at) 
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute(['Test User 1', $testEmail, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee']);
    echo "✅ تم إضافة المستخدم الأول بنجاح\n";
    
    // محاولة إضافة المستخدم الثاني بنفس البريد الإلكتروني
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, user_type, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute(['Test User 2', $testEmail, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee']);
        echo "✅ تم إضافة المستخدم الثاني بنفس البريد الإلكتروني بنجاح!\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            echo "❌ فشل في إضافة المستخدم الثاني - قاعدة البيانات ترفض التكرار\n";
        } else {
            echo "❌ خطأ آخر: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // 3. فحص النتيجة النهائية
    echo "3️⃣ فحص النتيجة النهائية:\n";
    echo "========================\n";
    
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE email = ?');
    $stmt->execute([$testEmail]);
    $users = $stmt->fetchAll();
    
    echo "عدد المستخدمين بنفس البريد الإلكتروني: " . count($users) . "\n";
    foreach ($users as $user) {
        echo "   - ID: " . $user['id'] . " | Name: " . $user['name'] . " | Email: " . $user['email'] . "\n";
    }

    echo "\n";

    // 4. تنظيف البيانات التجريبية
    echo "4️⃣ تنظيف البيانات التجريبية:\n";
    echo "============================\n";
    
    $stmt = $pdo->prepare('DELETE FROM users WHERE email = ?');
    $stmt->execute([$testEmail]);
    echo "✅ تم حذف البيانات التجريبية\n";

    echo "\n✅ تم الانتهاء من الاختبار!\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
