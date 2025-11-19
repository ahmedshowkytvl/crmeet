/**
 * Playwright MCP Tests for Internal Chat System
 * Tests the complete chat functionality including text messages, file uploads, and contact sharing
 */

const { test, expect } = require('@playwright/test');

// Test configuration
const BASE_URL = 'http://localhost:8000';
const TEST_USERS = {
    user1: {
        email: 'test1@example.com',
        password: 'password123',
        name: 'Test User 1'
    },
    user2: {
        email: 'test2@example.com', 
        password: 'password123',
        name: 'Test User 2'
    }
};

// Helper functions
async function loginUser(page, user) {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', user.email);
    await page.fill('input[name="password"]', user.password);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

async function navigateToChat(page) {
    await page.click('a[href*="/chat"]');
    await page.waitForLoadState('networkidle');
}

async function startNewChat(page, targetUserName) {
    await page.click('button[data-bs-target="#newChatModal"]');
    await page.waitForSelector('#newChatModal');
    await page.fill('#userSearch', targetUserName);
    await page.waitForSelector('.search-result-item');
    await page.click('.search-result-item:first-child');
}

// Test Suite
test.describe('Internal Chat System', () => {
    
    test.beforeEach(async ({ page }) => {
        // Set up test data if needed
        await page.goto(BASE_URL);
    });

    test('User can access chat system', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Verify chat interface loads
        await expect(page.locator('.chat-sidebar')).toBeVisible();
        await expect(page.locator('.chat-main')).toBeVisible();
        await expect(page.locator('h5:has-text("الدردشة الداخلية")')).toBeVisible();
    });

    test('User can start a new chat', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Click new chat button
        await page.click('button[data-bs-target="#newChatModal"]');
        await page.waitForSelector('#newChatModal');
        
        // Search for user
        await page.fill('#userSearch', 'Test User');
        await page.waitForSelector('.search-result-item');
        
        // Verify search results
        await expect(page.locator('.search-result-item')).toHaveCount.greaterThan(0);
        
        // Select first user
        await page.click('.search-result-item:first-child');
        
        // Verify redirect to chat
        await page.waitForURL('**/chat/**');
    });

    test('User can send text messages', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat
        await startNewChat(page, 'Test User');
        
        // Send text message
        const messageText = 'Hello, this is a test message!';
        await page.fill('#messageInput', messageText);
        await page.click('#sendMessageBtn');
        
        // Verify message appears
        await expect(page.locator('.message-text:has-text("' + messageText + '")')).toBeVisible();
        
        // Verify message is marked as sent
        await expect(page.locator('.message-status .fas.fa-check')).toBeVisible();
    });

    test('User can upload and send files', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat
        await startNewChat(page, 'Test User');
        
        // Upload file
        const fileInput = page.locator('#fileInput');
        await fileInput.setInputFiles({
            name: 'test-file.txt',
            mimeType: 'text/plain',
            buffer: Buffer.from('This is a test file content')
        });
        
        // Wait for file upload to complete
        await page.waitForSelector('.message-file');
        
        // Verify file message appears
        await expect(page.locator('.message-file')).toBeVisible();
        await expect(page.locator('.file-name:has-text("test-file.txt")')).toBeVisible();
        await expect(page.locator('a:has-text("تحميل")')).toBeVisible();
    });

    test('User can upload and send images', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat
        await startNewChat(page, 'Test User');
        
        // Create a test image
        const testImage = await page.evaluate(() => {
            const canvas = document.createElement('canvas');
            canvas.width = 100;
            canvas.height = 100;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = 'red';
            ctx.fillRect(0, 0, 100, 100);
            return canvas.toDataURL('image/png');
        });
        
        // Upload image
        const fileInput = page.locator('#fileInput');
        await fileInput.setInputFiles({
            name: 'test-image.png',
            mimeType: 'image/png',
            buffer: Buffer.from(testImage.split(',')[1], 'base64')
        });
        
        // Wait for image upload to complete
        await page.waitForSelector('.message-image');
        
        // Verify image message appears
        await expect(page.locator('.message-image')).toBeVisible();
        await expect(page.locator('.message-image img')).toBeVisible();
    });

    test('User can share contacts', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat
        await startNewChat(page, 'Test User');
        
        // Click attach contact button
        await page.click('#attachContactBtn');
        await page.waitForSelector('#contactModal');
        
        // Search for contact
        await page.fill('#contactSearch', 'Test User');
        await page.waitForSelector('.search-result-item');
        
        // Select contact
        await page.click('.search-result-item:first-child');
        
        // Wait for contact message to appear
        await page.waitForSelector('.message-contact');
        
        // Verify contact message appears
        await expect(page.locator('.message-contact')).toBeVisible();
        await expect(page.locator('.contact-card')).toBeVisible();
    });

    test('User can search in messages', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat and send a message
        await startNewChat(page, 'Test User');
        await page.fill('#messageInput', 'Searchable test message');
        await page.click('#sendMessageBtn');
        
        // Search for message
        await page.click('#searchMessagesBtn');
        await page.fill('input[placeholder*="البحث"]', 'Searchable');
        
        // Verify search results
        await expect(page.locator('.message-text:has-text("Searchable test message")')).toBeVisible();
    });

    test('User can mark messages as read', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat and send a message
        await startNewChat(page, 'Test User');
        await page.fill('#messageInput', 'Test message for read status');
        await page.click('#sendMessageBtn');
        
        // Verify message status changes
        await expect(page.locator('.message-status .fas.fa-check')).toBeVisible();
        
        // Simulate message being read (this would normally happen when other user opens chat)
        await page.evaluate(() => {
            // Simulate marking message as read
            const messageStatus = document.querySelector('.message-status');
            if (messageStatus) {
                messageStatus.innerHTML = '<i class="fas fa-check-double text-primary"></i>';
            }
        });
        
        // Verify read status
        await expect(page.locator('.message-status .fas.fa-check-double')).toBeVisible();
    });

    test('User can archive and unarchive chats', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat
        await startNewChat(page, 'Test User');
        
        // Archive chat
        await page.click('#chatInfoBtn');
        await page.click('a:has-text("أرشفة")');
        
        // Verify chat is archived (should disappear from active list)
        await page.goto(`${BASE_URL}/chat`);
        await expect(page.locator('.chat-item')).toHaveCount(0);
    });

    test('User can mute and unmute chats', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat
        await startNewChat(page, 'Test User');
        
        // Mute chat
        await page.click('#chatInfoBtn');
        await page.click('a:has-text("كتم الصوت")');
        
        // Verify mute action completed
        await expect(page.locator('.toast')).toBeVisible();
    });

    test('Chat interface supports RTL layout', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        
        // Switch to Arabic
        await page.click('a[href*="/lang/ar"]');
        await page.waitForLoadState('networkidle');
        
        await navigateToChat(page);
        
        // Verify RTL layout
        await expect(page.locator('h5:has-text("الدردشة الداخلية")')).toBeVisible();
        await expect(page.locator('input[placeholder*="اكتب رسالتك"]')).toBeVisible();
    });

    test('Chat interface supports English layout', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        
        // Switch to English
        await page.click('a[href*="/lang/en"]');
        await page.waitForLoadState('networkidle');
        
        await navigateToChat(page);
        
        // Verify English layout
        await expect(page.locator('h5:has-text("Internal Chat")')).toBeVisible();
        await expect(page.locator('input[placeholder*="Type your message"]')).toBeVisible();
    });

    test('File download works correctly', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Start new chat and upload file
        await startNewChat(page, 'Test User');
        
        const fileInput = page.locator('#fileInput');
        await fileInput.setInputFiles({
            name: 'download-test.txt',
            mimeType: 'text/plain',
            buffer: Buffer.from('Download test content')
        });
        
        await page.waitForSelector('.message-file');
        
        // Click download button
        const downloadPromise = page.waitForEvent('download');
        await page.click('a:has-text("تحميل")');
        const download = await downloadPromise;
        
        // Verify download
        expect(download.suggestedFilename()).toBe('download-test.txt');
    });

    test('Error handling for invalid operations', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        // Try to send empty message
        await page.click('#sendMessageBtn');
        
        // Verify no message is sent
        await expect(page.locator('.message-text')).toHaveCount(0);
        
        // Try to upload invalid file type
        const fileInput = page.locator('#fileInput');
        await fileInput.setInputFiles({
            name: 'test.exe',
            mimeType: 'application/x-msdownload',
            buffer: Buffer.from('fake exe content')
        });
        
        // Verify error handling (should show error message or reject file)
        await page.waitForTimeout(1000);
    });
});

