# تحليل شامل لنظام إدارة الموظفين
# EET Global Management System Web Analysis

Write-Host "🔍 بدء التحليل الشامل لنظام إدارة الموظفين" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

# التحقق من وجود Node.js
try {
    $nodeVersion = node --version
    Write-Host "✅ Node.js مثبت: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Node.js غير مثبت. يرجى تثبيته أولاً." -ForegroundColor Red
    exit 1
}

# التحقق من وجود Playwright
try {
    $playwrightCheck = npm list playwright 2>$null
    Write-Host "✅ Playwright مثبت" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Playwright غير مثبت. يتم تثبيته الآن..." -ForegroundColor Yellow
    npm install playwright
    npx playwright install chromium
}

# إنشاء مجلدات الإخراج
$outputDir = "mcp_output"
$directories = @(
    "$outputDir",
    "$outputDir\screenshots",
    "$outputDir\html",
    "$outputDir\reports",
    "$outputDir\raw_logs"
)

foreach ($dir in $directories) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force
        Write-Host "📁 تم إنشاء المجلد: $dir" -ForegroundColor Green
    }
}

# تشغيل التحليل
Write-Host "🚀 بدء التحليل..." -ForegroundColor Yellow

try {
    # تشغيل المحلل الشامل
    node comprehensive_analysis_report.js
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ تم التحليل بنجاح!" -ForegroundColor Green
        
        # عرض النتائج
        Write-Host "`n📊 النتائج:" -ForegroundColor Cyan
        Write-Host "===========" -ForegroundColor Cyan
        
        if (Test-Path "$outputDir\reports\comprehensive_report.html") {
            Write-Host "📄 التقرير الشامل: $outputDir\reports\comprehensive_report.html" -ForegroundColor Green
        }
        
        if (Test-Path "$outputDir\reports\comprehensive_report.json") {
            Write-Host "📋 البيانات المفصلة: $outputDir\reports\comprehensive_report.json" -ForegroundColor Green
        }
        
        if (Test-Path "$outputDir\reports\comprehensive_report.csv") {
            Write-Host "📊 البيانات المجدولة: $outputDir\reports\comprehensive_report.csv" -ForegroundColor Green
        }
        
        # عرض لقطات الشاشة
        $screenshots = Get-ChildItem "$outputDir\screenshots" -Filter "*.png" -ErrorAction SilentlyContinue
        if ($screenshots.Count -gt 0) {
            Write-Host "🖼️ لقطات الشاشة: $($screenshots.Count) صورة" -ForegroundColor Green
        }
        
        # عرض ملفات HTML
        $htmlFiles = Get-ChildItem "$outputDir\html" -Filter "*.html" -ErrorAction SilentlyContinue
        if ($htmlFiles.Count -gt 0) {
            Write-Host "📄 نسخ HTML: $($htmlFiles.Count) ملف" -ForegroundColor Green
        }
        
        Write-Host "`n🎉 تم الانتهاء من التحليل!" -ForegroundColor Green
        Write-Host "يمكنك فتح التقرير الشامل في المتصفح:" -ForegroundColor Yellow
        Write-Host "start $outputDir\reports\comprehensive_report.html" -ForegroundColor Cyan
        
    } else {
        Write-Host "❌ فشل في التحليل" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ في تشغيل التحليل: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n📝 ملاحظات:" -ForegroundColor Yellow
Write-Host "- تأكد من أن الخادم يعمل على http://192.168.15.29:8000" -ForegroundColor White
Write-Host "- يمكنك تعديل الإعدادات في crawler_config.json" -ForegroundColor White
Write-Host "- للتحليل المتقدم، استخدم: node run_crawler.js --help" -ForegroundColor White

Write-Host "`n🔗 روابط مفيدة:" -ForegroundColor Cyan
Write-Host "- دليل الاستخدام: WEB_CRAWLER_README.md" -ForegroundColor White
Write-Host "- تقرير التحليل: WEB_ANALYSIS_REPORT.md" -ForegroundColor White
Write-Host "- البيانات المفصلة: detailed_analysis.json" -ForegroundColor White










