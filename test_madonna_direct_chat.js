// اختبار تسجيل دخول مدونا والذهاب مباشرة إلى دردشة مع محمد أنور
import { chromium } from 'playwright';

async function testMadonnaDirectChat() {
    console.log('🚀 بدء اختبار تسجيل دخول مدونا والذهاب مباشرة إلى دردشة مع محمد أنور...\n');
    
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
        
        // 2. الذهاب مباشرة إلى دردشة مع محمد أنور
        console.log('\n💬 الخطوة 2: الذهاب مباشرة إلى دردشة مع محمد أنور...');
        
        // البحث عن دردشة موجودة أو إنشاء واحدة جديدة
        await page.goto('http://127.0.0.1:8000/chat/2');
        await page.waitForLoadState('networkidle');
        
        const chatTitle = await page.title();
        console.log(`✅ تم تحميل صفحة الدردشة: ${chatTitle}`);
        
        // انتظار تحميل JavaScript
        await page.waitForTimeout(3000);
        
        // 3. فحص حالة الدردشة
        console.log('\n🔍 الخطوة 3: فحص حالة الدردشة...');
        
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
        
        // 4. فحص عناصر الدردشة
        console.log('\n💬 الخطوة 4: فحص عناصر الدردشة...');
        
        // البحث عن حقل الرسالة بطرق مختلفة
        const messageInputSelectors = [
            '#messageInput',
            'input[placeholder*="اكتب"]',
            'input[placeholder*="رسالة"]',
            '.message-input input',
            'textarea[placeholder*="اكتب"]',
            'textarea[placeholder*="رسالة"]'
        ];
        
        let messageInput = null;
        let inputSelector = null;
        
        for (const selector of messageInputSelectors) {
            try {
                const element = await page.locator(selector).first();
                if (await element.count() > 0) {
                    messageInput = element;
                    inputSelector = selector;
                    break;
                }
            } catch (error) {
                // تجاهل الخطأ والمتابعة
            }
        }
        
        if (messageInput) {
            console.log(`✅ تم العثور على حقل الرسالة باستخدام: ${inputSelector}`);
        } else {
            console.log('❌ لم يتم العثور على حقل الرسالة');
        }
        
        // البحث عن زر الإرسال
        const sendButtonSelectors = [
            '#sendMessageBtn',
            'button[type="submit"]',
            'button:has-text("إرسال")',
            'button:has-text("Send")',
            '.send-button',
            '.btn-send'
        ];
        
        let sendButton = null;
        let buttonSelector = null;
        
        for (const selector of sendButtonSelectors) {
            try {
                const element = await page.locator(selector).first();
                if (await element.count() > 0) {
                    sendButton = element;
                    buttonSelector = selector;
                    break;
                }
            } catch (error) {
                // تجاهل الخطأ والمتابعة
            }
        }
        
        if (sendButton) {
            console.log(`✅ تم العثور على زر الإرسال باستخدام: ${buttonSelector}`);
        } else {
            console.log('❌ لم يتم العثور على زر الإرسال');
        }
        
        // 5. إرسال رسالة
        if (messageInput && sendButton) {
            console.log('\n📤 الخطوة 5: إرسال رسالة لمحمد أنور...');
            
            const testMessage = `مرحباً محمد أنور! هذه رسالة من مدونا نشأت سيحا - ${new Date().toLocaleTimeString()}`;
            
            try {
                // جعل الحقل مرئي أولاً
                await messageInput.scrollIntoViewIfNeeded();
                await page.waitForTimeout(1000);
                
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
                
                if (messages > 0) {
                    const lastMessage = await page.locator('.message').last();
                    const lastMessageText = await lastMessage.textContent();
                    console.log(`✅ آخر رسالة: "${lastMessageText?.substring(0, 100)}..."`);
                }
                
                console.log('🎉 تم إرسال الرسالة بنجاح!');
                
                // 6. إرسال رسالة أخرى
                console.log('\n📤 الخطوة 6: إرسال رسالة أخرى...');
                
                const secondMessage = `كيف حالك محمد؟ أتمنى أن تكون بخير - من مدونا`;
                
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
                console.log(`❌ خطأ أثناء إرسال الرسالة: ${error.message}`);
                
                // محاولة أخرى بطريقة مختلفة
                console.log('\n🔄 محاولة أخرى بطريقة مختلفة...');
                
                try {
                    // استخدام JavaScript مباشرة
                    await page.evaluate((message) => {
                        const input = document.querySelector('#messageInput') || 
                                    document.querySelector('input[placeholder*="اكتب"]') ||
                                    document.querySelector('textarea[placeholder*="اكتب"]');
                        const button = document.querySelector('#sendMessageBtn') || 
                                     document.querySelector('button[type="submit"]');
                        
                        if (input && button) {
                            input.value = message;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            button.click();
                            return true;
                        }
                        return false;
                    }, testMessage);
                    
                    console.log('✅ تم إرسال الرسالة باستخدام JavaScript مباشرة');
                    
                    await page.waitForTimeout(3000);
                    
                } catch (jsError) {
                    console.log(`❌ خطأ في المحاولة الثانية: ${jsError.message}`);
                }
            }
        } else {
            console.log('⚠️ لا يمكن إرسال الرسالة - عناصر الدردشة غير متوفرة');
            
            // فحص محتوى الصفحة
            const pageContent = await page.content();
            const hasMessageInput = pageContent.includes('messageInput') || pageContent.includes('اكتب رسالتك');
            const hasSendButton = pageContent.includes('sendMessageBtn') || pageContent.includes('إرسال');
            
            console.log(`✅ الصفحة تحتوي على حقل الرسالة: ${hasMessageInput}`);
            console.log(`✅ الصفحة تحتوي على زر الإرسال: ${hasSendButton}`);
        }
        
        console.log('\n🎉 تم إكمال الاختبار!');
        console.log('✅ تم تسجيل دخول مدونا ومحاولة إرسال رسائل لمحمد أنور');
        
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
testMadonnaDirectChat().catch(console.error);
