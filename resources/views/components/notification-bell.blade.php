@props(['userId'])

<div x-data="notificationBell({{ $userId }})" 
     class="notification-bell-container">
    <!-- زر الجرس -->
    <button 
        @click="toggleDropdown"
        class="notification-bell-button"
        type="button"
        :aria-label="translations[currentLocale].notifications"
    >
        <!-- أيقونة الجرس -->
        <template x-if="unreadCount > 0">
            <svg class="notification-icon active" fill="currentColor" viewBox="0 0 24 24">
                <path d="M10 20h4c0 1.1-.9 2-2 2s-2-.9-2-2zm9-5v-5c0-3.07-1.64-5.64-4.5-6.32V3c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C8.63 4.36 7 6.92 7 10v5l-2 2v1h14v-1l-2-2z"/>
            </svg>
        </template>
        
        <template x-if="unreadCount === 0">
            <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </template>

        <!-- عداد الإشعارات -->
        <span x-show="unreadCount > 0" class="notification-badge" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
        
        <!-- مؤشر الاتصال -->
        <span x-show="!isConnected" class="connection-indicator offline" :title="translations[currentLocale].offline"></span>
    </button>

    <!-- القائمة المنسدلة -->
    <div 
        x-show="isOpen" 
        @click.away="isOpen = false"
        x-transition
        class="notification-dropdown"
        style="display: none;"
        :dir="isRTL() ? 'rtl' : 'ltr'"
    >
        <!-- Header -->
        <div class="dropdown-header">
            <h3 class="dropdown-title">
                <span x-text="translations[currentLocale].notifications"></span>
                <span x-show="unreadCount > 0" class="unread-count-badge" x-text="unreadCount"></span>
            </h3>
            <button 
                x-show="unreadCount > 0" 
                @click="markAllAsRead"
                class="mark-all-read-btn"
            >
                <span x-text="translations[currentLocale].mark_all_as_read"></span>
            </button>
        </div>

        <!-- Filters -->
        <div class="dropdown-filters">
            <button 
                @click="filter = 'all'" 
                :class="{'active': filter === 'all'}"
                class="filter-btn"
            >
                <span x-text="translations[currentLocale].notification_filters.all"></span>
            </button>
            <button 
                @click="filter = 'message'" 
                :class="{'active': filter === 'message'}"
                class="filter-btn"
            >
                📨 <span x-text="translations[currentLocale].notification_types.message"></span>
            </button>
            <button 
                @click="filter = 'task'" 
                :class="{'active': filter === 'task'}"
                class="filter-btn"
            >
                📋 <span x-text="translations[currentLocale].notification_types.task"></span>
            </button>
            <button 
                @click="filter = 'asset'" 
                :class="{'active': filter === 'asset'}"
                class="filter-btn"
            >
                📱 <span x-text="translations[currentLocale].notification_types.asset"></span>
            </button>
            <button 
                @click="filter = 'birthday'" 
                :class="{'active': filter === 'birthday'}"
                class="filter-btn"
            >
                🎂 <span x-text="translations[currentLocale].notification_types.birthday"></span>
            </button>
        </div>

        <!-- Notifications List -->
        <div class="dropdown-content">
            <template x-if="loading && notifications.length === 0">
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p x-text="translations[currentLocale].loading"></p>
                </div>
            </template>

            <template x-if="!loading && filteredNotifications.length === 0">
                <div class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p x-text="translations[currentLocale].no_notifications"></p>
                </div>
            </template>

            <template x-for="notification in filteredNotifications" :key="notification.id">
                <div 
                    @click="handleNotificationClick(notification)"
                    :class="{'unread': !notification.is_read}"
                    class="notification-item"
                >
                    <div class="notification-icon-wrapper">
                        <template x-if="notification.type === 'message'">
                            <span class="type-icon text-blue-500">📨</span>
                        </template>
                        <template x-if="notification.type === 'task'">
                            <span class="type-icon text-green-500">📋</span>
                        </template>
                        <template x-if="notification.type === 'asset'">
                            <span class="type-icon text-yellow-500">📱</span>
                        </template>
                        <template x-if="notification.type === 'birthday'">
                            <span class="type-icon text-pink-500">🎂</span>
                        </template>
                        <span x-show="!notification.is_read" class="unread-dot"></span>
                    </div>

                    <div class="notification-content">
                        <div class="notification-header">
                            <h4 class="notification-title" x-text="(() => {
                                // Get locale from document element or fallback
                                const locale = (document.documentElement.getAttribute('lang') || document.documentElement.lang || 'ar').split('-')[0];
                                
                                // For birthday notifications with metadata, use translations
                                if (notification.type === 'birthday' && notification.metadata) {
                                    if (locale === 'ar' && notification.metadata.title_ar) {
                                        return notification.metadata.title_ar;
                                    }
                                    if (locale === 'en' && notification.metadata.title_en) {
                                        return notification.metadata.title_en;
                                    }
                                    // Fallback: if metadata exists but wrong locale
                                    if (locale === 'en' && notification.metadata.title_ar) {
                                        return notification.metadata.title_en || notification.metadata.title_ar;
                                    }
                                    if (locale === 'ar' && notification.metadata.title_en) {
                                        return notification.metadata.title_ar || notification.metadata.title_en;
                                    }
                                }
                                // For old birthday notifications without metadata, translate based on locale
                                if (notification.type === 'birthday') {
                                    // Check if title contains Arabic text
                                    const title = notification.title || '';
                                    const isArabicTitle = /[\u0600-\u06FF]/.test(title);
                                    
                                    if (locale === 'en' && isArabicTitle) {
                                        return 'Happy Birthday! 🎉';
                                    } else if (locale === 'ar' && !isArabicTitle) {
                                        return 'عيد ميلاد سعيد! 🎉';
                                    }
                                }
                                return notification.localized_title || notification.title;
                            })()"></h4>
                            <span class="notification-time" x-text="notification.time_ago"></span>
                        </div>
                        <p class="notification-body" x-text="(() => {
                            // Get locale from document element or fallback
                            const locale = (document.documentElement.getAttribute('lang') || document.documentElement.lang || 'ar').split('-')[0];
                            
                            // For birthday notifications with metadata, use translations
                            if (notification.type === 'birthday' && notification.metadata) {
                                if (locale === 'ar' && notification.metadata.body_ar) {
                                    return notification.metadata.body_ar;
                                }
                                if (locale === 'en' && notification.metadata.body_en) {
                                    return notification.metadata.body_en;
                                }
                                // Fallback: if metadata exists but wrong locale, use opposite
                                if (locale === 'en' && notification.metadata.body_ar) {
                                    return notification.metadata.body_en || notification.metadata.body_ar;
                                }
                                if (locale === 'ar' && notification.metadata.body_en) {
                                    return notification.metadata.body_ar || notification.metadata.body_en;
                                }
                            }
                            // For old birthday notifications without metadata, translate based on locale
                            if (notification.type === 'birthday') {
                                // Check if body contains Arabic text
                                const body = notification.body || '';
                                const isArabicBody = /[\u0600-\u06FF]/.test(body);
                                
                                if (locale === 'en' && isArabicBody) {
                                    // Extract name from old format if exists, otherwise simple translation
                                    return 'It\'s their birthday today! We wish them a wonderful day! 🎂';
                                } else if (locale === 'ar' && !isArabicBody) {
                                    return 'اليوم هو عيد ميلاد! نتمنى له يوماً سعيداً! 🎂';
                                }
                            }
                            return notification.localized_body || notification.body;
                        })()"></p>
                        <div x-show="notification.actor_name && notification.type === 'birthday'" class="notification-actor">
                            <span class="actor-name" x-text="(() => {
                                if (!notification.actor_name && !notification.actor) return null;
                                const locale = document.documentElement.lang || 'ar';
                                if (notification.type === 'birthday' && notification.actor) {
                                    if (locale === 'en') return notification.actor.name || notification.actor_name;
                                    return notification.actor.name_ar || notification.actor.name || notification.actor_name;
                                }
                                return notification.actor_name;
                            })()"></span>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="hasMore && !loading">
                <button @click="loadMore" class="load-more-btn">
                    <span x-text="translations[currentLocale].load_more"></span>
                </button>
            </template>
        </div>
    </div>
