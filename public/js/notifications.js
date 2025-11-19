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
                },
                time: {
                    now: 'الآن',
                    minutes_ago: 'منذ :minutes دقيقة',
                    hours_ago: 'منذ :hours ساعة',
                    days_ago: 'منذ :days يوم'
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
                },
                time: {
                    now: 'Now',
                    minutes_ago: ':minutes minutes ago',
                    hours_ago: ':hours hours ago',
                    days_ago: ':days days ago'
                }
            }
        },
        
        // Get current locale
        getCurrentLocale() {
            return document.documentElement.lang || 'ar';
        },
        
        // Get current locale for Alpine.js expressions
        get currentLocale() {
            return this.getCurrentLocale();
        },
        
        // Check if current locale is RTL
        isRTL() {
            const locale = this.getCurrentLocale();
            return ['ar', 'he', 'fa', 'ur'].includes(locale);
        },
        
        // Check if user menu is open (for compatibility)
        isUserMenuOpen() {
            return false; // Always return false since we removed user menu interaction
        },
        
        // Translation function
        trans(key, params = {}) {
            const locale = this.getCurrentLocale();
            let translation = this.translations[locale];
            
            // Handle nested keys like 'notification_filters.all'
            const keys = key.split('.');
            for (const k of keys) {
                if (translation && typeof translation === 'object') {
                    translation = translation[k];
                } else {
                    translation = key; // Fallback to original key
                    break;
                }
            }
            
            // If translation is not found, use the key
            if (typeof translation !== 'string') {
                translation = key;
            }
            
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
                    this.notifications.forEach(n => {
                        n.is_read = true;
                    });
                    this.unreadCount = 0;
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
        
        // Set filter for notifications
        setFilter(filter) {
            this.filter = filter;
        },
        
        // Close notifications dropdown
        closeNotifications() {
            this.isOpen = false;
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
        async requestNotificationPermission(event) {
            // يجب استدعاؤها فقط بعد تفاعل المستخدم (click, keypress, etc.)
            if (!event || (event.isTrusted !== true)) return;
            if ('Notification' in window && Notification.permission === 'default') {
                try {
                    await Notification.requestPermission();
                } catch (e) {
                    console.warn('Notification permission request failed:', e);
                }
            }
        },

        /**
         * تنسيق الوقت
         */
        timeAgo(date) {
            const now = new Date();
            const diff = now - new Date(date);
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) {
                return this.translations[this.currentLocale].time.now;
            } else if (minutes < 60) {
                return this.translations[this.currentLocale].time.minutes_ago.replace(':minutes', minutes);
            } else if (hours < 24) {
                return this.translations[this.currentLocale].time.hours_ago.replace(':hours', hours);
            } else {
                return this.translations[this.currentLocale].time.days_ago.replace(':days', days);
            }
        }
    }));
});

