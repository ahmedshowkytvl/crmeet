import { chromium } from 'playwright';
import fs from 'fs';

/**
 * سكريبت مراقبة متقدم لصفحة إنشاء حساب كلمة المرور
 * يراقب جميع الأحداث والشبكة واللوجز بعناية فائقة
 */

class DetailedPasswordAccountTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.logs = [];
        this.networkLogs = [];
        this.errors = [];
        this.successCount = 0;
        this.attempts = 0;
        this.maxAttempts = 2;
        this.baseUrl = 'http://127.0.0.1:8000';
        this.credentials = {
            email: 'admin@company.com',
            password: 'P@ssW0rd'
        };
    }

    async init() {
        console.log('🚀 بدء مراقبة مفصلة لصفحة إنشاء حساب كلمة المرور...\n');
        
        this.browser = await chromium.launch({
            headless: false,
            slowMo: 1000, // أبطأ لمراقبة أفضل
            devtools: true // فتح أدوات المطور
        });
        
        const context = await this.browser.newContext({
            viewport: { width: 1280, height: 720 },
            locale: 'ar-EG'
        });
        
        this.page = await context.newPage();
        
        // مراقبة جميع أنواع الأحداث
        this.setupEventListeners();
        
        console.log('✅ تم إعداد المراقبة التفصيلية\n');
    }

    setupEventListeners() {
        // مراقبة Console
        this.page.on('console', msg => {
            const logEntry = {
                type: 'console',
                level: msg.type(),
                message: msg.text(),
                location: msg.location(),
                timestamp: new Date().toISOString()
            };
            this.logs.push(logEntry);
            console.log(`🔍 Console [${msg.type()}]:`, msg.text());
        });

        // مراقبة Network
        this.page.on('request', request => {
            const networkEntry = {
                type: 'request',
                method: request.method(),
                url: request.url(),
                headers: request.headers(),
                timestamp: new Date().toISOString()
            };
            this.networkLogs.push(networkEntry);
            console.log(`📤 Request: ${request.method()} ${request.url()}`);
        });

        this.page.on('response', response => {
            const networkEntry = {
                type: 'response',
                status: response.status(),
                url: response.url(),
                headers: response.headers(),
                timestamp: new Date().toISOString()
            };
            this.networkLogs.push(networkEntry);
            console.log(`📥 Response: ${response.status()} ${response.url()}`);
        });

        this.page.on('requestfailed', request => {
            const errorEntry = {
                type: 'network_error',
                url: request.url(),
                error: request.failure()?.errorText,
                timestamp: new Date().toISOString()
            };
            this.errors.push(errorEntry);
            console.log(`❌ Network Error: ${request.failure()?.errorText} - ${request.url()}`);
        });

        // مراقبة Page Errors
        this.page.on('pageerror', error => {
            const errorEntry = {
                type: 'page_error',
                message: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            };
            this.errors.push(errorEntry);
            console.log(`❌ Page Error:`, error.message);
        });

        // مراقبة DOM Events
        this.page.on('domcontentloaded', () => {
            console.log('📄 DOM Content Loaded');
        });

        this.page.on('load', () => {
            console.log('🔄 Page Load Complete');
        });

        this.page.on('framenavigated', frame => {
            console.log(`🔗 Frame Navigated: ${frame.url()}`);
        });
    }

    async login() {
        try {
            console.log('🔑 تسجيل الدخول...');
            
            await this.page.goto(`${this.baseUrl}/login`, { waitUntil: 'networkidle' });
            await this.page.waitForSelector('input[name="email"]', { timeout: 10000 });
            
            console.log('📝 ملء بيانات تسجيل الدخول...');
            await this.page.fill('input[name="email"]', this.credentials.email);
            await this.page.fill('input[name="password"]', this.credentials.password);
            
            console.log('📤 إرسال نموذج تسجيل الدخول...');
            await this.page.click('button[type="submit"]');
            
            // مراقبة التوجيه
            await this.page.waitForLoadState('networkidle');
            
            const currentUrl = this.page.url();
            console.log(`📍 URL الحالي بعد تسجيل الدخول: ${currentUrl}`);
            
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
            await this.page.waitForTimeout(2000);
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
            
            console.log('✅ تم الانتقال إلى الصفحة بنجاح');
            console.log(`📍 URL الحالي: ${this.page.url()}\n`);
            await this.page.waitForTimeout(2000);
            
            return true;
        } catch (error) {
            console.log('❌ خطأ في الانتقال:', error.message);
            await this.captureScreenshot('navigation-error');
            return false;
        }
    }

    async fillAndSubmitForm(attempt) {
        try {
            console.log(`\n${'═'.repeat(80)}`);
            console.log(`   محاولة ${attempt} من ${this.maxAttempts} - مراقبة مفصلة`);
            console.log('═'.repeat(80) + '\n');
            
            const testData = {
                name: `Detailed Test Account ${attempt}`,
                email: `testuser${attempt}`,
                password: `TestPass${attempt}!@#123`,
                url: `https://test-example-${attempt}.com`,
                notes: `حساب اختبار مفصل رقم ${attempt}\nتم الإنشاء: ${new Date().toLocaleString('ar-EG')}\nالغرض: مراقبة تفصيلية لعملية الإنشاء`
            };
            
            console.log('📝 ملء البيانات:');
            console.log(`   ✓ الاسم: ${testData.name}`);
            console.log(`   ✓ البريد/المستخدم: ${testData.email}`);
            console.log(`   ✓ كلمة المرور: ${testData.password}`);
            console.log(`   ✓ الرابط: ${testData.url}`);
            console.log(`   ✓ الملاحظات: ${testData.notes.split('\n')[0]}...`);
            console.log('');
            
            // ملء الحقول مع مراقبة كل خطوة
            await this.fillFieldWithMonitoring('input#name', testData.name, 'الاسم');
            await this.fillFieldWithMonitoring('input#email', testData.email, 'البريد/المستخدم');
            await this.fillFieldWithMonitoring('input#password', testData.password, 'كلمة المرور');
            await this.fillFieldWithMonitoring('input#url', testData.url, 'الرابط');
            await this.fillFieldWithMonitoring('textarea#notes', testData.notes, 'الملاحظات');
            
            // اختيار الفئة
            await this.selectCategoryWithMonitoring();
            
            console.log('✅ تم ملء جميع الحقول بنجاح\n');
            
            // البحث عن زر الإرسال
            console.log('🔍 البحث عن زر الإرسال...');
            const submitButton = await this.page.$('button[type="submit"].btn-primary');
            if (!submitButton) {
                throw new Error('لم يتم العثور على زر الإرسال');
            }
            
            const buttonText = await submitButton.textContent();
            console.log(`✅ وجد زر الإرسال: "${buttonText.trim()}"\n`);
            
            // مراقبة النموذج قبل الإرسال
            console.log('🔍 مراقبة النموذج قبل الإرسال...');
            await this.monitorFormBeforeSubmit();
            
            // إرسال النموذج مع مراقبة دقيقة
            console.log('📤 إرسال النموذج مع مراقبة مفصلة...');
            await this.submitFormWithMonitoring(submitButton);
            
            // مراقبة النتيجة
            console.log('🔍 مراقبة النتيجة...');
            const result = await this.monitorSubmissionResult();
            
            return result;
            
        } catch (error) {
            console.log(`\n❌ خطأ في المحاولة ${attempt}:`, error.message);
            await this.captureScreenshot(`error-${attempt}`);
            this.errors.push({
                attempt: attempt,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            
            return { success: false, message: error.message };
        }
    }

    async fillFieldWithMonitoring(selector, value, fieldName) {
        try {
            console.log(`   📝 ملء ${fieldName}...`);
            await this.page.waitForSelector(selector, { timeout: 5000 });
            
            // مسح القيمة القديمة
            await this.page.fill(selector, '');
            await this.page.waitForTimeout(300);
            
            // ملء القيمة الجديدة
            await this.page.fill(selector, value);
            await this.page.waitForTimeout(500);
            
            // التحقق من القيمة
            const actualValue = await this.page.inputValue(selector);
            if (actualValue === value) {
                console.log(`   ✅ تم ملء ${fieldName} بنجاح: "${value}"`);
            } else {
                console.log(`   ⚠️  تحذير: قيمة ${fieldName} غير متطابقة. المتوقع: "${value}", الفعلي: "${actualValue}"`);
            }
            
            return true;
        } catch (error) {
            console.log(`   ❌ فشل ملء ${fieldName}:`, error.message);
            throw error;
        }
    }

    async selectCategoryWithMonitoring() {
        try {
            console.log('   🔍 اختيار الفئة...');
            
            // البحث عن فئة صالحة
            const options = await this.page.$$eval('select[name="category_id"] option', options => 
                options.map(option => ({
                    value: option.value,
                    text: option.textContent.trim(),
                    disabled: option.disabled
                }))
            );
            
            let validOption = null;
            for (const option of options) {
                if (option.value && option.value !== '' && option.value !== '0' && !option.disabled) {
                    validOption = option;
                    break;
                }
            }
            
            if (validOption) {
                console.log(`   ✅ اختيار الفئة: "${validOption.text}" (القيمة: ${validOption.value})`);
                await this.page.selectOption('select[name="category_id"]', validOption.value);
                await this.page.waitForTimeout(500);
                
                // التحقق من الاختيار
                const selectedValue = await this.page.inputValue('select[name="category_id"]');
                if (selectedValue === validOption.value) {
                    console.log('   ✅ تم اختيار الفئة بنجاح');
                } else {
                    console.log('   ⚠️  تحذير: لم يتم تأكيد اختيار الفئة');
                }
            } else {
                console.log('   ⚠️  لم يتم العثور على فئة صالحة - ترك الحقل فارغ');
                await this.page.selectOption('select[name="category_id"]', '');
            }
            
        } catch (error) {
            console.log('   ❌ خطأ في اختيار الفئة:', error.message);
        }
    }

    async monitorFormBeforeSubmit() {
        try {
            // فحص حالة النموذج
            const form = await this.page.$('form');
            if (!form) {
                console.log('   ❌ لم يتم العثور على النموذج');
                return;
            }
            
            // فحص جميع الحقول المطلوبة
            const requiredFields = ['name', 'password'];
            for (const field of requiredFields) {
                const input = await this.page.$(`input[name="${field}"]`);
                if (input) {
                    const value = await input.inputValue();
                    const hasError = await this.page.$(`input[name="${field}"].is-invalid`);
                    console.log(`   📋 حقل ${field}: "${value}" ${hasError ? '(خطأ)' : '(صحيح)'}`);
                }
            }
            
            // فحص الفئة
            const categoryValue = await this.page.inputValue('select[name="category_id"]');
            console.log(`   📋 الفئة المختارة: "${categoryValue}"`);
            
        } catch (error) {
            console.log('   ❌ خطأ في مراقبة النموذج:', error.message);
        }
    }

    async submitFormWithMonitoring(submitButton) {
        try {
            // مراقبة التوجيه
            const [response] = await Promise.all([
                this.page.waitForResponse(response => 
                    response.url().includes('/password-accounts') && 
                    response.request().method() === 'POST'
                ),
                submitButton.click()
            ]);
            
            console.log(`   📥 استجابة الخادم: ${response.status()} ${response.url()}`);
            
            // انتظار تحميل الصفحة
            await this.page.waitForLoadState('networkidle');
            await this.page.waitForTimeout(3000);
            
            console.log(`   📍 URL بعد الإرسال: ${this.page.url()}`);
            
        } catch (error) {
            console.log('   ❌ خطأ في إرسال النموذج:', error.message);
            throw error;
        }
    }

    async monitorSubmissionResult() {
        try {
            console.log('   🔍 فحص رسائل النجاح...');
            
            // فحص رسائل النجاح
            const successAlert = await this.page.$('.alert-success');
            if (successAlert) {
                const text = await successAlert.textContent();
                console.log(`   ✅ رسالة نجاح: "${text.trim()}"`);
                return { success: true, message: text.trim() };
            }
            
            console.log('   🔍 فحص رسائل الخطأ...');
            
            // فحص رسائل الخطأ
            const errorAlert = await this.page.$('.alert-danger');
            if (errorAlert) {
                const text = await errorAlert.textContent();
                console.log(`   ❌ رسالة خطأ: "${text.trim()}"`);
                return { success: false, message: text.trim() };
            }
            
            console.log('   🔍 فحص أخطاء التحقق...');
            
            // فحص أخطاء التحقق
            const validationErrors = await this.page.$$('.invalid-feedback:visible');
            if (validationErrors.length > 0) {
                const errors = [];
                for (const error of validationErrors) {
                    const text = await error.textContent();
                    if (text.trim()) {
                        errors.push(text.trim());
                        console.log(`   ❌ خطأ تحقق: "${text.trim()}"`);
                    }
                }
                return { success: false, message: `أخطاء التحقق: ${errors.join(', ')}` };
            }
            
            console.log('   🔍 فحص التوجيه...');
            
            // فحص التوجيه
            const currentUrl = this.page.url();
            if (!currentUrl.includes('/create')) {
                console.log(`   ✅ تم التوجيه إلى: ${currentUrl}`);
                return { success: true, message: `تم التوجيه إلى: ${currentUrl}` };
            }
            
            console.log('   ⚠️  لا توجد استجابة واضحة');
            return { success: false, message: 'لا توجد استجابة واضحة' };
            
        } catch (error) {
            console.log('   ❌ خطأ في فحص النتيجة:', error.message);
            return { success: false, message: `خطأ في فحص النتيجة: ${error.message}` };
        }
    }

    async captureScreenshot(name) {
        try {
            const timestamp = Date.now();
            const filename = `screenshots/detailed-${name}-${timestamp}.png`;
            
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

    async saveDetailedLogs() {
        try {
            const timestamp = Date.now();
            const logData = {
                timestamp: new Date().toISOString(),
                summary: {
                    totalAttempts: this.attempts,
                    successCount: this.successCount,
                    failureCount: this.attempts - this.successCount,
                    totalLogs: this.logs.length,
                    totalNetworkLogs: this.networkLogs.length,
                    totalErrors: this.errors.length
                },
                consoleLogs: this.logs,
                networkLogs: this.networkLogs,
                errors: this.errors
            };
            
            const filename = `detailed-logs-${timestamp}.json`;
            fs.writeFileSync(filename, JSON.stringify(logData, null, 2));
            console.log(`📄 تم حفظ اللوجز التفصيلية: ${filename}`);
            
            // حفظ ملخص سريع
            const summaryFilename = `summary-${timestamp}.txt`;
            const summary = `
=== ملخص الاختبار التفصيلي ===
التاريخ: ${new Date().toLocaleString('ar-EG')}
المحاولات: ${this.attempts}
النجاح: ${this.successCount}
الفشل: ${this.attempts - this.successCount}
إجمالي اللوجز: ${this.logs.length}
إجمالي شبكة: ${this.networkLogs.length}
إجمالي الأخطاء: ${this.errors.length}

=== الأخطاء الرئيسية ===
${this.errors.map((err, i) => `${i + 1}. ${err.type}: ${err.message || err.error}`).join('\n')}

=== آخر 10 لوجز ===
${this.logs.slice(-10).map(log => `[${log.level}] ${log.message}`).join('\n')}
            `.trim();
            
            fs.writeFileSync(summaryFilename, summary);
            console.log(`📄 تم حفظ الملخص: ${summaryFilename}`);
            
        } catch (error) {
            console.log('⚠️  فشل حفظ اللوجز:', error.message);
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
                const result = await this.fillAndSubmitForm(i);
                
                if (result.success) {
                    console.log(`\n🎉 نجحت المحاولة ${i}!`);
                    console.log(`   ${result.message}\n`);
                    this.successCount++;
                    await this.captureScreenshot(`success-${i}`);
                    break;
                } else {
                    console.log(`\n⚠️  فشلت المحاولة ${i}`);
                    console.log(`   السبب: ${result.message}\n`);
                    await this.captureScreenshot(`failed-${i}`);
                    
                    if (i < this.maxAttempts) {
                        console.log('⏳ الانتظار 3 ثوانٍ قبل المحاولة التالية...\n');
                        await this.page.waitForTimeout(3000);
                        await this.navigateToCreatePage();
                    }
                }
            }
            
            // حفظ اللوجز التفصيلية
            await this.saveDetailedLogs();
            
            // عرض النتائج النهائية
            this.displayResults();
            
            return this.successCount > 0;
            
        } catch (error) {
            console.error('\n❌ خطأ عام:', error.message);
            await this.captureScreenshot('general-error');
            await this.saveDetailedLogs();
            return false;
        } finally {
            await this.cleanup();
        }
    }

    displayResults() {
        console.log('\n' + '═'.repeat(80));
        console.log('   📊 النتائج النهائية - مراقبة مفصلة');
        console.log('═'.repeat(80));
        console.log(`✅ المحاولات الناجحة: ${this.successCount} من ${this.attempts}`);
        console.log(`❌ المحاولات الفاشلة: ${this.attempts - this.successCount}`);
        console.log(`📝 عدد اللوجز: ${this.logs.length}`);
        console.log(`🌐 عدد أحداث الشبكة: ${this.networkLogs.length}`);
        console.log(`❌ عدد الأخطاء: ${this.errors.length}`);
        
        if (this.errors.length > 0) {
            console.log('\n🔍 الأخطاء الرئيسية:');
            this.errors.forEach((err, index) => {
                console.log(`   ${index + 1}. [${err.type}] ${err.message || err.error}`);
            });
        }
        
        console.log('═'.repeat(80) + '\n');
    }

    async cleanup() {
        console.log('🧹 تنظيف وإغلاق...');
        
        if (this.browser) {
            await this.browser.close();
            console.log('✅ تم إغلاق المتصفح');
        }
        
        console.log('\n✅ انتهى الاختبار التفصيلي\n');
    }
}

// تشغيل الاختبار التفصيلي
(async () => {
    const tester = new DetailedPasswordAccountTester();
    const success = await tester.run();
    process.exit(success ? 0 : 1);
})();
