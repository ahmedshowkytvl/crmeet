import { chromium } from 'playwright';
import fs from 'fs';

/**
 * سكريبت ذكي لاختبار صفحة إنشاء حساب كلمة المرور
 * يحل مشكلة زر الإرسال الخفي في dropdown
 */

class TargetedPasswordAccountTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.errors = [];
        this.successCount = 0;
        this.attempts = 0;
        this.maxAttempts = 3;
        this.baseUrl = 'http://127.0.0.1:8000';
        this.credentials = {
            email: 'admin@company.com',
            password: 'P@ssW0rd'
        };
    }

    async init() {
        console.log('🚀 بدء اختبار ذكي لصفحة إنشاء حساب كلمة المرور...\n');
        
        this.browser = await chromium.launch({
            headless: false,
            slowMo: 500
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
            console.log('🔑 تسجيل الدخول...');
            
            await this.page.goto(`${this.baseUrl}/login`, { waitUntil: 'networkidle' });
            await this.page.waitForSelector('input[name="email"]', { timeout: 10000 });
            
            await this.page.fill('input[name="email"]', this.credentials.email);
            await this.page.fill('input[name="password"]', this.credentials.password);
            
            await this.page.click('button[type="submit"]');
            await this.page.waitForLoadState('networkidle');
            
            const currentUrl = this.page.url();
            if (currentUrl.includes('/login')) {
                throw new Error('فشل تسجيل الدخول');
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

    async findSubmitButton() {
        try {
            console.log('🔍 البحث عن زر الإرسال الصحيح...');
            
            // البحث عن جميع أزرار submit في الصفحة
            const submitButtons = await this.page.$$('button[type="submit"]');
            console.log(`   وجد ${submitButtons.length} زر submit`);
            
            for (let i = 0; i < submitButtons.length; i++) {
                const button = submitButtons[i];
                const text = await button.textContent();
                const classes = await button.getAttribute('class');
                const isVisible = await button.isVisible();
                
                console.log(`   زر ${i + 1}: "${text.trim()}" | visible: ${isVisible} | classes: ${classes}`);
                
                // البحث عن الزر الرئيسي (ليس في dropdown)
                if (isVisible && !classes?.includes('dropdown-item')) {
                    console.log(`✅ وجد زر الإرسال الرئيسي: "${text.trim()}"`);
                    return button;
                }
            }
            
            // إذا لم نجد زر مرئي، نبحث عن الزر الذي يحتوي على كلمات محددة
            const mainButtonSelectors = [
                'button[type="submit"]:has-text("Create")',
                'button[type="submit"]:has-text("إنشاء")',
                'button[type="submit"]:has-text("Save")',
                'button[type="submit"]:has-text("حفظ")',
                'button.btn-primary[type="submit"]',
                'form .btn-primary[type="submit"]'
            ];
            
            for (const selector of mainButtonSelectors) {
                try {
                    const button = await this.page.$(selector);
                    if (button && await button.isVisible()) {
                        const text = await button.textContent();
                        console.log(`✅ وجد زر الإرسال باستخدام selector: "${selector}" - النص: "${text.trim()}"`);
                        return button;
                    }
                } catch (e) {
                    continue;
                }
            }
            
            throw new Error('لم يتم العثور على زر إرسال صحيح');
            
        } catch (error) {
            console.log('❌ خطأ في البحث عن زر الإرسال:', error.message);
            throw error;
        }
    }

    async fillAndSubmitForm(attempt) {
        try {
            console.log(`\n${'═'.repeat(60)}`);
            console.log(`   محاولة ${attempt} من ${this.maxAttempts}`);
            console.log('═'.repeat(60) + '\n');
            
            const testData = {
                name: `Smart Test Account ${attempt}`,
                email: `testuser${attempt}`, // username بدلاً من email
                password: `SmartPass${attempt}!@#123`,
                url: `https://smart-example-${attempt}.com`,
                notes: `حساب اختبار ذكي رقم ${attempt}\nتم الإنشاء: ${new Date().toLocaleString('ar-EG')}\nالغرض: اختبار نظام إدارة كلمات المرور`
            };
            
            console.log('📝 ملء البيانات:');
            console.log(`   ✓ الاسم: ${testData.name}`);
            console.log(`   ✓ البريد: ${testData.email}`);
            console.log(`   ✓ كلمة المرور: ${testData.password}`);
            console.log(`   ✓ الرابط: ${testData.url}`);
            console.log(`   ✓ الملاحظات: ${testData.notes.split('\n')[0]}...`);
            console.log('');
            
            // ملء الحقول
            await this.fillField('input#name', testData.name, 'الاسم');
            await this.fillField('input#email', testData.email, 'البريد');
            await this.fillField('input#password', testData.password, 'كلمة المرور');
            await this.fillField('input#url', testData.url, 'الرابط');
            await this.fillField('textarea#notes', testData.notes, 'الملاحظات');
            
            // اختيار فئة صالحة
            await this.selectValidCategory();
            
            console.log('✅ تم ملء جميع الحقول بنجاح\n');
            
            // البحث عن زر الإرسال الصحيح
            const submitButton = await this.findSubmitButton();
            
            // النقر على زر الإرسال
            console.log('📤 إرسال النموذج...');
            await submitButton.click();
            await this.page.waitForTimeout(3000);
            
            // فحص النتيجة
            const result = await this.checkSubmissionResult();
            
            if (result.success) {
                console.log(`\n🎉 نجحت المحاولة ${attempt}!`);
                console.log(`   ${result.message}\n`);
                this.successCount++;
                await this.captureScreenshot(`success-${attempt}`);
                return true;
            } else {
                console.log(`\n⚠️  فشلت المحاولة ${attempt}`);
                console.log(`   السبب: ${result.message}\n`);
                await this.captureScreenshot(`failed-${attempt}`);
                
                // العودة إلى صفحة الإنشاء للمحاولة التالية
                if (attempt < this.maxAttempts) {
                    await this.navigateToCreatePage();
                }
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
            if (attempt < this.maxAttempts) {
                try {
                    await this.navigateToCreatePage();
                } catch (navError) {
                    console.log('❌ فشل العودة إلى صفحة الإنشاء');
                }
            }
            
            return false;
        }
    }

    async fillField(selector, value, fieldName) {
        try {
            await this.page.waitForSelector(selector, { timeout: 5000 });
            await this.page.fill(selector, '');
            await this.page.fill(selector, value);
            await this.page.waitForTimeout(300);
            
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

    async selectValidCategory() {
        try {
            console.log('🔍 البحث عن فئة صالحة...');
            
            // البحث عن حقل الفئة
            const categorySelectors = [
                'select[name="category_id"]',
                'select#category_id',
                '.form-select[name="category_id"]',
                'select.form-control[name="category_id"]'
            ];
            
            let categoryField = null;
            for (const selector of categorySelectors) {
                try {
                    categoryField = await this.page.$(selector);
                    if (categoryField) {
                        console.log(`✅ وجد حقل الفئة: ${selector}`);
                        break;
                    }
                } catch (e) {
                    continue;
                }
            }
            
            if (!categoryField) {
                console.log('⚠️  لم يتم العثور على حقل الفئة - تخطي');
                return;
            }
            
            // الحصول على جميع الخيارات المتاحة
            const options = await this.page.$$eval(`${categoryField ? 'select[name="category_id"], select#category_id' : 'select'} option`, options => 
                options.map(option => ({
                    value: option.value,
                    text: option.textContent.trim(),
                    disabled: option.disabled
                }))
            );
            
            console.log(`   وجد ${options.length} خيار في الفئة`);
            
            // البحث عن خيار صالح (ليس فارغ وليس معطل وليس 0)
            let validOption = null;
            for (const option of options) {
                if (option.value && 
                    option.value !== '' && 
                    option.value !== '0' &&
                    !option.disabled && 
                    option.text !== 'Select Category' &&
                    !option.text.includes('Select Category')) {
                    validOption = option;
                    break;
                }
            }
            
            if (validOption) {
                console.log(`✅ اختيار الفئة: "${validOption.text}" (القيمة: ${validOption.value})`);
                
                // اختيار الفئة
                await this.page.selectOption('select[name="category_id"], select#category_id', validOption.value);
                await this.page.waitForTimeout(500);
                
                // التحقق من الاختيار
                const selectedValue = await this.page.inputValue('select[name="category_id"], select#category_id');
                if (selectedValue === validOption.value) {
                    console.log('✅ تم اختيار الفئة بنجاح');
                } else {
                    console.log('⚠️  تحذير: لم يتم تأكيد اختيار الفئة');
                }
            } else {
                // إذا لم نجد فئة صالحة، نترك الحقل فارغ (null)
                console.log('⚠️  لم يتم العثور على فئة صالحة - سيتم ترك الحقل فارغ');
                await this.page.selectOption('select[name="category_id"], select#category_id', '');
                await this.page.waitForTimeout(500);
                console.log('⚠️  لم يتم العثور على فئة صالحة - سيتم استخدام القيمة الافتراضية');
                
                // محاولة إنشاء فئة جديدة
                const createCategoryLink = await this.page.$('a:has-text("Create New Category"), a:has-text("إنشاء فئة جديدة")');
                if (createCategoryLink) {
                    console.log('🔗 النقر على إنشاء فئة جديدة...');
                    await createCategoryLink.click();
                    await this.page.waitForTimeout(2000);
                    
                    // ملء بيانات الفئة الجديدة
                    await this.page.fill('input[name="name"]', 'Test Category');
                    await this.page.fill('input[name="name_ar"]', 'فئة اختبار');
                    
                    // حفظ الفئة الجديدة
                    const saveButton = await this.page.$('button:has-text("Save"), button:has-text("حفظ")');
                    if (saveButton) {
                        await saveButton.click();
                        await this.page.waitForTimeout(2000);
                        console.log('✅ تم إنشاء فئة جديدة');
                    }
                }
            }
            
        } catch (error) {
            console.log('❌ خطأ في اختيار الفئة:', error.message);
            // لا نوقف العملية إذا فشل اختيار الفئة
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
            const filename = `screenshots/smart-test-${name}-${timestamp}.png`;
            
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
                    console.log('🎉 تم النجاح! لا حاجة لمحاولات إضافية.\n');
                    break;
                } else if (i < this.maxAttempts) {
                    console.log('⏳ الانتظار 2 ثانية قبل المحاولة التالية...\n');
                    await this.page.waitForTimeout(2000);
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
        console.log('\n' + '═'.repeat(60));
        console.log('   📊 النتائج النهائية');
        console.log('═'.repeat(60));
        console.log(`✅ المحاولات الناجحة: ${this.successCount} من ${this.attempts}`);
        console.log(`❌ المحاولات الفاشلة: ${this.attempts - this.successCount}`);
        console.log(`📝 عدد الأخطاء المسجلة: ${this.errors.length}`);
        
        if (this.errors.length > 0) {
            console.log('\n🔍 تفاصيل الأخطاء:');
            this.errors.forEach((err, index) => {
                console.log(`   ${index + 1}. المحاولة ${err.attempt}: ${err.error}`);
            });
        }
        
        console.log('═'.repeat(60) + '\n');
    }

    async cleanup() {
        console.log('🧹 تنظيف وإغلاق...');
        
        if (this.browser) {
            await this.browser.close();
            console.log('✅ تم إغلاق المتصفح');
        }
        
        console.log('\n✅ انتهى الاختبار الذكي\n');
    }
}

// تشغيل الاختبار
(async () => {
    const tester = new TargetedPasswordAccountTester();
    const success = await tester.run();
    process.exit(success ? 0 : 1);
})();