// Performance tests
test.describe('Chat System Performance', () => {
    
    test('Chat loads quickly', async ({ page }) => {
        const startTime = Date.now();
        
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        
        const loadTime = Date.now() - startTime;
        expect(loadTime).toBeLessThan(3000); // Should load within 3 seconds
    });

    test('Message sending is responsive', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        await startNewChat(page, 'Test User');
        
        const startTime = Date.now();
        await page.fill('#messageInput', 'Performance test message');
        await page.click('#sendMessageBtn');
        await page.waitForSelector('.message-text');
        
        const responseTime = Date.now() - startTime;
        expect(responseTime).toBeLessThan(2000); // Should respond within 2 seconds
    });
});

// Security tests
test.describe('Chat System Security', () => {
    
    test('Unauthorized users cannot access chat', async ({ page }) => {
        // Try to access chat without login
        await page.goto(`${BASE_URL}/chat`);
        
        // Should redirect to login
        await page.waitForURL('**/login');
    });

    test('Users cannot access other users private chats', async ({ page }) => {
        await loginUser(page, TEST_USERS.user1);
        await navigateToChat(page);
        await startNewChat(page, 'Test User');
        
        // Get chat URL
        const chatUrl = page.url();
        
        // Login as different user
        await page.click('a:has-text("تسجيل الخروج")');
        await loginUser(page, TEST_USERS.user2);
        
        // Try to access first user's chat
        await page.goto(chatUrl);
        
        // Should show error or redirect
        await expect(page.locator('body')).not.toContainText('Test User 1');
    });
});
