# PowerShell script لبدء نظام مراقبة النظام
# System Monitor Startup Script for Windows PowerShell

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   بدء نظام مراقبة النظام" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# التحقق من PHP
Write-Host "[1/4] التحقق من PHP..." -ForegroundColor Green
try {
    $phpVersion = php --version 2>$null
    if ($phpVersion) {
        Write-Host "✓ PHP موجود: $($phpVersion.Split("`n")[0])" -ForegroundColor Green
    } else {
        throw "PHP غير موجود"
    }
} catch {
    Write-Host "✗ خطأ: PHP غير مثبت أو غير موجود في PATH" -ForegroundColor Red
    Write-Host "يرجى تثبيت PHP وإضافته إلى PATH" -ForegroundColor Yellow
    Read-Host "اضغط Enter للخروج"
    exit 1
}

# التحقق من Node.js
Write-Host "[2/4] التحقق من Node.js..." -ForegroundColor Green
try {
    $nodeVersion = node --version 2>$null
    if ($nodeVersion) {
        Write-Host "✓ Node.js موجود: $nodeVersion" -ForegroundColor Green
    } else {
        throw "Node.js غير موجود"
    }
} catch {
    Write-Host "✗ خطأ: Node.js غير مثبت أو غير موجود في PATH" -ForegroundColor Red
    Write-Host "يرجى تثبيت Node.js من https://nodejs.org" -ForegroundColor Yellow
    Read-Host "اضغط Enter للخروج"
    exit 1
}

# تثبيت تبعيات WebSocket
Write-Host "[3/4] تثبيت تبعيات WebSocket..." -ForegroundColor Green
if (Test-Path "node_modules") {
    Write-Host "✓ تبعيات Node.js موجودة بالفعل" -ForegroundColor Green
} else {
    Write-Host "تثبيت تبعيات WebSocket..." -ForegroundColor Yellow
    npm install ws
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ تم تثبيت التبعيات بنجاح" -ForegroundColor Green
    } else {
        Write-Host "✗ فشل في تثبيت التبعيات" -ForegroundColor Red
        Read-Host "اضغط Enter للخروج"
        exit 1
    }
}

# بدء الخوادم
Write-Host "[4/4] بدء الخوادم..." -ForegroundColor Green

# بدء خادم Laravel
Write-Host "بدء خادم Laravel..." -ForegroundColor Yellow
$laravelJob = Start-Job -ScriptBlock {
    Set-Location $using:PWD
    php artisan serve --host=0.0.0.0 --port=8000
}

# انتظار قليل
Start-Sleep -Seconds 3

# بدء خادم WebSocket
Write-Host "بدء خادم WebSocket..." -ForegroundColor Yellow
$websocketJob = Start-Job -ScriptBlock {
    Set-Location $using:PWD
    node websocket-server.js
}

# انتظار قليل
Start-Sleep -Seconds 2

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   تم تشغيل النظام بنجاح!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "روابط الوصول:" -ForegroundColor Yellow
Write-Host "- واجهة المراقب: http://localhost:8000/system-monitor" -ForegroundColor White
Write-Host "- خادم WebSocket: ws://localhost:8080/ws/system-monitor" -ForegroundColor White
Write-Host ""
Write-Host "للوصول من الشبكة:" -ForegroundColor Yellow
Write-Host "1. اعرف عنوان IP الخاص بك" -ForegroundColor White
Write-Host "2. استخدم: http://YOUR_IP:8000/system-monitor" -ForegroundColor White
Write-Host ""
Write-Host "لإيقاف النظام، اضغط Ctrl+C" -ForegroundColor Red
Write-Host ""

# عرض حالة الخوادم
while ($true) {
    $laravelStatus = if ($laravelJob.State -eq "Running") { "✓ يعمل" } else { "✗ متوقف" }
    $websocketStatus = if ($websocketJob.State -eq "Running") { "✓ يعمل" } else { "✗ متوقف" }
    
    Write-Host "حالة الخوادم: Laravel $laravelStatus | WebSocket $websocketStatus" -ForegroundColor Cyan
    
    Start-Sleep -Seconds 10
}

# تنظيف عند الإنهاء
$laravelJob | Stop-Job
$websocketJob | Stop-Job
$laravelJob | Remove-Job
$websocketJob | Remove-Job






