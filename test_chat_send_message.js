// اختبار Playwright MCP لإرسال رسالة في الدردشة
import { chromium } from 'playwright';

async function testChatSendMessage() {
    console.log('🚀 بدء اختبار إرسال رسالة في الدردشة...\n');
    
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
        
        // 3. فحص العناصر الأساسية
        console.log('\n🔍 الخطوة 3: فحص العناصر الأساسية...');
        
        // البحث عن حقل الإدخال
        const messageInput = await page.locator('#messageInput').first();
        const inputExists = await messageInput.count() > 0;
        console.log(`✅ حقل الإدخال (messageInput) موجود: ${inputExists}`);
        
        // البحث عن زر الإرسال
        const sendButton = await page.locator('#sendMessageBtn').first();
        const buttonExists = await sendButton.count() > 0;
        console.log(`✅ زر الإرسال (sendMessageBtn) موجود: ${buttonExists}`);
        
        // فحص منطقة الرسائل
        const messagesArea = await page.locator('#chatMessages').first();
        const messagesExists = await messagesArea.count() > 0;
        console.log(`✅ منطقة الرسائل (chatMessages) موجودة: ${messagesExists}`);
        
        // 4. فحص الرسائل الموجودة
        console.log('\n📊 الخطوة 4: فحص الرسائل الموجودة...');
        const existingMessages = await page.locator('.message').count();
        console.log(`✅ عدد الرسائل الموجودة: ${existingMessages}`);
        
        // 5. مراقبة طلبات الشبكة
        console.log('\n🌐 الخطوة 5: إعداد مراقبة طلبات الشبكة...');
        
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
        
        // 6. محاولة إرسال رسالة
        console.log('\n📤 الخطوة 6: محاولة إرسال رسالة...');
        
        if (inputExists && buttonExists) {
            try {
                // كتابة رسالة
                const testMessage = `رسالة اختبار من Playwright - ${new Date().toLocaleTimeString()}`;
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
                
                if (newMessages > existingMessages) {
                    console.log('✅ تم إرسال الرسالة بنجاح!');
                    
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
        } else {
            console.log('❌ لم يتم العثور على حقل الإدخال أو زر الإرسال');
        }
        
        // 7. تحليل طلبات الشبكة
        console.log('\n📊 الخطوة 7: تحليل طلبات الشبكة...');
        
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
        
        // 8. فحص أخطاء Console
        console.log('\n🔍 الخطوة 8: فحص أخطاء Console...');
        
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text()
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
        
        // 9. محاولة إرسال رسالة أخرى
        console.log('\n📤 الخطوة 9: محاولة إرسال رسالة أخرى...');
        
        if (inputExists && buttonExists) {
            try {
                // مسح الحقل أولاً
                await messageInput.fill('');
                
                // كتابة رسالة جديدة
                const secondMessage = `رسالة اختبار ثانية - ${new Date().toLocaleTimeString()}`;
                await messageInput.fill(secondMessage);
                console.log(`✅ تم كتابة الرسالة الثانية: "${secondMessage}"`);
                
                // انتظار قليل
                await page.waitForTimeout(1000);
                
                // الضغط على زر الإرسال
                await sendButton.click();
                console.log('✅ تم الضغط على زر الإرسال مرة أخرى');
                
                // انتظار الاستجابة
                await page.waitForTimeout(3000);
                
                // فحص الرسائل مرة أخرى
                const finalMessages = await page.locator('.message').count();
                console.log(`✅ عدد الرسائل النهائي: ${finalMessages}`);
                
                if (finalMessages > newMessages) {
                    console.log('✅ تم إرسال الرسالة الثانية بنجاح!');
                } else {
                    console.log('⚠️ لم يتم إضافة رسالة جديدة في المحاولة الثانية');
                }
                
            } catch (error) {
                console.log(`❌ خطأ أثناء إرسال الرسالة الثانية: ${error.message}`);
            }
        }
        
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
testChatSendMessage().catch(console.error);
