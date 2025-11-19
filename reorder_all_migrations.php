<?php
/**
 * إعادة ترتيب جميع المايجريشنز بالترتيب الصحيح
 */

$migrationsDir = 'database/migrations/';

// الترتيب الصحيح للمايجريشنز
$correctOrder = [
    // 1. الجداول الأساسية
    '0001_01_01_000001_create_cache_table.php',
    '0001_01_01_000002_create_jobs_table.php',
    
    // 2. جداول الأصول (بدون foreign keys)
    '2024_01_01_000001_create_asset_categories_table.php',
    '2024_01_01_000002_create_asset_locations_table.php',
    
    // 3. جداول المستخدمين والأدوار
    '2025_09_18_141700_create_departments_table.php',
    '2025_09_18_141701_create_roles_table.php',
    '2025_09_18_141744_create_complete_users_table.php',
    
    // 4. جداول الصلاحيات
    '2025_09_09_154823_create_permissions_table.php',
    '2025_09_09_154825_create_role_permissions_table.php',
    
    // 5. جداول الأصول (مع foreign keys)
    '2025_09_18_141745_create_assets_table.php',
    '2025_09_18_141746_create_asset_assignments_table.php',
    '2025_09_18_141747_create_asset_logs_table.php',
    '2025_09_18_141748_create_asset_category_properties_table.php',
    '2025_09_18_141749_create_asset_property_values_table.php',
    
    // 6. جداول أخرى
    '2025_01_15_140000_create_contacts_table.php',
    '2025_01_15_141000_create_contact_categories_table.php',
    '2025_01_16_100100_create_branches_table.php',
    '2025_01_16_100200_create_phone_types_table.php',
    
    // 7. باقي المايجريشنز
    '2025_09_18_141800_eate_hiring_documents_table.php',
    '2025_09_18_141801_eate_user_phones_table.php',
    '2025_09_18_141802_eate_tasks_table.php',
    '2025_09_18_141803_eate_employee_requests_table.php',
    '2025_09_18_141804_eate_comments_table.php',
    '2025_09_18_141805_d_foreign_keys_to_tables.php',
    '2025_09_18_141807_d_foreign_keys_to_users_table.php',
    '2025_09_18_141808_eate_employee_emails_table.php',
    '2025_09_18_141809_eate_password_accounts_table.php',
    '2025_09_18_141810_eate_password_assignments_table.php',
    '2025_09_18_141811_eate_password_audit_logs_table.php',
    '2025_09_18_141812_eate_password_history_table.php',
    '2025_09_18_141813_eate_chat_rooms_table.php',
    '2025_09_18_141814_eate_chat_messages_table.php',
    '2025_09_18_141815_eate_chat_participants_table.php',
    '2025_09_18_141816_eate_notifications_table.php',
];

$counter = 100000;

foreach ($correctOrder as $file) {
    $oldPath = $migrationsDir . $file;
    if (file_exists($oldPath)) {
        $newName = '2025_09_18_' . $counter . '_' . substr($file, 20);
        $newPath = $migrationsDir . $newName;
        
        if (rename($oldPath, $newPath)) {
            echo "✅ تم نقل: $file -> $newName\n";
        } else {
            echo "❌ فشل نقل: $file\n";
        }
        $counter++;
    }
}

echo "\n🎉 تم إعادة ترتيب جميع المايجريشنز بنجاح!\n";
?>
