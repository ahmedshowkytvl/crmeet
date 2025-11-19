import { chromium } from 'playwright';

async function testPasswordAccountsPage() {
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
        
        console.log('✅ تم تحميل الصفحة بنجاح');
        console.log(`📍 URL: ${page.url()}`);
        
        // فحص الفلاتر
        console.log('\n🔍 فحص الفلاتر:');
        
        const searchInput = await page.$('input[name="search"]');
        console.log(`   ✓ فلتر البحث: ${searchInput ? 'موجود' : 'غير موجود'}`);
        
        const categorySelect = await page.$('select[name="category"]');
        console.log(`   ✓ فلتر الفئة: ${categorySelect ? 'موجود' : 'غير موجود'}`);
        
        const statusSelect = await page.$('select[name="status"]');
        console.log(`   ✓ فلتر الحالة: ${statusSelect ? 'موجود' : 'غير موجود'}`);
        
        const employeeSelect = await page.$('select[name="employee"]');
        console.log(`   ✓ فلتر الموظف: ${employeeSelect ? 'موجود' : 'غير موجود'}`);
        
        if (employeeSelect) {
            const options = await page.$$eval('select[name="employee"] option', options =>
                options.map(option => option.textContent.trim())
            );
            console.log(`   ✓ عدد الموظفين في القائمة: ${options.length - 1}`); // -1 for "All Employees"
        }
        
        // فحص الجدول
        console.log('\n📋 فحص الجدول:');
        const table = await page.$('table');
        if (table) {
            console.log('   ✓ الجدول موجود');
            
            const headers = await page.$$eval('table thead th', ths =>
                ths.map(th => th.textContent.trim())
            );
            console.log(`   ✓ أعمدة الجدول: ${headers.join(', ')}`);
            
            const rows = await page.$$('table tbody tr');
            console.log(`   ✓ عدد الحسابات المعروضة: ${rows.length}`);
            
            if (rows.length > 0) {
                console.log('\n📝 أمثلة على الحسابات:');
                for (let i = 0; i < Math.min(rows.length, 3); i++) {
                    const cells = await rows[i].$$('td');
                    const name = await cells[1]?.textContent();
                    const email = await cells[2]?.textContent();
                    const assignedUsers = await cells[6]?.textContent();
                    
                    console.log(`   ${i + 1}. ${name?.trim()}`);
                    console.log(`      Email: ${email?.trim()}`);
                    console.log(`      Assigned: ${assignedUsers?.trim()}`);
                }
            }
        } else {
            console.log('   ❌ الجدول غير موجود');
        }
        
        await page.screenshot({ path: 'password-accounts-page.png', fullPage: true });
        console.log('\n📸 تم حفظ لقطة الشاشة: password-accounts-page.png');
        
        // انتظار 5 ثواني
        await page.waitForTimeout(5000);
        
    } catch (error) {
        console.error('❌ خطأ:', error.message);
        await page.screenshot({ path: 'password-accounts-error.png', fullPage: true });
    } finally {
        await browser.close();
    }
}

testPasswordAccountsPage();





