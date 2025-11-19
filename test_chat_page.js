// اختبار Playwright MCP لفحص صفحة الدردشة المحددة
import { chromium } from 'playwright';

async function testSpecificChatPage() {
    console.log('🚀 بدء اختبار صفحة الدردشة المحددة...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // إظهار المتصفح
        slowMo: 500 // إبطاء العمليات لمراقبة أفضل
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. الذهاب إلى صفحة تسجيل الدخول
        console.log('📝 الخطوة 1: تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('networkidle');
        
        // تسجيل الدخول
        await page.fill('input[name="email"]', 'admin@stafftobia.com');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard**', { timeout: 10000 });
        console.log('✅ تم تسجيل الدخول بنجاح!');
        
        // 2. الذهاب إلى صفحة الدردشة المحددة
        console.log('\n💬 الخطوة 2: الذهاب إلى صفحة الدردشة /chat/2...');
        await page.goto('http://127.0.0.1:8000/chat/2');
        await page.waitForLoadState('networkidle');
        
        // التحقق من تحميل الصفحة
        const title = await page.title();
        const currentUrl = page.url();
        console.log(`✅ تم تحميل الصفحة: ${title}`);
        console.log(`✅ URL الحالي: ${currentUrl}`);
        
        // 3. فحص عناصر واجهة المستخدم
        console.log('\n🔍 الخطوة 3: فحص عناصر واجهة المستخدم...');
        
        // البحث عن حقل إدخال الرسالة
        const messageInput = await page.locator('input[type="text"], textarea, [contenteditable="true"]').first();
        const inputExists = await messageInput.count() > 0;
        console.log(`✅ حقل إدخال الرسالة موجود: ${inputExists}`);
        
        if (inputExists) {
            const inputType = await messageInput.getAttribute('type');
            const inputTag = await messageInput.evaluate(el => el.tagName);
            console.log(`✅ نوع الحقل: ${inputTag} (${inputType || 'N/A'})`);
        }
        
        // البحث عن زر الإرسال
        const sendButton = await page.locator('button:has-text("إرسال"), button[type="submit"], button[title*="إرسال"]').first();
        const buttonExists = await sendButton.count() > 0;
        console.log(`✅ زر الإرسال موجود: ${buttonExists}`);
        
        if (buttonExists) {
            const buttonText = await sendButton.textContent();
            console.log(`✅ نص زر الإرسال: "${buttonText}"`);
        }
        
        // البحث عن منطقة الرسائل
        const messagesArea = await page.locator('[class*="message"], [class*="chat"], [id*="message"], [id*="chat"]').first();
        const messagesExists = await messagesArea.count() > 0;
        console.log(`✅ منطقة الرسائل موجودة: ${messagesExists}`);
        
        // 4. محاولة إرسال رسالة تجريبية
        console.log('\n📤 الخطوة 4: محاولة إرسال رسالة تجريبية...');
        
        if (inputExists && buttonExists) {
            try {
                // كتابة رسالة تجريبية
                await messageInput.fill('رسالة تجريبية من Playwright');
                console.log('✅ تم كتابة الرسالة التجريبية');
                
                // انتظار قليل
                await page.waitForTimeout(1000);
                
                // الضغط على زر الإرسال
                await sendButton.click();
                console.log('✅ تم الضغط على زر الإرسال');
                
                // انتظار الاستجابة
                await page.waitForTimeout(3000);
                
                // فحص إذا كانت الرسالة ظهرت
                const messages = await page.locator('[class*="message"], [class*="chat-message"]').count();
                console.log(`✅ عدد الرسائل الموجودة: ${messages}`);
                
            } catch (error) {
                console.log(`❌ خطأ أثناء إرسال الرسالة: ${error.message}`);
            }
        }
        
        // 5. فحص أخطاء Console
        console.log('\n🔍 الخطوة 5: فحص أخطاء Console...');
        
        // جمع رسائل Console
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text()
            });
        });
        
        // جمع أخطاء الشبكة
        const networkErrors = [];
        page.on('response', response => {
            if (!response.ok()) {
                networkErrors.push({
                    url: response.url(),
                    status: response.status(),
                    statusText: response.statusText()
                });
            }
        });
        
        // انتظار قليل لجمع المزيد من الرسائل
        await page.waitForTimeout(2000);
        
        // عرض أخطاء Console
        const errors = consoleMessages.filter(msg => msg.type === 'error');
        console.log(`✅ عدد أخطاء Console: ${errors.length}`);
        errors.forEach((error, index) => {
            console.log(`❌ خطأ ${index + 1}: ${error.text}`);
        });
        
        // عرض أخطاء الشبكة
        console.log(`✅ عدد أخطاء الشبكة: ${networkErrors.length}`);
        networkErrors.forEach((error, index) => {
            console.log(`❌ خطأ شبكة ${index + 1}: ${error.url} - ${error.status} ${error.statusText}`);
        });
        
        // 6. فحص طلبات الشبكة
        console.log('\n🌐 الخطوة 6: فحص طلبات الشبكة...');
        
        // جمع جميع الطلبات
        const requests = [];
        page.on('request', request => {
            requests.push({
                url: request.url(),
                method: request.method(),
                headers: request.headers()
            });
        });
        
        // إعادة تحميل الصفحة لجمع الطلبات
        await page.reload();
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
        
        console.log(`✅ عدد طلبات الشبكة: ${requests.length}`);
        
        // عرض طلبات مهمة
        const importantRequests = requests.filter(req => 
            req.url.includes('chat') || 
            req.url.includes('message') || 
            req.url.includes('api') ||
            req.method !== 'GET'
        );
        
        console.log(`✅ عدد الطلبات المهمة: ${importantRequests.length}`);
        importantRequests.forEach((req, index) => {
            console.log(`📡 طلب ${index + 1}: ${req.method} ${req.url}`);
        });
        
        console.log('\n✅ تم إكمال الاختبار بنجاح!');
        
    } catch (error) {
        console.error('❌ حدث خطأ أثناء الاختبار:', error.message);
        console.log(`URL الحالي: ${page.url()}`);
    } finally {
        // انتظار قليل قبل الإغلاق
        await page.waitForTimeout(3000);
        await browser.close();
        console.log('\n🔚 تم إغلاق المتصفح');
    }
}

// تشغيل الاختبار
testSpecificChatPage().catch(console.error);
