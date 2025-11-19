<?php
/**
 * إعادة ترتيب المايجريشنز لضمان إنشاء جدول المستخدمين أولاً
 */

$migrationsDir = 'database/migrations/';
$files = glob($migrationsDir . '*.php');

// المايجريشنز التي يجب أن تعمل بعد إنشاء جدول المستخدمين
$afterUsers = [
    '2025_01_16_100300_create_hiring_documents_table.php',
    '2025_01_16_100400_create_user_phones_table.php',
    '2025_09_09_130510_create_tasks_table.php',
    '2025_09_09_130518_create_employee_requests_table.php',
    '2025_09_09_130527_create_comments_table.php',
    '2025_09_09_135234_add_foreign_keys_to_tables.php',
    '2025_09_18_141802_create_departments_table.php',
    '2025_09_18_141907_add_foreign_keys_to_users_table.php',
    '2025_09_28_081256_create_employee_emails_table.php',
    '2025_09_28_084531_create_password_accounts_table.php',
    '2025_09_28_084534_create_password_assignments_table.php',
    '2025_09_28_084537_create_password_audit_logs_table.php',
    '2025_09_28_084540_create_password_history_table.php',
    '2025_09_28_084712_create_chat_rooms_table.php',
    '2025_09_28_084715_create_chat_messages_table.php',
    '2025_09_28_084719_create_chat_participants_table.php',
    '2025_09_28_085620_create_notifications_table.php',
];

$counter = 141800; // بداية من 141800

foreach ($afterUsers as $file) {
    $oldPath = $migrationsDir . $file;
    if (file_exists($oldPath)) {
        $newName = '2025_09_18_' . $counter . '_' . substr($file, 20); // إزالة التاريخ القديم
        $newPath = $migrationsDir . $newName;
        
        if (rename($oldPath, $newPath)) {
            echo "✅ تم نقل: $file -> $newName\n";
        } else {
            echo "❌ فشل نقل: $file\n";
        }
        $counter++;
    }
}

echo "\n🎉 تم إعادة ترتيب المايجريشنز بنجاح!\n";
?>
