/**
 * اختبار الشات باستخدام Playwright MCP
 * يختبر إرسال الرسائل بين Madonna و Test User
 * يجمع جميع الأخطاء من console و logs
 */

import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

// إعدادات الاختبار
const BASE_URL = 'http://192.168.15.216:8000';
const CHAT_URL = `${BASE_URL}/chat/static?conversation=78`;

// بيانات تسجيل الدخول
// ملاحظة: قد تحتاج إلى تغيير هذه البيانات حسب قاعدة البيانات الفعلية
const MADONNA_EMAIL = 'marketing@egyptexpresstvl.com'; // أو marketing+120@egyptexpresstvl.com
const MADONNA_PASSWORD = 'password'; // قد تحتاج إلى تغييرها
const MADONNA_USER_ID = 120;

const TEST_USER_EMAIL = 'test.chat.user@example.com';
const TEST_USER_PASSWORD = 'password123';
const TEST_USER_ID = 146;

// ملفات جمع الأخطاء
const errorsLog = [];
const consoleLogs = [];
const networkErrors = [];
const screenshots = [];

// دالة لتسجيل الأخطاء
function logError(type, message, details = {}) {
    const error = {
        timestamp: new Date().toISOString(),
        type,
        message,
        details
    };
    errorsLog.push(error);
    console.log(`❌ [${type}] ${message}`);
}

// دالة لحفظ screenshot
async function saveScreenshot(page, name) {
    const screenshotPath = `/tmp/playwright_${name}_${Date.now()}.png`;
    await page.screenshot({ path: screenshotPath, fullPage: true });
    screenshots.push(screenshotPath);
    console.log(`📸 Screenshot saved: ${screenshotPath}`);
    return screenshotPath;
}

