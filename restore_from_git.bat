@echo off
REM سكريبت عكسي لاستعادة المشروع من Git وتحديث قاعدة البيانات من النسخة الاحتياطية (Windows)
REM Script to restore project from Git and update database from backup (Windows)

setlocal enabledelayedexpansion

echo === بدء عملية الاستعادة من Git وتحديث قاعدة البيانات ===
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
    if "!line:~0,13!"=="GITHUB_TOKEN" set GITHUB_TOKEN=%%b
)

REM قراءة GitHub Token من متغير البيئة إذا لم يكن في .env
if "%GITHUB_TOKEN%"=="" set GITHUB_TOKEN=%GITHUB_TOKEN_ENV%
if "%GITHUB_TOKEN%"=="" (
    echo ⚠ تحذير: GITHUB_TOKEN غير موجود في .env
    echo    سيتم محاولة استخدام المصادقة الحالية
    echo.
)

REM تعيين القيم الافتراضية
if "%DB_CONNECTION%"=="" set DB_CONNECTION=mysql
if "%DB_HOST%"=="" set DB_HOST=127.0.0.1
if "%DB_DATABASE%"=="" set DB_DATABASE=laravel
if "%DB_USERNAME%"=="" set DB_USERNAME=root
if "%DB_PORT%"=="" set DB_PORT=3306

REM التحقق من وجود مجلد النسخ الاحتياطي
if not exist database_backups (
    echo ❌ خطأ: مجلد النسخ الاحتياطي غير موجود: database_backups
    exit /b 1
)

REM البحث عن أحدث ملف backup
echo 🔍 البحث عن أحدث نسخة احتياطية...

REM البحث عن ملفات .sql.gz
set LATEST_BACKUP=
for /f "delims=" %%f in ('dir /b /o-d database_backups\*.sql.gz 2^>nul') do (
    set LATEST_BACKUP=database_backups\%%f
    goto :found
)

REM البحث عن ملفات .sql
for /f "delims=" %%f in ('dir /b /o-d database_backups\*.sql 2^>nul') do (
    set LATEST_BACKUP=database_backups\%%f
    goto :found
)

:found
if "%LATEST_BACKUP%"=="" (
    echo ❌ خطأ: لم يتم العثور على أي نسخة احتياطية
    exit /b 1
)

echo ✓ تم العثور على النسخة الاحتياطية: %LATEST_BACKUP%
echo.

REM استعادة المشروع من Git
echo 🔄 جاري استعادة المشروع من Git...
for /f "tokens=*" %%i in ('git branch --show-current') do set CURRENT_BRANCH=%%i

REM استخدام token إذا كان متوفراً
if not "%GITHUB_TOKEN%"=="" (
    REM الحصول على URL الحالي
    for /f "tokens=*" %%u in ('git remote get-url origin') do set REMOTE_URL=%%u
    
    REM استخراج اسم المستخدم والمستودع
    set REPO_PATH=
    echo %REMOTE_URL% | findstr /C:"@" >nul
    if not errorlevel 1 (
        REM SSH format: git@github.com:user/repo.git
        for /f "tokens=2 delims=:" %%p in ("%REMOTE_URL%") do set REPO_PATH=%%p
        set REPO_PATH=!REPO_PATH:.git=!
    ) else (
        REM HTTPS format: https://github.com/user/repo.git
        for /f "tokens=2 delims=/" %%p in ("%REMOTE_URL%") do (
            for /f "tokens=2 delims=/" %%q in ("%%p") do set REPO_PATH=%%q
        )
        set REPO_PATH=!REPO_PATH:.git=!
    )
    
    if not "!REPO_PATH!"=="" (
        REM تحديث URL لاستخدام token
        set GITHUB_URL=https://%GITHUB_TOKEN%@github.com/!REPO_PATH!.git
        git remote set-url origin "!GITHUB_URL!"
        
        REM استعادة المشروع
        git pull origin %CURRENT_BRANCH%
        
        REM استعادة URL الأصلي
        set ORIGINAL_URL=https://github.com/!REPO_PATH!.git
        git remote set-url origin "!ORIGINAL_URL!"
    ) else (
        git pull origin %CURRENT_BRANCH%
    )
) else (
    REM استخدام المصادقة الحالية
    git pull origin %CURRENT_BRANCH%
)

