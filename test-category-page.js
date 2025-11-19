import { chromium } from 'playwright';

async function testCategoryPage() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        console.log('🔑 تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        
        console.log('🔗 الانتقال إلى صفحة الفئة...');
        await page.goto('http://127.0.0.1:8000/password-categories/10');
        await page.waitForLoadState('networkidle');
        
        console.log('✅ تم تحميل الصفحة بنجاح');
        console.log(`📍 URL: ${page.url()}`);
        
        const title = await page.title();
        console.log(`📄 العنوان: ${title}`);
        
        // فحص الحسابات
        const rows = await page.$$('table tbody tr');
        console.log(`📋 عدد الحسابات: ${rows.length}`);
        
        if (rows.length > 0) {
            console.log('\n✅ الحسابات المعروضة:');
            for (let i = 0; i < Math.min(rows.length, 5); i++) {
                const cells = await rows[i].$$('td');
                const name = await cells[0]?.textContent();
                console.log(`   ${i + 1}. ${name?.trim()}`);
            }
        }
        
        await page.screenshot({ path: 'category-page.png', fullPage: true });
        console.log('\n📸 تم حفظ لقطة الشاشة: category-page.png');
        
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'category-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testCategoryPage();

