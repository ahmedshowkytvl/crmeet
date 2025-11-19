@echo off
echo ========================================
echo    نظام إدارة الموظفين - العرض التقديمي
echo    Employee Management System Presentation
echo ========================================
echo.

echo [1] فتح العرض التقديمي HTML...
start "" "employee_management_presentation.html"

echo.
echo [2] إنشاء عرض PowerPoint...
cscript "create_employee_management_presentation.vbs"

echo.
echo [3] فتح مجلد الصور...
start "" "screenshots"

echo.
echo [4] عرض الصور المُلتقطة...
echo الصور المُلتقطة:
dir screenshots\*.png

echo.
echo ========================================
echo تم تشغيل العرض التقديمي بنجاح!
echo Presentation launched successfully!
echo ========================================
pause
