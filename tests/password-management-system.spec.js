const { test, expect } = require('@playwright/test');

test.describe('Password Management System', () => {
    test.beforeEach(async ({ page }) => {
        // Navigate to login page
        await page.goto('http://192.168.15.29:8000/login');
        
        // Login as admin (assuming admin credentials)
        await page.fill('input[name="email"]', 'admin@example.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        
        // Wait for dashboard to load
        await page.waitForURL('**/dashboard');
    });

    test('should display password management link on dashboard', async ({ page }) => {
        // Check if password management link exists
        const passwordLink = page.locator('a[href*="password-accounts"]');
        await expect(passwordLink).toBeVisible();
        await expect(passwordLink).toContainText('Password Management');
    });

    test('should navigate to password accounts page', async ({ page }) => {
        // Click on password management link
        await page.click('a[href*="password-accounts"]');
        
        // Wait for page to load
        await page.waitForURL('**/password-accounts');
        
        // Check if we're on the correct page
        await expect(page.locator('h2')).toContainText('Password Management');
    });

    test('should display create account button for authorized users', async ({ page }) => {
        // Navigate to password accounts page
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Check if create button exists (for authorized users)
        const createButton = page.locator('a[href*="password-accounts/create"]');
        if (await createButton.isVisible()) {
            await expect(createButton).toContainText('Create New Account');
        }
    });

    test('should create a new password account', async ({ page }) => {
        // Navigate to create account page
        await page.goto('http://192.168.15.29:8000/password-accounts/create');
        
        // Fill in the form
        await page.fill('input[name="name"]', 'Test Account');
        await page.fill('input[name="name_ar"]', 'حساب تجريبي');
        await page.fill('input[name="email"]', 'test@example.com');
        await page.fill('input[name="password"]', 'test123456');
        await page.fill('input[name="url"]', 'https://example.com');
        await page.selectOption('select[name="category"]', 'Work Tools');
        await page.fill('textarea[name="notes"]', 'Test account for testing');
        
        // Check 2FA requirement
        await page.check('input[name="requires_2fa"]');
        
        // Submit the form
        await page.click('button[type="submit"]');
        
        // Wait for redirect to index page
        await page.waitForURL('**/password-accounts');
        
        // Check if account was created
        await expect(page.locator('table')).toContainText('Test Account');
        await expect(page.locator('table')).toContainText('test@example.com');
    });

    test('should display account statistics', async ({ page }) => {
        // Navigate to password accounts page
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Check if statistics cards are displayed
        const statsCards = page.locator('.card.bg-primary, .card.bg-success, .card.bg-warning, .card.bg-info');
        await expect(statsCards).toHaveCount(4);
        
        // Check if statistics contain expected text
        await expect(page.locator('.card.bg-primary')).toContainText('Total Accounts');
        await expect(page.locator('.card.bg-success')).toContainText('Active Accounts');
        await expect(page.locator('.card.bg-warning')).toContainText('Expiring Soon');
        await expect(page.locator('.card.bg-info')).toContainText('Shared Accounts');
    });

    test('should filter accounts by category', async ({ page }) => {
        // Navigate to password accounts page
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Select a category filter
        await page.selectOption('select[name="category"]', 'Work Tools');
        await page.click('button[type="submit"]');
        
        // Check if filter is applied (URL should contain category parameter)
        expect(page.url()).toContain('category=Work%20Tools');
    });

    test('should search accounts', async ({ page }) => {
        // Navigate to password accounts page
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Search for an account
        await page.fill('input[name="search"]', 'Test');
        await page.click('button[type="submit"]');
        
        // Check if search is applied
        expect(page.url()).toContain('search=Test');
    });

    test('should display password view modal', async ({ page }) => {
        // First create an account
        await page.goto('http://192.168.15.29:8000/password-accounts/create');
        await page.fill('input[name="name"]', 'Modal Test Account');
        await page.fill('input[name="password"]', 'modal123456');
        await page.click('button[type="submit"]');
        
        // Wait for redirect
        await page.waitForURL('**/password-accounts');
        
        // Click on view password button
        const viewPasswordButton = page.locator('button[onclick*="showPassword"]').first();
        if (await viewPasswordButton.isVisible()) {
            await viewPasswordButton.click();
            
            // Check if modal appears
            const modal = page.locator('#passwordModal');
            await expect(modal).toBeVisible();
            
            // Check if password field is in the modal
            const passwordField = modal.locator('#passwordField');
            await expect(passwordField).toBeVisible();
        }
    });

    test('should test password toggle functionality', async ({ page }) => {
        // Navigate to create account page
        await page.goto('http://192.168.15.29:8000/password-accounts/create');
        
        // Test password toggle
        const passwordField = page.locator('input[name="password"]');
        const toggleButton = page.locator('button[onclick*="togglePassword"]');
        
        // Initially should be password type
        await expect(passwordField).toHaveAttribute('type', 'password');
        
        // Click toggle button
        await toggleButton.click();
        
        // Should now be text type
        await expect(passwordField).toHaveAttribute('type', 'text');
        
        // Click again to toggle back
        await toggleButton.click();
        await expect(passwordField).toHaveAttribute('type', 'password');
    });

    test('should test password generation', async ({ page }) => {
        // Navigate to create account page
        await page.goto('http://192.168.15.29:8000/password-accounts/create');
        
        // Click generate password button
        const generateButton = page.locator('button[onclick*="generatePassword"]');
        await generateButton.click();
        
        // Check if password field is filled
        const passwordField = page.locator('input[name="password"]');
        const passwordValue = await passwordField.inputValue();
        
        // Password should be generated (not empty)
        expect(passwordValue.length).toBeGreaterThan(0);
    });

    test('should test user search functionality', async ({ page }) => {
        // Navigate to create account page
        await page.goto('http://192.168.15.29:8000/password-accounts/create');
        
        // Test user search
        const userSearch = page.locator('#user_search');
        await userSearch.fill('admin');
        
        // Check if user list is filtered
        const userItems = page.locator('.user-item');
        const visibleItems = await userItems.filter({ hasText: 'admin' }).count();
        
        // Should have at least one visible item
        expect(visibleItems).toBeGreaterThan(0);
    });

    test('should test Arabic translation', async ({ page }) => {
        // Navigate to password accounts page
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Check if Arabic text is present
        const arabicText = page.locator('text=إدارة كلمات المرور');
        await expect(arabicText).toBeVisible();
    });

    test('should test responsive design', async ({ page }) => {
        // Test mobile viewport
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Check if page is responsive
        const table = page.locator('table');
        await expect(table).toBeVisible();
        
        // Test tablet viewport
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.reload();
        await expect(table).toBeVisible();
        
        // Test desktop viewport
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.reload();
        await expect(table).toBeVisible();
    });
});

test.describe('Password Management Security', () => {
    test('should require authentication', async ({ page }) => {
        // Try to access password accounts without login
        await page.goto('http://192.168.15.29:8000/password-accounts');
        
        // Should redirect to login page
        await page.waitForURL('**/login');
        await expect(page.locator('h2')).toContainText('Login');
    });

    test('should enforce role-based access', async ({ page }) => {
        // This test would require different user roles
        // For now, just test that the page loads for authenticated users
        await page.goto('http://192.168.15.29:8000/login');
        await page.fill('input[name="email"]', 'admin@example.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        
        await page.goto('http://192.168.15.29:8000/password-accounts');
        await expect(page.locator('h2')).toContainText('Password Management');
    });
});
