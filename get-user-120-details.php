<?php
/**
 * الحصول على تفاصيل كاملة للمستخدم رقم 120
 * Get full details for user ID 120
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

    echo "🔍 تفاصيل المستخدم رقم 120:\n";
    echo "=====================================\n\n";

    // الحصول على جميع تفاصيل المستخدم
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([120]);
    $user = $stmt->fetch();

    if ($user) {
        echo "📋 المعلومات الأساسية:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . ($user['username'] ?? 'غير محدد') . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "First Name: " . ($user['first_name'] ?? 'غير محدد') . "\n";
        echo "Last Name: " . ($user['last_name'] ?? 'غير محدد') . "\n";
        echo "Full Name: " . ($user['full_name'] ?? 'غير محدد') . "\n";
        echo "Display Name: " . ($user['display_name'] ?? 'غير محدد') . "\n";
        echo "Phone: " . ($user['phone'] ?? 'غير محدد') . "\n";
        echo "Phone Number: " . ($user['phone_number'] ?? 'غير محدد') . "\n";
        echo "Role: " . ($user['role'] ?? 'غير محدد') . "\n";
        echo "Status: " . ($user['status'] ?? 'غير محدد') . "\n";
        echo "Nationality: " . ($user['nationality'] ?? 'غير محدد') . "\n";
        echo "Date of Birth: " . ($user['date_of_birth'] ?? 'غير محدد') . "\n";
        echo "Bio: " . ($user['bio'] ?? 'غير محدد') . "\n";
        echo "Avatar URL: " . ($user['avatar_url'] ?? 'غير محدد') . "\n";
        echo "Profile Picture: " . ($user['profile_picture'] ?? 'غير محدد') . "\n";
        echo "Passport Number: " . ($user['passport_number'] ?? 'غير محدد') . "\n";
        echo "Passport Expiry: " . ($user['passport_expiry'] ?? 'غير محدد') . "\n";
        echo "Emergency Contact: " . ($user['emergency_contact'] ?? 'غير محدد') . "\n";
        echo "Emergency Phone: " . ($user['emergency_phone'] ?? 'غير محدد') . "\n";
        echo "Dietary Requirements: " . ($user['dietary_requirements'] ?? 'غير محدد') . "\n";
        echo "Medical Conditions: " . ($user['medical_conditions'] ?? 'غير محدد') . "\n";
        echo "Preferred Language: " . ($user['preferred_language'] ?? 'غير محدد') . "\n";
        
        echo "\n🔐 معلومات الحساب:\n";
        echo "Is Verified: " . ($user['is_verified'] ? 'نعم' : 'لا') . "\n";
        echo "Email Verified: " . ($user['email_verified'] ? 'نعم' : 'لا') . "\n";
        echo "Phone Verified: " . ($user['phone_verified'] ? 'نعم' : 'لا') . "\n";
        echo "Email Notifications: " . ($user['email_notifications'] ? 'مفعل' : 'معطل') . "\n";
        echo "SMS Notifications: " . ($user['sms_notifications'] ? 'مفعل' : 'معطل') . "\n";
        echo "Marketing Emails: " . ($user['marketing_emails'] ? 'مفعل' : 'معطل') . "\n";
        
        echo "\n📅 تواريخ مهمة:\n";
        echo "Created At: " . ($user['created_at'] ?? 'غير محدد') . "\n";
        echo "Updated At: " . ($user['updated_at'] ?? 'غير محدد') . "\n";
        echo "Last Login: " . ($user['last_login'] ?? 'غير محدد') . "\n";
        echo "Last Login At: " . ($user['last_login_at'] ?? 'غير محدد') . "\n";
        echo "Login Count: " . ($user['login_count'] ?? '0') . "\n";
        
        echo "\n🔑 معلومات إضافية:\n";
        echo "Verification Token: " . ($user['verification_token'] ?? 'غير محدد') . "\n";
        echo "Reset Token: " . ($user['reset_token'] ?? 'غير محدد') . "\n";
        echo "Reset Token Expiry: " . ($user['reset_token_expiry'] ?? 'غير محدد') . "\n";
        
        if ($user['preferences']) {
            echo "Preferences: " . $user['preferences'] . "\n";
        }
        
    } else {
        echo "❌ المستخدم رقم 120 غير موجود\n";
    }

} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات:\n";
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ خطأ عام:\n";
    echo "Error: " . $e->getMessage() . "\n";
}
