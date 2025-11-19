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

echo "=== إصلاح نظام الحالة (Online/Offline) ===\n\n";

// 1. إضافة جدول لتتبع حالة المستخدمين
$createTableSql = "
CREATE TABLE IF NOT EXISTS user_online_status (
    id SERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    is_online BOOLEAN DEFAULT false,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
";

try {
    $pdo->exec($createTableSql);
    echo "✅ تم إنشاء جدول user_online_status\n";
} catch (PDOException $e) {
    echo "❌ خطأ في إنشاء جدول user_online_status: " . $e->getMessage() . "\n";
}

// 2. إضافة عمود last_activity في جدول users
$addColumnSql = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
";

try {
    $pdo->exec($addColumnSql);
    echo "✅ تم إضافة عمود last_activity\n";
} catch (PDOException $e) {
    echo "❌ خطأ في إضافة عمود last_activity: " . $e->getMessage() . "\n";
}

// 3. إنشاء إدخالات للمستخدمين الموجودين
$users = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);

foreach ($users as $userId) {
    try {
        $sql = "INSERT INTO user_online_status (user_id, is_online, last_seen) 
                VALUES (?, false, CURRENT_TIMESTAMP) 
                ON CONFLICT (user_id) DO NOTHING";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        // تجاهل الأخطاء للمستخدمين الموجودين
    }
}

echo "✅ تم إنشاء إدخالات للمستخدمين الموجودين\n";

// 4. إنشاء دالة لتحديث حالة المستخدم
$createFunctionSql = "
CREATE OR REPLACE FUNCTION update_user_online_status(p_user_id BIGINT, p_is_online BOOLEAN)
RETURNS VOID AS $$
BEGIN
    INSERT INTO user_online_status (user_id, is_online, last_seen, updated_at)
    VALUES (p_user_id, p_is_online, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ON CONFLICT (user_id) 
    DO UPDATE SET 
        is_online = p_is_online,
        last_seen = CASE WHEN p_is_online THEN CURRENT_TIMESTAMP ELSE last_seen END,
        updated_at = CURRENT_TIMESTAMP;
    
    UPDATE users 
    SET last_activity = CURRENT_TIMESTAMP 
    WHERE id = p_user_id;
END;
$$ LANGUAGE plpgsql;
";

try {
    $pdo->exec($createFunctionSql);
    echo "✅ تم إنشاء دالة update_user_online_status\n";
} catch (PDOException $e) {
    echo "❌ خطأ في إنشاء الدالة: " . $e->getMessage() . "\n";
}

// 5. إنشاء دالة للحصول على حالة المستخدم
$getStatusFunctionSql = "
CREATE OR REPLACE FUNCTION get_user_online_status(p_user_id BIGINT)
RETURNS TABLE(is_online BOOLEAN, last_seen TIMESTAMP) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        COALESCE(uos.is_online, false) as is_online,
        COALESCE(uos.last_seen, u.last_activity) as last_seen
    FROM users u
    LEFT JOIN user_online_status uos ON u.id = uos.user_id
    WHERE u.id = p_user_id;
END;
$$ LANGUAGE plpgsql;
";

try {
    $pdo->exec($getStatusFunctionSql);
    echo "✅ تم إنشاء دالة get_user_online_status\n";
} catch (PDOException $e) {
    echo "❌ خطأ في إنشاء دالة get_user_online_status: " . $e->getMessage() . "\n";
}

echo "\n=== انتهاء إصلاح نظام الحالة ===\n";
?>
