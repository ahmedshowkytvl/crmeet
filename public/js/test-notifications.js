/**
 * Test script for notification translations
 */

// Test function to switch language
function testNotificationLanguage(locale) {
    console.log('Testing notification language:', locale);
    
    // Update document language
    document.documentElement.lang = locale;
    
    // Dispatch language change event
    document.dispatchEvent(new CustomEvent('languageChanged', {
        detail: { locale: locale }
    }));
    
    // Test translation function
    const notificationBell = document.querySelector('[x-data*="notificationBell"]');
    if (notificationBell && notificationBell._x_dataStack) {
        const component = notificationBell._x_dataStack[0];
        if (component && component.trans) {
            console.log('Testing translations for:', locale);
            console.log('notifications:', component.trans('notifications'));
            console.log('mark_all_as_read:', component.trans('mark_all_as_read'));
            console.log('no_notifications:', component.trans('no_notifications'));
            console.log('loading:', component.trans('loading'));
            console.log('load_more:', component.trans('load_more'));
            console.log('offline:', component.trans('offline'));
            console.log('notification_types.message:', component.trans('notification_types.message'));
            console.log('notification_types.task:', component.trans('notification_types.task'));
            console.log('notification_types.asset:', component.trans('notification_types.asset'));
            console.log('notification_types.birthday:', component.trans('notification_types.birthday'));
            console.log('notification_filters.all:', component.trans('notification_filters.all'));
        }
    }
}

// Auto-test on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification translation test loaded');
    
    // Test Arabic
    setTimeout(() => {
        console.log('=== Testing Arabic ===');
        testNotificationLanguage('ar');
    }, 1000);
    
    // Test English
    setTimeout(() => {
        console.log('=== Testing English ===');
        testNotificationLanguage('en');
    }, 2000);
    
    // Test Arabic again
    setTimeout(() => {
        console.log('=== Testing Arabic Again ===');
        testNotificationLanguage('ar');
    }, 3000);
});

// Export for manual testing
window.testNotificationLanguage = testNotificationLanguage;