</div>

<style>
.notification-bell-container {
    position: relative;
    display: inline-block;
}

.notification-bell-button {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.notification-bell-button:hover {
    background-color: rgba(59, 130, 246, 0.1);
}

.notification-icon {
    width: 24px;
    height: 24px;
    color: white;
    transition: color 0.2s;
}

.notification-icon.active {
    color: white;
    animation: bellRing 0.5s ease-in-out;
}

@keyframes bellRing {
    0%, 100% { transform: rotate(0deg); }
    10%, 30% { transform: rotate(-10deg); }
    20%, 40% { transform: rotate(10deg); }
}

.notification-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9px;
    background-color: #ef4444;
    color: white;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.connection-indicator {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ef4444;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 380px;
    max-height: 80vh;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1000;
}

/* Position adjustment based on language */
html[lang="en"] .notification-dropdown {
    right: -200px;
}

html[lang="ar"] .notification-dropdown {
    right: 200px;
}

.dropdown-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
}

.dropdown-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unread-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    background: #3b82f6;
    color: white;
    font-size: 12px;
    font-weight: 600;
    border-radius: 10px;
}

.mark-all-read-btn {
    font-size: 13px;
    color: #3b82f6;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
}

.mark-all-read-btn:hover {
    background-color: rgba(59, 130, 246, 0.1);
}

.dropdown-filters {
    padding: 12px 16px;
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #e5e7eb;
}

.filter-btn {
    padding: 6px 12px;
    font-size: 13px;
    color: #6b7280;
    background: transparent;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn:hover {
    background-color: #f3f4f6;
}

.filter-btn.active {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.dropdown-content {
    flex: 1;
    overflow-y: auto;
    max-height: 555px;
    height: 555px;
}

.loading-state, .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #9ca3af;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-icon {
    width: 48px;
    height: 48px;
    color: #d1d5db;
    margin-bottom: 12px;
}

.notification-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f9fafb;
}

.notification-item.unread {
    background-color: #eff6ff;
}

.notification-icon-wrapper {
    position: relative;
    flex-shrink: 0;
}

.type-icon {
    font-size: 2rem;
}

.unread-dot {
    position: absolute;
    top: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background: #3b82f6;
    border: 2px solid white;
    border-radius: 50%;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}

.notification-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.notification-time {
    font-size: 12px;
    color: #9ca3af;
    white-space: nowrap;
}

.notification-body {
    font-size: 13px;
    color: #6b7280;
    margin: 0 0 8px 0;
    line-height: 1.5;
}

.notification-actor {
    display: flex;
    align-items: center;
    gap: 6px;
}

.actor-name {
    font-size: 12px;
    color: #6b7280;
}

.load-more-btn {
    width: 100%;
    padding: 12px;
    font-size: 14px;
    color: #3b82f6;
    background: #f9fafb;
    border: none;
    border-top: 1px solid #e5e7eb;
    cursor: pointer;
}

.load-more-btn:hover {
    background-color: #f3f4f6;
}
</style>

