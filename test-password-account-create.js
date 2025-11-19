import { chromium } from 'playwright';
import fs from 'fs';

/**
 * سكريبت اختبار صفحة إنشاء حساب كلمة المرور
 * يقوم بإضافة وتعديل البيانات تلقائياً
 * يجمع الأخطاء ويحاول إصلاحها حتى النجاح
 */

class PasswordAccountTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.errors = [];
        this.attempts = 0;
        this.maxAttempts = 10;
        this.baseUrl = 'http://127.0.0.1:8000';
    }

    async init() {
        console.log('🚀 بدء اختبار صفحة إنشاء حساب كلمة المرور...\n');
        
        this.browser = await chromium.launch({
            headless: false,
            slowMo: 500
        });
        
        this.page = await this.browser.newPage();
        
        // التقاط الأخطاء في Console
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                this.errors.push({
                    type: 'console_error',
                    message: msg.text(),
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // التقاط أخطاء الصفحة
        this.page.on('pageerror', error => {
            this.errors.push({
                type: 'page_error',
                message: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
        });
        
        // التقاط أخطاء الطلبات
        this.page.on('requestfailed', request => {
            this.errors.push({
                type: 'request_failed',
                url: request.url(),
                failure: request.failure().errorText,
                timestamp: new Date().toISOString()
            });
        });
    }

    async login() {
        try {
            console.log('🔑 تسجيل الدخول...');
            
            await this.page.goto(`${this.baseUrl}/login`);
            await this.page.waitForLoadState('networkidle');
            
            // ملء بيانات تسجيل الدخول
            await this.page.fill('input[name="email"]', 'admin@company.com');
            await this.page.fill('input[name="password"]', 'P@ssW0rd');
            
            // النقر على زر تسجيل الدخول
            await this.page.click('button[type="submit"]');
            await this.page.waitForLoadState('networkidle');
            
            console.log('✅ تم تسجيل الدخول بنجاح\n');
            
            return true;
        } catch (error) {
            this.errors.push({
                type: 'login_error',
                message: error.message,
                timestamp: new Date().toISOString()
            });
            console.log('❌ فشل تسجيل الدخول:', error.message);
            return false;
        }
    }

    async navigateToCreatePage() {
        try {
            console.log('🔗 الانتقال إلى صفحة إنشاء حساب كلمة المرور...');
            
            await this.page.goto(`${this.baseUrl}/password-accounts/create`);
            await this.page.waitForLoadState('networkidle');
            await this.page.waitForTimeout(1000);
            
            console.log('✅ تم الانتقال إلى الصفحة\n');
            
            return true;
        } catch (error) {
            this.errors.push({
                type: 'navigation_error',
                message: error.message,
                timestamp: new Date().toISOString()
            });
            console.log('❌ فشل الانتقال:', error.message);
            return false;
        }
    }

    async checkPageElements() {
        console.log('🔍 فحص عناصر الصفحة...');
        
        const elements = {
            form: await this.page.$('form'),
            nameInput: await this.page.$('input#name'),
            emailInput: await this.page.$('input#email'),
            passwordInput: await this.page.$('input#password'),
            urlInput: await this.page.$('input#url'),
            notesTextarea: await this.page.$('textarea#notes'),
            submitButton: await this.page.$('button[type="submit"]')
        };
        
        const missing = [];
        for (const [key, element] of Object.entries(elements)) {
            if (!element) {
                missing.push(key);
                this.errors.push({
                    type: 'missing_element',
                    element: key,
                    timestamp: new Date().toISOString()
                });
            }
        }
        
        if (missing.length > 0) {
            console.log('❌ عناصر مفقودة:', missing.join(', '));
            return false;
        }
        
        console.log('✅ جميع العناصر موجودة\n');
        return true;
    }

    async fillFormData(attempt) {
        try {
            console.log(`📝 محاولة ${attempt}: ملء بيانات النموذج...`);
            
            const testData = {
                name: `Test Account ${attempt} - ${Date.now()}`,
                email: `testuser${attempt}@test.com`,
                password: `TestPassword${attempt}!@#`,
                url: `https://test-account-${attempt}.com`,
                notes: `هذا حساب اختبار رقم ${attempt} تم إنشاؤه بواسطة Playwright في ${new Date().toLocaleString('ar-EG')}`
            };
            
            // ملء الحقول
            await this.page.fill('input#name', testData.name);
            await this.page.waitForTimeout(300);
            
            await this.page.fill('input#email', testData.email);
            await this.page.waitForTimeout(300);
            
            await this.page.fill('input#password', testData.password);
            await this.page.waitForTimeout(300);
            
            await this.page.fill('input#url', testData.url);
            await this.page.waitForTimeout(300);
            
            await this.page.fill('textarea#notes', testData.notes);
            await this.page.waitForTimeout(300);
            
            console.log('✅ تم ملء البيانات:');
            console.log('   - الاسم:', testData.name);
            console.log('   - البريد:', testData.email);
            console.log('   - كلمة المرور:', testData.password);
            console.log('   - الرابط:', testData.url);
            console.log('   - الملاحظات:', testData.notes.substring(0, 50) + '...\n');
            
            return true;
        } catch (error) {
            this.errors.push({
                type: 'fill_form_error',
                message: error.message,
                timestamp: new Date().toISOString()
            });
            console.log('❌ فشل ملء النموذج:', error.message);
            return false;
        }
    }

    async submitForm() {
        try {
            console.log('📤 إرسال النموذج...');
            
            // النقر على زر الإرسال
            await this.page.click('button[type="submit"]');
            
            // الانتظار للاستجابة
            await this.page.waitForTimeout(2000);
            
            // فحص وجود رسائل نجاح أو خطأ
            const successMessage = await this.page.$('.alert-success');
            const errorMessage = await this.page.$('.alert-danger');
            const validationErrors = await this.page.$$('.invalid-feedback');
            
            if (successMessage) {
                const text = await successMessage.textContent();
                console.log('✅ نجح الإرسال:', text);
                return { success: true, message: text };
            }
            
            if (errorMessage) {
                const text = await errorMessage.textContent();
                console.log('❌ فشل الإرسال:', text);
                this.errors.push({
                    type: 'submission_error',
                    message: text,
                    timestamp: new Date().toISOString()
                });
                return { success: false, message: text };
            }
            
            if (validationErrors.length > 0) {
                const errors = [];
                for (const error of validationErrors) {
                    const text = await error.textContent();
                    if (text.trim()) {
                        errors.push(text.trim());
                    }
                }
                console.log('❌ أخطاء التحقق:', errors.join(', '));
                this.errors.push({
                    type: 'validation_errors',
                    errors: errors,
                    timestamp: new Date().toISOString()
                });
                return { success: false, message: errors.join(', ') };
            }
            
            // فحص إذا تم التوجيه لصفحة أخرى
            const currentUrl = this.page.url();
            if (currentUrl !== `${this.baseUrl}/password-accounts/create`) {
                console.log('✅ تم التوجيه إلى:', currentUrl);
                return { success: true, message: 'تم التوجيه بنجاح' };
            }
            
            console.log('⚠️  لا توجد استجابة واضحة');
            return { success: false, message: 'لا توجد استجابة واضحة' };
            
        } catch (error) {
            this.errors.push({
                type: 'submit_error',
                message: error.message,
                timestamp: new Date().toISOString()
            });
            console.log('❌ خطأ في الإرسال:', error.message);
            return { success: false, message: error.message };
        }
    }

    async captureScreenshot(name) {
        try {
            const filename = `screenshot-${name}-${Date.now()}.png`;
            await this.page.screenshot({ path: filename, fullPage: true });
            console.log(`📸 تم حفظ لقطة الشاشة: ${filename}`);
        } catch (error) {
            console.log('⚠️  فشل حفظ لقطة الشاشة:', error.message);
        }
    }

    async analyzeErrors() {
        if (this.errors.length === 0) {
            return null;
        }
        
        console.log('\n🔍 تحليل الأخطاء...');
        console.log(`   - عدد الأخطاء: ${this.errors.length}`);
        
        const errorTypes = {};
        this.errors.forEach(error => {
            errorTypes[error.type] = (errorTypes[error.type] || 0) + 1;
        });
        
        console.log('   - أنواع الأخطاء:');
        for (const [type, count] of Object.entries(errorTypes)) {
            console.log(`     * ${type}: ${count}`);
        }
        
        // حفظ الأخطاء في ملف
        const errorReport = {
            timestamp: new Date().toISOString(),
            totalErrors: this.errors.length,
            errorTypes: errorTypes,
            errors: this.errors
        };
        
        const filename = `error-report-${Date.now()}.json`;
        fs.writeFileSync(filename, JSON.stringify(errorReport, null, 2));
        console.log(`   - تم حفظ تقرير الأخطاء: ${filename}\n`);
        
        return errorReport;
    }

    async attemptFix() {
        console.log('🔧 محاولة إصلاح الأخطاء...\n');
        
        // تحليل الأخطاء الشائعة ومحاولة إصلاحها
        const hasValidationErrors = this.errors.some(e => e.type === 'validation_errors');
        const hasMissingElements = this.errors.some(e => e.type === 'missing_element');
        const hasNetworkErrors = this.errors.some(e => e.type === 'request_failed');
        
        if (hasValidationErrors) {
            console.log('   - تم اكتشاف أخطاء تحقق، سيتم استخدام بيانات مختلفة في المحاولة القادمة');
        }
        
        if (hasMissingElements) {
            console.log('   - تم اكتشاف عناصر مفقودة، سيتم إعادة تحميل الصفحة');
            await this.navigateToCreatePage();
        }
        
        if (hasNetworkErrors) {
            console.log('   - تم اكتشاف أخطاء شبكة، سيتم الانتظار قبل المحاولة القادمة');
            await this.page.waitForTimeout(2000);
        }
        
        // مسح الأخطاء للمحاولة القادمة
        this.errors = [];
    }

    async run() {
        try {
            await this.init();
            
            // تسجيل الدخول
            const loginSuccess = await this.login();
            if (!loginSuccess) {
                console.log('❌ فشل تسجيل الدخول، الخروج...');
                await this.cleanup();
                return false;
            }
            
            // الانتقال إلى صفحة الإنشاء
            const navSuccess = await this.navigateToCreatePage();
            if (!navSuccess) {
                console.log('❌ فشل الانتقال، الخروج...');
                await this.cleanup();
                return false;
            }
            
            // فحص العناصر
            const elementsOk = await this.checkPageElements();
            if (!elementsOk) {
                await this.captureScreenshot('missing-elements');
                await this.analyzeErrors();
            }
            
            // محاولات الإضافة والتعديل
            while (this.attempts < this.maxAttempts) {
                this.attempts++;
                console.log(`\n${'='.repeat(60)}`);
                console.log(`محاولة ${this.attempts} من ${this.maxAttempts}`);
                console.log('='.repeat(60) + '\n');
                
                // ملء النموذج
                const fillSuccess = await this.fillFormData(this.attempts);
                if (!fillSuccess) {
                    await this.captureScreenshot(`fill-error-${this.attempts}`);
                    await this.analyzeErrors();
                    await this.attemptFix();
                    continue;
                }
                
                // إرسال النموذج
                const submitResult = await this.submitForm();
                
                if (submitResult.success) {
                    console.log('\n🎉 نجحت العملية!');
                    await this.captureScreenshot('success');
                    await this.analyzeErrors();
                    break;
                } else {
                    console.log(`\n⚠️  المحاولة ${this.attempts} فشلت: ${submitResult.message}`);
                    await this.captureScreenshot(`attempt-${this.attempts}`);
                    await this.analyzeErrors();
                    await this.attemptFix();
                    
                    // الانتظار قبل المحاولة التالية
                    console.log('⏳ الانتظار 3 ثوانٍ قبل المحاولة التالية...\n');
                    await this.page.waitForTimeout(3000);
                    
                    // إعادة الانتقال إلى صفحة الإنشاء
                    await this.navigateToCreatePage();
                }
            }
            
            if (this.attempts >= this.maxAttempts) {
                console.log(`\n❌ فشلت جميع المحاولات (${this.maxAttempts})`);
                return false;
            }
            
            return true;
            
        } catch (error) {
            console.error('❌ خطأ عام:', error);
            this.errors.push({
                type: 'general_error',
                message: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
            await this.captureScreenshot('general-error');
            await this.analyzeErrors();
            return false;
        } finally {
            await this.cleanup();
        }
    }

    async cleanup() {
        console.log('\n🧹 تنظيف...');
        
        // طباعة الملخص النهائي
        console.log('\n📊 ملخص الاختبار:');
        console.log(`   - عدد المحاولات: ${this.attempts}`);
        console.log(`   - عدد الأخطاء: ${this.errors.length}`);
        
        if (this.browser) {
            await this.browser.close();
            console.log('✅ تم إغلاق المتصفح');
        }
        
        console.log('\n✅ انتهى الاختبار\n');
    }
}

// تشغيل الاختبار
(async () => {
    const tester = new PasswordAccountTester();
    const success = await tester.run();
    process.exit(success ? 0 : 1);
})();