if errorlevel 1 (
    echo ⚠ تحذير: فشل pull من Git، سيتم المتابعة مع استعادة قاعدة البيانات
    echo.
) else (
    echo ✓ تم استعادة المشروع بنجاح
    echo.
)

REM فك الضغط إذا كان الملف مضغوطاً
set RESTORE_FILE=%LATEST_BACKUP%
if "%LATEST_BACKUP:~-3%"==".gz" (
    echo 🔄 جاري فك الضغط...
    set RESTORE_FILE=%LATEST_BACKUP:~0,-3%
    REM استخدام PowerShell لفك الضغط
    powershell -command "Expand-Archive -Path '%LATEST_BACKUP%' -DestinationPath 'database_backups\temp' -Force"
    if exist "database_backups\temp\*.sql" (
        for %%f in ("database_backups\temp\*.sql") do (
            copy "%%f" "%RESTORE_FILE%"
        )
        rmdir /s /q "database_backups\temp"
    ) else (
        REM محاولة فك الضغط باستخدام 7-Zip أو WinRAR إذا كان متوفراً
        echo ⚠ تحذير: لم يتم العثور على أداة لفك الضغط، سيتم استخدام الملف كما هو
        set RESTORE_FILE=%LATEST_BACKUP%
    )
    echo ✓ تم فك الضغط: %RESTORE_FILE%
    echo.
)

REM التحذير قبل الاستعادة
echo ⚠ تحذير: سيتم استبدال قاعدة البيانات الحالية بالنسخة الاحتياطية
set /p CONFIRM="هل تريد المتابعة؟ (y/n): "
if /i not "%CONFIRM%"=="y" (
    echo تم الإلغاء
    exit /b 0
)

echo.
echo 🔄 جاري استعادة قاعدة البيانات...

REM استعادة قاعدة البيانات حسب نوعها
if /i "%DB_CONNECTION%"=="pgsql" goto pgsql_restore
if /i "%DB_CONNECTION%"=="postgres" goto pgsql_restore
if /i "%DB_CONNECTION%"=="mysql" goto mysql_restore
if /i "%DB_CONNECTION%"=="mariadb" goto mysql_restore

echo ❌ نوع قاعدة البيانات غير مدعوم: %DB_CONNECTION%
exit /b 1

:mysql_restore
if "%DB_PASSWORD%"=="" (
    mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USERNAME% %DB_DATABASE% < %RESTORE_FILE%
) else (
    mysql -h %DB_HOST% -P %DB_PORT% -u %DB_USERNAME% -p%DB_PASSWORD% %DB_DATABASE% < %RESTORE_FILE%
)
if errorlevel 1 (
    echo ❌ فشل استعادة قاعدة البيانات
    exit /b 1
)
echo ✓ تم استعادة قاعدة البيانات بنجاح
goto cleanup

:pgsql_restore
set PGPASSWORD=%DB_PASSWORD%
if "%DB_PASSWORD%"=="" (
    psql -h %DB_HOST% -p %DB_PORT% -U %DB_USERNAME% -d %DB_DATABASE% -f %RESTORE_FILE%
) else (
    psql -h %DB_HOST% -p %DB_PORT% -U %DB_USERNAME% -d %DB_DATABASE% -f %RESTORE_FILE%
)
if errorlevel 1 (
    echo ❌ فشل استعادة قاعدة البيانات
    exit /b 1
)
echo ✓ تم استعادة قاعدة البيانات بنجاح
goto cleanup

:cleanup
REM تنظيف الملف المؤقت إذا كان مضغوطاً
if "%LATEST_BACKUP:~-3%"==".gz" (
    if exist "%RESTORE_FILE%" del "%RESTORE_FILE%"
)

echo.
echo === تم الانتهاء بنجاح ===
echo ✅ تم استعادة المشروع من Git
echo ✅ تم تحديث قاعدة البيانات من النسخة الاحتياطية
echo.

endlocal

