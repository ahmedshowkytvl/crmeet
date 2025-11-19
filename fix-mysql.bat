@echo off
echo إصلاح MySQL في XAMPP...
echo.

echo 1. إيقاف جميع عمليات MySQL...
taskkill /f /im mysqld.exe 2>nul
taskkill /f /im mysqld-nt.exe 2>nul

echo 2. إنشاء نسخة احتياطية من البيانات...
if exist "D:\xampp\mysql\data_backup" rmdir /s /q "D:\xampp\mysql\data_backup"
if exist "D:\xampp\mysql\data" move "D:\xampp\mysql\data" "D:\xampp\mysql\data_backup"

echo 3. إنشاء مجلد البيانات الجديد...
mkdir "D:\xampp\mysql\data"

echo 4. تهيئة قاعدة البيانات...
"D:\xampp\mysql\bin\mysqld.exe" --initialize-insecure --user=mysql --datadir=D:\xampp\mysql\data

echo 5. تشغيل MySQL...
start "MySQL" "D:\xampp\mysql\bin\mysqld.exe" --console

echo.
echo تم الانتهاء! جرب تشغيل MySQL من XAMPP Control Panel
pause
