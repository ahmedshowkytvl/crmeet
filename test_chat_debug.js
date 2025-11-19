// اختبار Playwright MCP لفحص JavaScript Console في الدردشة
import { chromium } from 'playwright';

async function testChatDebug() {
    console.log('🚀 بدء اختبار فحص JavaScript Console في الدردشة...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // إظهار المتصفح
        slowMo: 1000 // إبطاء العمليات لمراقبة أفضل
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. تسجيل الدخول
        console.log('📝 الخطوة 1: تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('networkidle');
        
        await page.fill('input[name="email"]', 'admin@stafftobia.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard**', { timeout: 10000 });
        console.log('✅ تم تسجيل الدخول بنجاح!');
        
        // 2. الذهاب إلى صفحة الدردشة
        console.log('\n💬 الخطوة 2: الذهاب إلى صفحة الدردشة /chat/2...');
        await page.goto('http://127.0.0.1:8000/chat/2');
        await page.waitForLoadState('networkidle');
        
        const title = await page.title();
        console.log(`✅ تم تحميل الصفحة: ${title}`);
        
        // 3. جمع رسائل Console
        console.log('\n🔍 الخطوة 3: جمع رسائل Console...');
        
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text(),
                location: msg.location()
            });
        });
        
        // انتظار تحميل JavaScript
        await page.waitForTimeout(3000);
        
        // عرض جميع رسائل Console
        console.log(`✅ عدد رسائل Console: ${consoleMessages.length}`);
        consoleMessages.forEach((msg, index) => {
            console.log(`📝 رسالة ${index + 1} [${msg.type}]: ${msg.text}`);
        });
        
        // 4. فحص متغيرات JavaScript
        console.log('\n🔍 الخطوة 4: فحص متغيرات JavaScript...');
        
        try {
            // فحص window.chatApp
            const chatAppExists = await page.evaluate(() => {
                return typeof window.chatApp !== 'undefined';
            });
            console.log(`✅ window.chatApp موجود: ${chatAppExists}`);
            
            if (chatAppExists) {
                // فحص currentChatRoomId
                const currentChatRoomId = await page.evaluate(() => {
                    return window.chatApp?.currentChatRoomId;
                });
                console.log(`✅ currentChatRoomId: ${currentChatRoomId}`);
                
                // فحص options
                const options = await page.evaluate(() => {
                    return window.chatApp?.options;
                });
                console.log(`✅ options:`, options);
            }
            
            // فحص window.currentUserId
            const currentUserId = await page.evaluate(() => {
                return window.currentUserId;
            });
            console.log(`✅ currentUserId: ${currentUserId}`);
            
        } catch (error) {
            console.log(`❌ خطأ في فحص JavaScript: ${error.message}`);
        }
        
        // 5. محاولة تنفيذ sendMessage مباشرة
        console.log('\n📤 الخطوة 5: محاولة تنفيذ sendMessage مباشرة...');
        
        try {
            const result = await page.evaluate(() => {
                if (window.chatApp) {
                    // تعيين رسالة في الحقل
                    const messageInput = document.getElementById('messageInput');
                    if (messageInput) {
                        messageInput.value = 'رسالة اختبار مباشرة من JavaScript';
                        
                        // محاولة إرسال الرسالة
                        return window.chatApp.sendMessage();
                    }
                }
                return 'chatApp غير موجود أو messageInput غير موجود';
            });
            
            console.log(`✅ نتيجة تنفيذ sendMessage: ${result}`);
            
        } catch (error) {
            console.log(`❌ خطأ في تنفيذ sendMessage: ${error.message}`);
        }
        
        // 6. مراقبة طلبات الشبكة
        console.log('\n🌐 الخطوة 6: مراقبة طلبات الشبكة...');
        
        const networkRequests = [];
        page.on('request', request => {
            if (request.method() !== 'GET') {
                networkRequests.push({
                    url: request.url(),
                    method: request.method(),
                    postData: request.postData(),
                    headers: request.headers()
                });
            }
        });
        
        // انتظار قليل لمراقبة الطلبات
        await page.waitForTimeout(2000);
        
        console.log(`✅ عدد طلبات الشبكة: ${networkRequests.length}`);
        networkRequests.forEach((req, index) => {
            console.log(`📡 طلب ${index + 1}: ${req.method} ${req.url}`);
            if (req.postData) {
                console.log(`   البيانات: ${req.postData}`);
            }
        });
        
        // 7. فحص العناصر مرة أخرى
        console.log('\n🔍 الخطوة 7: فحص العناصر مرة أخرى...');
        
        const messageInput = await page.locator('#messageInput').first();
        const sendButton = await page.locator('#sendMessageBtn').first();
        
        const inputValue = await messageInput.inputValue();
        const inputExists = await messageInput.count() > 0;
        const buttonExists = await sendButton.count() > 0;
        
        console.log(`✅ حقل الإدخال موجود: ${inputExists}`);
        console.log(`✅ زر الإرسال موجود: ${buttonExists}`);
        console.log(`✅ قيمة الحقل: "${inputValue}"`);
        
        // 8. محاولة إرسال رسالة باستخدام JavaScript مباشرة
        console.log('\n📤 الخطوة 8: محاولة إرسال رسالة باستخدام JavaScript مباشرة...');
        
        try {
            await page.evaluate(() => {
                const messageInput = document.getElementById('messageInput');
                const sendButton = document.getElementById('sendMessageBtn');
                
                if (messageInput && sendButton) {
                    messageInput.value = 'رسالة اختبار مباشرة';
                    
                    // محاولة إرسال الحدث
                    const event = new Event('click');
                    sendButton.dispatchEvent(event);
                    
                    return 'تم إرسال الحدث';
                }
                return 'العناصر غير موجودة';
            });
            
            // انتظار الاستجابة
            await page.waitForTimeout(3000);
            
            // فحص الرسائل
            const messages = await page.locator('.message').count();
            console.log(`✅ عدد الرسائل بعد المحاولة المباشرة: ${messages}`);
            
        } catch (error) {
            console.log(`❌ خطأ في المحاولة المباشرة: ${error.message}`);
        }
        
        // 9. فحص رسائل Console النهائية
        console.log('\n🔍 الخطوة 9: فحص رسائل Console النهائية...');
        
        const finalConsoleMessages = [];
        page.on('console', msg => {
            finalConsoleMessages.push({
                type: msg.type(),
                text: msg.text(),
                location: msg.location()
            });
        });
        
        // انتظار قليل لجمع الرسائل
        await page.waitForTimeout(2000);
        
        console.log(`✅ عدد رسائل Console النهائية: ${finalConsoleMessages.length}`);
        finalConsoleMessages.forEach((msg, index) => {
            console.log(`📝 رسالة ${index + 1} [${msg.type}]: ${msg.text}`);
        });
        
        console.log('\n✅ تم إكمال الاختبار بنجاح!');
        
    } catch (error) {
        console.error('❌ حدث خطأ أثناء الاختبار:', error.message);
        console.log(`URL الحالي: ${page.url()}`);
    } finally {
        // انتظار قليل قبل الإغلاق
        await page.waitForTimeout(5000);
        await browser.close();
        console.log('\n🔚 تم إغلاق المتصفح');
    }
}

// تشغيل الاختبار
testChatDebug().catch(console.error);
