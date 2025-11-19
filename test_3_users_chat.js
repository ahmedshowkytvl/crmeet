// اختبار Playwright MCP لـ 3 مستخدمين للدردشة الخاصة
import { chromium } from 'playwright';

// قائمة المستخدمين للاختبار (3 فقط)
const testUsers = [
    { id: 123, name: 'Admin User', username: 'admin', password: 'P@ssW0rd' },
    { id: 67, name: 'Mohamed Anwar', username: 'mohamed_anwar', password: 'P@ssW0rd' },
    { id: 78, name: 'Khaled Ahmed', username: 'emp_156', password: 'P@ssW0rd' }
];

async function test3UsersChat() {
    console.log('🚀 بدء اختبار الدردشة الخاصة لـ 3 مستخدمين...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // إظهار المتصفح
        slowMo: 1000, // إبطاء العمليات لمراقبة أفضل
        args: ['--start-maximized'] // تكبير النافذة
    });
    
    const contexts = [];
    const pages = [];
    
    try {
        // إنشاء صفحات متعددة للمستخدمين
        console.log('👥 إنشاء صفحات متعددة للمستخدمين...');
        
        for (let i = 0; i < testUsers.length; i++) {
            const context = await browser.newContext();
            const page = await context.newPage();
            
            contexts.push(context);
            pages.push(page);
            
            console.log(`✅ تم إنشاء صفحة للمستخدم ${i + 1}: ${testUsers[i].name}`);
        }
        
        // تسجيل دخول جميع المستخدمين
        console.log('\n🔐 تسجيل دخول جميع المستخدمين...');
        
        for (let i = 0; i < pages.length; i++) {
            const page = pages[i];
            const user = testUsers[i];
            
            console.log(`📝 تسجيل دخول المستخدم ${i + 1}: ${user.name}...`);
            
            await page.goto('http://127.0.0.1:8000/login');
            await page.waitForLoadState('networkidle');
            
            // تسجيل الدخول
            await page.fill('input[name="email"]', user.username);
            await page.fill('input[name="password"]', user.password);
            await page.click('button[type="submit"]');
            
            try {
                await page.waitForURL('**/dashboard**', { timeout: 10000 });
                console.log(`✅ تم تسجيل دخول ${user.name} بنجاح!`);
            } catch (error) {
                console.log(`⚠️ مشكلة في تسجيل دخول ${user.name}: ${error.message}`);
            }
            
            // انتظار قليل بين كل تسجيل دخول
            await page.waitForTimeout(2000);
        }
        
        // اختبار الدردشة بين المستخدمين
        console.log('\n💬 بدء اختبار الدردشة بين المستخدمين...');
        
        // المستخدم 1 يرسل رسالة للمستخدم 2
        console.log('\n📤 المستخدم 1 يرسل رسالة للمستخدم 2...');
        await testDirectChat(pages[0], testUsers[0], testUsers[1]);
        
        // المستخدم 2 يرد على المستخدم 1
        console.log('\n📤 المستخدم 2 يرد على المستخدم 1...');
        await testDirectChat(pages[1], testUsers[1], testUsers[0]);
        
        // المستخدم 3 يرسل رسالة للمستخدم 1
        console.log('\n📤 المستخدم 3 يرسل رسالة للمستخدم 1...');
        await testDirectChat(pages[2], testUsers[2], testUsers[0]);
        
        // المستخدم 1 يرد على المستخدم 3
        console.log('\n📤 المستخدم 1 يرد على المستخدم 3...');
        await testDirectChat(pages[0], testUsers[0], testUsers[2]);
        
        // المستخدم 2 يرسل رسالة للمستخدم 3
        console.log('\n📤 المستخدم 2 يرسل رسالة للمستخدم 3...');
        await testDirectChat(pages[1], testUsers[1], testUsers[2]);
        
        // المستخدم 3 يرد على المستخدم 2
        console.log('\n📤 المستخدم 3 يرد على المستخدم 2...');
        await testDirectChat(pages[2], testUsers[2], testUsers[1]);
        
        // اختبار دردشة جماعية
        console.log('\n👥 اختبار دردشة جماعية بين المستخدمين الثلاثة...');
        await testGroupChat(pages[0], testUsers[0], [testUsers[1], testUsers[2]]);
        
        // انتظار نهائي لمراقبة النتائج
        console.log('\n⏳ انتظار نهائي لمراقبة النتائج...');
        await pages[0].waitForTimeout(5000);
        
        console.log('\n🎉 تم إكمال اختبار الدردشة لـ 3 مستخدمين بنجاح!');
        
    } catch (error) {
        console.error('❌ حدث خطأ أثناء الاختبار:', error.message);
    } finally {
        // إغلاق جميع الصفحات والمتصفح
        console.log('\n🔚 إغلاق جميع الصفحات والمتصفح...');
        
        for (const context of contexts) {
            await context.close();
        }
        
        await browser.close();
        console.log('✅ تم إغلاق المتصفح بنجاح');
    }
}

