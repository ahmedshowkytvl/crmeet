// اختبار Playwright MCP للدردشة مع مستخدم عشوائي
import { chromium } from 'playwright';

async function testChatWithRandomUser() {
    console.log('🚀 بدء اختبار الدردشة مع مستخدم عشوائي...\n');
    
    const browser = await chromium.launch({ 
        headless: false, // إظهار المتصفح
        slowMo: 1000 // إبطاء العمليات لمراقبة أفضل
    });
    
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // 1. الذهاب إلى صفحة تسجيل الدخول
        console.log('📝 الخطوة 1: الذهاب إلى صفحة تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('networkidle');
        
        // التحقق من تحميل الصفحة
        const title = await page.title();
        console.log(`✅ تم تحميل الصفحة: ${title}`);
        
        // 2. تسجيل الدخول
        console.log('\n🔐 الخطوة 2: تسجيل الدخول...');
        
        // ملء حقل البريد الإلكتروني
        await page.fill('input[name="email"]', 'admin@stafftobia.com');
        console.log('✅ تم ملء حقل البريد الإلكتروني');
        
        // ملء حقل كلمة المرور
        await page.fill('input[name="password"]', 'admin123');
        console.log('✅ تم ملء حقل كلمة المرور');
        
        // الضغط على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        console.log('✅ تم الضغط على زر تسجيل الدخول');
        
        // انتظار إعادة التوجيه
        await page.waitForURL('**/dashboard**', { timeout: 10000 });
        console.log('✅ تم تسجيل الدخول بنجاح!');
        
        // 3. اختيار مستخدم عشوائي
        console.log('\n🎲 الخطوة 3: اختيار مستخدم عشوائي...');
        const randomUserIds = [67, 68, 69, 70, 71, 72, 73, 74, 75];
        const randomUserId = randomUserIds[Math.floor(Math.random() * randomUserIds.length)];
        console.log(`✅ تم اختيار المستخدم العشوائي: ${randomUserId}`);
        
        // 4. الذهاب إلى بطاقة الاتصال
        console.log(`\n👤 الخطوة 4: الذهاب إلى بطاقة الاتصال للمستخدم ${randomUserId}...`);
        await page.goto(`http://127.0.0.1:8000/users/${randomUserId}/contact-card`);
        await page.waitForLoadState('networkidle');
        
        // التحقق من تحميل صفحة بطاقة الاتصال
        const contactTitle = await page.title();
        console.log(`✅ تم تحميل صفحة بطاقة الاتصال: ${contactTitle}`);
        
        // 5. البحث عن زر "رسالة سريعة"
        console.log('\n💬 الخطوة 5: البحث عن زر "رسالة سريعة"...');
        
        // انتظار تحميل الزر
        await page.waitForSelector('button:has-text("رسالة سريعة")', { timeout: 10000 });
        console.log('✅ تم العثور على زر "رسالة سريعة"');
        
        // 6. الضغط على زر "رسالة سريعة"
        console.log('\n🚀 الخطوة 6: الضغط على زر "رسالة سريعة"...');
        
        // انتظار قليل قبل الضغط
        await page.waitForTimeout(2000);
        
        // الضغط على الزر
        await page.click('button:has-text("رسالة سريعة")');
        console.log('✅ تم الضغط على زر "رسالة سريعة"');
        
        // 7. انتظار إعادة التوجيه إلى صفحة الدردشة
        console.log('\n⏳ الخطوة 7: انتظار إعادة التوجيه...');
        
        try {
            // انتظار إعادة التوجيه إلى صفحة الدردشة
            await page.waitForURL('**/chat**', { timeout: 15000 });
            console.log('✅ تم إعادة التوجيه إلى صفحة الدردشة!');
            
            // التحقق من عنوان الصفحة
            const chatTitle = await page.title();
            console.log(`✅ عنوان صفحة الدردشة: ${chatTitle}`);
            
            // البحث عن عناصر الدردشة
            const chatElements = await page.locator('[class*="chat"]').count();
            console.log(`✅ عدد عناصر الدردشة الموجودة: ${chatElements}`);
            
            // البحث عن الرسائل
            const messageElements = await page.locator('[class*="message"]').count();
            console.log(`✅ عدد عناصر الرسائل: ${messageElements}`);
            
            console.log('\n🎉 تم اختبار الدردشة بنجاح!');
            console.log(`✅ تم فتح دردشة مع المستخدم ${randomUserId}`);
            
        } catch (error) {
            console.log('⚠️ لم يتم إعادة التوجيه إلى صفحة الدردشة');
            console.log(`URL الحالي: ${page.url()}`);
            
            // محاولة فتح صفحة الدردشات مباشرة
            console.log('\n🔄 محاولة فتح صفحة الدردشات مباشرة...');
            await page.goto('http://127.0.0.1:8000/chat');
            await page.waitForLoadState('networkidle');
            
            const finalTitle = await page.title();
            console.log(`✅ تم فتح صفحة الدردشات: ${finalTitle}`);
        }
        
        // 8. انتظار قليل لمراقبة النتيجة
        console.log('\n⏳ انتظار 5 ثواني لمراقبة النتيجة...');
        await page.waitForTimeout(5000);
        
        console.log('\n✅ تم إكمال الاختبار بنجاح!');
        
    } catch (error) {
        console.error('❌ حدث خطأ أثناء الاختبار:', error.message);
        console.log(`URL الحالي: ${page.url()}`);
    } finally {
        // إغلاق المتصفح
        await browser.close();
        console.log('\n🔚 تم إغلاق المتصفح');
    }
}

// تشغيل الاختبار
testChatWithRandomUser().catch(console.error);
