import { chromium } from 'playwright';

/**
 * سكريبت بسيط لاختبار النموذج وفهم المشكلة
 */

async function testFormSubmission() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        console.log('🔑 تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        
        console.log('🔗 الانتقال إلى صفحة الإنشاء...');
        await page.goto('http://127.0.0.1:8000/password-accounts/create');
        await page.waitForLoadState('networkidle');
        
        console.log('📝 ملء النموذج...');
        await page.fill('input#name', 'Test Account Manual');
        await page.fill('input#email', 'testuser');
        await page.fill('input#password', 'TestPass123!');
        await page.fill('input#url', 'https://test.com');
        await page.fill('textarea#notes', 'Test notes');
        
        // اختيار فئة
        await page.selectOption('select[name="category_id"]', '10');
        
        console.log('📤 إرسال النموذج...');
        
        // مراقبة الاستجابة
        const [response] = await Promise.all([
            page.waitForResponse(response => 
                response.url().includes('/password-accounts') && 
                response.request().method() === 'POST'
            ),
            page.click('button[type="submit"]')
        ]);
        
        console.log(`📥 استجابة الخادم: ${response.status()} ${response.url()}`);
        
        // انتظار تحميل الصفحة
        await page.waitForLoadState('networkidle');
        
        // فحص محتوى الصفحة
        const pageContent = await page.content();
        console.log('🔍 فحص محتوى الصفحة...');
        
        // فحص رسائل الخطأ
        const errorElements = await page.$$('.alert-danger, .invalid-feedback, .error');
        if (errorElements.length > 0) {
            console.log(`❌ وجد ${errorElements.length} عنصر خطأ`);
            for (let i = 0; i < errorElements.length; i++) {
                const text = await errorElements[i].textContent();
                console.log(`   خطأ ${i + 1}: ${text}`);
            }
        } else {
            console.log('✅ لم يتم العثور على رسائل خطأ');
        }
        
        // فحص URL الحالي
        const currentUrl = page.url();
        console.log(`📍 URL الحالي: ${currentUrl}`);
        
        // فحص إذا كان النموذج لا يزال موجود
        const formExists = await page.$('form') !== null;
        console.log(`📋 النموذج موجود: ${formExists}`);
        
        // فحص إذا كان هناك رسائل نجاح
        const successElements = await page.$$('.alert-success');
        if (successElements.length > 0) {
            console.log('✅ وجد رسائل نجاح');
            for (const element of successElements) {
                const text = await element.textContent();
                console.log(`   نجاح: ${text}`);
            }
        }
        
        // لقطة شاشة
        await page.screenshot({ path: 'manual-test-result.png', fullPage: true });
        console.log('📸 تم حفظ لقطة الشاشة: manual-test-result.png');
        
        // انتظار لمشاهدة النتيجة
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'manual-test-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testFormSubmission();
