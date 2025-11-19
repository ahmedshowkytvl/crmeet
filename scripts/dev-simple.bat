@echo off
echo Starting Laravel Development Environment...
echo.

REM Start Laravel Server
start "Laravel Server" cmd /k "php artisan serve"

REM Start Queue Worker  
start "Queue Worker" cmd /k "php artisan queue:listen --tries=1"

REM Start Vite Dev Server
start "Vite Dev Server" cmd /k "npm run dev"

echo.
echo All services started in separate windows.
echo Press any key to exit (this will NOT stop the services)...
pause >nul
