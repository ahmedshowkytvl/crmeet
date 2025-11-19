// نظام التحقق من صحة المحادثات
class ChatValidator {
    constructor() {
        this.currentRoomId = null;
        this.validationResults = null;
    }

    /**
     * التحقق من صحة المحادثة الحالية
     * @param {number} roomId - معرف الغرفة
     * @returns {Promise<Object>} نتيجة التحقق
     */
    async validateCurrentChat(roomId) {
        try {
            this.currentRoomId = roomId;
            const response = await fetch(`/api/chat/validate/${roomId}`);
            const data = await response.json();
            
            this.validationResults = data;
            this.handleValidationResult(data);
            
            return data;
        } catch (error) {
            console.error('خطأ في التحقق من المحادثة:', error);
            return {
                is_valid: false,
                message: 'خطأ في الاتصال بالخادم',
                error: error.message
            };
        }
    }

    /**
     * معالجة نتيجة التحقق
     * @param {Object} result - نتيجة التحقق
     */
    handleValidationResult(result) {
        if (result.is_valid) {
            this.showSuccessMessage(result.message);
            this.enablePrivateChatFeatures();
        } else {
            this.showErrorMessage(result.message);
            this.disablePrivateChatFeatures();
            this.showGroupChatSuggestion();
        }
    }

    /**
     * عرض رسالة نجاح
     * @param {string} message - الرسالة
     */
    showSuccessMessage(message) {
        this.showNotification(message, 'success');
    }

    /**
     * عرض رسالة خطأ
     * @param {string} message - الرسالة
     */
    showErrorMessage(message) {
        this.showNotification(message, 'error');
    }

