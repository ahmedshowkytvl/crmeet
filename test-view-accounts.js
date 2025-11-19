import { chromium } from 'playwright';

async function testViewAccounts() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    try {
        console.log('🔑 تسجيل الدخول...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'admin@company.com');
        await page.fill('input[name="password"]', 'P@ssW0rd');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        
        console.log('🔗 الانتقال إلى صفحة الحسابات...');
        await page.goto('http://127.0.0.1:8000/password-accounts');
        await page.waitForLoadState('networkidle');
        
        console.log('🔍 فحص الحسابات المعروضة...');
        
        // فحص عدد الصفوف في الجدول
        const rows = await page.$$('table tbody tr');
        console.log(`📋 عدد الحسابات المعروضة: ${rows.length}`);
        
        if (rows.length === 0) {
            console.log('⚠️  لا توجد حسابات معروضة');
            
            // فحص إذا كانت هناك رسالة "لا توجد بيانات"
            const noDataMsg = await page.$('text=No data available');
            if (noDataMsg) {
                console.log('📢 وجد رسالة: No data available');
            }
            
            const noRecordsMsg = await page.$('text=No records found');
            if (noRecordsMsg) {
                console.log('📢 وجد رسالة: No records found');
            }
        } else {
            console.log('\n📋 الحسابات المعروضة:');
            for (let i = 0; i < rows.length; i++) {
                const cells = await rows[i].$$('td');
                if (cells.length > 0) {
                    const texts = [];
                    for (const cell of cells) {
                        const text = await cell.textContent();
                        texts.push(text.trim());
                    }
                    console.log(`   ${i + 1}. ${texts.join(' | ')}`);
                }
            }
        }
        
        // فحص محتوى الصفحة بالكامل
        const pageText = await page.textContent('body');
        if (pageText.includes('Test Account')) {
            console.log('\n✅ وجد "Test Account" في الصفحة');
        } else {
            console.log('\n❌ لم يتم العثور على "Test Account" في الصفحة');
        }
        
        await page.screenshot({ path: 'accounts-list.png', fullPage: true });
        console.log('\n📸 تم حفظ لقطة الشاشة: accounts-list.png');
        
        // انتظار لمشاهدة النتيجة
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'accounts-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testViewAccounts();

