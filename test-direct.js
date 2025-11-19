import { chromium } from 'playwright';

/**
 * سكريبت بسيط لاختبار النموذج بدون JavaScript validation
 */

async function testFormDirectly() {
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
        await page.fill('input#name', 'Test Account Direct');
        await page.fill('input#email', 'testuser');
        await page.fill('input#password', 'TestPass123!');
        await page.fill('input#url', 'https://test.com');
        await page.fill('textarea#notes', 'Test notes');
        
        // اختيار فئة
        await page.selectOption('select[name="category_id"]', '10');
        
        console.log('🔍 فحص النموذج قبل الإرسال...');
        
        // فحص إذا كان النموذج صحيح
        const form = await page.$('form');
        if (!form) {
            throw new Error('النموذج غير موجود');
        }
        
        const formAction = await form.getAttribute('action');
        const formMethod = await form.getAttribute('method');
        console.log(`📋 إجراء النموذج: ${formMethod} ${formAction}`);
        
        // فحص زر الإرسال
        const submitButton = await page.$('button[type="submit"]');
        if (!submitButton) {
            throw new Error('زر الإرسال غير موجود');
        }
        
        const buttonText = await submitButton.textContent();
        console.log(`🔘 زر الإرسال: "${buttonText.trim()}"`);
        
        // فحص إذا كان الزر معطل
        const isDisabled = await submitButton.isDisabled();
        console.log(`🔘 الزر معطل: ${isDisabled}`);
        
        // فحص الحقول المطلوبة
        const nameField = await page.$('input#name');
        const passwordField = await page.$('input#password');
        
        const nameValue = await nameField?.inputValue();
        const passwordValue = await passwordField?.inputValue();
        
        console.log(`📝 حقل الاسم: "${nameValue}"`);
        console.log(`📝 حقل كلمة المرور: "${passwordValue}"`);
        
        // فحص إذا كانت الحقول صحيحة
        const nameHasError = await page.$('input#name.is-invalid') !== null;
        const passwordHasError = await page.$('input#password.is-invalid') !== null;
        
        console.log(`❌ حقل الاسم به خطأ: ${nameHasError}`);
        console.log(`❌ حقل كلمة المرور به خطأ: ${passwordHasError}`);
        
        if (nameHasError || passwordHasError) {
            console.log('❌ يوجد أخطاء في الحقول المطلوبة');
            
            // فحص رسائل الخطأ
            const nameError = await page.$('.invalid-feedback');
            if (nameError) {
                const errorText = await nameError.textContent();
                console.log(`❌ رسالة الخطأ: ${errorText}`);
            }
            
            await page.screenshot({ path: 'form-errors.png', fullPage: true });
            console.log('📸 تم حفظ لقطة الشاشة للأخطاء: form-errors.png');
            return;
        }
        
        console.log('📤 محاولة إرسال النموذج...');
        
        // محاولة إرسال النموذج باستخدام JavaScript مباشرة
        const formSubmitted = await page.evaluate(() => {
            const form = document.querySelector('form');
            if (form) {
                form.submit();
                return true;
            }
            return false;
        });
        
        if (formSubmitted) {
            console.log('✅ تم إرسال النموذج باستخدام JavaScript');
            
            // انتظار التوجيه
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
            }
            
        } else {
            console.log('❌ فشل إرسال النموذج');
        }
        
        await page.screenshot({ path: 'direct-test-result.png', fullPage: true });
        console.log('📸 تم حفظ لقطة الشاشة: direct-test-result.png');
        
        // انتظار لمشاهدة النتيجة
        await page.waitForTimeout(3000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'direct-test-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testFormDirectly();