    /**
     * عرض إشعار
     * @param {string} message - الرسالة
     * @param {string} type - نوع الإشعار
     */
    showNotification(message, type = 'info') {
        // إنشاء عنصر الإشعار
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // إضافة الإشعار إلى الصفحة
        document.body.appendChild(notification);

        // إزالة الإشعار تلقائياً بعد 5 ثوان
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * تفعيل ميزات المحادثة الخاصة
     */
    enablePrivateChatFeatures() {
        // إضافة فئة CSS للمحادثة الخاصة
        const chatContainer = document.querySelector('.chat-main');
        if (chatContainer) {
            chatContainer.classList.add('private-chat-valid');
        }

        // إضافة مؤشر بصري
        this.addPrivateChatIndicator();
    }

    /**
     * تعطيل ميزات المحادثة الخاصة
     */
    disablePrivateChatFeatures() {
        // إزالة فئة CSS للمحادثة الخاصة
        const chatContainer = document.querySelector('.chat-main');
        if (chatContainer) {
            chatContainer.classList.remove('private-chat-valid');
            chatContainer.classList.add('group-chat-detected');
        }

        // إزالة المؤشر البصري
        this.removePrivateChatIndicator();
    }

    /**
     * إضافة مؤشر المحادثة الخاصة
     */
    addPrivateChatIndicator() {
        const header = document.querySelector('.chat-header');
        if (header && !header.querySelector('.private-chat-indicator')) {
            const indicator = document.createElement('div');
            indicator.className = 'private-chat-indicator';
            indicator.innerHTML = `
                <i class="fas fa-lock me-2"></i>
                <span>محادثة خاصة</span>
            `;
            indicator.style.cssText = `
                background: #d4edda;
                color: #155724;
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                font-weight: 500;
                border: 1px solid #c3e6cb;
            `;
            header.appendChild(indicator);
        }
    }

    /**
     * إزالة مؤشر المحادثة الخاصة
     */
    removePrivateChatIndicator() {
        const indicator = document.querySelector('.private-chat-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    /**
     * عرض اقتراح المحادثة الجماعية
     */
    showGroupChatSuggestion() {
        const chatContainer = document.querySelector('.chat-main');
        if (chatContainer && !chatContainer.querySelector('.group-chat-suggestion')) {
            const suggestion = document.createElement('div');
            suggestion.className = 'group-chat-suggestion';
            suggestion.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-users me-2"></i>
                    <strong>محادثة جماعية مكتشفة</strong>
                    <p class="mb-2">هذه محادثة جماعية تحتوي على ${this.validationResults?.participant_count || 0} مشاركين.</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="chatValidator.createPrivateChat()">
                            <i class="fas fa-user-plus me-1"></i>
                            إنشاء محادثة خاصة
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="chatValidator.continueAsGroup()">
                            <i class="fas fa-users me-1"></i>
                            متابعة كمجموعة
                        </button>
                    </div>
                </div>
            `;
            suggestion.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                background: white;
                padding: 1rem;
                border-bottom: 1px solid #dee2e6;
            `;
            chatContainer.style.position = 'relative';
            chatContainer.insertBefore(suggestion, chatContainer.firstChild);
        }
    }

    /**
     * إنشاء محادثة خاصة جديدة
     */
    async createPrivateChat() {
        try {
            // جلب قائمة المستخدمين المتاحين
            const users = await this.getAvailableUsers();
            if (users.length === 0) {
                this.showErrorMessage('لا يوجد مستخدمون متاحون');
                return;
            }

            // عرض نافذة اختيار المستخدم
            this.showUserSelectionModal(users);
        } catch (error) {
            console.error('خطأ في إنشاء المحادثة الخاصة:', error);
            this.showErrorMessage('خطأ في إنشاء المحادثة الخاصة');
        }
    }

    /**
     * جلب المستخدمين المتاحين
     * @returns {Promise<Array>} قائمة المستخدمين
     */
    async getAvailableUsers() {
        try {
            const response = await fetch('/api/users');
            const data = await response.json();
            return data.users || [];
        } catch (error) {
            console.error('خطأ في جلب المستخدمين:', error);
            return [];
        }
    }

    /**
     * عرض نافذة اختيار المستخدم
     * @param {Array} users - قائمة المستخدمين
     */
    showUserSelectionModal(users) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إنشاء محادثة خاصة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>اختر المستخدم الذي تريد الدردشة معه:</p>
                        <div class="list-group">
                            ${users.map(user => `
                                <button class="list-group-item list-group-item-action" onclick="chatValidator.selectUser(${user.id}, '${user.username}')">
                                    <i class="fas fa-user me-2"></i>
                                    ${user.username}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // إزالة النافذة عند الإغلاق
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * اختيار مستخدم لإنشاء محادثة خاصة
     * @param {number} userId - معرف المستخدم
     * @param {string} username - اسم المستخدم
     */
    async selectUser(userId, username) {
        try {
            const response = await fetch('/api/chat/create-private', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user1_id: this.getCurrentUserId(),
                    user2_id: userId
                })
            });

            const data = await response.json();
            if (data.success) {
                this.showSuccessMessage(`تم إنشاء محادثة خاصة مع ${username}`);
                // إعادة توجيه إلى المحادثة الجديدة
                window.location.href = `/chat/${data.room_id}`;
            } else {
                this.showErrorMessage(data.message || 'خطأ في إنشاء المحادثة');
            }
        } catch (error) {
            console.error('خطأ في اختيار المستخدم:', error);
            this.showErrorMessage('خطأ في إنشاء المحادثة');
        }
    }

    /**
     * الحصول على معرف المستخدم الحالي
     * @returns {number} معرف المستخدم
     */
    getCurrentUserId() {
        // يمكن تحسين هذا لاستخدام بيانات المستخدم الحالي
        return 123; // معرف مؤقت
    }

    /**
     * متابعة كمجموعة
     */
    continueAsGroup() {
        // إزالة اقتراح المحادثة الجماعية
        const suggestion = document.querySelector('.group-chat-suggestion');
        if (suggestion) {
            suggestion.remove();
        }

        // إضافة مؤشر المحادثة الجماعية
        this.addGroupChatIndicator();
    }

    /**
     * إضافة مؤشر المحادثة الجماعية
     */
    addGroupChatIndicator() {
        const header = document.querySelector('.chat-header');
        if (header && !header.querySelector('.group-chat-indicator')) {
            const indicator = document.createElement('div');
            indicator.className = 'group-chat-indicator';
            indicator.innerHTML = `
                <i class="fas fa-users me-2"></i>
                <span>محادثة جماعية (${this.validationResults?.participant_count || 0} مشاركين)</span>
            `;
            indicator.style.cssText = `
                background: #fff3cd;
                color: #856404;
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                font-weight: 500;
                border: 1px solid #ffeaa7;
            `;
            header.appendChild(indicator);
        }
    }

    /**
     * التحقق التلقائي من المحادثة عند تحميل الصفحة
     */
    autoValidateOnLoad() {
        const currentPath = window.location.pathname;
        const roomIdMatch = currentPath.match(/\/chat\/(\d+)/);
        
        if (roomIdMatch) {
            const roomId = parseInt(roomIdMatch[1]);
            this.validateCurrentChat(roomId);
        }
    }
}

// إنشاء مثيل عام من المدقق
window.chatValidator = new ChatValidator();

// التحقق التلقائي عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    window.chatValidator.autoValidateOnLoad();
});

// تصدير الكلاس للاستخدام العام
window.ChatValidator = ChatValidator;



