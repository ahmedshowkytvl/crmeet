import { chromium } from 'playwright';

/**
 * سكريبت محسن لاستهداف النموذج الصحيح
 */

async function testCorrectForm() {
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
        await page.fill('input#name', 'Test Account Correct');
        await page.fill('input#email', 'testuser');
        await page.fill('input#password', 'TestPass123!');
        await page.fill('input#url', 'https://test.com');
        await page.fill('textarea#notes', 'Test notes');
        
        // اختيار فئة
        await page.selectOption('select[name="category_id"]', '10');
        
        console.log('🔍 فحص النماذج الموجودة...');
        
        // فحص جميع النماذج
        const forms = await page.$$('form');
        console.log(`📋 وجد ${forms.length} نموذج في الصفحة`);
        
        for (let i = 0; i < forms.length; i++) {
            const action = await forms[i].getAttribute('action');
            const method = await forms[i].getAttribute('method');
            console.log(`   نموذج ${i + 1}: ${method} ${action}`);
        }
        
        // البحث عن النموذج الصحيح (الذي يحتوي على حقل name)
        const correctForm = await page.$('form:has(input#name)');
        if (!correctForm) {
            throw new Error('لم يتم العثور على النموذج الصحيح');
        }
        
        const correctAction = await correctForm.getAttribute('action');
        const correctMethod = await correctForm.getAttribute('method');
        console.log(`✅ النموذج الصحيح: ${correctMethod} ${correctAction}`);
        
        // البحث عن زر الإرسال الصحيح داخل النموذج الصحيح
        const correctSubmitButton = await correctForm.$('button[type="submit"]');
        if (!correctSubmitButton) {
            throw new Error('لم يتم العثور على زر الإرسال الصحيح');
        }
        
        const buttonText = await correctSubmitButton.textContent();
        console.log(`✅ زر الإرسال الصحيح: "${buttonText.trim()}"`);
        
        console.log('📤 إرسال النموذج الصحيح...');
        
        // مراقبة الاستجابة
        const [response] = await Promise.all([
            page.waitForResponse(response => 
                response.url().includes('/password-accounts') && 
                response.request().method() === 'POST'
            ),
            correctSubmitButton.click()
        ]);
        
        console.log(`📥 استجابة الخادم: ${response.status()} ${response.url()}`);
        
        // انتظار تحميل الصفحة
        await page.waitForLoadState('networkidle');
        
        const currentUrl = page.url();
        console.log(`📍 URL بعد الإرسال: ${currentUrl}`);
        
        // فحص الرسائل
        const alerts = await page.$$('.alert');
        if (alerts.length > 0) {
            console.log(`📢 وجد ${alerts.length} رسالة`);
            for (let i = 0; i < alerts.length; i++) {
                const text = await alerts[i].textContent();
                const classes = await alerts[i].getAttribute('class');
                console.log(`   ${classes.includes('success') ? '✅' : '❌'} ${text}`);
            }
        } else {
            console.log('📢 لم يتم العثور على رسائل');
        }
        
        // فحص أخطاء التحقق
        const validationErrors = await page.$$('.invalid-feedback');
        if (validationErrors.length > 0) {
            console.log(`❌ وجد ${validationErrors.length} خطأ تحقق`);
            for (let i = 0; i < validationErrors.length; i++) {
                const text = await validationErrors[i].textContent();
                console.log(`   خطأ ${i + 1}: ${text}`);
            }
        }
        
        await page.screenshot({ path: 'correct-form-result.png', fullPage: true });
        console.log('📸 تم حفظ لقطة الشاشة: correct-form-result.png');
        
        // انتظار لمشاهدة النتيجة
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'correct-form-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testCorrectForm();
