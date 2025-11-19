/**
 * اختبار سريع لنظام الإشعارات
 * Quick test for notification system
 */

console.log('🔔 Testing Notification System...');

// اختبار التبديل بين اللغات
function testLanguageSwitch() {
    console.log('🌐 Testing language switch...');
    
    // اختبار العربية
    document.documentElement.lang = 'ar';
    console.log('Arabic (ar):', document.documentElement.lang);
    
    // اختبار الإنجليزية
    setTimeout(() => {
        document.documentElement.lang = 'en';
        console.log('English (en):', document.documentElement.lang);
    }, 1000);
}

// اختبار Alpine.js component
function testAlpineComponent() {
    console.log('⚡ Testing Alpine.js component...');
    
    const notificationBell = document.querySelector('[x-data*="notificationBell"]');
    if (notificationBell) {
        console.log('✅ Notification bell component found');
        
        // اختبار النقر على الجرس
        const bellButton = notificationBell.querySelector('.notification-bell-button');
        if (bellButton) {
            console.log('✅ Bell button found');
            // bellButton.click(); // Uncomment to test click
        }
    } else {
        console.log('❌ Notification bell component not found');
    }
}

// اختبار الترجمة
function testTranslations() {
    console.log('🔤 Testing translations...');
    
    const testKeys = [
        'notifications',
        'mark_all_as_read',
        'no_notifications',
        'loading',
        'load_more',
        'offline',
        'notification_types.message',
        'notification_types.task',
        'notification_filters.all'
    ];
    
    testKeys.forEach(key => {
        console.log(`Testing key: ${key}`);
    });
}

// تشغيل الاختبارات
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Starting notification tests...');
    
    testLanguageSwitch();
    testAlpineComponent();
    testTranslations();
    
    console.log('✅ All tests completed!');
});

// تصدير الدوال للاختبار اليدوي
window.testNotificationSystem = {
    testLanguageSwitch,
    testAlpineComponent,
    testTranslations
};

console.log('📝 Manual test functions available:');
console.log('- testNotificationSystem.testLanguageSwitch()');
console.log('- testNotificationSystem.testAlpineComponent()');
console.log('- testNotificationSystem.testTranslations()');
