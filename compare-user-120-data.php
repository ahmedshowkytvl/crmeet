<?php
/**
 * مقارنة بيانات المستخدم رقم 120 بين النموذج وقاعدة البيانات
 * Compare user 120 data between form and database
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

    echo "🔍 مقارنة بيانات المستخدم رقم 120:\n";
    echo "====================================\n\n";

    // الحصول على جميع بيانات المستخدم من قاعدة البيانات
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([120]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ المستخدم رقم 120 غير موجود في قاعدة البيانات\n";
        exit;
    }

    echo "📊 البيانات من قاعدة البيانات:\n";
    echo "==============================\n";
    foreach ($user as $key => $value) {
        if ($value !== null && $value !== '') {
            echo "   $key: $value\n";
        }
    }

    echo "\n📋 البيانات المعروضة في النموذج:\n";
    echo "================================\n";
    echo "   Name: Test User CRUD Updated\n";
    echo "   Name (AR): مستخدم اختبار CRUD محدث\n";
    echo "   Email: test.crud@example.com\n";
    echo "   Department: Information Technology\n";
    echo "   Role: Employee\n";
    echo "   Work Phone: 01234567891\n";
    echo "   Mobile Phone: 01123456790\n";
    echo "   Work Email: test.crud@company.com\n";
    echo "   Microsoft Teams ID: test.crud@company.com\n";
    echo "   Job Title: Software Developer\n";
    echo "   Company: Egypt Express Travel\n";

    echo "\n🔍 التحليل:\n";
    echo "===========\n";

    // مقارنة الحقول الأساسية
    $comparisons = [
        'name' => ['db' => $user['name'], 'form' => 'Test User CRUD Updated'],
        'name_ar' => ['db' => $user['name_ar'], 'form' => 'مستخدم اختبار CRUD محدث'],
        'email' => ['db' => $user['email'], 'form' => 'test.crud@example.com'],
        'phone_work' => ['db' => $user['phone_work'], 'form' => '01234567891'],
        'phone_personal' => ['db' => $user['phone_personal'], 'form' => '01123456790'],
        'work_email' => ['db' => $user['work_email'], 'form' => 'test.crud@company.com'],
        'microsoft_teams_id' => ['db' => $user['microsoft_teams_id'], 'form' => 'test.crud@company.com'],
        'job_title' => ['db' => $user['job_title'], 'form' => 'Software Developer'],
        'company' => ['db' => $user['company'], 'form' => 'Egypt Express Travel'],
    ];

    $matches = 0;
    $total = count($comparisons);

    foreach ($comparisons as $field => $data) {
        $dbValue = $data['db'] ?? '';
        $formValue = $data['form'];
        
        if ($dbValue === $formValue) {
            echo "   ✅ $field: متطابق\n";
            $matches++;
        } else {
            echo "   ❌ $field: مختلف\n";
            echo "      قاعدة البيانات: '$dbValue'\n";
            echo "      النموذج: '$formValue'\n";
        }
    }

    // مقارنة القسم
    if ($user['department_id']) {
        $stmt = $pdo->prepare('SELECT name FROM departments WHERE id = ?');
        $stmt->execute([$user['department_id']]);
        $department = $stmt->fetch();
        $dbDept = $department['name'] ?? '';
        $formDept = 'Information Technology';
        
        if ($dbDept === $formDept) {
            echo "   ✅ Department: متطابق ($dbDept)\n";
            $matches++;
        } else {
            echo "   ❌ Department: مختلف\n";
            echo "      قاعدة البيانات: '$dbDept'\n";
            echo "      النموذج: '$formDept'\n";
        }
        $total++;
    }

    // مقارنة الدور
    if ($user['role_id']) {
        $stmt = $pdo->prepare('SELECT name FROM roles WHERE id = ?');
        $stmt->execute([$user['role_id']]);
        $role = $stmt->fetch();
        $dbRole = $role['name'] ?? '';
        $formRole = 'Employee';
        
        if ($dbRole === $formRole) {
            echo "   ✅ Role: متطابق ($dbRole)\n";
            $matches++;
        } else {
            echo "   ❌ Role: مختلف\n";
            echo "      قاعدة البيانات: '$dbRole'\n";
            echo "      النموذج: '$formRole'\n";
        }
        $total++;
    }

    echo "\n📈 النتيجة:\n";
    echo "===========\n";
    echo "   الحقول المتطابقة: $matches من $total\n";
    echo "   نسبة التطابق: " . round(($matches / $total) * 100, 2) . "%\n";

    if ($matches === $total) {
        echo "   ✅ جميع البيانات متطابقة - البيانات مسترجعة بالكامل\n";
    } else {
        echo "   ⚠️  بعض البيانات مختلفة - قد تكون هناك مشكلة في الاسترجاع\n";
    }

    echo "\n🔍 الحقول المفقودة في النموذج:\n";
    echo "===============================\n";
    $missingFields = [];
    foreach ($user as $key => $value) {
        if ($value !== null && $value !== '' && !array_key_exists($key, $comparisons)) {
            $missingFields[] = $key;
        }
    }
    
    if ($missingFields) {
        foreach ($missingFields as $field) {
            echo "   - $field: " . $user[$field] . "\n";
        }
    } else {
        echo "   لا توجد حقول مفقودة\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

