# PowerShell script to run CRM application
Write-Host "Starting CRM Application..." -ForegroundColor Green
Write-Host ""

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host ".env file not found. Copying from .env.example..." -ForegroundColor Yellow
    Copy-Item .env.example .env
    Write-Host "Please configure your .env file before running the application." -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if vendor directory exists (Composer dependencies)
if (-not (Test-Path vendor)) {
    Write-Host "Installing Composer dependencies..." -ForegroundColor Yellow
    composer install
}

# Check if node_modules exists (NPM dependencies)
if (-not (Test-Path node_modules)) {
    Write-Host "Installing NPM dependencies..." -ForegroundColor Yellow
    npm install
}

# Generate application key if not set
php artisan key:generate --force

# Clear and cache config
php artisan config:clear
php artisan cache:clear

# Run migrations
Write-Host "Running database migrations..." -ForegroundColor Yellow
php artisan migrate --force

# Start the development server
Write-Host ""
Write-Host "Starting Laravel development server..." -ForegroundColor Green
Write-Host "Server will be available at http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Run the dev script from composer.json
composer run dev

