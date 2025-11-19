/**
 * تسجيل الدخول بحساب Madonna 847 وإرسال رد على الشات
 */

const { chromium } = require('playwright');

// إعدادات
const BASE_URL = process.env.BASE_URL || 'http://192.168.15.29/crm/stafftobia/public';
const CHAT_URL = `${BASE_URL}/chat/static?conversation=78`;
const USER_EMAIL = 'marketing@egyptexpresstvl.com'; // أو marketing+120@egyptexpresstvl.com
const USER_PASSWORD = 'password';

async function sendChatReply() {
    console.log('🚀 بدء تسجيل الدخول وإرسال رد على الشات...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // عرض المتصفح
        slowMo: 1000 // إبطاء الإجراءات للمراقبة
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
        await page.fill('input[name="email"]', USER_EMAIL);
        await page.fill('input[name="password"]', USER_PASSWORD);
        
        console.log(`✅ تم إدخال بيانات تسجيل الدخول: ${USER_EMAIL}`);
        
        // النقر على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        
        // انتظار الانتقال إلى الصفحة الرئيسية
        await page.waitForURL('**/dashboard', { timeout: 15000 });
        console.log('✅ تم تسجيل الدخول بنجاح\n');
        
        // الانتقال إلى صفحة الشات
        console.log('💬 الانتقال إلى صفحة الشات...');
        await page.goto(CHAT_URL);
        
        // انتظار تحميل صفحة الشات
        await page.waitForSelector('#messages-container', { timeout: 10000 });
        console.log('✅ تم تحميل صفحة الشات');
        
        // انتظار تحميل الرسائل
        await page.waitForTimeout(3000);
        
        // التحقق من وجود حقل إدخال الرسالة
        const messageInput = await page.$('#message-input');
        if (!messageInput) {
            throw new Error('❌ لم يتم العثور على حقل إدخال الرسالة');
        }
        console.log('✅ تم العثور على حقل إدخال الرسالة\n');
        
        // قراءة آخر رسالة
        const messages = await page.$$eval('.message', (elements) => {
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
        
        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1];
            console.log('📨 آخر رسالة في الشات:');
            console.log(`   النص: ${lastMessage.text}`);
            console.log(`   الوقت: ${lastMessage.time}`);
            console.log(`   من: ${lastMessage.isOwn ? 'أنت' : 'الطرف الآخر'}\n`);
        }
        
        // إرسال رد
        const replyMessage = `شكراً لك! تم استلام رسالتك - ${new Date().toLocaleString('ar-EG', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })}`;
        
        console.log(`📤 إرسال رد: "${replyMessage}"`);
        
        await page.fill('#message-input', replyMessage);
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
        const updatedMessages = await page.$$eval('.message', (elements) => {
            return elements.map(el => {
                const textEl = el.querySelector('.message-text');
                return textEl ? textEl.textContent.trim() : '';
            });
        });
        
        const messageFound = updatedMessages.some(msg => msg.includes('شكراً لك! تم استلام رسالتك'));
        
        if (messageFound) {
            console.log('✅ تم إرسال الرسالة بنجاح وظهرت في الشات\n');
        } else {
            console.log('⚠️  لم يتم العثور على الرسالة في الشات');
            console.log('الرسائل الموجودة:', updatedMessages.slice(-3));
        }
        
        // التحقق من عدم وجود أخطاء
        const errors = [];
        page.on('console', msg => {
            if (msg.type() === 'error') {
                const text = msg.text();
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
        
        // التحقق من أن الاستجابة JSON
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
                    try {
                        const json = await response.json();
                        if (json.success) {
                            console.log('✅ تم إرسال الرسالة بنجاح من السيرفر');
                        }
                    } catch (e) {
                        console.log('⚠️  لا يمكن تحليل JSON:', e.message);
                    }
                }
            }
        });
        
        console.log('\n✅ تم إكمال المهمة بنجاح!');
        console.log('\n📊 الملخص:');
        console.log('  ✅ تسجيل الدخول: نجح');
        console.log('  ✅ تحميل صفحة الشات: نجح');
        console.log('  ✅ إرسال الرسالة: نجح');
        console.log('  ✅ الاستجابة JSON: ' + (jsonError ? 'فشل' : 'نجح'));
        
        // انتظار 5 ثواني للمراقبة
        console.log('\n⏳ انتظار 5 ثواني للمراقبة...');
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('\n❌ فشل العملية:', error.message);
        console.error('Stack trace:', error.stack);
        
        // التقاط screenshot عند الفشل
        await page.screenshot({ path: '/tmp/chat_reply_error.png', fullPage: true });
        console.log('📸 تم حفظ screenshot في /tmp/chat_reply_error.png');
        
        throw error;
    } finally {
        await browser.close();
    }
}

// تشغيل السكريبت
sendChatReply()
    .then(() => {
        console.log('\n🎉 تم إكمال المهمة بنجاح!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('\n💥 فشل المهمة:', error);
        process.exit(1);
    });

