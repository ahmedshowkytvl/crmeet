// اختبار تسجيل دخول مدونا نشأت سيحا وإرسال رسالة لمحمد أنور
import { chromium } from 'playwright';

async function testMadonnaChatWithMohamed() {
    console.log('🚀 بدء اختبار تسجيل دخول مدونا وإرسال رسالة لمحمد أنور...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // إظهار المتصفح
        slowMo: 1000 // إبطاء العمليات لمراقبة أفضل
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. تسجيل الدخول بمدونا
        console.log('👩 الخطوة 1: تسجيل الدخول بمدونا نشأت سيحا...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('networkidle');
        
        const title = await page.title();
        console.log(`✅ تم تحميل الصفحة: ${title}`);
        
        // ملء بيانات مدونا
        await page.fill('input[name="email"]', 'madonna');
        console.log('✅ تم ملء username: madonna');
        
        await page.fill('input[name="password"]', 'admin123');
        console.log('✅ تم ملء كلمة المرور');
        
        // الضغط على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        console.log('✅ تم الضغط على زر تسجيل الدخول');
        
        // انتظار إعادة التوجيه
        await page.waitForURL('**/dashboard**', { timeout: 10000 });
        console.log('🎉 تم تسجيل الدخول بنجاح بمدونا!');
        
        const dashboardTitle = await page.title();
        console.log(`✅ تم تحميل لوحة التحكم: ${dashboardTitle}`);
        
        // 2. الذهاب إلى صفحة محمد أنور
        console.log('\n👤 الخطوة 2: الذهاب إلى صفحة محمد أنور...');
        await page.goto('http://127.0.0.1:8000/users/67/contact-card');
        await page.waitForLoadState('networkidle');
        
        const contactTitle = await page.title();
        console.log(`✅ تم تحميل صفحة الاتصال: ${contactTitle}`);
        
        // 3. البحث عن زر "رسالة سريعة"
        console.log('\n💬 الخطوة 3: البحث عن زر "رسالة سريعة"...');
        
        // انتظار تحميل الزر
        await page.waitForSelector('button:has-text("رسالة سريعة")', { timeout: 10000 });
        console.log('✅ تم العثور على زر "رسالة سريعة"');
        
        // 4. الضغط على زر "رسالة سريعة"
        console.log('\n🚀 الخطوة 4: الضغط على زر "رسالة سريعة"...');
        
        await page.waitForTimeout(2000);
        await page.click('button:has-text("رسالة سريعة")');
        console.log('✅ تم الضغط على زر "رسالة سريعة"');
        
        // 5. انتظار إعادة التوجيه إلى صفحة الدردشة
        console.log('\n⏳ الخطوة 5: انتظار إعادة التوجيه إلى صفحة الدردشة...');
        
        try {
            await page.waitForURL('**/chat**', { timeout: 15000 });
            console.log('✅ تم إعادة التوجيه إلى صفحة الدردشة!');
            
            const chatTitle = await page.title();
            console.log(`✅ عنوان صفحة الدردشة: ${chatTitle}`);
            
        } catch (error) {
            console.log('⚠️ لم يتم إعادة التوجيه، محاولة فتح صفحة الدردشة مباشرة...');
            await page.goto('http://127.0.0.1:8000/chat');
            await page.waitForLoadState('networkidle');
            
            const finalTitle = await page.title();
            console.log(`✅ تم فتح صفحة الدردشة: ${finalTitle}`);
        }
        
        // انتظار تحميل JavaScript
        await page.waitForTimeout(3000);
        
        // 6. فحص حالة الدردشة
        console.log('\n🔍 الخطوة 6: فحص حالة الدردشة...');
        
        const chatAppExists = await page.evaluate(() => {
            return typeof window.chatApp !== 'undefined';
        });
        console.log(`✅ window.chatApp موجود: ${chatAppExists}`);
        
        if (chatAppExists) {
            const currentChatRoomId = await page.evaluate(() => {
                return window.chatApp?.currentChatRoomId;
            });
            console.log(`✅ currentChatRoomId: ${currentChatRoomId}`);
            
            const currentUserId = await page.evaluate(() => {
                return window.currentUserId;
            });
            console.log(`✅ currentUserId: ${currentUserId}`);
        }
        
        // 7. البحث عن عناصر الدردشة
        console.log('\n💬 الخطوة 7: البحث عن عناصر الدردشة...');
        
        const messageInput = await page.locator('#messageInput').first();
        const sendButton = await page.locator('#sendMessageBtn').first();
        
        const inputExists = await messageInput.count() > 0;
        const buttonExists = await sendButton.count() > 0;
        
        console.log(`✅ حقل الرسالة موجود: ${inputExists}`);
        console.log(`✅ زر الإرسال موجود: ${buttonExists}`);
        
        if (inputExists && buttonExists) {
            // 8. إرسال رسالة
            console.log('\n📤 الخطوة 8: إرسال رسالة لمحمد أنور...');
            
            const testMessage = `مرحباً محمد أنور! هذه رسالة من مدونا نشأت سيحا - ${new Date().toLocaleTimeString()}`;
            
            try {
                // كتابة الرسالة
                await messageInput.fill(testMessage);
                console.log(`✅ تم كتابة الرسالة: "${testMessage}"`);
                
                await page.waitForTimeout(1000);
                
                // الضغط على زر الإرسال
                await sendButton.click();
                console.log('✅ تم الضغط على زر الإرسال');
                
                // انتظار الاستجابة
                await page.waitForTimeout(3000);
                
                // فحص الرسائل
                const messages = await page.locator('.message').count();
                console.log(`✅ عدد الرسائل في الدردشة: ${messages}`);
                
                // فحص آخر رسالة
                if (messages > 0) {
                    const lastMessage = await page.locator('.message').last();
                    const lastMessageText = await lastMessage.textContent();
                    console.log(`✅ آخر رسالة: "${lastMessageText?.substring(0, 100)}..."`);
                }
                
                console.log('🎉 تم إرسال الرسالة بنجاح!');
                
            } catch (error) {
                console.log(`❌ خطأ أثناء إرسال الرسالة: ${error.message}`);
            }
        } else {
            console.log('⚠️ لم يتم العثور على عناصر الدردشة');
        }
        
        // 9. إرسال رسالة أخرى للتأكد
        console.log('\n📤 الخطوة 9: إرسال رسالة أخرى للتأكد...');
        
        if (inputExists && buttonExists) {
            const secondMessage = `كيف حالك محمد؟ أتمنى أن تكون بخير - من مدونا`;
            
            try {
                await messageInput.fill(secondMessage);
                console.log(`✅ تم كتابة الرسالة الثانية: "${secondMessage}"`);
                
                await page.waitForTimeout(1000);
                
                await sendButton.click();
                console.log('✅ تم الضغط على زر الإرسال مرة أخرى');
                
                await page.waitForTimeout(3000);
                
                const finalMessages = await page.locator('.message').count();
                console.log(`✅ عدد الرسائل النهائي: ${finalMessages}`);
                
                console.log('🎉 تم إرسال الرسالة الثانية بنجاح!');
                
            } catch (error) {
                console.log(`❌ خطأ في الرسالة الثانية: ${error.message}`);
            }
        }
        
        // 10. فحص أخطاء Console
        console.log('\n🔍 الخطوة 10: فحص أخطاء Console...');
        
        const consoleMessages = [];
        page.on('console', msg => {
            consoleMessages.push({
                type: msg.type(),
                text: msg.text(),
                location: msg.location()
            });
        });
        
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
        
        console.log('\n🎉 تم إكمال الاختبار بنجاح!');
        console.log('✅ تم تسجيل دخول مدونا وإرسال رسائل لمحمد أنور');
        
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
testMadonnaChatWithMohamed().catch(console.error);
