import { chromium } from 'playwright';
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

class WebCrawlerAnalyzer {
    constructor(config) {
        this.config = {
            START_URL: config.START_URL || 'http://192.168.15.29:8000',
            MAX_DEPTH: config.MAX_DEPTH || 2,
            ALLOWED_DOMAINS: config.ALLOWED_DOMAINS || ['192.168.15.29:8000'],
            RATE_LIMIT_MS: config.RATE_LIMIT_MS || 300,
            OUTPUT_DIR: config.OUTPUT_DIR || './mcp_output',
            VISIT_TIMEOUT_MS: config.VISIT_TIMEOUT_MS || 30000,
            VIEWPORT: config.VIEWPORT || { width: 1366, height: 768 },
            INCLUDE_A11Y: config.INCLUDE_A11Y || true,
            HEADLESS: config.HEADLESS || true,
            AUTH: config.AUTH || null,
            IMAGE_SIZE_THRESHOLD_BYTES: config.IMAGE_SIZE_THRESHOLD_BYTES || 1024 * 1024, // 1MB
            FILE_SIZE_THRESHOLD: config.FILE_SIZE_THRESHOLD || 1024 * 1024, // 1MB
            ROBOTS_CHECK: config.ROBOTS_CHECK || false,
            GOLDEN_DIR: config.GOLDEN_DIR || null,
            LOG_LEVEL: config.LOG_LEVEL || 'INFO'
        };

        this.visitedUrls = new Set();
        this.urlQueue = [];
        this.results = [];
        this.browser = null;
        this.context = null;
    }

