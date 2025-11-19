// اختبار Playwright MCP لفحص تحميل ملفات JavaScript في الدردشة
import { chromium } from 'playwright';

async function testChatFileLoading() {
    console.log('🚀 بدء اختبار فحص تحميل ملفات JavaScript في الدردشة...\n');
    
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
        
        // 2. مراقبة تحميل الملفات
        console.log('\n📁 الخطوة 2: مراقبة تحميل الملفات...');
        
        const loadedFiles = [];
        page.on('response', response => {
            const url = response.url();
            if (url.includes('.js') || url.includes('.css')) {
                loadedFiles.push({
                    url: url,
                    status: response.status(),
                    type: url.includes('.js') ? 'JavaScript' : 'CSS'
                });
            }
        });
        
        // 3. الذهاب إلى صفحة الدردشة
        console.log('\n💬 الخطوة 3: الذهاب إلى صفحة الدردشة /chat/2...');
        await page.goto('http://127.0.0.1:8000/chat/2');
        await page.waitForLoadState('networkidle');
        
        const title = await page.title();
        console.log(`✅ تم تحميل الصفحة: ${title}`);
        
        // انتظار تحميل جميع الملفات
        await page.waitForTimeout(3000);
        
        // 4. فحص الملفات المحملة
        console.log('\n📊 الخطوة 4: فحص الملفات المحملة...');
        
        console.log(`✅ عدد الملفات المحملة: ${loadedFiles.length}`);
        loadedFiles.forEach((file, index) => {
            console.log(`📁 ملف ${index + 1}: ${file.type} - ${file.url} (${file.status})`);
        });
        
        // فحص ملف chat.js بشكل خاص
        const chatJsFile = loadedFiles.find(file => file.url.includes('chat.js'));
        if (chatJsFile) {
            console.log(`✅ ملف chat.js تم تحميله بنجاح: ${chatJsFile.url}`);
        } else {
            console.log('❌ ملف chat.js لم يتم تحميله');
        }
        
        // 5. فحص رسائل Console
        console.log('\n🔍 الخطوة 5: فحص رسائل Console...');
        
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
        
        console.log(`✅ عدد رسائل Console: ${consoleMessages.length}`);
        consoleMessages.forEach((msg, index) => {
            console.log(`📝 رسالة ${index + 1} [${msg.type}]: ${msg.text}`);
        });
        
        // 6. فحص متغيرات JavaScript
        console.log('\n🔍 الخطوة 6: فحص متغيرات JavaScript...');
        
        try {
            // فحص window.chatApp
            const chatAppExists = await page.evaluate(() => {
                return typeof window.chatApp !== 'undefined';
            });
            console.log(`✅ window.chatApp موجود: ${chatAppExists}`);
            
            // فحص ChatApp class
            const chatAppClassExists = await page.evaluate(() => {
                return typeof ChatApp !== 'undefined';
            });
            console.log(`✅ ChatApp class موجود: ${chatAppClassExists}`);
            
            // فحص currentUserId
            const currentUserId = await page.evaluate(() => {
                return window.currentUserId;
            });
            console.log(`✅ currentUserId: ${currentUserId}`);
            
            if (chatAppExists) {
                const currentChatRoomId = await page.evaluate(() => {
                    return window.chatApp?.currentChatRoomId;
                });
                console.log(`✅ currentChatRoomId: ${currentChatRoomId}`);
            }
            
        } catch (error) {
            console.log(`❌ خطأ في فحص JavaScript: ${error.message}`);
        }
        
        // 7. محاولة تحميل ملف chat.js يدوياً
        console.log('\n📁 الخطوة 7: محاولة تحميل ملف chat.js يدوياً...');
        
        try {
            await page.evaluate(() => {
                const script = document.createElement('script');
                script.src = '/js/chat.js';
                script.onload = () => console.log('تم تحميل chat.js يدوياً');
                script.onerror = () => console.log('خطأ في تحميل chat.js يدوياً');
                document.head.appendChild(script);
            });
            
            // انتظار تحميل الملف
            await page.waitForTimeout(2000);
            
            // فحص إذا تم تحميل ChatApp class
            const chatAppClassExistsAfter = await page.evaluate(() => {
                return typeof ChatApp !== 'undefined';
            });
            console.log(`✅ ChatApp class موجود بعد التحميل اليدوي: ${chatAppClassExistsAfter}`);
            
        } catch (error) {
            console.log(`❌ خطأ في التحميل اليدوي: ${error.message}`);
        }
        
        // 8. محاولة إنشاء ChatApp يدوياً
        console.log('\n🔧 الخطوة 8: محاولة إنشاء ChatApp يدوياً...');
        
        try {
            await page.evaluate(() => {
                if (typeof ChatApp !== 'undefined') {
                    window.chatApp = new ChatApp({
                        chatRoomId: 2,
                        sendMessageUrl: '/chat/messages/send-text',
                        sendContactUrl: '/chat/messages/send-contact',
                        uploadFileUrl: '/chat/files/upload',
                        searchUsersUrl: '/chat/search/users',
                        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    });
                    console.log('تم إنشاء ChatApp يدوياً');
                } else {
                    console.log('ChatApp class غير موجود');
                }
            });
            
            // انتظار قليل
            await page.waitForTimeout(1000);
            
            // فحص إذا تم إنشاء chatApp
            const chatAppExistsAfter = await page.evaluate(() => {
                return typeof window.chatApp !== 'undefined';
            });
            console.log(`✅ window.chatApp موجود بعد الإنشاء اليدوي: ${chatAppExistsAfter}`);
            
            if (chatAppExistsAfter) {
                const currentChatRoomId = await page.evaluate(() => {
                    return window.chatApp?.currentChatRoomId;
                });
                console.log(`✅ currentChatRoomId بعد الإنشاء اليدوي: ${currentChatRoomId}`);
            }
            
        } catch (error) {
            console.log(`❌ خطأ في إنشاء ChatApp يدوياً: ${error.message}`);
        }
        
        // 9. محاولة إرسال رسالة
        console.log('\n📤 الخطوة 9: محاولة إرسال رسالة...');
        
        try {
            const result = await page.evaluate(() => {
                if (window.chatApp) {
                    const messageInput = document.getElementById('messageInput');
                    if (messageInput) {
                        messageInput.value = 'رسالة اختبار من التحميل اليدوي';
                        return window.chatApp.sendMessage();
                    }
                    return 'messageInput غير موجود';
                }
                return 'chatApp غير موجود';
            });
            
            console.log(`✅ نتيجة إرسال الرسالة: ${result}`);
            
            // انتظار الاستجابة
            await page.waitForTimeout(3000);
            
            // فحص الرسائل
            const messages = await page.locator('.message').count();
            console.log(`✅ عدد الرسائل بعد المحاولة: ${messages}`);
            
        } catch (error) {
            console.log(`❌ خطأ في إرسال الرسالة: ${error.message}`);
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
testChatFileLoading().catch(console.error);
