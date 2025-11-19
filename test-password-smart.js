import { chromium } from 'playwright';
import fs from 'fs';

/**
 * سكريبت اختبار متقدم لصفحة إنشاء حساب كلمة المرور
 * يقوم بإضافة وتعديل البيانات تلقائياً مع معالجة ذكية للأخطاء
 */

class SmartPasswordAccountTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.errors = [];
        this.successCount = 0;
        this.attempts = 0;
        this.maxAttempts = 5;
        this.baseUrl = 'http://127.0.0.1:8000';
        this.credentials = {
            email: 'admin@company.com',
            password: 'P@ssW0rd'
        };
    }

    async init() {
        console.log('🚀 بدء اختبار متقدم لصفحة إنشاء حساب كلمة المرور...\n');
        
        this.browser = await chromium.launch({
            headless: false,
            slowMo: 300
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1280, height: 720 },
            locale: 'ar-EG'
        });
        
        this.page = await context.newPage();
        
        // التقاط جميع أنواع الأخطاء
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log('❌ Console Error:', msg.text());
            }
        });
    }

    async login() {
        try {
            console.log('🔑 تسجيل الدخول باستخدام:', this.credentials.email);
            
            await this.page.goto(`${this.baseUrl}/login`, { waitUntil: 'networkidle' });
            
            // الانتظار للتأكد من تحميل الصفحة
            await this.page.waitForSelector('input[name="email"]', { timeout: 10000 });
            
            // ملء بيانات تسجيل الدخول
            await this.page.fill('input[name="email"]', this.credentials.email);
            await this.page.fill('input[name="password"]', this.credentials.password);
            
            // النقر على زر تسجيل الدخول
            await this.page.click('button[type="submit"]');
            await this.page.waitForLoadState('networkidle');
            
            // التحقق من نجاح تسجيل الدخول
            const currentUrl = this.page.url();
            if (currentUrl.includes('/login')) {
                // فحص رسائل الخطأ
                const errorMsg = await this.page.$('.alert-danger');
                if (errorMsg) {
                    const errorText = await errorMsg.textContent();
                    throw new Error(`فشل تسجيل الدخول: ${errorText}`);
                }
                throw new Error('فشل تسجيل الدخول - لا يزال في صفحة تسجيل الدخول');
            }
            
            console.log('✅ تم تسجيل الدخول بنجاح\n');
            await this.page.waitForTimeout(1000);
            
            return true;
        } catch (error) {
            console.log('❌ خطأ في تسجيل الدخول:', error.message);
            await this.captureScreenshot('login-error');
            return false;
        }
    }

    async navigateToCreatePage() {
        try {
            console.log('🔗 الانتقال إلى صفحة إنشاء حساب كلمة المرور...');
            
            await this.page.goto(`${this.baseUrl}/password-accounts/create`, { waitUntil: 'networkidle' });
            
            // الانتظار لتحميل النموذج الرئيسي (يحتوي على حقل name)
            await this.page.waitForSelector('input#name', { timeout: 10000 });
            
            console.log('✅ تم الانتقال إلى الصفحة بنجاح\n');
            await this.page.waitForTimeout(1000);
            
            return true;
        } catch (error) {
            console.log('❌ خطأ في الانتقال:', error.message);
            await this.captureScreenshot('navigation-error');
            return false;
        }
    }

    async fillAndSubmitForm(attempt) {
        try {
            console.log(`\n${'═'.repeat(70)}`);
            console.log(`   محاولة ${attempt} من ${this.maxAttempts}`);
            console.log('═'.repeat(70) + '\n');
            
            const testData = {
                name: `Test Password Account ${attempt}`,
                email: `test.account${attempt}@example.com`,
                password: `SecurePass${attempt}!@#123`,
                url: `https://example-${attempt}.com/login`,
                notes: `
حساب اختبار تلقائي رقم ${attempt}
تم الإنشاء: ${new Date().toLocaleString('ar-EG')}
الغرض: اختبار نظام إدارة كلمات المرور
الحالة: نشط
                `.trim()
            };
            
            console.log('📝 ملء البيانات:');
            console.log(`   ✓ الاسم: ${testData.name}`);
            console.log(`   ✓ البريد: ${testData.email}`);
            console.log(`   ✓ كلمة المرور: ${testData.password}`);
            console.log(`   ✓ الرابط: ${testData.url}`);
            console.log(`   ✓ الملاحظات: ${testData.notes.substring(0, 40)}...`);
            console.log('');
            
            // ملء الحقول واحداً تلو الآخر مع التحقق
            await this.fillField('input#name', testData.name, 'الاسم');
            await this.fillField('input#email', testData.email, 'البريد');
            await this.fillField('input#password', testData.password, 'كلمة المرور');
            await this.fillField('input#url', testData.url, 'الرابط');
            await this.fillField('textarea#notes', testData.notes, 'الملاحظات');
            
            console.log('✅ تم ملء جميع الحقول بنجاح\n');
            
            // إرسال النموذج
            console.log('📤 إرسال النموذج...');
            
            // استخدام multiple selectors للعثور على زر الإرسال
            const submitSelectors = [
                'button[type="submit"]:has-text("Create Account")',
                'button[type="submit"]:has-text("إنشاء")',
                'button.btn-primary[type="submit"]',
                'form button[type="submit"]:visible'
            ];
            
            let clicked = false;
            for (const selector of submitSelectors) {
                try {
                    await this.page.click(selector, { timeout: 3000 });
                    clicked = true;
                    console.log(`✅ تم النقر على الزر باستخدام: ${selector}`);
                    break;
                } catch (e) {
                    continue;
                }
            }
            
            if (!clicked) {
                // محاولة أخيرة: استخدام JavaScript
                const result = await this.page.evaluate(() => {
                    const buttons = document.querySelectorAll('button[type="submit"]');
                    for (const btn of buttons) {
                        if (btn.offsetParent !== null) { // is visible
                            btn.click();
                            return true;
                        }
                    }
                    return false;
                });
                
                if (!result) {
                    throw new Error('لم يتم العثور على زر إرسال مرئي');
                }
                console.log('✅ تم النقر على الزر باستخدام JavaScript');
            }
            
            await this.page.waitForTimeout(2000);
            
            // فحص النتيجة
            const result = await this.checkSubmissionResult();
            
            if (result.success) {
                console.log(`\n✅ نجحت المحاولة ${attempt}!`);
                console.log(`   ${result.message}\n`);
                this.successCount++;
                await this.captureScreenshot(`success-${attempt}`);
                return true;
            } else {
                console.log(`\n⚠️  فشلت المحاولة ${attempt}`);
                console.log(`   السبب: ${result.message}\n`);
                await this.captureScreenshot(`failed-${attempt}`);
                
                // العودة إلى صفحة الإنشاء للمحاولة التالية
                await this.navigateToCreatePage();
                return false;
            }
            
        } catch (error) {
            console.log(`\n❌ خطأ في المحاولة ${attempt}:`, error.message);
            await this.captureScreenshot(`error-${attempt}`);
            this.errors.push({
                attempt: attempt,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            
            // محاولة العودة إلى صفحة الإنشاء
            try {
                await this.navigateToCreatePage();
            } catch (navError) {
                console.log('❌ فشل العودة إلى صفحة الإنشاء');
            }
            
            return false;
        }
    }

    async fillField(selector, value, fieldName) {
        try {
            await this.page.waitForSelector(selector, { timeout: 5000 });
            await this.page.fill(selector, ''); // مسح القيمة القديمة
            await this.page.fill(selector, value);
            await this.page.waitForTimeout(200);
            
            // التحقق من القيمة
            const actualValue = await this.page.inputValue(selector);
            if (actualValue !== value) {
                console.log(`⚠️  تحذير: قيمة ${fieldName} غير متطابقة`);
            }
            
            return true;
        } catch (error) {
            console.log(`❌ فشل ملء ${fieldName}:`, error.message);
            throw error;
        }
    }

    async checkSubmissionResult() {
        try {
            // فحص رسائل النجاح
            const successAlert = await this.page.$('.alert-success');
            if (successAlert) {
                const text = await successAlert.textContent();
                return { success: true, message: text.trim() };
            }
            
            // فحص رسائل الخطأ
            const errorAlert = await this.page.$('.alert-danger');
            if (errorAlert) {
                const text = await errorAlert.textContent();
                return { success: false, message: text.trim() };
            }
            
            // فحص أخطاء التحقق
            const validationErrors = await this.page.$$('.invalid-feedback:visible');
            if (validationErrors.length > 0) {
                const errors = [];
                for (const error of validationErrors) {
                    const text = await error.textContent();
                    if (text.trim()) errors.push(text.trim());
                }
                return { success: false, message: `أخطاء التحقق: ${errors.join(', ')}` };
            }
            
            // فحص التوجيه
            const currentUrl = this.page.url();
            if (!currentUrl.includes('/create')) {
                return { success: true, message: `تم التوجيه إلى: ${currentUrl}` };
            }
            
            return { success: false, message: 'لا توجد استجابة واضحة' };
            
        } catch (error) {
            return { success: false, message: `خطأ في فحص النتيجة: ${error.message}` };
        }
    }

    async captureScreenshot(name) {
        try {
            const timestamp = Date.now();
            const filename = `screenshots/test-${name}-${timestamp}.png`;
            
            // التأكد من وجود مجلد screenshots
            if (!fs.existsSync('screenshots')) {
                fs.mkdirSync('screenshots');
            }
            
            await this.page.screenshot({ 
                path: filename, 
                fullPage: true 
            });
            
            console.log(`📸 لقطة شاشة: ${filename}`);
        } catch (error) {
            console.log('⚠️  فشل حفظ لقطة الشاشة:', error.message);
        }
    }

    async run() {
        try {
            await this.init();
            
            // تسجيل الدخول
            const loginSuccess = await this.login();
            if (!loginSuccess) {
                console.log('\n❌ فشل تسجيل الدخول - الخروج');
                return false;
            }
            
            // الانتقال إلى صفحة الإنشاء
            const navSuccess = await this.navigateToCreatePage();
            if (!navSuccess) {
                console.log('\n❌ فشل الانتقال إلى صفحة الإنشاء - الخروج');
                return false;
            }
            
            // تنفيذ المحاولات
            for (let i = 1; i <= this.maxAttempts; i++) {
                this.attempts = i;
                const success = await this.fillAndSubmitForm(i);
                
                if (success) {
                    console.log('⏳ الانتظار 2 ثانية قبل المحاولة التالية...\n');
                    await this.page.waitForTimeout(2000);
                } else {
                    console.log('⏳ الانتظار 3 ثوانٍ قبل إعادة المحاولة...\n');
                    await this.page.waitForTimeout(3000);
                }
            }
            
            // عرض النتائج النهائية
            this.displayResults();
            
            return this.successCount > 0;
            
        } catch (error) {
            console.error('\n❌ خطأ عام:', error.message);
            await this.captureScreenshot('general-error');
            return false;
        } finally {
            await this.cleanup();
        }
    }

    displayResults() {
        console.log('\n' + '═'.repeat(70));
        console.log('   📊 النتائج النهائية');
        console.log('═'.repeat(70));
        console.log(`✅ المحاولات الناجحة: ${this.successCount} من ${this.attempts}`);
        console.log(`❌ المحاولات الفاشلة: ${this.attempts - this.successCount}`);
        console.log(`📝 عدد الأخطاء المسجلة: ${this.errors.length}`);
        
        if (this.errors.length > 0) {
            console.log('\n🔍 تفاصيل الأخطاء:');
            this.errors.forEach((err, index) => {
                console.log(`   ${index + 1}. المحاولة ${err.attempt}: ${err.error}`);
            });
            
            // حفظ الأخطاء
            const errorReport = {
                timestamp: new Date().toISOString(),
                totalAttempts: this.attempts,
                successCount: this.successCount,
                failureCount: this.attempts - this.successCount,
                errors: this.errors
            };
            
            fs.writeFileSync(
                'error-report-final.json',
                JSON.stringify(errorReport, null, 2)
            );
            console.log('\n📄 تم حفظ تقرير الأخطاء: error-report-final.json');
        }
        
        console.log('═'.repeat(70) + '\n');
    }

    async cleanup() {
        console.log('🧹 تنظيف وإغلاق...');
        
        if (this.browser) {
            await this.browser.close();
            console.log('✅ تم إغلاق المتصفح');
        }
        
        console.log('\n✅ انتهى الاختبار\n');
    }
}

// تشغيل الاختبار
(async () => {
    const tester = new SmartPasswordAccountTester();
    const success = await tester.run();
    process.exit(success ? 0 : 1);
})();

