/**
 * User Status Management JavaScript
 * يدير حالة المستخدم (عبر الإنترنت/خارج الإنترنت)
 */

class UserStatusManager {
    constructor() {
        this.isOnline = false;
        this.heartbeatInterval = null;
        this.init();
    }

    init() {
        // تحديث الحالة عند تحميل الصفحة
        this.updateStatus(true);
        
        // بدء نبضات القلب
        this.startHeartbeat();
        
        // تحديث الحالة عند إغلاق الصفحة
        window.addEventListener('beforeunload', () => {
            this.updateStatus(false);
        });
        
        // تحديث الحالة عند العودة للصفحة
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.updateStatus(true);
            } else {
                this.updateStatus(false);
            }
        });
    }

    /**
     * تحديث حالة المستخدم
     */
    async updateStatus(isOnline) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                return; // Skip if no CSRF token
            }

            const response = await fetch('/user-status/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                body: JSON.stringify({
                    is_online: isOnline
                })
            });

            if (!response.ok) {
                return; // Silently fail
            }

            const data = await response.json();
            
            if (data && data.success) {
                this.isOnline = isOnline;
            }
        } catch (error) {
            // Silently fail - do nothing
            return;
        }
    }

    /**
     * بدء نبضات القلب لتحديث النشاط
     */
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            this.updateActivity();
        }, 30000); // كل 30 ثانية
    }

    /**
     * تحديث آخر نشاط
     */
    async updateActivity() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                return; // Skip if no CSRF token
            }

            const response = await fetch('/user-status/activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                }
            });

            if (!response.ok) {
                return; // Silently fail
            }
        } catch (error) {
            // Silently fail - do nothing
            return;
        }
    }

    /**
     * الحصول على حالة مستخدم معين
     */
    async getUserStatus(userId) {
        try {
            const response = await fetch(`/user-status/${userId}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('خطأ في الحصول على حالة المستخدم:', error);
            return { success: false, is_online: false };
        }
    }

    /**
     * الحصول على جميع المستخدمين عبر الإنترنت
     */
    async getOnlineUsers() {
        try {
            const response = await fetch('/user-status/online/users');
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('خطأ في الحصول على المستخدمين عبر الإنترنت:', error);
            return { success: false, users: [] };
        }
    }

    /**
     * إيقاف نبضات القلب
     */
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }
}

// تهيئة مدير الحالة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    window.userStatusManager = new UserStatusManager();
});

// تصدير الكلاس للاستخدام العام
window.UserStatusManager = UserStatusManager;
