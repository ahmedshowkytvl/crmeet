import { chromium } from 'playwright';

async function testViewSimple() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        console.log('🔑 تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.waitForLoadState('load');
        
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        
        console.log('⏳ انتظار تحميل الصفحة...');
        await page.waitForTimeout(3000);
        
        console.log(`📍 URL الحالي: ${page.url()}`);
        
        console.log('🔗 الانتقال إلى صفحة الحسابات...');
        await page.goto('http://127.0.0.1:8000/password-accounts');
        
        console.log('⏳ انتظار تحميل الصفحة...');
        await page.waitForTimeout(5000);
        
        console.log(`📍 URL الحالي: ${page.url()}`);
        
        // فحص محتوى الصفحة
        const title = await page.title();
        console.log(`📄 عنوان الصفحة: ${title}`);
        
        // فحص الجدول
        const table = await page.$('table');
        if (table) {
            console.log('✅ وجد جدول');
            const rows = await page.$$('table tbody tr');
            console.log(`📋 عدد الصفوف: ${rows.length}`);
        } else {
            console.log('❌ لم يتم العثور على جدول');
        }
        
        await page.screenshot({ path: 'accounts-simple.png', fullPage: true });
        console.log('📸 تم حفظ لقطة الشاشة: accounts-simple.png');
        
        // انتظار 10 ثواني لمشاهدة الصفحة
        await page.waitForTimeout(10000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'error-simple.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testViewSimple();