// دالة اختبار الدردشة المباشرة
async function testDirectChat(page, sender, receiver) {
    try {
        console.log(`📝 ${sender.name} يرسل رسالة إلى ${receiver.name}...`);
        
        // الذهاب إلى صفحة بطاقة الاتصال للمستقبل
        await page.goto(`http://127.0.0.1:8000/users/${receiver.id}/contact-card`);
        await page.waitForLoadState('networkidle');
        
        // انتظار تحميل الصفحة
        await page.waitForTimeout(2000);
        
        // البحث عن زر "رسالة سريعة"
        try {
            await page.waitForSelector('button:has-text("رسالة سريعة")', { timeout: 10000 });
            await page.click('button:has-text("رسالة سريعة")');
            console.log(`✅ تم الضغط على زر "رسالة سريعة" من ${sender.name} إلى ${receiver.name}`);
            
            // انتظار إعادة التوجيه إلى صفحة الدردشة
            await page.waitForTimeout(3000);
            
            // فحص URL للتأكد من إعادة التوجيه
            const currentUrl = page.url();
            console.log(`✅ URL الحالي: ${currentUrl}`);
            
            if (currentUrl.includes('/chat')) {
                console.log(`✅ تم إعادة التوجيه إلى صفحة الدردشة بنجاح!`);
                
                // البحث عن عناصر الدردشة
                const messageInput = await page.locator('#messageInput').first();
                const sendButton = await page.locator('#sendMessageBtn').first();
                
                const inputExists = await messageInput.count() > 0;
                const buttonExists = await sendButton.count() > 0;
                
                if (inputExists && buttonExists) {
                    // إرسال رسالة
                    const message = `مرحباً ${receiver.name}! هذه رسالة من ${sender.name} - ${new Date().toLocaleTimeString()}`;
                    
                    await messageInput.fill(message);
                    console.log(`✅ تم كتابة الرسالة: "${message.substring(0, 50)}..."`);
                    
                    await page.waitForTimeout(1000);
                    
                    await sendButton.click();
                    console.log(`✅ تم إرسال الرسالة من ${sender.name} إلى ${receiver.name}`);
                    
                    // انتظار الاستجابة
                    await page.waitForTimeout(3000);
                    
                    // فحص الرسائل
                    const messages = await page.locator('.message').count();
                    console.log(`✅ عدد الرسائل في دردشة ${sender.name}: ${messages}`);
                    
                } else {
                    console.log(`⚠️ لم يتم العثور على عناصر الدردشة لـ ${sender.name}`);
                }
            } else {
                console.log(`⚠️ لم يتم إعادة التوجيه إلى صفحة الدردشة`);
            }
            
        } catch (error) {
            console.log(`⚠️ لم يتم العثور على زر "رسالة سريعة" لـ ${receiver.name}: ${error.message}`);
        }
        
    } catch (error) {
        console.log(`❌ خطأ في دردشة ${sender.name} إلى ${receiver.name}: ${error.message}`);
    }
}

// دالة اختبار الدردشة الجماعية
async function testGroupChat(page, sender, receivers) {
    try {
        console.log(`👥 ${sender.name} يبدأ دردشة جماعية مع ${receivers.map(r => r.name).join(', ')}...`);
        
        // الذهاب إلى صفحة الدردشة
        await page.goto('http://127.0.0.1:8000/chat');
        await page.waitForLoadState('networkidle');
        
        await page.waitForTimeout(3000);
        
        // البحث عن عناصر الدردشة
        const messageInput = await page.locator('#messageInput').first();
        const sendButton = await page.locator('#sendMessageBtn').first();
        
        const inputExists = await messageInput.count() > 0;
        const buttonExists = await sendButton.count() > 0;
        
        if (inputExists && buttonExists) {
            // إرسال رسالة جماعية
            const message = `مرحباً جميعاً! هذه رسالة جماعية من ${sender.name} - ${new Date().toLocaleTimeString()}`;
            
            await messageInput.fill(message);
            console.log(`✅ تم كتابة الرسالة الجماعية: "${message.substring(0, 50)}..."`);
            
            await page.waitForTimeout(1000);
            
            await sendButton.click();
            console.log(`✅ تم إرسال الرسالة الجماعية من ${sender.name}`);
            
            // انتظار الاستجابة
            await page.waitForTimeout(3000);
            
            // فحص الرسائل
            const messages = await page.locator('.message').count();
            console.log(`✅ عدد الرسائل في الدردشة الجماعية: ${messages}`);
            
        } else {
            console.log(`⚠️ لم يتم العثور على عناصر الدردشة الجماعية`);
        }
        
    } catch (error) {
        console.log(`❌ خطأ في الدردشة الجماعية: ${error.message}`);
    }
}

// تشغيل الاختبار
test3UsersChat().catch(console.error);