    async init() {
        // إنشاء مجلدات الإخراج
        await this.createOutputDirectories();
        
        // تشغيل المتصفح
        this.browser = await chromium.launch({ 
            headless: this.config.HEADLESS,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.context = await this.browser.newContext({
            viewport: this.config.VIEWPORT,
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        });

        // إعداد المصادقة إذا كانت متوفرة
        if (this.config.AUTH) {
            await this.setupAuthentication();
        }

        console.log('✅ تم تهيئة محلل الويب بنجاح');
    }

    async createOutputDirectories() {
        const dirs = [
            this.config.OUTPUT_DIR,
            path.join(this.config.OUTPUT_DIR, 'screenshots'),
            path.join(this.config.OUTPUT_DIR, 'html'),
            path.join(this.config.OUTPUT_DIR, 'raw_logs'),
            path.join(this.config.OUTPUT_DIR, 'reports')
        ];

        for (const dir of dirs) {
            try {
                await fs.mkdir(dir, { recursive: true });
            } catch (error) {
                console.log(`📁 المجلد موجود بالفعل: ${dir}`);
            }
        }
    }

    async setupAuthentication() {
        const auth = this.config.AUTH;
        
        if (auth.type === 'cookie') {
            const cookies = Array.isArray(auth.value) ? auth.value : [auth.value];
            await this.context.addCookies(cookies);
        } else if (auth.type === 'basic') {
            await this.context.setHTTPCredentials({
                username: auth.value.username,
                password: auth.value.password
            });
        }
        // يمكن إضافة المزيد من أنواع المصادقة هنا
    }

    async crawl() {
        console.log(`🚀 بدء تحليل الموقع: ${this.config.START_URL}`);
        
        this.urlQueue.push({
            url: this.config.START_URL,
            depth: 0,
            parentUrl: null
        });

        while (this.urlQueue.length > 0) {
            const { url, depth, parentUrl } = this.urlQueue.shift();
            
            if (this.shouldSkipUrl(url) || depth > this.config.MAX_DEPTH) {
                continue;
            }

            console.log(`📄 تحليل الصفحة: ${url} (العمق: ${depth})`);
            
            try {
                const result = await this.analyzePage(url, depth, parentUrl);
                this.results.push(result);
                
                // إضافة الروابط الجديدة إلى قائمة الانتظار
                if (depth < this.config.MAX_DEPTH) {
                    for (const link of result.internalLinks) {
                        if (!this.visitedUrls.has(link)) {
                            this.urlQueue.push({
                                url: link,
                                depth: depth + 1,
                                parentUrl: url
                            });
                        }
                    }
                }

                this.visitedUrls.add(url);
                
                // انتظار لتجنب الضغط على الخادم
                await this.delay(this.config.RATE_LIMIT_MS);
                
            } catch (error) {
                console.error(`❌ خطأ في تحليل الصفحة ${url}:`, error.message);
                this.results.push({
                    url,
                    depth,
                    parentUrl,
                    error: error.message,
                    status: 'ERROR',
                    timestamp: new Date().toISOString()
                });
            }
        }

        await this.generateReports();
        console.log('✅ تم الانتهاء من تحليل الموقع');
    }

    shouldSkipUrl(url) {
        // تجاهل الروابط المزعجة
        const skipPatterns = [
            /logout/i,
            /mailto:/i,
            /tel:/i,
            /#.*$/,
            /\.(pdf|doc|docx|xls|xlsx|zip|rar)$/i,
            /javascript:/i,
            /data:/i
        ];

        for (const pattern of skipPatterns) {
            if (pattern.test(url)) return true;
        }

        // التحقق من النطاقات المسموحة
        try {
            const urlObj = new URL(url);
            const allowed = this.config.ALLOWED_DOMAINS.some(domain => 
                urlObj.hostname === domain || urlObj.host === domain
            );
            return !allowed;
        } catch {
            return true;
        }
    }

    async analyzePage(url, depth, parentUrl) {
        const page = await this.context.newPage();
        const result = {
            url,
            finalUrl: url,
            depth,
            parentUrl,
            timestamp: new Date().toISOString(),
            status: null,
            statusCode: null,
            timings: {},
            screenshots: [],
            htmlSnapshot: null,
            internalLinks: [],
            externalLinks: [],
            consoleLogs: [],
            networkRequests: [],
            failedRequests: [],
            brokenImages: [],
            accessibilityViolations: [],
            performanceIssues: [],
            forms: [],
            issues: [],
            loadTime: 0
        };

        try {
            // تسجيل أحداث الصفحة
            await this.setupPageEventListeners(page, result);

            // تحميل الصفحة
            const startTime = Date.now();
            const response = await page.goto(url, { 
                waitUntil: 'networkidle',
                timeout: this.config.VISIT_TIMEOUT_MS 
            });
            result.loadTime = Date.now() - startTime;

            // الحصول على معلومات الاستجابة
            if (response) {
                result.status = response.status();
                result.statusCode = response.status();
                result.finalUrl = response.url();
            }

            // انتظار استقرار الصفحة
            await page.waitForTimeout(1000);

            // التقاط لقطة شاشة
            const screenshotPath = await this.takeScreenshot(page, url, depth);
            result.screenshots.push(screenshotPath);

            // حفظ HTML
            const htmlPath = await this.saveHTMLSnapshot(page, url, depth);
            result.htmlSnapshot = htmlPath;

            // جمع الروابط
            await this.collectLinks(page, result);

            // فحص الصور المكسورة
            await this.checkBrokenImages(page, result);

            // فحص النماذج
            await this.analyzeForms(page, result);

            // فحص إمكانية الوصول
            if (this.config.INCLUDE_A11Y) {
                await this.runAccessibilityChecks(page, result);
            }

            // تحليل الأداء
            await this.analyzePerformance(page, result);

            // تحديد المشاكل
            this.identifyIssues(result);

        } finally {
            await page.close();
        }

        return result;
    }

    async setupPageEventListeners(page, result) {
        // تسجيل رسائل وحدة التحكم
        page.on('console', msg => {
            result.consoleLogs.push({
                type: msg.type(),
                text: msg.text(),
                timestamp: new Date().toISOString(),
                location: msg.location()
            });
        });

        // تسجيل طلبات الشبكة
        page.on('request', request => {
            result.networkRequests.push({
                url: request.url(),
                method: request.method(),
                headers: request.headers(),
                timestamp: new Date().toISOString()
            });
        });

        // تسجيل الاستجابات الفاشلة
        page.on('response', response => {
            if (response.status() >= 400) {
                result.failedRequests.push({
                    url: response.url(),
                    status: response.status(),
                    statusText: response.statusText(),
                    timestamp: new Date().toISOString()
                });
            }
        });

        // تسجيل أخطاء الصفحة
        page.on('pageerror', error => {
            result.consoleLogs.push({
                type: 'error',
                text: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
        });
    }

    async takeScreenshot(page, url, depth) {
        const safePath = this.getSafePath(url, depth);
        const filename = `${safePath}.png`;
        const filepath = path.join(this.config.OUTPUT_DIR, 'screenshots', filename);
        
        await page.screenshot({ 
            path: filepath, 
            fullPage: true,
            type: 'png'
        });
        
        return filepath;
    }

    async saveHTMLSnapshot(page, url, depth) {
        const safePath = this.getSafePath(url, depth);
        const filename = `${safePath}.html`;
        const filepath = path.join(this.config.OUTPUT_DIR, 'html', filename);
        
        const html = await page.content();
        await fs.writeFile(filepath, html, 'utf8');
        
        return filepath;
    }

    async collectLinks(page, result) {
        const links = await page.evaluate(() => {
            const allLinks = Array.from(document.querySelectorAll('a[href]'));
            return allLinks.map(link => ({
                href: link.href,
                text: link.textContent?.trim(),
                title: link.title
            }));
        });

        for (const link of links) {
            try {
                const linkUrl = new URL(link.href);
                const baseUrl = new URL(result.url);
                
                if (linkUrl.hostname === baseUrl.hostname || 
                    this.config.ALLOWED_DOMAINS.includes(linkUrl.hostname)) {
                    result.internalLinks.push(link.href);
                } else {
                    result.externalLinks.push(link.href);
                }
            } catch {
                // تجاهل الروابط غير الصحيحة
            }
        }

        // إزالة التكرارات
        result.internalLinks = [...new Set(result.internalLinks)];
        result.externalLinks = [...new Set(result.externalLinks)];
    }

    async checkBrokenImages(page, result) {
        result.brokenImages = await page.evaluate(() => {
            const images = Array.from(document.querySelectorAll('img'));
            const broken = [];
            
            images.forEach((img, index) => {
                if (img.naturalWidth === 0 || img.complete === false) {
                    broken.push({
                        src: img.src,
                        alt: img.alt,
                        index,
                        naturalWidth: img.naturalWidth,
                        naturalHeight: img.naturalHeight
                    });
                }
            });
            
            return broken;
        });
    }

    async analyzeForms(page, result) {
        result.forms = await page.evaluate(() => {
            const forms = Array.from(document.querySelectorAll('form'));
            return forms.map((form, index) => ({
                index,
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
    }

    async runAccessibilityChecks(page, result) {
        try {
            // حقن axe-core
            await page.addScriptTag({
                url: 'https://unpkg.com/axe-core@4.8.2/axe.min.js'
            });

            const accessibilityResults = await page.evaluate(() => {
                return new Promise((resolve) => {
                    if (window.axe) {
                        window.axe.run(document, {
                            rules: {
                                // تفعيل قواعد محددة
                                'color-contrast': { enabled: true },
                                'keyboard-navigation': { enabled: true },
                                'aria-labels': { enabled: true }
                            }
                        }).then(resolve).catch(() => resolve({ violations: [] }));
                    } else {
                        resolve({ violations: [] });
                    }
                });
            });

            result.accessibilityViolations = accessibilityResults.violations || [];
        } catch (error) {
            console.warn('⚠️ فشل في فحص إمكانية الوصول:', error.message);
            result.accessibilityViolations = [];
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

        result.timings = metrics;

        // فحص مشاكل الأداء
        if (metrics.ttfb > 1500) {
            result.performanceIssues.push({
                type: 'slow_ttfb',
                value: metrics.ttfb,
                threshold: 1500,
                severity: 'medium'
            });
        }

        if (metrics.domContentLoaded > 3000) {
            result.performanceIssues.push({
                type: 'slow_dom_loading',
                value: metrics.domContentLoaded,
                threshold: 3000,
                severity: 'high'
            });
        }
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
                likelyCause: 'backend_error',
                suggestedFix: 'فحص سجلات الخادم وإصلاح الأخطاء'
            });
        } else if (result.statusCode >= 400) {
            issues.push({
                severity: 'high',
                type: 'client_error',
                description: `خطأ في العميل: ${result.statusCode}`,
                evidence: `HTTP ${result.statusCode}`,
                likelyCause: 'missing_resource',
                suggestedFix: 'فحص مسار الصفحة وإصلاح الرابط'
            });
        }

        // أخطاء وحدة التحكم
        const errorCount = result.consoleLogs.filter(log => log.type === 'error').length;
        if (errorCount > 0) {
            issues.push({
                severity: errorCount > 5 ? 'high' : 'medium',
                type: 'console_errors',
                description: `${errorCount} خطأ في وحدة التحكم`,
                evidence: `${errorCount} console errors`,
                likelyCause: 'js_runtime_exception',
                suggestedFix: 'فحص وإصلاح أخطاء JavaScript'
            });
        }

        // الصور المكسورة
        if (result.brokenImages.length > 0) {
            issues.push({
                severity: result.brokenImages.length > 3 ? 'high' : 'medium',
                type: 'broken_images',
                description: `${result.brokenImages.length} صورة مكسورة`,
                evidence: `${result.brokenImages.length} broken images`,
                likelyCause: 'broken_image_path',
                suggestedFix: 'فحص مسارات الصور وإصلاحها'
            });
        }

        // مشاكل إمكانية الوصول
        if (result.accessibilityViolations.length > 0) {
            const criticalA11y = result.accessibilityViolations.filter(v => v.impact === 'critical').length;
            issues.push({
                severity: criticalA11y > 0 ? 'high' : 'medium',
                type: 'accessibility_violations',
                description: `${result.accessibilityViolations.length} انتهاك لإمكانية الوصول`,
                evidence: `${result.accessibilityViolations.length} a11y violations`,
                likelyCause: 'accessibility_missing_labels',
                suggestedFix: 'إضافة تسميات alt للصور وتحسين إمكانية الوصول'
            });
        }

        // مشاكل الأداء
        result.performanceIssues.forEach(issue => {
            issues.push({
                severity: issue.severity,
                type: 'performance_issue',
                description: `مشكلة في الأداء: ${issue.type}`,
                evidence: `${issue.value}ms (عتبة: ${issue.threshold}ms)`,
                likelyCause: 'slow_backend',
                suggestedFix: 'تحسين استجابة الخادم أو تحسين الكود'
            });
        });

        result.issues = issues;
    }

    getSafePath(url, depth) {
        try {
            const urlObj = new URL(url);
            const pathname = urlObj.pathname.replace(/[^a-zA-Z0-9]/g, '_');
            const hostname = urlObj.hostname.replace(/[^a-zA-Z0-9]/g, '_');
            return `${hostname}_${depth}_${pathname}`.substring(0, 100);
        } catch {
            return `unknown_${depth}_${Date.now()}`;
        }
    }

    async generateReports() {
        console.log('📊 إنشاء التقارير...');

        // تقرير JSON
        const jsonReport = {
            summary: {
                totalPages: this.results.length,
                crawledAt: new Date().toISOString(),
                config: this.config
            },
            pages: this.results
        };

        const jsonPath = path.join(this.config.OUTPUT_DIR, 'reports', 'report.json');
        await fs.writeFile(jsonPath, JSON.stringify(jsonReport, null, 2), 'utf8');

        // تقرير CSV
        const csvData = this.results.map(result => ({
            url: result.url,
            status: result.statusCode || 'ERROR',
            final_url: result.finalUrl,
            load_time: result.loadTime,
            console_error_count: result.consoleLogs.filter(log => log.type === 'error').length,
            failed_requests_count: result.failedRequests.length,
            accessibility_violations_count: result.accessibilityViolations.length,
            primary_issue_severity: result.issues.length > 0 ? result.issues[0].severity : 'none',
            primary_issue_summary: result.issues.length > 0 ? result.issues[0].description : 'none',
            screenshot_path: result.screenshots[0] || 'none'
        }));

        const csvContent = this.generateCSV(csvData);
        const csvPath = path.join(this.config.OUTPUT_DIR, 'reports', 'report.csv');
        await fs.writeFile(csvPath, csvContent, 'utf8');

        // تقرير HTML
        await this.generateHTMLReport();

        console.log(`✅ تم إنشاء التقارير في: ${this.config.OUTPUT_DIR}/reports`);
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

    async generateHTMLReport() {
        const criticalIssues = this.results.flatMap(r => r.issues.filter(i => i.severity === 'critical'));
        const highIssues = this.results.flatMap(r => r.issues.filter(i => i.severity === 'high'));
        const mediumIssues = this.results.flatMap(r => r.issues.filter(i => i.severity === 'medium'));

        const html = `
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير تحليل الموقع</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #007acc; }
        .summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .severity-critical { color: #dc3545; font-weight: bold; }
        .severity-high { color: #fd7e14; font-weight: bold; }
        .severity-medium { color: #ffc107; font-weight: bold; }
        .severity-low { color: #28a745; font-weight: bold; }
        .issue-card { background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin: 10px 0; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #007acc; color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; }
        .stat-label { font-size: 0.9em; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 تقرير تحليل الموقع الشامل</h1>
            <p>تم إنشاؤه في: ${new Date().toLocaleString('ar-SA')}</p>
        </div>

        <div class="summary">
            <h2>📊 ملخص تنفيذي</h2>
            <p>تم تحليل <strong>${this.results.length}</strong> صفحة من الموقع ${this.config.START_URL}.</p>
            <p>تم اكتشاف <span class="severity-critical">${criticalIssues.length}</span> مشكلة حرجة، 
               <span class="severity-high">${highIssues.length}</span> مشكلة عالية الأولوية، 
               و <span class="severity-medium">${mediumIssues.length}</span> مشكلة متوسطة.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">${this.results.length}</div>
                <div class="stat-label">إجمالي الصفحات</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${criticalIssues.length}</div>
                <div class="stat-label">مشاكل حرجة</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${highIssues.length}</div>
                <div class="stat-label">مشاكل عالية</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${this.results.reduce((sum, r) => sum + r.consoleLogs.filter(l => l.type === 'error').length, 0)}</div>
                <div class="stat-label">أخطاء JavaScript</div>
            </div>
        </div>

        <h2>🚨 المشاكل الحرجة (${criticalIssues.length})</h2>
        ${criticalIssues.map(issue => `
            <div class="issue-card">
                <h3 class="severity-critical">${issue.description}</h3>
                <p><strong>الأدلة:</strong> ${issue.evidence}</p>
                <p><strong>السبب المحتمل:</strong> ${issue.likelyCause}</p>
                <p><strong>الحل المقترح:</strong> ${issue.suggestedFix}</p>
            </div>
        `).join('')}

        <h2>⚠️ المشاكل عالية الأولوية (${highIssues.length})</h2>
        ${highIssues.map(issue => `
            <div class="issue-card">
                <h3 class="severity-high">${issue.description}</h3>
                <p><strong>الأدلة:</strong> ${issue.evidence}</p>
                <p><strong>السبب المحتمل:</strong> ${issue.likelyCause}</p>
                <p><strong>الحل المقترح:</strong> ${issue.suggestedFix}</p>
            </div>
        `).join('')}

        <h2>📋 تفاصيل الصفحات</h2>
        ${this.results.map(result => `
            <div class="issue-card">
                <h3>${result.url}</h3>
                <p><strong>الحالة:</strong> ${result.statusCode || 'ERROR'} | 
                   <strong>وقت التحميل:</strong> ${result.loadTime}ms | 
                   <strong>المشاكل:</strong> ${result.issues.length}</p>
                ${result.issues.length > 0 ? `
                    <ul>
                        ${result.issues.map(issue => `<li class="severity-${issue.severity}">${issue.description}</li>`).join('')}
                    </ul>
                ` : '<p style="color: #28a745;">✅ لا توجد مشاكل</p>'}
            </div>
        `).join('')}
    </div>
</body>
</html>`;

        const htmlPath = path.join(this.config.OUTPUT_DIR, 'reports', 'report.html');
        await fs.writeFile(htmlPath, html, 'utf8');
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

// تشغيل المحلل
async function main() {
    const config = {
        START_URL: "http://192.168.15.29:8000",
        MAX_DEPTH: 2,
        ALLOWED_DOMAINS: ["192.168.15.29:8000"],
        RATE_LIMIT_MS: 300,
        OUTPUT_DIR: "./mcp_output",
        VISIT_TIMEOUT_MS: 30000,
        VIEWPORT: { width: 1366, height: 768 },
        INCLUDE_A11Y: true,
        HEADLESS: true,
        LOG_LEVEL: 'INFO'
    };

    const analyzer = new WebCrawlerAnalyzer(config);
    
    try {
        await analyzer.init();
        await analyzer.crawl();
        console.log('🎉 تم الانتهاء من التحليل بنجاح!');
        console.log(`📁 يمكنك العثور على التقارير في: ${config.OUTPUT_DIR}/reports`);
    } catch (error) {
        console.error('❌ خطأ في التحليل:', error);
    } finally {
        await analyzer.close();
    }
}

// تشغيل المحلل إذا تم استدعاء الملف مباشرة
if (import.meta.url === `file://${process.argv[1]}`) {
    main().catch(console.error);
}

export default WebCrawlerAnalyzer;