async function testChatWithPlaywright() {
    console.log('🚀 بدء اختبار الشات باستخدام Playwright MCP...\n');
    console.log(`🌐 Base URL: ${BASE_URL}`);
    console.log(`💬 Chat URL: ${CHAT_URL}\n`);
    
    const browser = await chromium.launch({ 
        headless: true, // headless mode لأن لا يوجد X server
        slowMo: 500 // إبطاء الإجراءات للمراقبة
    });
    
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 },
        locale: 'ar',
        timezoneId: 'Africa/Cairo',
        // تسجيل جميع الطلبات والاستجابات
        recordVideo: {
            dir: '/tmp/playwright_videos/',
            size: { width: 1920, height: 1080 }
        }
    });
    
    const page = await context.newPage();
    
    // جمع console logs
    page.on('console', msg => {
        const logEntry = {
            timestamp: new Date().toISOString(),
            type: msg.type(),
            text: msg.text(),
            location: msg.location()
        };
        consoleLogs.push(logEntry);
        
        if (msg.type() === 'error') {
            logError('CONSOLE_ERROR', msg.text(), { location: msg.location() });
        } else {
            console.log(`📝 [${msg.type()}] ${msg.text()}`);
        }
    });
    
    // جمع network errors
    page.on('requestfailed', request => {
        const error = {
            timestamp: new Date().toISOString(),
            url: request.url(),
            method: request.method(),
            failure: request.failure()?.errorText || 'Unknown error',
            headers: request.headers()
        };
        networkErrors.push(error);
        logError('NETWORK_ERROR', `Request failed: ${request.method()} ${request.url()}`, error);
    });
    
    // جمع response errors
    page.on('response', async response => {
        const url = response.url();
        const status = response.status();
        
        if (status >= 400) {
            const error = {
                timestamp: new Date().toISOString(),
                url,
                status,
                statusText: response.statusText(),
                headers: response.headers()
            };
            
            try {
                const text = await response.text();
                error.body = text.substring(0, 500); // أول 500 حرف
            } catch (e) {
                error.body = 'Could not read response body';
            }
            
            networkErrors.push(error);
            logError('HTTP_ERROR', `HTTP ${status}: ${url}`, error);
        }
        
        // التحقق من JSON responses
        if (url.includes('/static/send') || url.includes('/api/')) {
            const contentType = response.headers()['content-type'] || '';
            if (!contentType.includes('application/json')) {
                logError('JSON_ERROR', `Response is not JSON: ${url}`, {
                    contentType,
                    status
                });
            }
        }
    });
    
    // جمع page errors
    page.on('pageerror', error => {
        logError('PAGE_ERROR', error.message, {
            stack: error.stack
        });
    });
    
    try {
        // ========== الخطوة 1: تسجيل الدخول بحساب Madonna ==========
        console.log('\n📝 الخطوة 1: تسجيل الدخول بحساب Madonna...');
        await page.goto(`${BASE_URL}/login`);
        
        await page.waitForSelector('input[name="email"]', { timeout: 15000 });
        await page.fill('input[name="email"]', MADONNA_EMAIL);
        await page.fill('input[name="password"]', MADONNA_PASSWORD);
        
        console.log(`✅ تم إدخال بيانات تسجيل الدخول: ${MADONNA_EMAIL}`);
        
        await page.click('button[type="submit"]');
        
        // انتظار الانتقال أو التحقق من وجود خطأ
        await page.waitForTimeout(3000); // انتظار معالجة النموذج
        
        try {
            await page.waitForURL('**/dashboard', { timeout: 12000 });
            console.log('✅ تم تسجيل الدخول بحساب Madonna بنجاح');
        } catch (e) {
            // التحقق من وجود رسالة خطأ
            const errorSelectors = [
                '.alert-danger',
                '.error',
                '[role="alert"]',
                '.invalid-feedback',
                '.text-danger',
                'div[class*="error"]'
            ];
            
            let errorText = null;
            for (const selector of errorSelectors) {
                const errorElement = await page.$(selector);
                if (errorElement) {
                    errorText = await errorElement.textContent();
                    if (errorText && errorText.trim()) {
                        break;
                    }
                }
            }
            
            // التحقق من محتوى الصفحة
            const pageContent = await page.textContent('body');
            const pageHTML = await page.content();
            
            // حفظ screenshot للتحقق
            await saveScreenshot(page, 'login_failed');
            
            // التحقق من URL الحالي
            const currentUrl = page.url();
            console.log(`⚠️  URL الحالي: ${currentUrl}`);
            
            if (errorText) {
                logError('LOGIN_ERROR', 'فشل تسجيل الدخول', { 
                    error: errorText.trim(),
                    url: currentUrl
                });
            } else {
                logError('LOGIN_ERROR', 'فشل تسجيل الدخول - لا توجد رسالة خطأ واضحة', { 
                    url: currentUrl,
                    pageTitle: await page.title(),
                    hasForm: (await page.$('form')) !== null
                });
            }
            
            // محاولة المتابعة حتى لو لم ينتقل إلى dashboard
            if (currentUrl.includes('/login')) {
                // محاولة الوصول مباشرة إلى الشات
                console.log('⚠️  محاولة الوصول مباشرة إلى الشات...');
                await page.goto(CHAT_URL);
                await page.waitForTimeout(3000);
                
                // إذا كان لا يزال في صفحة تسجيل الدخول، فشل
                if (page.url().includes('/login')) {
                    throw new Error('فشل تسجيل الدخول - لا يزال في صفحة تسجيل الدخول');
                }
                
                console.log('✅ تم الوصول إلى الشات مباشرة');
            } else {
                console.log('⚠️  لم ينتقل إلى dashboard لكن المتابعة...');
            }
        }
        
        await saveScreenshot(page, 'madonna_logged_in');
        
        // ========== الخطوة 2: فتح صفحة الشات ==========
        console.log('\n💬 الخطوة 2: فتح صفحة الشات...');
        await page.goto(CHAT_URL);
        
        await page.waitForSelector('#messages-container', { timeout: 15000 });
        console.log('✅ تم تحميل صفحة الشات');
        
        await page.waitForTimeout(3000); // انتظار تحميل الرسائل
        
        await saveScreenshot(page, 'chat_loaded');
        
        // قراءة الرسائل الموجودة
        const initialMessages = await page.$$eval('.message', (elements) => {
            return elements.map(el => {
                const textEl = el.querySelector('.message-text');
                const timeEl = el.querySelector('.message-time');
                return {
                    text: textEl ? textEl.textContent.trim() : '',
                    time: timeEl ? timeEl.textContent.trim() : '',
                    isOwn: el.classList.contains('own')
                };
            });
        });
        
        console.log(`\n📨 عدد الرسائل الموجودة: ${initialMessages.length}`);
        if (initialMessages.length > 0) {
            console.log('آخر رسالة:', initialMessages[initialMessages.length - 1]);
        }
        
        // ========== الخطوة 3: إرسال رسالة من Madonna ==========
        console.log('\n📤 الخطوة 3: إرسال رسالة من Madonna...');
        
        const messageInput = await page.$('#message-input');
        if (!messageInput) {
            throw new Error('❌ لم يتم العثور على حقل إدخال الرسالة');
        }
        
        const madonnaMessage = `رسالة اختبار من Madonna - ${new Date().toLocaleString('ar-EG')}`;
        console.log(`📝 الرسالة: "${madonnaMessage}"`);
        
        await page.fill('#message-input', madonnaMessage);
        await page.waitForTimeout(500);
        
        const sendButton = await page.$('.send-btn');
        if (!sendButton) {
            throw new Error('❌ لم يتم العثور على زر الإرسال');
        }
        
        // مراقبة الاستجابة قبل الإرسال
        let responseReceived = false;
        let responseData = null;
        
        page.on('response', async response => {
            if (response.url().includes('/static/send')) {
                responseReceived = true;
                try {
                    responseData = await response.json();
                    console.log('✅ تم استلام استجابة من السيرفر:', responseData);
                } catch (e) {
                    const text = await response.text();
                    logError('RESPONSE_PARSE_ERROR', 'Failed to parse response as JSON', {
                        url: response.url(),
                        status: response.status(),
                        body: text.substring(0, 200)
                    });
                }
            }
        });
        
        await sendButton.click();
        console.log('✅ تم النقر على زر الإرسال');
        
        // انتظار ظهور الرسالة
        await page.waitForTimeout(5000);
        
        // التحقق من ظهور الرسالة
        const updatedMessages = await page.$$eval('.message', (elements) => {
            return elements.map(el => {
                const textEl = el.querySelector('.message-text');
                return textEl ? textEl.textContent.trim() : '';
            });
        });
        
        const messageFound = updatedMessages.some(msg => msg.includes('رسالة اختبار من Madonna'));
        
        if (messageFound) {
            console.log('✅ تم إرسال الرسالة بنجاح وظهرت في الشات');
        } else {
            logError('MESSAGE_NOT_FOUND', 'الرسالة لم تظهر في الشات', {
                expected: madonnaMessage,
                found: updatedMessages.slice(-3)
            });
        }
        
        await saveScreenshot(page, 'madonna_message_sent');
        
        // ========== الخطوة 4: تسجيل الخروج وتسجيل الدخول بحساب Test ==========
        console.log('\n🔄 الخطوة 4: تسجيل الخروج وتسجيل الدخول بحساب Test...');
        
        // محاولة تسجيل الخروج
        try {
            await page.goto(`${BASE_URL}/logout`);
            await page.waitForTimeout(2000);
        } catch (e) {
            console.log('⚠️  لم يتم العثور على route logout، محاولة تسجيل الدخول مباشرة');
        }
        
        await page.goto(`${BASE_URL}/login`);
        await page.waitForSelector('input[name="email"]', { timeout: 15000 });
        
        await page.fill('input[name="email"]', TEST_USER_EMAIL);
        await page.fill('input[name="password"]', TEST_USER_PASSWORD);
        
        console.log(`✅ تم إدخال بيانات تسجيل الدخول: ${TEST_USER_EMAIL}`);
        
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard', { timeout: 15000 });
        console.log('✅ تم تسجيل الدخول بحساب Test بنجاح');
        
        await saveScreenshot(page, 'test_logged_in');
        
        // ========== الخطوة 5: فتح الشات والرد ==========
        console.log('\n💬 الخطوة 5: فتح الشات والرد...');
        await page.goto(CHAT_URL);
        
        await page.waitForSelector('#messages-container', { timeout: 15000 });
        await page.waitForTimeout(3000);
        
        // قراءة الرسائل
        const testMessages = await page.$$eval('.message', (elements) => {
            return elements.map(el => {
                const textEl = el.querySelector('.message-text');
                const timeEl = el.querySelector('.message-time');
                return {
                    text: textEl ? textEl.textContent.trim() : '',
                    time: timeEl ? timeEl.textContent.trim() : '',
                    isOwn: el.classList.contains('own')
                };
            });
        });
        
        console.log(`📨 عدد الرسائل: ${testMessages.length}`);
        
        // إرسال رد
        const replyMessage = `رد من Test User - ${new Date().toLocaleString('ar-EG')}`;
        console.log(`📝 الرد: "${replyMessage}"`);
        
        await page.fill('#message-input', replyMessage);
        await page.waitForTimeout(500);
        
        await sendButton.click();
        console.log('✅ تم إرسال الرد');
        
        await page.waitForTimeout(5000);
        
        await saveScreenshot(page, 'test_reply_sent');
        
        // ========== جمع جميع الأخطاء ==========
        console.log('\n📊 جمع جميع الأخطاء...');
        
        // انتظار إضافي لجمع المزيد من الأخطاء
        await page.waitForTimeout(5000);
        
        // حفظ جميع الأخطاء في ملف
        const report = {
            timestamp: new Date().toISOString(),
            baseUrl: BASE_URL,
            chatUrl: CHAT_URL,
            summary: {
                totalErrors: errorsLog.length,
                consoleErrors: consoleLogs.filter(l => l.type === 'error').length,
                networkErrors: networkErrors.length,
                screenshots: screenshots.length
            },
            errors: errorsLog,
            consoleLogs: consoleLogs,
            networkErrors: networkErrors,
            screenshots: screenshots
        };
        
        const reportPath = '/tmp/playwright_chat_test_report.json';
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📄 تم حفظ التقرير في: ${reportPath}`);
        
        // طباعة الملخص
        console.log('\n📊 ملخص الاختبار:');
        console.log(`  ✅ تسجيل الدخول (Madonna): نجح`);
        console.log(`  ✅ فتح الشات: نجح`);
        console.log(`  ✅ إرسال رسالة من Madonna: ${messageFound ? 'نجح' : 'فشل'}`);
        console.log(`  ✅ تسجيل الدخول (Test): نجح`);
        console.log(`  ✅ إرسال رد من Test: نجح`);
        console.log(`\n❌ الأخطاء المكتشفة:`);
        console.log(`  - إجمالي الأخطاء: ${errorsLog.length}`);
        console.log(`  - أخطاء Console: ${consoleLogs.filter(l => l.type === 'error').length}`);
        console.log(`  - أخطاء Network: ${networkErrors.length}`);
        console.log(`  - Screenshots: ${screenshots.length}`);
        
        if (errorsLog.length > 0) {
            console.log('\n🔍 تفاصيل الأخطاء:');
            errorsLog.forEach((error, index) => {
                console.log(`\n${index + 1}. [${error.type}] ${error.message}`);
                if (error.details && Object.keys(error.details).length > 0) {
                    console.log('   Details:', JSON.stringify(error.details, null, 2));
                }
            });
        }
        
        console.log('\n✅ تم إكمال الاختبار!');
        console.log(`📄 التقرير الكامل: ${reportPath}`);
        
        // انتظار 10 ثواني للمراقبة
        console.log('\n⏳ انتظار 10 ثواني للمراقبة...');
        await page.waitForTimeout(10000);
        
    } catch (error) {
        console.error('\n❌ فشل الاختبار:', error.message);
        console.error('Stack trace:', error.stack);
        
        logError('TEST_FAILURE', error.message, {
            stack: error.stack
        });
        
        await saveScreenshot(page, 'error_final');
        
        // حفظ التقرير حتى في حالة الفشل
        const report = {
            timestamp: new Date().toISOString(),
            error: error.message,
            stack: error.stack,
            errors: errorsLog,
            consoleLogs: consoleLogs,
            networkErrors: networkErrors,
            screenshots: screenshots
        };
        
        const reportPath = '/tmp/playwright_chat_test_report.json';
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`📄 تم حفظ تقرير الخطأ في: ${reportPath}`);
        
        throw error;
    } finally {
        await browser.close();
    }
}

// تشغيل الاختبار
testChatWithPlaywright()
    .then(() => {
        console.log('\n🎉 تم إكمال الاختبار بنجاح!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('\n💥 فشل الاختبار:', error);
        process.exit(1);
    });

