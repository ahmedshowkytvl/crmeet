import { chromium } from 'playwright';

async function openPasswordAccounts() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        console.log('🔑 تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        
        console.log('🔗 فتح صفحة Password Accounts...');
        await page.goto('http://127.0.0.1:8000/password-accounts');
        
        console.log('✅ الصفحة مفتوحة - اضغط Ctrl+C للإغلاق');
        
        // ابقَ مفتوحاً
        await page.waitForTimeout(300000); // 5 دقائق
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
    } finally {
        await browser.close();
    }
}

openPasswordAccounts();





