import { chromium } from 'playwright';

async function testQuick() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        
        await page.goto('http://127.0.0.1:8000/password-accounts');
        await page.waitForLoadState('networkidle');
        
        // انتظار 2 ثانية
        await page.waitForTimeout(2000);
        
        // فحص عدد الحسابات
        const totalText = await page.textContent('.card.bg-primary h4');
        console.log(`📊 إجمالي الحسابات: ${totalText}`);
        
        const rows = await page.$$('table tbody tr');
        console.log(`📋 الحسابات المعروضة في الجدول: ${rows.length}`);
        
        if (rows.length > 0) {
            console.log('✅ الحسابات تظهر الآن!');
        } else {
            console.log('❌ لا تزال الحسابات لا تظهر');
        }
        
        await page.screenshot({ path: 'quick-test.png', fullPage: true });
        
        // انتظار 5 ثواني
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
    } finally {
        await browser.close();
    }
}

testQuick();





