@echo off
echo Starting CRM Application...
echo.

REM Check if .env exists
if not exist .env (
    echo .env file not found. Copying from .env.example...
    copy .env.example .env
    echo Please configure your .env file before running the application.
    pause
    exit /b 1
)

REM Check if vendor directory exists (Composer dependencies)
if not exist vendor (
    echo Installing Composer dependencies...
    composer install
)

REM Check if node_modules exists (NPM dependencies)
if not exist node_modules (
    echo Installing NPM dependencies...
    npm install
)

REM Generate application key if not set
php artisan key:generate --force

REM Clear and cache config
php artisan config:clear
php artisan cache:clear

REM Run migrations
echo Running database migrations...
php artisan migrate --force

REM Start the development server
echo.
echo Starting Laravel development server...
echo Server will be available at http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

REM Run the dev script from composer.json
composer run dev

