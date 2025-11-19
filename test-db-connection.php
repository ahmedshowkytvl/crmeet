<?php
/**
 * سكريبت لاختبار الاتصال بقاعدة البيانات MySQL
 * Test script for MySQL database connection
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

    echo "🔄 جاري اختبار الاتصال بقاعدة البيانات...\n";
    echo "Host: $host\n";
    echo "Port: $port\n";
    echo "Database: $database\n";
    echo "Username: $username\n\n";

    // إنشاء الاتصال
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ تم الاتصال بنجاح بقاعدة البيانات!\n\n";

    // اختبار الاستعلام
    echo "🔍 اختبار الاستعلام...\n";
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "MySQL Version: " . $version['version'] . "\n\n";

    // عرض الجداول الموجودة
    echo "📋 الجداول الموجودة في قاعدة البيانات:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  لا توجد جداول في قاعدة البيانات\n";
    } else {
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }

    echo "\n✅ اختبار الاتصال مكتمل بنجاح!\n";
    echo "🎉 يمكنك الآن استخدام MCP مع قاعدة البيانات\n";

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات:\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "🔧 حلول مقترحة:\n";
    echo "1. تأكد من تشغيل خادم MySQL\n";
    echo "2. تحقق من إعدادات الاتصال في ملف .env\n";
    echo "3. تأكد من وجود قاعدة البيانات '$database'\n";
    echo "4. تحقق من صلاحيات المستخدم '$username'\n";
    
} catch (Exception $e) {
    echo "❌ خطأ عام:\n";
    echo "Error: " . $e->getMessage() . "\n";
}
