-- تكوين قاعدة البيانات للوصول الخارجي
-- ملف: database-wan-setup.sql

-- 1. إنشاء مستخدم للوصول الخارجي
CREATE USER wan_user WITH PASSWORD 'wan_password_2024';

-- 2. منح الصلاحيات للمستخدم الجديد
GRANT CONNECT ON DATABASE "CRM_ALL" TO wan_user;
GRANT USAGE ON SCHEMA public TO wan_user;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO wan_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO wan_user;

-- 3. منح الصلاحيات للجداول المستقبلية
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO wan_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO wan_user;

-- 4. تحديث إعدادات الاتصال
-- تحديث ملف postgresql.conf:
-- listen_addresses = '*'
-- port = 5432

-- تحديث ملف pg_hba.conf:
-- host    CRM_ALL    wan_user    0.0.0.0/0    md5
-- host    CRM_ALL    postgres   0.0.0.0/0    md5

-- 5. إنشاء قاعدة بيانات للاختبار
CREATE DATABASE crm_wan_test OWNER wan_user;

-- 6. إعداد النسخ الاحتياطي التلقائي
-- يمكن إضافة هذا إلى crontab:
-- 0 2 * * * pg_dump -h localhost -U postgres CRM_ALL > /backup/crm_backup_$(date +\%Y\%m\%d).sql

COMMENT ON DATABASE "CRM_ALL" IS 'قاعدة بيانات نظام إدارة الموظفين - متاحة للوصول الخارجي';
COMMENT ON USER wan_user IS 'مستخدم للوصول الخارجي عبر الشبكة الواسعة';





