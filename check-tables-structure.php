<?php
/**
 * فحص هيكل الجداول
 * Check tables structure
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

    echo "🔍 فحص هيكل الجداول:\n";
    echo "====================\n\n";

    // فحص جدول المهام
    echo "📋 جدول المهام (tasks):\n";
    $stmt = $pdo->query('DESCRIBE tasks');
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    echo "\n";

    // فحص جدول جهات الاتصال
    echo "📞 جدول جهات الاتصال (contacts):\n";
    $stmt = $pdo->query('DESCRIBE contacts');
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    echo "\n";

    // فحص جدول الطلبات
    echo "📝 جدول الطلبات (employee_requests):\n";
    $stmt = $pdo->query('DESCRIBE employee_requests');
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
