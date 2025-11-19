#!/usr/bin/env node

import WebCrawlerAnalyzer from './web_crawler_analyzer.js';
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// دالة لقراءة ملف التكوين
async function loadConfig(configPath = './crawler_config.json') {
    try {
        const configData = await fs.readFile(configPath, 'utf8');
        return JSON.parse(configData);
    } catch (error) {
        console.warn('⚠️ لم يتم العثور على ملف التكوين، سيتم استخدام الإعدادات الافتراضية');
        return {
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
    }
}

// دالة لعرض المساعدة
function showHelp() {
    console.log(`
🔍 محلل الويب الشامل - دليل الاستخدام

الاستخدام:
  node run_crawler.js [خيارات]

الخيارات:
  --config, -c     مسار ملف التكوين (افتراضي: ./crawler_config.json)
  --url, -u        رابط البداية (يتجاوز التكوين)
  --depth, -d      عمق الزحف (افتراضي: 2)
  --output, -o     مجلد الإخراج (افتراضي: ./mcp_output)
  --headless, -h   تشغيل بدون واجهة (افتراضي: true)
  --verbose, -v    عرض تفاصيل أكثر
  --help           عرض هذه المساعدة

أمثلة:
  node run_crawler.js
  node run_crawler.js --url http://localhost:8000 --depth 3
  node run_crawler.js --config my_config.json --output ./results
  node run_crawler.js --headless false --verbose

ملفات الإخراج:
  📊 reports/report.html     - تقرير HTML شامل
  📄 reports/report.json     - بيانات JSON كاملة
  📋 reports/report.csv      - بيانات CSV للتحليل
  🖼️  screenshots/           - لقطات الشاشة
  📄 html/                   - نسخ HTML
  📝 raw_logs/               - سجلات مفصلة

المميزات:
  ✅ زحف ذكي للصفحات
  ✅ تحليل الأداء
  ✅ فحص إمكانية الوصول
  ✅ اكتشاف المشاكل
  ✅ تقارير تفصيلية
  ✅ دعم المصادقة
  ✅ فحص النماذج
  ✅ تحليل الصور المكسورة
`);
}

// دالة لمعالجة معاملات سطر الأوامر
function parseArgs() {
    const args = process.argv.slice(2);
    const options = {};
    
    for (let i = 0; i < args.length; i++) {
        const arg = args[i];
        
        switch (arg) {
            case '--help':
            case '-h':
                if (arg === '--help') {
                    showHelp();
                    process.exit(0);
                } else {
                    options.headless = true;
                }
                break;
            case '--config':
            case '-c':
                options.config = args[++i];
                break;
            case '--url':
            case '-u':
                options.url = args[++i];
                break;
            case '--depth':
            case '-d':
                options.depth = parseInt(args[++i]);
                break;
            case '--output':
            case '-o':
                options.output = args[++i];
                break;
            case '--headless':
                options.headless = args[++i] === 'true';
                break;
            case '--verbose':
            case '-v':
                options.verbose = true;
                break;
            default:
                if (arg.startsWith('--')) {
                    console.warn(`⚠️ خيار غير معروف: ${arg}`);
                }
        }
    }
    
    return options;
}

// دالة رئيسية
async function main() {
    console.log('🚀 بدء تشغيل محلل الويب الشامل...\n');
    
    const options = parseArgs();
    
    // تحميل التكوين
    const config = await loadConfig(options.config);
    
    // تطبيق الخيارات من سطر الأوامر
    if (options.url) config.START_URL = options.url;
    if (options.depth) config.MAX_DEPTH = options.depth;
    if (options.output) config.OUTPUT_DIR = options.output;
    if (options.headless !== undefined) config.HEADLESS = options.headless;
    if (options.verbose) config.LOG_LEVEL = 'DEBUG';
    
    // عرض معلومات التشغيل
    console.log('📋 إعدادات التشغيل:');
    console.log(`   🌐 الرابط: ${config.START_URL}`);
    console.log(`   📏 العمق: ${config.MAX_DEPTH}`);
    console.log(`   📁 الإخراج: ${config.OUTPUT_DIR}`);
    console.log(`   👻 بدون واجهة: ${config.HEADLESS ? 'نعم' : 'لا'}`);
    console.log(`   🔍 إمكانية الوصول: ${config.INCLUDE_A11Y ? 'مفعل' : 'معطل'}`);
    console.log(`   ⏱️  المهلة الزمنية: ${config.VISIT_TIMEOUT_MS}ms`);
    console.log(`   🚦 التوقف: ${config.RATE_LIMIT_MS}ms\n`);
    
    // إنشاء المحلل
    const analyzer = new WebCrawlerAnalyzer(config);
    
    try {
        console.log('🔧 تهيئة المحلل...');
        await analyzer.init();
        
        console.log('🕷️ بدء الزحف...');
        const startTime = Date.now();
        
        await analyzer.crawl();
        
        const endTime = Date.now();
        const duration = ((endTime - startTime) / 1000).toFixed(2);
        
        console.log(`\n🎉 تم الانتهاء من التحليل بنجاح!`);
        console.log(`⏱️  الوقت المستغرق: ${duration} ثانية`);
        console.log(`📊 إجمالي الصفحات: ${analyzer.results.length}`);
        
        // عرض ملخص سريع
        const criticalIssues = analyzer.results.flatMap(r => r.issues.filter(i => i.severity === 'critical'));
        const highIssues = analyzer.results.flatMap(r => r.issues.filter(i => i.severity === 'high'));
        const totalErrors = analyzer.results.reduce((sum, r) => sum + r.consoleLogs.filter(l => l.type === 'error').length, 0);
        
        console.log(`\n📈 ملخص المشاكل:`);
        console.log(`   🚨 حرجة: ${criticalIssues.length}`);
        console.log(`   ⚠️  عالية: ${highIssues.length}`);
        console.log(`   💥 أخطاء JS: ${totalErrors}`);
        
        console.log(`\n📁 الملفات المُنشأة:`);
        console.log(`   📊 ${config.OUTPUT_DIR}/reports/report.html`);
        console.log(`   📄 ${config.OUTPUT_DIR}/reports/report.json`);
        console.log(`   📋 ${config.OUTPUT_DIR}/reports/report.csv`);
        console.log(`   🖼️  ${config.OUTPUT_DIR}/screenshots/`);
        console.log(`   📄 ${config.OUTPUT_DIR}/html/`);
        
        // عرض المشاكل الحرجة إذا وجدت
        if (criticalIssues.length > 0) {
            console.log(`\n🚨 المشاكل الحرجة التي تحتاج اهتمام فوري:`);
            criticalIssues.slice(0, 5).forEach((issue, index) => {
                console.log(`   ${index + 1}. ${issue.description}`);
                console.log(`      💡 ${issue.suggestedFix}`);
            });
        }
        
    } catch (error) {
        console.error('❌ خطأ في التحليل:', error.message);
        if (options.verbose) {
            console.error(error.stack);
        }
        process.exit(1);
    } finally {
        await analyzer.close();
    }
}

// تشغيل البرنامج
if (import.meta.url === `file://${process.argv[1]}`) {
    main().catch(error => {
        console.error('❌ خطأ عام:', error);
        process.exit(1);
    });
}










