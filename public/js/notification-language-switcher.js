/**
 * ========================================
 * Notification Language Switcher
 * ========================================
 */

class NotificationLanguageSwitcher {
    constructor() {
        this.currentLocale = this.getCurrentLocale();
        this.translations = this.getTranslations();
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateNotificationLanguage();
    }

    getCurrentLocale() {
        return document.documentElement.lang || 'ar';
    }

    getTranslations() {
        return {
            ar: {
                notifications: 'الإشعارات',
                mark_all_as_read: 'تحديد الكل كمقروء',
                no_notifications: 'لا توجد إشعارات',
                loading: 'جاري التحميل...',
                offline: 'غير متصل',
                error_loading: 'خطأ في تحميل الإشعارات',
                notification_types: {
                    message: 'رسائل',
                    task: 'مهام',
                    asset: 'أجهزة',
                    birthday: 'أعياد ميلاد'
                },
                notification_filters: {
                    all: 'الكل',
                    unread: 'غير مقروء',
                    read: 'مقروء',
                    today: 'اليوم',
                    this_week: 'هذا الأسبوع',
                    this_month: 'هذا الشهر'
                },
                notification_actions: {
                    view: 'عرض',
                    dismiss: 'تجاهل',
                    respond: 'رد',
                    complete: 'إكمال',
                    approve: 'موافقة',
                    reject: 'رفض'
                }
            },
            en: {
                notifications: 'Notifications',
                mark_all_as_read: 'Mark All as Read',
                no_notifications: 'No notifications',
                loading: 'Loading...',
                offline: 'Offline',
                error_loading: 'Error loading notifications',
                notification_types: {
                    message: 'Messages',
                    task: 'Tasks',
                    asset: 'Devices',
                    birthday: 'Birthdays'
                },
                notification_filters: {
                    all: 'All',
                    unread: 'Unread',
                    read: 'Read',
                    today: 'Today',
                    this_week: 'This Week',
                    this_month: 'This Month'
                },
                notification_actions: {
                    view: 'View',
                    dismiss: 'Dismiss',
                    respond: 'Respond',
                    complete: 'Complete',
                    approve: 'Approve',
                    reject: 'Reject'
                }
            }
        };
    }

    trans(key, params = {}) {
        const translation = this.getNestedTranslation(key);
        
        if (!translation) {
            console.warn(`Translation missing for key: ${key}`);
            return key;
        }

        // Replace parameters
        return translation.replace(/:(\w+)/g, (match, param) => {
            return params[param] || match;
        });
    }

    getNestedTranslation(key) {
        const keys = key.split('.');
        let translation = this.translations[this.currentLocale];
        
        for (const k of keys) {
            if (translation && typeof translation === 'object' && k in translation) {
                translation = translation[k];
            } else {
                return null;
            }
        }
        
        return typeof translation === 'string' ? translation : null;
    }

    isRTL() {
        return ['ar', 'he', 'fa', 'ur'].includes(this.currentLocale);
    }

    bindEvents() {
        // Listen for language changes
        document.addEventListener('languageChanged', (event) => {
            this.currentLocale = event.detail.locale;
            this.updateNotificationLanguage();
        });

        // Listen for Alpine.js updates
        document.addEventListener('alpine:updated', () => {
            this.updateNotificationLanguage();
        });
    }

    updateNotificationLanguage() {
        // Update notification bell component
        const notificationBell = document.querySelector('[x-data*="notificationBell"]');
        if (notificationBell && notificationBell._x_dataStack) {
            const component = notificationBell._x_dataStack[0];
            if (component && component.trans) {
                component.translations = this.translations;
                component.getCurrentLocale = () => this.currentLocale;
                component.isRTL = () => this.isRTL();
            }
        }

        // Update notification dropdown direction
        const dropdown = document.querySelector('.notification-dropdown');
        if (dropdown) {
            dropdown.setAttribute('dir', this.isRTL() ? 'rtl' : 'ltr');
        }

        // Update notification elements
        this.updateNotificationElements();
    }

    updateNotificationElements() {
        // Update notification titles
        const notificationTitles = document.querySelectorAll('.notification-title');
        notificationTitles.forEach(title => {
            const key = title.getAttribute('data-trans-key');
            if (key) {
                title.textContent = this.trans(key);
            }
        });

        // Update notification buttons
        const notificationButtons = document.querySelectorAll('.notification-btn');
        notificationButtons.forEach(button => {
            const key = button.getAttribute('data-trans-key');
            if (key) {
                button.textContent = this.trans(key);
            }
        });

        // Update filter buttons
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(button => {
            const filter = button.getAttribute('data-filter');
            if (filter) {
                const key = `notification_filters.${filter}`;
                button.textContent = this.trans(key);
            }
        });
    }

    // Public method to switch language
    switchLanguage(locale) {
        if (this.translations[locale]) {
            this.currentLocale = locale;
            document.documentElement.lang = locale;
            
            // Dispatch language change event
            document.dispatchEvent(new CustomEvent('languageChanged', {
                detail: { locale: locale }
            }));
            
            // Update notification language
            this.updateNotificationLanguage();
            
            return true;
        }
        return false;
    }

    // Public method to get current language
    getCurrentLanguage() {
        return this.currentLocale;
    }

    // Public method to get available languages
    getAvailableLanguages() {
        return Object.keys(this.translations);
    }
}

// Initialize the language switcher
document.addEventListener('DOMContentLoaded', () => {
    window.notificationLanguageSwitcher = new NotificationLanguageSwitcher();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationLanguageSwitcher;
}
