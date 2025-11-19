/**
 * Modern Chat Application
 * Advanced chat interface with real-time functionality
 */
class ModernChatApp {
    constructor(config) {
        this.config = config;
        this.currentChatId = null;
        this.currentChatType = null;
        this.messages = [];
        this.conversations = [];
        this.searchResults = [];
        this.selectedUsers = [];
        this.chatType = 'private';
        this.pollingInterval = null;
        this.lastMessageId = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadConversations();
        this.setupWebSocket();
        this.startNotificationCheck();
        
        // إيقاف الـ polling عند مغادرة الصفحة
        window.addEventListener('beforeunload', () => {
            this.stopMessagesPolling();
        });
        
        // إيقاف الـ polling عند إخفاء الصفحة (مثل تبديل Tab)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopMessagesPolling();
            } else if (this.currentChatId) {
                this.startMessagesPolling();
            }
        });
    }

    setupEventListeners() {
        // Global search
        const globalSearch = document.getElementById('globalSearch');
        if (globalSearch) {
            globalSearch.addEventListener('input', this.debounce((e) => {
                this.performGlobalSearch(e.target.value);
            }, 300));
        }

        // Chat type selection
        const chatTypeCards = document.querySelectorAll('.chat-type-card');
        chatTypeCards.forEach(card => {
            card.addEventListener('click', (e) => {
                this.selectChatType(e.currentTarget.dataset.type);
            });
        });

        // User search
        const userSearch = document.getElementById('userSearch');
        if (userSearch) {
            userSearch.addEventListener('input', this.debounce((e) => {
                this.searchUsers(e.target.value);
            }, 300));
        }

        // Group member search
        const groupMemberSearch = document.getElementById('groupMemberSearch');
        if (groupMemberSearch) {
            groupMemberSearch.addEventListener('input', this.debounce((e) => {
                this.searchGroupMembers(e.target.value);
            }, 300));
        }

        // Create chat button
        const createChatBtn = document.getElementById('createChatBtn');
        if (createChatBtn) {
            createChatBtn.addEventListener('click', () => {
                this.createChat();
            });
        }

        // File upload
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.handleFileUpload(e.target.files);
            });
        }

        // File upload area
        const fileUploadArea = document.getElementById('fileUploadArea');
        if (fileUploadArea) {
            fileUploadArea.addEventListener('click', () => {
                fileInput.click();
            });
        }

        // Drag and drop
        if (fileUploadArea) {
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('drag-over');
            });

            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.classList.remove('drag-over');
            });

            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('drag-over');
                this.handleFileUpload(e.dataTransfer.files);
            });
        }
    }

    setupWebSocket() {
        // WebSocket connection for real-time updates
        if (window.Echo) {
            this.echo = window.Echo;
            
            // Listen for new messages
            this.echo.channel('chat')
                .listen('MessageSent', (e) => {
                    this.handleNewMessage(e.message);
                })
                .listen('MessageRead', (e) => {
                    this.updateMessageStatus(e.messageId, 'read');
                })
                .listen('UserTyping', (e) => {
                    this.showTypingIndicator(e.userId, e.chatId);
                })
                .listen('UserOnline', (e) => {
                    this.updateUserStatus(e.userId, 'online');
                })
                .listen('UserOffline', (e) => {
                    this.updateUserStatus(e.userId, 'offline');
                });
        }
    }

    async loadConversations() {
        try {
            const response = await fetch(this.config.conversationsUrl, {
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                this.conversations = await response.json();
                this.renderConversations();
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }

    renderConversations() {
        const chatList = document.getElementById('chatList');
        if (!chatList) return;

        chatList.innerHTML = '';

        if (this.conversations.length === 0) {
            chatList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <p>لا توجد دردشات بعد</p>
                    <button class="btn btn-primary" id="startNewChatBtn">
                        بدء دردشة جديدة
                    </button>
                </div>
            `;
            return;
        }

        this.conversations.forEach(conversation => {
            const chatItem = this.createChatItem(conversation);
            chatList.appendChild(chatItem);
        });
    }

    createChatItem(conversation) {
        const isPrivate = conversation.type === 'private';
        const otherUser = isPrivate ? conversation.other_participant : null;
        const unreadCount = conversation.unread_count || 0;

        const chatItem = document.createElement('div');
        chatItem.className = `chat-item ${unreadCount > 0 ? 'unread' : ''}`;
        chatItem.dataset.chatId = conversation.id;
        chatItem.dataset.chatType = conversation.type;

        chatItem.innerHTML = `
            <div class="chat-avatar">
                ${this.getAvatarHTML(conversation, otherUser, isPrivate)}
                ${isPrivate ? `<div class="online-indicator" id="online-${otherUser?.id}"></div>` : ''}
            </div>
            <div class="chat-content">
                <div class="chat-name">
                    ${conversation.display_name}
                    ${isPrivate ? '<span class="chat-type-badge private">خاص</span>' : '<span class="chat-type-badge group">جماعي</span>'}
                </div>
                <div class="chat-preview">
                    ${this.getLastMessagePreview(conversation.last_message)}
                </div>
            </div>
            <div class="chat-meta">
                ${conversation.last_message ? `<div class="chat-time">${this.formatTime(conversation.last_message.created_at)}</div>` : ''}
                ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
            </div>
        `;

        chatItem.addEventListener('click', () => {
            this.selectChat(conversation.id, conversation.type);
        });

        return chatItem;
    }

    getAvatarHTML(conversation, otherUser, isPrivate) {
        if (isPrivate && otherUser?.profile_picture) {
            return `<img src="${this.config.assetUrl}/storage/${otherUser.profile_picture}" alt="${otherUser.name}" class="rounded-circle">`;
        } else if (!isPrivate && conversation.avatar) {
            return `<img src="${this.config.assetUrl}/storage/${conversation.avatar}" alt="${conversation.name}" class="rounded-circle">`;
        } else {
            const iconClass = isPrivate ? 'fas fa-user' : 'fas fa-users';
            const placeholderClass = isPrivate ? 'private' : 'group';
            return `<div class="avatar-placeholder ${placeholderClass}"><i class="${iconClass}"></i></div>`;
        }
    }

    getLastMessagePreview(lastMessage) {
        if (!lastMessage) return '<div class="chat-preview text-muted">لا توجد رسائل بعد</div>';

        switch (lastMessage.type) {
            case 'image':
                return '<i class="fas fa-image me-1"></i>صورة';
            case 'file':
                return '<i class="fas fa-file me-1"></i>ملف';
            case 'contact':
                return '<i class="fas fa-user me-1"></i>جهة اتصال';
            default:
                return this.truncateText(lastMessage.message, 50);
        }
    }

    async selectChat(chatId, chatType) {
        // إيقاف الـ polling القديم قبل بدء واحد جديد
        this.stopMessagesPolling();
        
        this.currentChatId = chatId;
        this.currentChatType = chatType;

        // Update UI
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-chat-id="${chatId}"]`).classList.add('active');

        // Show active chat
        document.getElementById('chatWelcome').style.display = 'none';
        document.getElementById('activeChat').style.display = 'block';

        // Load messages
        await this.loadMessages(chatId);

        // Mark as read
        this.markAsRead(chatId);

        // Update partner info
        this.updatePartnerInfo(chatId, chatType);

        // بدء التحديث التلقائي للرسائل (كل 500ms)
        this.startMessagesPolling();
    }

    startMessagesPolling() {
        // إيقاف أي polling سابق
        this.stopMessagesPolling();
        
        // بدء polling محسن كل 2 ثانية بدلاً من 500ms لتوفير الأداء
        this.pollingInterval = setInterval(() => {
            if (this.currentChatId) {
                // تحديث الرسائل في الشات الحالي
                this.loadMessages(this.currentChatId, true);
            }
            // تحديث قائمة المحادثات كل 10 ثواني بدلاً من كل مرة
            if (!this.conversationsPollingInterval) {
                this.startConversationsPolling();
            }
        }, 2000); // كل ثانيتين بدلاً من نصف ثانية
    }

    startConversationsPolling() {
        // تحديث قائمة المحادثات كل 10 ثواني
        this.conversationsPollingInterval = setInterval(() => {
            this.loadConversations();
        }, 10000);
    }

    stopConversationsPolling() {
        if (this.conversationsPollingInterval) {
            clearInterval(this.conversationsPollingInterval);
            this.conversationsPollingInterval = null;
        }
    }

    stopMessagesPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
        this.stopConversationsPolling();
    }

    async loadMessages(chatId, silent = false) {
        try {
            const url = this.config.loadMessagesUrl.replace(':id', chatId);
            const response = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                const oldMessagesCount = this.messages.length;
                this.messages = data.messages;
                
                // تحديث آخر message ID
                if (this.messages.length > 0) {
                    this.lastMessageId = this.messages[this.messages.length - 1].id;
                }
                
                // عرض الرسائل فقط إذا كان هناك رسائل جديدة أو أول مرة
                if (!silent || this.messages.length !== oldMessagesCount) {
                    this.renderMessages();
                    
                    // إذا كان في رسائل جديدة، اعمل scroll
                    if (silent && this.messages.length > oldMessagesCount) {
                        const container = document.getElementById('messagesContainer');
                        if (container) {
                            container.scrollTo({
                                top: container.scrollHeight,
                                behavior: 'smooth'
                            });
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    renderMessages() {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        container.innerHTML = '';

        this.messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            container.appendChild(messageElement);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    createMessageElement(message) {
        const isOwn = message.user_id === this.config.currentUserId;
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isOwn ? 'own' : 'other'} ${message.is_temp ? 'temp-message' : ''}`;
        messageDiv.dataset.messageId = message.id;

        // إضافة مؤشر التحميل للرسائل المؤقتة
        const loadingIndicator = message.is_temp ? 
            '<div class="message-status"><div class="status-icon sending"><i class="fas fa-spinner fa-spin"></i></div></div>' : '';

        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-text">${this.formatMessageContent(message)}</div>
                <div class="message-time">${this.formatTime(message.created_at)}</div>
                ${loadingIndicator}
            </div>
        `;

        return messageDiv;
    }

    formatMessageContent(message) {
        switch (message.type) {
            case 'image':
                return `<img src="${this.config.assetUrl}/storage/${message.attachment_url}" alt="Image" class="message-image">`;
            case 'file':
                return `<div class="message-file">
                    <i class="fas fa-file"></i>
                    <span>${message.attachment_name}</span>
                </div>`;
            case 'contact':
                return `<div class="message-contact">
                    <i class="fas fa-user"></i>
                    <span>جهة اتصال</span>
                </div>`;
            default:
                return this.escapeHtml(message.message);
        }
    }

    async sendMessage(content) {
        if (!this.currentChatId || !content.trim()) return;

        // إضافة الرسالة فوراً للواجهة لإعطاء شعور بالسرعة
        const tempMessage = {
            id: 'temp_' + Date.now(),
            chat_room_id: this.currentChatId,
            user_id: this.config.currentUserId,
            message: content,
            type: 'text',
            created_at: new Date().toISOString(),
            user: {
                id: this.config.currentUserId,
                name: 'أنت',
                profile_picture: null
            },
            is_temp: true
        };

        this.addMessageToUI(tempMessage);

        try {
            const response = await fetch(this.config.sendMessageUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    chat_room_id: this.currentChatId,
                    content: content
                })
            });

            if (response.ok) {
                // التحقق من نوع المحتوى قبل parsing JSON
                const contentType = response.headers.get('content-type');
                console.log('Response Content-Type:', contentType);
                
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text);
                    throw new Error('Server response is not JSON');
                }
                
                const data = await response.json();
                if (data.success) {
                    // إزالة الرسالة المؤقتة وإضافة الرسالة الحقيقية
                    this.removeTempMessage(tempMessage.id);
                    
                    // إضافة chat_room_id للرسالة إذا لم تكن موجودة
                    if (!data.message.chat_room_id && this.currentChatId) {
                        data.message.chat_room_id = this.currentChatId;
                    }
                    this.addMessageToUI(data.message);
                    this.updateConversationList(data.message);
                } else {
                    // إزالة الرسالة المؤقتة في حالة الفشل
                    this.removeTempMessage(tempMessage.id);
                    this.showErrorMessage('فشل في إرسال الرسالة');
                }
            } else {
                this.removeTempMessage(tempMessage.id);
                this.showErrorMessage('حدث خطأ في إرسال الرسالة');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.removeTempMessage(tempMessage.id);
            this.showErrorMessage('حدث خطأ في الاتصال');
        }
    }

    removeTempMessage(tempId) {
        const tempMessageElement = document.querySelector(`[data-message-id="${tempId}"]`);
        if (tempMessageElement) {
            tempMessageElement.remove();
        }
    }

    showErrorMessage(message) {
        // إضافة رسالة خطأ مؤقتة
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4757;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(255, 71, 87, 0.3);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;
        errorDiv.textContent = message;
        
        document.body.appendChild(errorDiv);
        
        // إزالة رسالة الخطأ بعد 3 ثواني
        setTimeout(() => {
            errorDiv.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => errorDiv.remove(), 300);
        }, 3000);
    }

    // دالة جديدة لإضافة الرسالة مباشرة للـ UI بدون انتظار
    addMessageToUI(message) {
        // إضافة الرسالة للمصفوفة
        this.messages.push(message);
        
        // إضافة الرسالة مباشرة للـ container
        const container = document.getElementById('messagesContainer');
        if (container) {
            const messageElement = this.createMessageElement(message);
            container.appendChild(messageElement);
            
            // Scroll to bottom بسلاسة
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    handleNewMessage(message) {
        this.messages.push(message);
        
        // استخدام chat_room_id بدلاً من chat_id لأن الـ backend بيرسل chat_room_id
        const chatId = message.chat_room_id || message.chat_id;
        
        if (chatId === this.currentChatId) {
            this.renderMessages();
        }

        // Update conversation list
        this.updateConversationList(message);
    }

    updateConversationList(message) {
        // استخدام chat_room_id بدلاً من chat_id لأن الـ backend بيرسل chat_room_id
        const chatId = message.chat_room_id || message.chat_id;
        const conversation = this.conversations.find(c => c.id === chatId);
        if (conversation) {
            conversation.last_message = message;
            conversation.last_message_at = message.created_at;
            
            if (chatId !== this.currentChatId) {
                conversation.unread_count = (conversation.unread_count || 0) + 1;
            }
        }

        this.renderConversations();
    }

    async markAsRead(chatId) {
        try {
            const url = this.config.markAsReadUrl.replace(':id', chatId);
            await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            });
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    async performGlobalSearch(query) {
        if (query.length < 3) return;

        try {
            const response = await fetch(`${this.config.globalSearchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                this.searchResults = await response.json();
                this.renderSearchResults();
            }
        } catch (error) {
            console.error('Error performing search:', error);
        }
    }

    renderSearchResults() {
        // Implement search results display
        console.log('Search results:', this.searchResults);
    }

    async searchUsers(query) {
        if (query.length < 2) return;

        try {
            const response = await fetch(`${this.config.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
            const users = await response.json();
                this.renderUserSearchResults(users);
            }
        } catch (error) {
            console.error('Error searching users:', error);
        }
    }

    renderUserSearchResults(users) {
        const container = document.getElementById('userSearchResults');
        if (!container) return;

        container.innerHTML = '';

        users.forEach(user => {
            const userDiv = document.createElement('div');
            userDiv.className = 'user-search-item';
            userDiv.innerHTML = `
                <div class="user-avatar">
                        ${user.profile_picture ? 
                        `<img src="${this.config.assetUrl}/storage/${user.profile_picture}" alt="${user.name}">` :
                            `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`
                        }
                    </div>
                <div class="user-info">
                    <div class="user-name">${user.name}</div>
                    <div class="user-email">${user.email}</div>
                </div>
                <button class="btn btn-sm btn-primary select-user" data-user-id="${user.id}">
                    اختيار
                </button>
            `;

            userDiv.querySelector('.select-user').addEventListener('click', () => {
                this.selectUser(user);
            });

            container.appendChild(userDiv);
        });
    }

    selectUser(user) {
        if (this.chatType === 'private') {
            this.selectedUsers = [user];
            this.updateCreateButton();
        } else {
            if (!this.selectedUsers.find(u => u.id === user.id)) {
                this.selectedUsers.push(user);
                this.updateSelectedMembers();
            }
        }
    }

    updateSelectedMembers() {
        const container = document.getElementById('selectedMembers');
        if (!container) return;

        container.innerHTML = '';

        this.selectedUsers.forEach(user => {
            const memberDiv = document.createElement('div');
            memberDiv.className = 'selected-member';
            memberDiv.innerHTML = `
                <div class="member-avatar">
                    ${user.profile_picture ? 
                        `<img src="${this.config.assetUrl}/storage/${user.profile_picture}" alt="${user.name}">` :
                        `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`
                    }
                </div>
                <span class="member-name">${user.name}</span>
                <button class="btn-remove-member" data-user-id="${user.id}">
                    <i class="fas fa-times"></i>
                </button>
            `;

            memberDiv.querySelector('.btn-remove-member').addEventListener('click', () => {
                this.removeUser(user.id);
            });

            container.appendChild(memberDiv);
        });

        this.updateCreateButton();
    }

    removeUser(userId) {
        this.selectedUsers = this.selectedUsers.filter(u => u.id !== userId);
        this.updateSelectedMembers();
    }

    selectChatType(type) {
        this.chatType = type;
        this.selectedUsers = [];

        document.querySelectorAll('.chat-type-card').forEach(card => {
            card.classList.remove('selected');
        });
        document.querySelector(`[data-type="${type}"]`).classList.add('selected');

        const userSelection = document.getElementById('userSelection');
        const groupSelection = document.getElementById('groupSelection');

        if (type === 'private') {
            userSelection.style.display = 'block';
            groupSelection.style.display = 'none';
        } else {
            userSelection.style.display = 'none';
            groupSelection.style.display = 'block';
        }

        this.updateCreateButton();
    }

    updateCreateButton() {
        const createBtn = document.getElementById('createChatBtn');
        if (!createBtn) return;

        const canCreate = this.chatType === 'private' ? 
            this.selectedUsers.length === 1 : 
            this.selectedUsers.length > 0;

        createBtn.disabled = !canCreate;
    }

    async createChat() {
        if (this.chatType === 'private' && this.selectedUsers.length !== 1) return;
        if (this.chatType === 'group' && this.selectedUsers.length === 0) return;

        try {
            const response = await fetch(this.config.createChatUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: this.chatType,
                    users: this.selectedUsers.map(u => u.id),
                    name: this.chatType === 'group' ? document.getElementById('groupName').value : null
                })
            });

            if (response.ok) {
                const chat = await response.json();
                this.selectChat(chat.id, chat.type);
                this.closeModal('newChatModal');
            }
        } catch (error) {
            console.error('Error creating chat:', error);
        }
    }

    handleFileUpload(files) {
        // Implement file upload functionality
        console.log('Files to upload:', files);
    }

    updateUserStatus(userId, status) {
        const indicator = document.getElementById(`online-${userId}`);
        if (indicator) {
            indicator.style.background = status === 'online' ? 'var(--success-color)' : 'var(--text-muted)';
        }
    }

    showTypingIndicator(userId, chatId) {
        if (chatId === this.currentChatId) {
            // Show typing indicator
            console.log(`User ${userId} is typing in chat ${chatId}`);
        }
    }

    updateMessageStatus(messageId, status) {
        const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
        if (messageElement) {
            messageElement.dataset.status = status;
        }
    }

    updatePartnerInfo(chatId, chatType) {
        const partnerName = document.getElementById('partnerName');
        const partnerStatus = document.getElementById('partnerStatus');

        if (chatType === 'private') {
            const conversation = this.conversations.find(c => c.id === chatId);
            const otherUser = conversation?.other_participant;
            
            if (otherUser) {
                partnerName.textContent = otherUser.name;
                partnerStatus.textContent = 'آخر ظهور منذ 5 دقائق';
            }
        } else {
            const conversation = this.conversations.find(c => c.id === chatId);
            if (conversation) {
                partnerName.textContent = conversation.name;
                partnerStatus.textContent = `${conversation.participant_count} أعضاء`;
            }
        }
    }

    startNotificationCheck() {
        // Check for notifications every 30 seconds
        setInterval(() => {
            this.checkNotifications();
        }, 30000);
    }

    async checkNotifications() {
        try {
            const response = await fetch('/notifications/unread-count', {
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationCount(data.count);
            }
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    updateNotificationCount(count) {
        const badge = document.getElementById('notificationCount');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) return 'الآن';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}د`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}س`;
        if (diff < 604800000) return `${Math.floor(diff / 86400000)}أ`;
        
        return date.toLocaleDateString('ar-SA');
    }

    truncateText(text, length) {
        return text.length > length ? text.substring(0, length) + '...' : text;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // The ModernChatApp will be initialized in the Blade template
    console.log('Chat application ready');
});