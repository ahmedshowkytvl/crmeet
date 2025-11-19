// اختبار Playwright MCP لفحص عناصر واجهة المستخدم في الدردشة
import { chromium } from 'playwright';

async function testChatUIElements() {
    console.log('🚀 بدء اختبار عناصر واجهة المستخدم في الدردشة...\n');
    
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
        
        // 3. فحص جميع العناصر في الصفحة
        console.log('\n🔍 الخطوة 3: فحص جميع العناصر في الصفحة...');
        
        // البحث عن جميع الأزرار
        const allButtons = await page.locator('button').all();
        console.log(`✅ عدد الأزرار في الصفحة: ${allButtons.length}`);
        
        for (let i = 0; i < allButtons.length; i++) {
            const button = allButtons[i];
            const text = await button.textContent();
            const classes = await button.getAttribute('class');
            const type = await button.getAttribute('type');
            const isVisible = await button.isVisible();
            
            console.log(`🔘 زر ${i + 1}: "${text}" | type: ${type} | visible: ${isVisible} | classes: ${classes}`);
        }
        
        // البحث عن جميع حقول الإدخال
        const allInputs = await page.locator('input, textarea').all();
        console.log(`\n✅ عدد حقول الإدخال في الصفحة: ${allInputs.length}`);
        
        for (let i = 0; i < allInputs.length; i++) {
            const input = allInputs[i];
            const type = await input.getAttribute('type');
            const placeholder = await input.getAttribute('placeholder');
            const classes = await input.getAttribute('class');
            const isVisible = await input.isVisible();
            
            console.log(`📝 حقل ${i + 1}: type: ${type} | placeholder: "${placeholder}" | visible: ${isVisible} | classes: ${classes}`);
        }
        
        // البحث عن عناصر الدردشة
        console.log('\n🔍 البحث عن عناصر الدردشة...');
        
        // البحث عن عناصر تحتوي على "message" في الـ class أو id
        const messageElements = await page.locator('[class*="message"], [id*="message"], [class*="chat"], [id*="chat"]').all();
        console.log(`✅ عدد عناصر الرسائل/الدردشة: ${messageElements.length}`);
        
        for (let i = 0; i < messageElements.length; i++) {
            const element = messageElements[i];
            const tagName = await element.evaluate(el => el.tagName);
            const classes = await element.getAttribute('class');
            const id = await element.getAttribute('id');
            const text = await element.textContent();
            
            console.log(`💬 عنصر ${i + 1}: ${tagName} | id: ${id} | classes: ${classes} | text: "${text?.substring(0, 50)}..."`);
        }
        
        // البحث عن منطقة الرسائل بشكل أكثر دقة
        console.log('\n🔍 البحث عن منطقة الرسائل...');
        
        // البحث عن عناصر تحتوي على "messages" أو "chat-messages"
        const messagesContainer = await page.locator('[class*="messages"], [id*="messages"], [class*="chat-messages"], [id*="chat-messages"]').first();
        const messagesContainerExists = await messagesContainer.count() > 0;
        console.log(`✅ منطقة الرسائل موجودة: ${messagesContainerExists}`);
        
        if (messagesContainerExists) {
            const containerClasses = await messagesContainer.getAttribute('class');
            const containerId = await messagesContainer.getAttribute('id');
            console.log(`✅ منطقة الرسائل - id: ${containerId}, classes: ${containerClasses}`);
        }
        
        // البحث عن حقل إدخال الرسالة بشكل أكثر دقة
        console.log('\n🔍 البحث عن حقل إدخال الرسالة...');
        
        // البحث عن input مع placeholder يحتوي على "رسالة" أو "message"
        const messageInputByPlaceholder = await page.locator('input[placeholder*="رسالة"], input[placeholder*="message"], textarea[placeholder*="رسالة"], textarea[placeholder*="message"]').first();
        const inputByPlaceholderExists = await messageInputByPlaceholder.count() > 0;
        console.log(`✅ حقل الإدخال بالـ placeholder موجود: ${inputByPlaceholderExists}`);
        
        if (inputByPlaceholderExists) {
            const placeholder = await messageInputByPlaceholder.getAttribute('placeholder');
            const type = await messageInputByPlaceholder.getAttribute('type');
            console.log(`✅ حقل الإدخال - placeholder: "${placeholder}", type: ${type}`);
        }
        
        // البحث عن input مع id أو class يحتوي على "message" أو "chat"
        const messageInputByClass = await page.locator('input[id*="message"], input[class*="message"], input[id*="chat"], input[class*="chat"]').first();
        const inputByClassExists = await messageInputByClass.count() > 0;
        console.log(`✅ حقل الإدخال بالـ class/id موجود: ${inputByClassExists}`);
        
        if (inputByClassExists) {
            const classes = await messageInputByClass.getAttribute('class');
            const id = await messageInputByClass.getAttribute('id');
            console.log(`✅ حقل الإدخال - id: ${id}, classes: ${classes}`);
        }
        
        // البحث عن زر الإرسال بشكل أكثر دقة
        console.log('\n🔍 البحث عن زر الإرسال...');
        
        // البحث عن زر مع text يحتوي على "إرسال" أو "send"
        const sendButtonByText = await page.locator('button:has-text("إرسال"), button:has-text("Send"), button:has-text("send")').first();
        const buttonByTextExists = await sendButtonByText.count() > 0;
        console.log(`✅ زر الإرسال بالنص موجود: ${buttonByTextExists}`);
        
        if (buttonByTextExists) {
            const text = await sendButtonByText.textContent();
            const classes = await sendButtonByClass.getAttribute('class');
            const isVisible = await sendButtonByText.isVisible();
            console.log(`✅ زر الإرسال - text: "${text}", visible: ${isVisible}, classes: ${classes}`);
        }
        
        // البحث عن زر مع class أو id يحتوي على "send"
        const sendButtonByClass = await page.locator('button[class*="send"], button[id*="send"], button[class*="submit"], button[type="submit"]').first();
        const buttonByClassExists = await sendButtonByClass.count() > 0;
        console.log(`✅ زر الإرسال بالـ class/id موجود: ${buttonByClassExists}`);
        
        if (buttonByClassExists) {
            const classes = await sendButtonByClass.getAttribute('class');
            const id = await sendButtonByClass.getAttribute('id');
            const type = await sendButtonByClass.getAttribute('type');
            const isVisible = await sendButtonByClass.isVisible();
            console.log(`✅ زر الإرسال - id: ${id}, type: ${type}, visible: ${isVisible}, classes: ${classes}`);
        }
        
        // 4. محاولة إرسال رسالة باستخدام العنصر الصحيح
        console.log('\n📤 الخطوة 4: محاولة إرسال رسالة...');
        
        let messageInput = null;
        let sendButton = null;
        
        // تحديد حقل الإدخال الصحيح
        if (inputByPlaceholderExists) {
            messageInput = messageInputByPlaceholder;
            console.log('✅ تم تحديد حقل الإدخال بالـ placeholder');
        } else if (inputByClassExists) {
            messageInput = messageInputByClass;
            console.log('✅ تم تحديد حقل الإدخال بالـ class/id');
        } else {
            // البحث عن أول input من نوع text
            messageInput = await page.locator('input[type="text"]').first();
            const inputExists = await messageInput.count() > 0;
            console.log(`✅ حقل الإدخال text موجود: ${inputExists}`);
        }
        
        // تحديد زر الإرسال الصحيح
        if (buttonByTextExists && await sendButtonByText.isVisible()) {
            sendButton = sendButtonByText;
            console.log('✅ تم تحديد زر الإرسال بالنص');
        } else if (buttonByClassExists && await sendButtonByClass.isVisible()) {
            sendButton = sendButtonByClass;
            console.log('✅ تم تحديد زر الإرسال بالـ class/id');
        }
        
        if (messageInput && sendButton) {
            try {
                // كتابة رسالة
                await messageInput.fill('رسالة اختبار من Playwright - ' + new Date().toLocaleTimeString());
                console.log('✅ تم كتابة الرسالة');
                
                // انتظار قليل
                await page.waitForTimeout(1000);
                
                // الضغط على زر الإرسال
                await sendButton.click();
                console.log('✅ تم الضغط على زر الإرسال');
                
                // انتظار الاستجابة
                await page.waitForTimeout(3000);
                
                console.log('✅ تم إرسال الرسالة بنجاح!');
                
            } catch (error) {
                console.log(`❌ خطأ أثناء إرسال الرسالة: ${error.message}`);
            }
        } else {
            console.log('❌ لم يتم العثور على حقل الإدخال أو زر الإرسال المناسب');
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
testChatUIElements().catch(console.error);
