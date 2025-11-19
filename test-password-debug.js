import { chromium } from 'playwright';

async function debugPasswordAccountsPage() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        console.log('🔑 تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        
        console.log('🔗 الانتقال إلى صفحة Password Accounts...');
        await page.goto('http://127.0.0.1:8000/password-accounts');
        await page.waitForLoadState('networkidle');
        
        // فحص محتوى الصفحة
        const bodyText = await page.textContent('body');
        console.log('\n📄 محتوى الصفحة (أول 500 حرف):');
        console.log(bodyText.substring(0, 500));
        
        // فحص إذا كانت هناك رسائل خطأ
        const errorMessages = await page.$$('.alert-danger, .text-danger');
        if (errorMessages.length > 0) {
            console.log('\n❌ رسائل خطأ:');
            for (const msg of errorMessages) {
                const text = await msg.textContent();
                console.log(`   - ${text.trim()}`);
            }
        }
        
        // فحص card-body
        const cardBody = await page.$('.card-body');
        if (cardBody) {
            const cardText = await cardBody.textContent();
            console.log('\n📦 محتوى card-body:');
            console.log(cardText.substring(0, 200));
        }
        
        await page.screenshot({ path: 'password-debug.png', fullPage: true });
        console.log('\n📸 تم حفظ لقطة الشاشة: password-debug.png');
        
        // انتظار 10 ثواني
        await page.waitForTimeout(10000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'password-debug-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

debugPasswordAccountsPage();





