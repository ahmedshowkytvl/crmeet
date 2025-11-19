import { chromium } from 'playwright';
import fs from 'fs/promises';
import path from 'path';

class ComprehensiveWebAnalyzer {
    constructor() {
        this.results = [];
        this.browser = null;
        this.context = null;
        this.baseUrl = 'http://192.168.15.29:8000';
        this.outputDir = './mcp_output';
        this.visitedUrls = new Set();
    }

    async init() {
        // إنشاء مجلدات الإخراج
        await this.createOutputDirectories();
        
        // تشغيل المتصفح
        this.browser = await chromium.launch({ 
            headless: false, // مرئي للمراقبة
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.context = await this.browser.newContext({
            viewport: { width: 1366, height: 768 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        });

        console.log('✅ تم تهيئة المحلل الشامل بنجاح');
    }

    async createOutputDirectories() {
        const dirs = [
            this.outputDir,
            path.join(this.outputDir, 'screenshots'),
            path.join(this.outputDir, 'html'),
            path.join(this.outputDir, 'reports'),
            path.join(this.outputDir, 'raw_logs')
        ];

        for (const dir of dirs) {
            try {
                await fs.mkdir(dir, { recursive: true });
            } catch (error) {
                console.log(`📁 المجلد موجود: ${dir}`);
            }
        }
    }

    async analyzeWebsite() {
        console.log('🚀 بدء التحليل الشامل للموقع...');
        
        // قائمة الصفحات المهمة للتحليل
        const pagesToAnalyze = [
            '/dashboard',
            '/users',
            '/tasks',
            '/departments',
            '/requests',
            '/chat',
            '/assets/dashboard',
            '/assets/assets',
            '/suppliers',
            '/contacts',
            '/password-accounts',
            '/users/create',
            '/tasks/create',
            '/departments/create'
        ];

        for (const page of pagesToAnalyze) {
            try {
                const url = `${this.baseUrl}${page}`;
                console.log(`📄 تحليل الصفحة: ${url}`);
                
                const result = await this.analyzePage(url);
                this.results.push(result);
                
                // انتظار بين الطلبات
                await this.delay(1000);
                
            } catch (error) {
                console.error(`❌ خطأ في تحليل ${page}:`, error.message);
                this.results.push({
                    url: `${this.baseUrl}${page}`,
                    error: error.message,
                    status: 'ERROR'
                });
            }
        }

        await this.generateComprehensiveReport();
        console.log('✅ تم الانتهاء من التحليل الشامل');
    }

    async analyzePage(url) {
        const page = await this.context.newPage();
        const result = {
            url,
            finalUrl: url,
            timestamp: new Date().toISOString(),
            status: null,
            statusCode: null,
            loadTime: 0,
            consoleLogs: [],
            networkRequests: [],
            failedRequests: [],
            brokenImages: [],
            accessibilityIssues: [],
            performanceMetrics: {},
            forms: [],
            links: [],
            issues: [],
            screenshots: []
        };

        try {
            // تسجيل الأحداث
            await this.setupEventListeners(page, result);

            // تحميل الصفحة
            const startTime = Date.now();
            const response = await page.goto(url, { 
                waitUntil: 'networkidle',
                timeout: 30000 
            });
            result.loadTime = Date.now() - startTime;

            if (response) {
                result.status = response.status();
                result.statusCode = response.status();
                result.finalUrl = response.url();
            }

            // انتظار استقرار الصفحة
            await page.waitForTimeout(2000);

            // التقاط لقطة شاشة
            const screenshotPath = await this.takeScreenshot(page, url);
            result.screenshots.push(screenshotPath);

            // حفظ HTML
            await this.saveHTML(page, url);

            // جمع البيانات
            await this.collectPageData(page, result);

            // فحص إمكانية الوصول
            await this.checkAccessibility(page, result);

            // تحليل الأداء
            await this.analyzePerformance(page, result);

            // تحديد المشاكل
            this.identifyIssues(result);

        } finally {
            await page.close();
        }

        return result;
    }

    async setupEventListeners(page, result) {
        page.on('console', msg => {
            result.consoleLogs.push({
                type: msg.type(),
                text: msg.text(),
                timestamp: new Date().toISOString()
            });
        });

        page.on('request', request => {
            result.networkRequests.push({
                url: request.url(),
                method: request.method(),
                timestamp: new Date().toISOString()
            });
        });

        page.on('response', response => {
            if (response.status() >= 400) {
                result.failedRequests.push({
                    url: response.url(),
                    status: response.status(),
                    timestamp: new Date().toISOString()
                });
            }
        });

        page.on('pageerror', error => {
            result.consoleLogs.push({
                type: 'error',
                text: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
        });
    }

    async takeScreenshot(page, url) {
        const safePath = this.getSafePath(url);
        const filename = `${safePath}.png`;
        const filepath = path.join(this.outputDir, 'screenshots', filename);
        
        await page.screenshot({ 
            path: filepath, 
            fullPage: true,
            type: 'png'
        });
        
        return filepath;
    }

    async saveHTML(page, url) {
        const safePath = this.getSafePath(url);
        const filename = `${safePath}.html`;
        const filepath = path.join(this.outputDir, 'html', filename);
        
        const html = await page.content();
        await fs.writeFile(filepath, html, 'utf8');
    }

    async collectPageData(page, result) {
        // جمع الروابط
        result.links = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('a[href]'));
            return links.map(link => ({
                href: link.href,
                text: link.textContent?.trim(),
                title: link.title
            }));
        });

        // جمع النماذج
        result.forms = await page.evaluate(() => {
            const forms = Array.from(document.querySelectorAll('form'));
            return forms.map(form => ({
                action: form.action,
                method: form.method,
                inputs: Array.from(form.querySelectorAll('input, select, textarea')).map(input => ({
                    type: input.type,
                    name: input.name,
                    required: input.required,
                    placeholder: input.placeholder
                }))
            }));
        });

        // فحص الصور المكسورة
        result.brokenImages = await page.evaluate(() => {
            const images = Array.from(document.querySelectorAll('img'));
            return images.filter(img => img.naturalWidth === 0).map(img => ({
                src: img.src,
                alt: img.alt
            }));
        });
    }

    async checkAccessibility(page, result) {
        try {
            // حقن axe-core
            await page.addScriptTag({
                url: 'https://unpkg.com/axe-core@4.8.2/axe.min.js'
            });

            const a11yResults = await page.evaluate(() => {
                return new Promise((resolve) => {
                    if (window.axe) {
                        window.axe.run(document).then(resolve).catch(() => resolve({ violations: [] }));
                    } else {
                        resolve({ violations: [] });
                    }
                });
            });

            result.accessibilityIssues = a11yResults.violations || [];
        } catch (error) {
            console.warn('⚠️ فشل فحص إمكانية الوصول:', error.message);
            result.accessibilityIssues = [];
        }
    }

    async analyzePerformance(page, result) {
        const metrics = await page.evaluate(() => {
            const navigation = performance.getEntriesByType('navigation')[0];
            const paint = performance.getEntriesByType('paint');
            
            return {
                domContentLoaded: navigation?.domContentLoadedEventEnd - navigation?.domContentLoadedEventStart,
                loadComplete: navigation?.loadEventEnd - navigation?.loadEventStart,
                firstPaint: paint.find(p => p.name === 'first-paint')?.startTime,
                firstContentfulPaint: paint.find(p => p.name === 'first-contentful-paint')?.startTime,
                ttfb: navigation?.responseStart - navigation?.requestStart
            };
        });

        result.performanceMetrics = metrics;
    }

    identifyIssues(result) {
        const issues = [];

        // مشاكل الاستجابة
        if (result.statusCode >= 500) {
            issues.push({
                severity: 'critical',
                type: 'server_error',
                description: `خطأ في الخادم: ${result.statusCode}`,
                evidence: `HTTP ${result.statusCode}`,
                suggestedFix: 'فحص سجلات الخادم وإصلاح الأخطاء'
            });
        } else if (result.statusCode >= 400) {
            issues.push({
                severity: 'high',
                type: 'client_error',
                description: `خطأ في العميل: ${result.statusCode}`,
                evidence: `HTTP ${result.statusCode}`,
                suggestedFix: 'فحص مسار الصفحة وإصلاح الرابط'
            });
        }

        // أخطاء JavaScript
        const errorCount = result.consoleLogs.filter(log => log.type === 'error').length;
        if (errorCount > 0) {
            issues.push({
                severity: errorCount > 5 ? 'high' : 'medium',
                type: 'javascript_errors',
                description: `${errorCount} خطأ في JavaScript`,
                evidence: `${errorCount} console errors`,
                suggestedFix: 'فحص وإصلاح أخطاء JavaScript'
            });
        }

        // الصور المكسورة
        if (result.brokenImages.length > 0) {
            issues.push({
                severity: 'medium',
                type: 'broken_images',
                description: `${result.brokenImages.length} صورة مكسورة`,
                evidence: `${result.brokenImages.length} broken images`,
                suggestedFix: 'فحص مسارات الصور وإصلاحها'
            });
        }

        // مشاكل إمكانية الوصول
        if (result.accessibilityIssues.length > 0) {
            const criticalA11y = result.accessibilityIssues.filter(v => v.impact === 'critical').length;
            issues.push({
                severity: criticalA11y > 0 ? 'high' : 'medium',
                type: 'accessibility_violations',
                description: `${result.accessibilityIssues.length} انتهاك لإمكانية الوصول`,
                evidence: `${result.accessibilityIssues.length} a11y violations`,
                suggestedFix: 'إضافة تسميات alt للصور وتحسين إمكانية الوصول'
            });
        }

        // مشاكل الأداء
        if (result.performanceMetrics.ttfb > 1500) {
            issues.push({
                severity: 'medium',
                type: 'slow_ttfb',
                description: `TTFB بطيء: ${result.performanceMetrics.ttfb}ms`,
                evidence: `TTFB: ${result.performanceMetrics.ttfb}ms`,
                suggestedFix: 'تحسين استجابة الخادم'
            });
        }

        result.issues = issues;
    }

    getSafePath(url) {
        try {
            const urlObj = new URL(url);
            const pathname = urlObj.pathname.replace(/[^a-zA-Z0-9]/g, '_');
            return pathname.substring(1) || 'home';
        } catch {
            return 'unknown';
        }
    }

    async generateComprehensiveReport() {
        console.log('📊 إنشاء التقرير الشامل...');

        // إحصائيات عامة
        const totalPages = this.results.length;
        const successfulPages = this.results.filter(r => r.statusCode && r.statusCode < 400).length;
        const criticalIssues = this.results.flatMap(r => r.issues.filter(i => i.severity === 'critical'));
        const highIssues = this.results.flatMap(r => r.issues.filter(i => i.severity === 'high'));
        const totalErrors = this.results.reduce((sum, r) => sum + r.consoleLogs.filter(l => l.type === 'error').length, 0);

        // تقرير JSON شامل
        const jsonReport = {
            summary: {
                totalPages,
                successfulPages,
                failedPages: totalPages - successfulPages,
                criticalIssues: criticalIssues.length,
                highIssues: highIssues.length,
                totalJavaScriptErrors: totalErrors,
                analyzedAt: new Date().toISOString(),
                baseUrl: this.baseUrl
            },
            pages: this.results
        };

        const jsonPath = path.join(this.outputDir, 'reports', 'comprehensive_report.json');
        await fs.writeFile(jsonPath, JSON.stringify(jsonReport, null, 2), 'utf8');

        // تقرير HTML شامل
        const htmlReport = this.generateHTMLReport(jsonReport);
        const htmlPath = path.join(this.outputDir, 'reports', 'comprehensive_report.html');
        await fs.writeFile(htmlPath, htmlReport, 'utf8');

        // تقرير CSV
        const csvData = this.results.map(result => ({
            url: result.url,
            status: result.statusCode || 'ERROR',
            load_time: result.loadTime,
            console_errors: result.consoleLogs.filter(l => l.type === 'error').length,
            failed_requests: result.failedRequests.length,
            broken_images: result.brokenImages.length,
            accessibility_violations: result.accessibilityIssues.length,
            forms_count: result.forms.length,
            links_count: result.links.length,
            primary_issue: result.issues.length > 0 ? result.issues[0].type : 'none',
            primary_severity: result.issues.length > 0 ? result.issues[0].severity : 'none'
        }));

        const csvContent = this.generateCSV(csvData);
        const csvPath = path.join(this.outputDir, 'reports', 'comprehensive_report.csv');
        await fs.writeFile(csvPath, csvContent, 'utf8');

        console.log(`✅ تم إنشاء التقارير في: ${this.outputDir}/reports`);
        console.log(`📊 ملخص النتائج:`);
        console.log(`   📄 إجمالي الصفحات: ${totalPages}`);
        console.log(`   ✅ صفحات ناجحة: ${successfulPages}`);
        console.log(`   🚨 مشاكل حرجة: ${criticalIssues.length}`);
        console.log(`   ⚠️ مشاكل عالية: ${highIssues.length}`);
        console.log(`   💥 أخطاء JavaScript: ${totalErrors}`);
    }

    generateHTMLReport(data) {
        const { summary, pages } = data;
        
        return `
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير التحليل الشامل - ${summary.baseUrl}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 15px; margin-bottom: 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { font-size: 1.2em; opacity: 0.9; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 3em; font-weight: bold; margin-bottom: 10px; }
        .stat-label { font-size: 1.1em; color: #666; }
        .critical { color: #e74c3c; }
        .high { color: #f39c12; }
        .medium { color: #f1c40f; }
        .low { color: #27ae60; }
        .success { color: #2ecc71; }
        .section { background: white; margin: 30px 0; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .section h2 { color: #2c3e50; margin-bottom: 20px; font-size: 1.8em; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .issue-card { background: #f8f9fa; border-left: 5px solid #e74c3c; padding: 20px; margin: 15px 0; border-radius: 8px; }
        .issue-card.medium { border-left-color: #f39c12; }
        .issue-card.low { border-left-color: #27ae60; }
        .page-card { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 10px; border: 1px solid #dee2e6; }
        .page-url { font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
        .page-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 10px 0; }
        .page-stat { background: white; padding: 10px; border-radius: 5px; text-align: center; }
        .page-stat-value { font-size: 1.5em; font-weight: bold; }
        .page-stat-label { font-size: 0.9em; color: #666; }
        .recommendations { background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; padding: 30px; border-radius: 15px; margin: 30px 0; }
        .recommendations h2 { color: white; border-bottom-color: rgba(255,255,255,0.3); }
        .recommendation { background: rgba(255,255,255,0.1); padding: 15px; margin: 10px 0; border-radius: 8px; }
        .footer { text-align: center; margin-top: 50px; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 تقرير التحليل الشامل</h1>
            <p>نظام إدارة الموظفين - EET Global Management System</p>
            <p>تم التحليل في: ${new Date().toLocaleString('ar-SA')}</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">${summary.totalPages}</div>
                <div class="stat-label">إجمالي الصفحات المحللة</div>
            </div>
            <div class="stat-card">
                <div class="stat-number success">${summary.successfulPages}</div>
                <div class="stat-label">صفحات تعمل بشكل صحيح</div>
            </div>
            <div class="stat-card">
                <div class="stat-number critical">${summary.criticalIssues}</div>
                <div class="stat-label">مشاكل حرجة</div>
            </div>
            <div class="stat-card">
                <div class="stat-number high">${summary.highIssues}</div>
                <div class="stat-label">مشاكل عالية الأولوية</div>
            </div>
            <div class="stat-card">
                <div class="stat-number medium">${summary.totalJavaScriptErrors}</div>
                <div class="stat-label">أخطاء JavaScript</div>
            </div>
        </div>

        <div class="section">
            <h2>🚨 المشاكل الحرجة</h2>
            ${criticalIssues.length > 0 ? criticalIssues.map(issue => `
                <div class="issue-card">
                    <h3>${issue.description}</h3>
                    <p><strong>الأدلة:</strong> ${issue.evidence}</p>
                    <p><strong>الحل المقترح:</strong> ${issue.suggestedFix}</p>
                </div>
            `).join('') : '<p style="color: #27ae60; font-weight: bold;">✅ لا توجد مشاكل حرجة</p>'}
        </div>

        <div class="section">
            <h2>⚠️ المشاكل عالية الأولوية</h2>
            ${highIssues.length > 0 ? highIssues.map(issue => `
                <div class="issue-card medium">
                    <h3>${issue.description}</h3>
                    <p><strong>الأدلة:</strong> ${issue.evidence}</p>
                    <p><strong>الحل المقترح:</strong> ${issue.suggestedFix}</p>
                </div>
            `).join('') : '<p style="color: #27ae60; font-weight: bold;">✅ لا توجد مشاكل عالية الأولوية</p>'}
        </div>

        <div class="section">
            <h2>📄 تفاصيل الصفحات</h2>
            ${pages.map(page => `
                <div class="page-card">
                    <div class="page-url">${page.url}</div>
                    <div class="page-stats">
                        <div class="page-stat">
                            <div class="page-stat-value ${page.statusCode && page.statusCode < 400 ? 'success' : 'critical'}">${page.statusCode || 'ERROR'}</div>
                            <div class="page-stat-label">الحالة</div>
                        </div>
                        <div class="page-stat">
                            <div class="page-stat-value">${page.loadTime}ms</div>
                            <div class="page-stat-label">وقت التحميل</div>
                        </div>
                        <div class="page-stat">
                            <div class="page-stat-value">${page.consoleLogs.filter(l => l.type === 'error').length}</div>
                            <div class="page-stat-label">أخطاء JS</div>
                        </div>
                        <div class="page-stat">
                            <div class="page-stat-value">${page.issues.length}</div>
                            <div class="page-stat-label">المشاكل</div>
                        </div>
                    </div>
                    ${page.issues.length > 0 ? `
                        <h4>المشاكل المكتشفة:</h4>
                        <ul>
                            ${page.issues.map(issue => `<li class="${issue.severity}">${issue.description}</li>`).join('')}
                        </ul>
                    ` : '<p style="color: #27ae60;">✅ لا توجد مشاكل</p>'}
                </div>
            `).join('')}
        </div>

        <div class="recommendations">
            <h2>💡 التوصيات والإجراءات</h2>
            <div class="recommendation">
                <strong>1. إصلاح المشاكل الحرجة:</strong> ركز على حل المشاكل التي تؤثر على عمل النظام الأساسي
            </div>
            <div class="recommendation">
                <strong>2. تحسين الأداء:</strong> قم بتحسين أوقات التحميل والاستجابة للخادم
            </div>
            <div class="recommendation">
                <strong>3. إمكانية الوصول:</strong> تأكد من أن جميع العناصر قابلة للوصول من قارئات الشاشة
            </div>
            <div class="recommendation">
                <strong>4. اختبار شامل:</strong> قم باختبار جميع الوظائف في بيئات مختلفة
            </div>
            <div class="recommendation">
                <strong>5. المراقبة المستمرة:</strong> استخدم أدوات المراقبة لاكتشاف المشاكل مبكراً
            </div>
        </div>

        <div class="footer">
            <p>تم إنشاء هذا التقرير بواسطة محلل الويب الشامل</p>
            <p>© ${new Date().getFullYear()} - EET Global Management System</p>
        </div>
    </div>
</body>
</html>`;
    }

    generateCSV(data) {
        if (data.length === 0) return '';
        
        const headers = Object.keys(data[0]);
        const csvRows = [
            headers.join(','),
            ...data.map(row => headers.map(header => `"${row[header] || ''}"`).join(','))
        ];
        
        return csvRows.join('\n');
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// تشغيل التحليل
async function main() {
    const analyzer = new ComprehensiveWebAnalyzer();
    
    try {
        await analyzer.init();
        await analyzer.analyzeWebsite();
        console.log('🎉 تم الانتهاء من التحليل الشامل بنجاح!');
    } catch (error) {
        console.error('❌ خطأ في التحليل:', error);
    } finally {
        await analyzer.close();
    }
}

// تشغيل إذا تم استدعاء الملف مباشرة
if (import.meta.url === `file://${process.argv[1]}`) {
    main().catch(console.error);
}

export default ComprehensiveWebAnalyzer;










