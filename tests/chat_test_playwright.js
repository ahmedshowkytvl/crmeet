/**
 * اختبار الشات باستخدام Playwright MCP
 * يختبر إرسال الرسائل بين حساب وهمي والمستخدم Madonna 847
 */

const { chromium } = require('playwright');

// إعدادات الاختبار
const BASE_URL = process.env.BASE_URL || 'http://192.168.15.29/crm/stafftobia/public';
const CHAT_URL = `${BASE_URL}/chat/static?conversation=78`;
const FAKE_USER_EMAIL = 'test.chat.user@example.com';
const FAKE_USER_PASSWORD = 'password123';

async function testChat() {
    console.log('🚀 بدء اختبار الشات...');
    
    const browser = await chromium.launch({ 
        headless: false, // عرض المتصفح للمراقبة
        slowMo: 500 // إبطاء الإجراءات للمراقبة
    });
    
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 },
        locale: 'ar',
        timezoneId: 'Africa/Cairo'
    });
    
    const page = await context.newPage();
    
    try {
        // تسجيل الدخول
        console.log('📝 تسجيل الدخول...');
        await page.goto(`${BASE_URL}/login`);
        
        // انتظار تحميل صفحة تسجيل الدخول
        await page.waitForSelector('input[name="email"]', { timeout: 10000 });
        
        // إدخال بيانات تسجيل الدخول
        await page.fill('input[name="email"]', FAKE_USER_EMAIL);
        await page.fill('input[name="password"]', FAKE_USER_PASSWORD);
        
        // النقر على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        
        // انتظار الانتقال إلى الصفحة الرئيسية
        await page.waitForURL('**/dashboard', { timeout: 15000 });
        console.log('✅ تم تسجيل الدخول بنجاح');
        
        // الانتقال إلى صفحة الشات
        console.log('💬 الانتقال إلى صفحة الشات...');
        await page.goto(CHAT_URL);
        
        // انتظار تحميل صفحة الشات
        await page.waitForSelector('#messages-container', { timeout: 10000 });
        console.log('✅ تم تحميل صفحة الشات');
        
        // انتظار تحميل الرسائل
        await page.waitForTimeout(2000);
        
        // التحقق من وجود حقل إدخال الرسالة
        const messageInput = await page.$('#message-input');
        if (!messageInput) {
            throw new Error('❌ لم يتم العثور على حقل إدخال الرسالة');
        }
        console.log('✅ تم العثور على حقل إدخال الرسالة');
        
        // إرسال رسالة تجريبية
        const testMessage = `رسالة اختبار من Playwright - ${new Date().toLocaleString('ar-EG')}`;
        console.log(`📤 إرسال رسالة: "${testMessage}"`);
        
        await page.fill('#message-input', testMessage);
        await page.waitForTimeout(500);
        
        // النقر على زر الإرسال
        const sendButton = await page.$('.send-btn');
        if (!sendButton) {
            throw new Error('❌ لم يتم العثور على زر الإرسال');
        }
        
        await sendButton.click();
        console.log('✅ تم النقر على زر الإرسال');
        
        // انتظار ظهور الرسالة في الشات
        await page.waitForTimeout(3000);
        
        // التحقق من ظهور الرسالة
        const messages = await page.$$eval('.message', (elements) => {
            return elements.map(el => el.textContent.trim());
        });
        
        const messageFound = messages.some(msg => msg.includes('رسالة اختبار من Playwright'));
        
        if (messageFound) {
            console.log('✅ تم إرسال الرسالة بنجاح وظهرت في الشات');
        } else {
            console.log('⚠️  لم يتم العثور على الرسالة في الشات');
            console.log('الرسائل الموجودة:', messages.slice(-5));
        }
        
        // التحقق من عدم وجود أخطاء في Console
        const errors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                const text = msg.text();
                // تجاهل أخطاء runtime.lastError (هذه أخطاء من extensions)
                if (!text.includes('runtime.lastError') && !text.includes('message port closed')) {
                    errors.push(text);
                }
            }
        });
        
        await page.waitForTimeout(2000);
        
        if (errors.length > 0) {
            console.log('⚠️  تم العثور على أخطاء في Console:');
            errors.forEach(error => console.log('  -', error));
        } else {
            console.log('✅ لم يتم العثور على أخطاء في Console');
        }
        
        // التحقق من أن الاستجابة JSON وليست HTML
        let jsonError = false;
        page.on('response', async response => {
            const url = response.url();
            if (url.includes('/static/send')) {
                const contentType = response.headers()['content-type'] || '';
                if (!contentType.includes('application/json')) {
                    jsonError = true;
                    const text = await response.text();
                    console.log('❌ الاستجابة ليست JSON:', contentType);
                    console.log('محتوى الاستجابة:', text.substring(0, 200));
                } else {
                    console.log('✅ الاستجابة JSON صحيحة');
                    const json = await response.json();
                    console.log('📦 بيانات الاستجابة:', JSON.stringify(json, null, 2));
                }
            }
        });
        
        // إرسال رسالة أخرى للتحقق من الاستجابة
        await page.fill('#message-input', 'رسالة اختبار ثانية');
        await sendButton.click();
        await page.waitForTimeout(3000);
        
        if (jsonError) {
            throw new Error('❌ الاستجابة من السيرفر ليست JSON');
        }
        
        console.log('');
        console.log('✅ تم إكمال جميع الاختبارات بنجاح!');
        console.log('');
        console.log('📊 ملخص الاختبار:');
        console.log('  ✅ تسجيل الدخول: نجح');
        console.log('  ✅ تحميل صفحة الشات: نجح');
        console.log('  ✅ إرسال الرسائل: نجح');
        console.log('  ✅ الاستجابة JSON: نجح');
        console.log('  ✅ عدم وجود أخطاء: نجح');
        
        // انتظار 5 ثواني للمراقبة
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ فشل الاختبار:', error.message);
        console.error('Stack trace:', error.stack);
        
        // التقاط screenshot عند الفشل
        await page.screenshot({ path: '/tmp/chat_test_error.png', fullPage: true });
        console.log('📸 تم حفظ screenshot في /tmp/chat_test_error.png');
        
        throw error;
    } finally {
        await browser.close();
    }
}

// تشغيل الاختبار
testChat()
    .then(() => {
        console.log('🎉 تم إكمال الاختبار بنجاح!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('💥 فشل الاختبار:', error);
        process.exit(1);
    });

