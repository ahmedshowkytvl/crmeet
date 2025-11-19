-- إضافة حقول بطاقة الاتصال الشاملة لجدول المستخدمين
-- يجب تشغيل هذا الملف في قاعدة البيانات

-- التحقق من وجود الحقول قبل إضافتها
SET @sql = '';

-- إضافة حقول الاتصال
SELECT @sql := CONCAT(@sql, 
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'phone_mobile') = 0 
    THEN 'ALTER TABLE users ADD COLUMN phone_mobile VARCHAR(255) NULL AFTER phone_personal; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'phone_emergency') = 0 
    THEN 'ALTER TABLE users ADD COLUMN phone_emergency VARCHAR(255) NULL AFTER phone_mobile; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'whatsapp') = 0 
    THEN 'ALTER TABLE users ADD COLUMN whatsapp VARCHAR(255) NULL AFTER phone_emergency; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'telegram') = 0 
    THEN 'ALTER TABLE users ADD COLUMN telegram VARCHAR(255) NULL AFTER whatsapp; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'skype') = 0 
    THEN 'ALTER TABLE users ADD COLUMN skype VARCHAR(255) NULL AFTER telegram; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'facebook') = 0 
    THEN 'ALTER TABLE users ADD COLUMN facebook VARCHAR(255) NULL AFTER skype; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'instagram') = 0 
    THEN 'ALTER TABLE users ADD COLUMN instagram VARCHAR(255) NULL AFTER facebook; ' 
    ELSE '' END
);

-- إضافة حقول العمل
SELECT @sql := CONCAT(@sql,
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'employee_id') = 0 
    THEN 'ALTER TABLE users ADD COLUMN employee_id VARCHAR(255) NULL AFTER job_title; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'hire_date') = 0 
    THEN 'ALTER TABLE users ADD COLUMN hire_date DATE NULL AFTER employee_id; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'work_location') = 0 
    THEN 'ALTER TABLE users ADD COLUMN work_location VARCHAR(255) NULL AFTER hire_date; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'office_room') = 0 
    THEN 'ALTER TABLE users ADD COLUMN office_room VARCHAR(255) NULL AFTER work_location; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'extension') = 0 
    THEN 'ALTER TABLE users ADD COLUMN extension VARCHAR(255) NULL AFTER office_room; ' 
    ELSE '' END
);

-- إضافة حقول شخصية
SELECT @sql := CONCAT(@sql,
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'birth_date') = 0 
    THEN 'ALTER TABLE users ADD COLUMN birth_date DATE NULL AFTER birthday; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'nationality') = 0 
    THEN 'ALTER TABLE users ADD COLUMN nationality VARCHAR(255) NULL AFTER birth_date; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'address') = 0 
    THEN 'ALTER TABLE users ADD COLUMN address TEXT NULL AFTER nationality; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'city') = 0 
    THEN 'ALTER TABLE users ADD COLUMN city VARCHAR(255) NULL AFTER address; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'country') = 0 
    THEN 'ALTER TABLE users ADD COLUMN country VARCHAR(255) NULL AFTER city; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'postal_code') = 0 
    THEN 'ALTER TABLE users ADD COLUMN postal_code VARCHAR(255) NULL AFTER country; ' 
    ELSE '' END
);

-- إضافة حقول إضافية
SELECT @sql := CONCAT(@sql,
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'skills') = 0 
    THEN 'ALTER TABLE users ADD COLUMN skills TEXT NULL AFTER bio; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'interests') = 0 
    THEN 'ALTER TABLE users ADD COLUMN interests TEXT NULL AFTER skills; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'languages') = 0 
    THEN 'ALTER TABLE users ADD COLUMN languages TEXT NULL AFTER interests; ' 
    ELSE '' END
);

-- إضافة إعدادات الخصوصية
SELECT @sql := CONCAT(@sql,
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'show_phone_work') = 0 
    THEN 'ALTER TABLE users ADD COLUMN show_phone_work BOOLEAN DEFAULT TRUE AFTER notification_preferences; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'show_phone_personal') = 0 
    THEN 'ALTER TABLE users ADD COLUMN show_phone_personal BOOLEAN DEFAULT FALSE AFTER show_phone_work; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'show_phone_mobile') = 0 
    THEN 'ALTER TABLE users ADD COLUMN show_phone_mobile BOOLEAN DEFAULT TRUE AFTER show_phone_personal; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'show_email') = 0 
    THEN 'ALTER TABLE users ADD COLUMN show_email BOOLEAN DEFAULT TRUE AFTER show_phone_mobile; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'show_address') = 0 
    THEN 'ALTER TABLE users ADD COLUMN show_address BOOLEAN DEFAULT FALSE AFTER show_email; ' 
    ELSE '' END,
    
    CASE WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'show_social_media') = 0 
    THEN 'ALTER TABLE users ADD COLUMN show_social_media BOOLEAN DEFAULT TRUE AFTER show_address; ' 
    ELSE '' END
);

-- تشغيل الاستعلامات
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- عرض رسالة نجاح
SELECT 'تم إضافة حقول بطاقة الاتصال الشاملة بنجاح!' AS message;
