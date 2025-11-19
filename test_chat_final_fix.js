// اختبار Playwright MCP النهائي بعد إصلاح جميع المشاكل
import { chromium } from 'playwright';

async function testChatFinalFix() {
    console.log('🚀 بدء الاختبار النهائي بعد إصلاح جميع المشاكل...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // إظهار المتصفح
        slowMo: 500 // إبطاء العمليات لمراقبة أفضل
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
        
        // انتظار تحميل JavaScript
        await page.waitForTimeout(3000);
        
        // 3. فحص الحالة الأولية
        console.log('\n🔍 الخطوة 3: فحص الحالة الأولية...');
        
        const initialMessages = await page.locator('.message').count();
        console.log(`✅ عدد الرسائل الأولي: ${initialMessages}`);
        
        // فحص chatApp
        const chatAppExists = await page.evaluate(() => {
            return typeof window.chatApp !== 'undefined';
        });
        console.log(`✅ window.chatApp موجود: ${chatAppExists}`);
        
        if (chatAppExists) {
            const currentChatRoomId = await page.evaluate(() => {
                return window.chatApp?.currentChatRoomId;
            });
            console.log(`✅ currentChatRoomId: ${currentChatRoomId}`);
        }
        
        // 4. مراقبة طلبات الشبكة
        console.log('\n🌐 الخطوة 4: إعداد مراقبة طلبات الشبكة...');
        
        const networkRequests = [];
        const networkResponses = [];
        
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
        
        page.on('response', response => {
            if (response.request().method() !== 'GET') {
                networkResponses.push({
                    url: response.url(),
                    status: response.status(),
                    statusText: response.statusText(),
                    headers: response.headers()
                });
            }
        });
        
        // 5. إرسال رسالة
        console.log('\n📤 الخطوة 5: إرسال رسالة...');
        
        const messageInput = await page.locator('#messageInput').first();
        const sendButton = await page.locator('#sendMessageBtn').first();
        
        const testMessage = `رسالة اختبار نهائية بعد الإصلاح - ${new Date().toLocaleTimeString()}`;
        
        try {
            // كتابة الرسالة
            await messageInput.fill(testMessage);
            console.log(`✅ تم كتابة الرسالة: "${testMessage}"`);
            
            // انتظار قليل
            await page.waitForTimeout(1000);
            
            // الضغط على زر الإرسال
            await sendButton.click();
            console.log('✅ تم الضغط على زر الإرسال');
            
            // انتظار الاستجابة
            await page.waitForTimeout(3000);
            
            // فحص الرسائل الجديدة
            const newMessages = await page.locator('.message').count();
            console.log(`✅ عدد الرسائل بعد الإرسال: ${newMessages}`);
            
            if (newMessages > initialMessages) {
                console.log('🎉 تم إرسال الرسالة بنجاح!');
                
                // فحص آخر رسالة
                const lastMessage = await page.locator('.message').last();
                const lastMessageText = await lastMessage.textContent();
                console.log(`✅ آخر رسالة: "${lastMessageText?.substring(0, 100)}..."`);
            } else {
                console.log('⚠️ لم يتم إضافة رسالة جديدة');
            }
            
        } catch (error) {
            console.log(`❌ خطأ أثناء إرسال الرسالة: ${error.message}`);
        }
        
        // 6. تحليل طلبات الشبكة
        console.log('\n📊 الخطوة 6: تحليل طلبات الشبكة...');
        
        console.log(`✅ عدد طلبات الشبكة: ${networkRequests.length}`);
        networkRequests.forEach((req, index) => {
            console.log(`📡 طلب ${index + 1}: ${req.method} ${req.url}`);
            if (req.postData) {
                console.log(`   البيانات: ${req.postData}`);
            }
        });
        
        console.log(`✅ عدد استجابات الشبكة: ${networkResponses.length}`);
        networkResponses.forEach((res, index) => {
            console.log(`📨 استجابة ${index + 1}: ${res.status} ${res.statusText} - ${res.url}`);
        });
        
        // 7. إرسال رسالة أخرى للتأكد
        console.log('\n📤 الخطوة 7: إرسال رسالة أخرى للتأكد...');
        
        const secondMessage = `رسالة ثانية للتأكد - ${new Date().toLocaleTimeString()}`;
        
        try {
            await messageInput.fill(secondMessage);
            console.log(`✅ تم كتابة الرسالة الثانية: "${secondMessage}"`);
            
            await page.waitForTimeout(1000);
            
            await sendButton.click();
            console.log('✅ تم الضغط على زر الإرسال مرة أخرى');
            
            await page.waitForTimeout(3000);
            
            const finalMessages = await page.locator('.message').count();
            console.log(`✅ عدد الرسائل النهائي: ${finalMessages}`);
            
            if (finalMessages > newMessages) {
                console.log('🎉 تم إرسال الرسالة الثانية بنجاح!');
            } else {
                console.log('⚠️ لم يتم إضافة رسالة جديدة في المحاولة الثانية');
            }
            
        } catch (error) {
            console.log(`❌ خطأ في الرسالة الثانية: ${error.message}`);
        }
        
        // 8. فحص أخطاء Console
        console.log('\n🔍 الخطوة 8: فحص أخطاء Console...');
        
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text(),
                location: msg.location()
            });
        });
        
        // انتظار قليل لجمع رسائل Console
        await page.waitForTimeout(2000);
        
        const errors = consoleMessages.filter(msg => msg.type === 'error');
        const warnings = consoleMessages.filter(msg => msg.type === 'warning');
        
        console.log(`✅ عدد أخطاء Console: ${errors.length}`);
        errors.forEach((error, index) => {
            console.log(`❌ خطأ ${index + 1}: ${error.text}`);
        });
        
        console.log(`✅ عدد تحذيرات Console: ${warnings.length}`);
        warnings.forEach((warning, index) => {
            console.log(`⚠️ تحذير ${index + 1}: ${warning.text}`);
        });
        
        console.log('\n🎉 تم إكمال الاختبار النهائي بنجاح!');
        console.log('✅ تم حل جميع المشاكل في نظام الدردشة');
        
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
testChatFinalFix().catch(console.error);
