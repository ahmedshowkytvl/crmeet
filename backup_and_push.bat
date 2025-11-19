@echo off
REM سكريبت لأخذ نسخة احتياطية من قاعدة البيانات ورفع المشروع على Git (Windows)
REM Script to backup database and push project to Git (Windows)

setlocal enabledelayedexpansion

echo === بدء عملية النسخ الاحتياطي والرفع على Git ===
echo.

REM التحقق من وجود ملف .env
if not exist .env (
    echo ❌ خطأ: ملف .env غير موجود
    exit /b 1
)

REM قراءة إعدادات قاعدة البيانات من .env
for /f "tokens=1,2 delims==" %%a in (.env) do (
    set "line=%%a"
    if "!line:~0,13!"=="DB_CONNECTION" set DB_CONNECTION=%%b
    if "!line:~0,8!"=="DB_HOST" set DB_HOST=%%b
    if "!line:~0,10!"=="DB_DATABASE" set DB_DATABASE=%%b
    if "!line:~0,10!"=="DB_USERNAME" set DB_USERNAME=%%b
    if "!line:~0,10!"=="DB_PASSWORD" set DB_PASSWORD=%%b
    if "!line:~0,8!"=="DB_PORT" set DB_PORT=%%b
)

REM تعيين القيم الافتراضية
if "%DB_CONNECTION%"=="" set DB_CONNECTION=mysql
if "%DB_HOST%"=="" set DB_HOST=127.0.0.1
if "%DB_DATABASE%"=="" set DB_DATABASE=laravel
if "%DB_USERNAME%"=="" set DB_USERNAME=root
if "%DB_PORT%"=="" set DB_PORT=3306

REM إنشاء مجلد النسخ الاحتياطي
if not exist database_backups mkdir database_backups

REM إنشاء اسم الملف مع التاريخ والوقت
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set TIMESTAMP=%datetime:~0,8%_%datetime:~8,6%
set BACKUP_FILE=database_backups\%DB_DATABASE%_backup_%TIMESTAMP%.sql

echo 📦 نوع قاعدة البيانات: %DB_CONNECTION%
echo 📦 قاعدة البيانات: %DB_DATABASE%
echo 📦 الملف الاحتياطي: %BACKUP_FILE%
echo.

REM أخذ نسخة احتياطية حسب نوع قاعدة البيانات
if /i "%DB_CONNECTION%"=="pgsql" goto pgsql_backup
if /i "%DB_CONNECTION%"=="postgres" goto pgsql_backup
if /i "%DB_CONNECTION%"=="mysql" goto mysql_backup
if /i "%DB_CONNECTION%"=="mariadb" goto mysql_backup

echo ❌ نوع قاعدة البيانات غير مدعوم: %DB_CONNECTION%
echo المدعومة: mysql, mariadb, pgsql, postgres
exit /b 1

:mysql_backup
echo 🔄 جاري أخذ نسخة احتياطية من MySQL...
if "%DB_PASSWORD%"=="" (
    mysqldump -h %DB_HOST% -P %DB_PORT% -u %DB_USERNAME% %DB_DATABASE% > %BACKUP_FILE%
) else (
    mysqldump -h %DB_HOST% -P %DB_PORT% -u %DB_USERNAME% -p%DB_PASSWORD% %DB_DATABASE% > %BACKUP_FILE%
)
if errorlevel 1 (
    echo ❌ فشل أخذ النسخة الاحتياطية
    exit /b 1
)
echo ✓ تم أخذ النسخة الاحتياطية بنجاح
goto compress

:pgsql_backup
echo 🔄 جاري أخذ نسخة احتياطية من PostgreSQL...
set PGPASSWORD=%DB_PASSWORD%
if "%DB_PASSWORD%"=="" (
    pg_dump -h %DB_HOST% -U %DB_USERNAME% -d %DB_DATABASE% -F p > %BACKUP_FILE%
) else (
    pg_dump -h %DB_HOST% -U %DB_USERNAME% -d %DB_DATABASE% -F p > %BACKUP_FILE%
)
if errorlevel 1 (
    echo ❌ فشل أخذ النسخة الاحتياطية
    exit /b 1
)
echo ✓ تم أخذ النسخة الاحتياطية بنجاح
goto compress

:compress
echo.
echo 🔄 جاري ضغط الملف الاحتياطي...
REM استخدام PowerShell لضغط الملف
powershell -command "Compress-Archive -Path '%BACKUP_FILE%' -DestinationPath '%BACKUP_FILE%.zip' -Force"
if exist "%BACKUP_FILE%.zip" (
    del "%BACKUP_FILE%"
    set BACKUP_FILE=%BACKUP_FILE%.zip
    echo ✓ تم ضغط الملف: %BACKUP_FILE%
) else (
    echo ⚠ فشل ضغط الملف، سيتم الاحتفاظ بالملف غير المضغوط
)
echo.

REM إنشاء ملف README
echo # نسخ احتياطية قاعدة البيانات > database_backups\README.md
echo. >> database_backups\README.md
echo هذا المجلد يحتوي على نسخ احتياطية من قاعدة البيانات. >> database_backups\README.md

REM التأكد من أن مجلد النسخ الاحتياطي غير موجود في .gitignore
findstr /C:"^database_backups" .gitignore >nul 2>&1
if not errorlevel 1 (
    echo ⚠ تم العثور على database_backups في .gitignore، سيتم إزالته
    powershell -command "(Get-Content .gitignore) | Where-Object {$_ -notmatch '^database_backups'} | Set-Content .gitignore"
)

REM إضافة جميع الملفات إلى Git
echo.
echo 🔄 جاري إضافة الملفات إلى Git...
git add .

REM التحقق من وجود تغييرات
git diff --staged --quiet
if errorlevel 1 (
    REM إنشاء رسالة commit
    for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set mydate=%%c-%%a-%%b
    for /f "tokens=1-2 delims=: " %%a in ('time /t') do set mytime=%%a:%%b
    set COMMIT_MESSAGE=Backup and push: Database backup %mydate% %mytime%
    
    echo 🔄 جاري عمل commit...
    git commit -m "%COMMIT_MESSAGE%"
    if errorlevel 1 (
        echo ❌ فشل عمل commit
        exit /b 1
    )
    echo ✓ تم عمل commit بنجاح
    echo.
    
    REM رفع التغييرات إلى Git
    echo 🔄 جاري رفع التغييرات إلى Git...
    for /f "tokens=*" %%i in ('git branch --show-current') do set CURRENT_BRANCH=%%i
    git push origin %CURRENT_BRANCH%
    if errorlevel 1 (
        echo ❌ فشل رفع التغييرات
        exit /b 1
    )
    echo ✓ تم رفع التغييرات بنجاح
) else (
    echo ⚠ لا توجد تغييرات لإضافتها
)

echo.
echo === تم الانتهاء بنجاح ===
echo 📁 الملف الاحتياطي: %BACKUP_FILE%
echo ✅ تم رفع المشروع على Git
echo.

endlocal

