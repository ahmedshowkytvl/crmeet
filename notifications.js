/**
 * ========================================
 * Alpine.js Component للإشعارات
 * ========================================
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', (userId) => ({
        userId: userId,
        notifications: [],
        unreadCount: 0,
        isOpen: false,
        loading: false,
        filter: 'all',
        offset: 0,
        limit: 20,
        hasMore: false,
        isConnected: false,
        
        // Translation helper
        translations: {
            ar: {
                notifications: 'الإشعارات',
                mark_all_as_read: 'تحديد الكل كمقروء',
                no_notifications: 'لا توجد إشعارات',
                loading: 'جاري التحميل...',
                load_more: 'تحميل المزيد',
                offline: 'غير متصل',
                notification_types: {
                    message: 'رسائل',
                    task: 'مهام',
                    asset: 'أجهزة',
                    birthday: 'أعياد ميلاد'
                },
                notification_filters: {
                    all: 'الكل'
                }
            },
            en: {
                notifications: 'Notifications',
                mark_all_as_read: 'Mark All as Read',
                no_notifications: 'No notifications',
                loading: 'Loading...',
                load_more: 'Load More',
                offline: 'Offline',
                notification_types: {
                    message: 'Messages',
                    task: 'Tasks',
                    asset: 'Devices',
                    birthday: 'Birthdays'
                },
                notification_filters: {
                    all: 'All'
                }
            }
        },
        
        // Reactive locale property - updates when document lang changes
        get currentLocale() {
            const lang = document.documentElement.getAttribute('lang') || document.documentElement.lang || 'ar';
            return lang.split('-')[0]; // Get 'ar' from 'ar-EG' etc
        },
        
        // Get current locale
        getCurrentLocale() {
            const lang = document.documentElement.getAttribute('lang') || document.documentElement.lang || 'ar';
            return lang.split('-')[0]; // Get 'ar' from 'ar-EG' etc
        },
        
        // Check if current locale is RTL
        isRTL() {
            const locale = this.getCurrentLocale();
            return ['ar', 'he', 'fa', 'ur'].includes(locale);
        },

        /**
         * الحصول على العنوان المترجم للإشعار
         */
        getNotificationTitle(notification) {
            const locale = this.getCurrentLocale();
            if (notification.type === 'birthday' && notification.metadata) {
                if (locale === 'ar' && notification.metadata.title_ar) {
                    return notification.metadata.title_ar;
                } else if (locale === 'en' && notification.metadata.title_en) {
                    return notification.metadata.title_en;
                }
            }
            return notification.localized_title || notification.title;
        },

        /**
         * الحصول على النص المترجم للإشعار
         */
        getNotificationBody(notification) {
            const locale = this.getCurrentLocale();
            if (notification.type === 'birthday' && notification.metadata) {
                if (locale === 'ar' && notification.metadata.body_ar) {
                    return notification.metadata.body_ar;
                } else if (locale === 'en' && notification.metadata.body_en) {
                    return notification.metadata.body_en;
                }
            }
            return notification.localized_body || notification.body;
        },

        /**
         * الحصول على اسم الفاعل باللغة المناسبة
         */
        getActorName(notification) {
            if (!notification.actor_name && !notification.actor) return null;
            const locale = this.getCurrentLocale();
            // For birthday notifications, show actor name in appropriate language
            if (notification.type === 'birthday' && notification.actor) {
                if (locale === 'en') {
                    return notification.actor.name || notification.actor_name;
                } else {
                    return notification.actor.name_ar || notification.actor.name || notification.actor_name;
                }
            }
            return notification.actor_name;
        },
        
        // Translation function
        trans(key, params = {}) {
            const locale = this.getCurrentLocale();
            const translation = this.translations[locale]?.[key] || key;
            
            // Replace parameters
            return translation.replace(/:(\w+)/g, (match, param) => {
                return params[param] || match;
            });
        },

        init() {
            this.fetchNotifications();
            this.fetchUnreadCount();
            this.setupBroadcasting();
            this.requestNotificationPermission();
        },

        /**
         * إعداد Laravel Echo للإشعارات الفورية
         */
        setupBroadcasting() {
            if (typeof Echo !== 'undefined') {
                Echo.private(`user.${this.userId}`)
                    .listen('.notification.created', (data) => {
                        console.log('New notification received:', data);
                        this.notifications.unshift(data.notification);
                        this.unreadCount++;
                        this.playSound();
                        this.showBrowserNotification(data.notification);
                    })
                    .listen('.notification.count-updated', (data) => {
                        this.unreadCount = data.unread_count;
                    });

                this.isConnected = true;
            }
        },

        /**
         * جلب الإشعارات
         */
        async fetchNotifications(loadMore = false) {
            this.loading = true;
            
            try {
                const currentOffset = loadMore ? this.offset : 0;
                const response = await fetch(`/notifications/api?limit=${this.limit}&offset=${currentOffset}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Handle both response formats
                    const notifications = data.notifications || (data.data ? data.data.notifications : []);
                    const pagination = data.data ? data.data.pagination : null;
                    
                    console.log('Processing notifications:', notifications);
                    console.log('First notification body:', notifications[0]?.body);
                    
                    if (loadMore) {
                        this.notifications = [...this.notifications, ...notifications];
                    } else {
                        this.notifications = notifications;
                    }
                    
                    console.log('Alpine notifications after assignment:', this.notifications);
                    console.log('First Alpine notification body:', this.notifications[0]?.body);

                    if (pagination) {
                        this.offset = pagination.offset + pagination.limit;
                        this.hasMore = pagination.has_more;
                    }
                }
            } catch (error) {
                console.error('Fetch notifications error:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * جلب عدد غير المقروءة
         */
        async fetchUnreadCount() {
            try {
                const response = await fetch('/notifications/unread-count', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.unreadCount = data.unread_count || (data.data ? data.data.unread_count : 0);
                }
            } catch (error) {
                console.error('Fetch unread count error:', error);
            }
        },

        /**
         * تصفية الإشعارات
         */
        get filteredNotifications() {
            if (this.filter === 'all') {
                return this.notifications;
            }
            return this.notifications.filter(n => n.type === this.filter);
        },

        /**
         * فتح/إغلاق القائمة
         */
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },

        /**
         * معالجة النقر على إشعار
         */
        async handleNotificationClick(notification) {
            // تحديد كمقروء
            if (!notification.is_read) {
                await this.markAsRead(notification.id);
                notification.is_read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                
                // تحديث unreadCount من السيرفر للتأكد من الدقة
                await this.fetchUnreadCount();
            }

            // بناء الرابط إذا لم يكن موجودًا
            let targetLink = notification.link;
            
            // إذا لم يكن هناك رابط، بناء الرابط من resource_type و resource_id
            if (!targetLink && notification.resource_type && notification.resource_id) {
                if (notification.resource_type === 'task') {
                    targetLink = `/tasks/${notification.resource_id}`;
                } else if (notification.resource_type === 'chat_message' || notification.type === 'message') {
                    // للحصول على chat_room_id من metadata
                    const chatRoomId = notification.metadata?.chat_room_id || notification.resource_id;
                    targetLink = `/chat/${chatRoomId}`;
                } else if (notification.resource_type === 'asset') {
                    targetLink = `/assets/${notification.resource_id}`;
                }
            }

            // الانتقال للرابط
            if (targetLink) {
                window.location.href = targetLink;
            } else {
                console.warn('لا يوجد رابط متاح للإشعار:', notification);
            }

            this.isOpen = false;
        },

        /**
         * تحديد إشعار كمقروء
         */
        async markAsRead(notificationId) {
            try {
                const response = await fetch('/notifications/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        notification_ids: [notificationId]
                    })
                });

                const data = await response.json();
                return data.success;
            } catch (error) {
                console.error('Mark as read error:', error);
                return false;
            }
        },

        /**
         * تحديد جميع الإشعارات كمقروءة
         */
        async markAllAsRead() {
            try {
                const response = await fetch('/notifications/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        mark_all: true
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // تحديث جميع الإشعارات كمقروءة
                    this.notifications.forEach(n => {
                        n.is_read = true;
                    });
                    
                    // تحديث العدد مباشرة من الـ response
                    const newUnreadCount = data.data?.unread_count ?? 0;
                    this.unreadCount = newUnreadCount;
                    
                    // إذا كان العدد غير صفر أو غير محدد، جلب العدد من السيرفر للتأكد
                    if (newUnreadCount > 0 || data.data?.unread_count === undefined) {
                        await this.fetchUnreadCount();
                    }
                }
            } catch (error) {
                console.error('Mark all as read error:', error);
            }
        },

        /**
         * تحميل المزيد
         */
        loadMore() {
            this.fetchNotifications(true);
        },

        /**
         * تشغيل صوت
         */
        playSound() {
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.5;
                audio.play().catch(err => console.log('Sound play failed:', err));
            } catch (error) {
                console.error('Error playing sound:', error);
            }
        },

        /**
         * إشعار المتصفح
         */
        showBrowserNotification(notification) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(notification.title, {
                    body: notification.body,
                    icon: notification.actor_avatar || '/images/notification-icon.png',
                    tag: `notification-${notification.id}`,
                });
            }
        },

        /**
         * طلب إذن الإشعارات
         */
        async requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        }
    }));
});

