@echo off
title CRM Server
color 0A
echo ========================================
echo    CRM Application Starting...
echo ========================================
echo.

cd /d "%~dp0"

REM Start PHP server
echo [1/2] Starting PHP Server on port 8000...
start "CRM PHP Server" cmd /k "php artisan serve"

timeout /t 3 /nobreak >nul

REM Start Vite dev server
echo [2/2] Starting Vite Dev Server...
start "CRM Vite Server" cmd /k "npm run dev"

echo.
echo ========================================
echo    CRM is running!
echo ========================================
echo.
echo PHP Server: http://localhost:8000
echo Vite Server: Running on default port
echo.
echo Close this window to stop all servers
echo.
pause

