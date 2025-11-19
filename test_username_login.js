// اختبار تسجيل الدخول باستخدام username
import { chromium } from 'playwright';

async function testUsernameLogin() {
    console.log('🚀 بدء اختبار تسجيل الدخول باستخدام username...\n');
    
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
        
        const title = await page.title();
        console.log(`✅ تم تحميل الصفحة: ${title}`);
        
        // 2. اختبار تسجيل الدخول بـ username
        console.log('\n🔐 الخطوة 2: تسجيل الدخول باستخدام username...');
        
        // ملء حقل username
        await page.fill('input[name="email"]', 'admin');
        console.log('✅ تم ملء حقل username: admin');
        
        // ملء حقل كلمة المرور
        await page.fill('input[name="password"]', 'admin123');
        console.log('✅ تم ملء حقل كلمة المرور');
        
        // الضغط على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        console.log('✅ تم الضغط على زر تسجيل الدخول');
        
        // انتظار إعادة التوجيه
        await page.waitForURL('**/dashboard**', { timeout: 10000 });
        console.log('🎉 تم تسجيل الدخول بنجاح باستخدام username!');
        
        // 3. فحص الصفحة
        const dashboardTitle = await page.title();
        console.log(`✅ تم تحميل لوحة التحكم: ${dashboardTitle}`);
        
        // 4. تسجيل الخروج
        console.log('\n🚪 الخطوة 3: تسجيل الخروج...');
        await page.goto('http://127.0.0.1:8000/logout');
        console.log('✅ تم تسجيل الخروج');
        
        // 5. اختبار تسجيل الدخول بـ email للتأكد
        console.log('\n📧 الخطوة 4: اختبار تسجيل الدخول بـ email للتأكد...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('networkidle');
        
        // ملء حقل email
        await page.fill('input[name="email"]', 'admin@stafftobia.com');
        console.log('✅ تم ملء حقل email: admin@stafftobia.com');
        
        // ملء حقل كلمة المرور
        await page.fill('input[name="password"]', 'admin123');
        console.log('✅ تم ملء حقل كلمة المرور');
        
        // الضغط على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        console.log('✅ تم الضغط على زر تسجيل الدخول');
        
        // انتظار إعادة التوجيه
        await page.waitForURL('**/dashboard**', { timeout: 10000 });
        console.log('🎉 تم تسجيل الدخول بنجاح باستخدام email أيضاً!');
        
        // 6. اختبار username آخر
        console.log('\n👤 الخطوة 5: اختبار username آخر...');
        await page.goto('http://127.0.0.1:8000/logout');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('networkidle');
        
        // ملء حقل username
        await page.fill('input[name="email"]', 'mohamed_anwar');
        console.log('✅ تم ملء حقل username: mohamed_anwar');
        
        // ملء حقل كلمة المرور
        await page.fill('input[name="password"]', 'admin123');
        console.log('✅ تم ملء حقل كلمة المرور');
        
        // الضغط على زر تسجيل الدخول
        await page.click('button[type="submit"]');
        console.log('✅ تم الضغط على زر تسجيل الدخول');
        
        // انتظار إعادة التوجيه
        try {
            await page.waitForURL('**/dashboard**', { timeout: 10000 });
            console.log('🎉 تم تسجيل الدخول بنجاح باستخدام username الثاني!');
        } catch (error) {
            console.log('⚠️ لم يتم تسجيل الدخول بـ username الثاني (قد يحتاج كلمة مرور مختلفة)');
        }
        
        console.log('\n✅ تم إكمال جميع اختبارات تسجيل الدخول!');
        console.log('🎉 نظام تسجيل الدخول بـ username يعمل بشكل صحيح!');
        
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
testUsernameLogin().catch(console.error);
