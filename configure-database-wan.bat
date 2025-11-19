@echo off
echo ========================================
echo    تكوين قاعدة البيانات للشبكة الواسعة
echo ========================================
echo.

echo [1/4] إيقاف خدمة PostgreSQL...
net stop postgresql-x64-13
timeout /t 3

echo [2/4] تحديث ملف postgresql.conf...
set PG_CONFIG_FILE=C:\Program Files\PostgreSQL\13\data\postgresql.conf
set PG_HBA_FILE=C:\Program Files\PostgreSQL\13\data\pg_hba.conf

REM نسخ احتياطي للملفات الأصلية
copy "%PG_CONFIG_FILE%" "%PG_CONFIG_FILE%.backup"
copy "%PG_HBA_FILE%" "%PG_HBA_FILE%.backup"

REM تحديث postgresql.conf
powershell -Command "(Get-Content '%PG_CONFIG_FILE%') -replace '^#?listen_addresses.*', 'listen_addresses = ''*''' | Set-Content '%PG_CONFIG_FILE%'"
powershell -Command "(Get-Content '%PG_CONFIG_FILE%') -replace '^#?port.*', 'port = 5432' | Set-Content '%PG_CONFIG_FILE%'"

echo [3/4] تحديث ملف pg_hba.conf...
echo # إعدادات الشبكة الواسعة >> "%PG_HBA_FILE%"
echo host    CRM_ALL    wan_user    0.0.0.0/0    md5 >> "%PG_HBA_FILE%"
echo host    CRM_ALL    postgres   0.0.0.0/0    md5 >> "%PG_HBA_FILE%"
echo host    all        all        0.0.0.0/0    md5 >> "%PG_HBA_FILE%"

echo [4/4] بدء خدمة PostgreSQL...
net start postgresql-x64-13
timeout /t 5

echo.
echo ✅ تم تكوين قاعدة البيانات للشبكة الواسعة
echo.
echo الآن يمكنك تشغيل:
echo   psql -h localhost -U postgres -d CRM_ALL -f database-wan-setup.sql
echo.
pause





