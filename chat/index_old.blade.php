@extends('layouts.app')

@section('title', 'الدردشة الداخلية')

@section('content')
<div class="chat-container">
    <!-- Header -->
    <div class="chat-header">
        <div class="header-left">
            <div class="search-container">
                <input type="text" class="search-input" id="globalSearch" placeholder="Search anything...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        <div class="header-center">
            <div class="header-icons">
                <button class="icon-btn" id="addChatBtn" title="إضافة دردشة جديدة">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="icon-btn" id="shortcutBtn" title="اختصارات">
                    <i class="fas fa-keyboard"></i>
                </button>
                <span class="quick-search-text">Quick search</span>
            </div>
        </div>
        <div class="header-right">
            <div class="user-profile">
                <div class="user-avatar">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="{{ auth()->user()->name }}">
                    @else
                        <div class="avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                <span class="user-name">{{ auth()->user()->name }}</span>
            </div>
            <button class="icon-btn notification-btn" id="notificationsBtn" title="الإشعارات">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationCount">0</span>
            </button>
        </div>
    </div>

    <div class="chat-main-layout">
        <!-- Left Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <h3 class="sidebar-title">Message</h3>
            </div>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn active" data-filter="all">All</button>
                <button class="tab-btn" data-filter="unread">Unread</button>
            </div>

            <!-- Chat List -->
            <div class="chat-list" id="chatList">
                @forelse($chatRooms as $chatRoom)
                    @php
                        $unreadCount = $chatRoom->participants()
                            ->where('user_id', auth()->id())
                            ->first()?->unread_count ?? 0;
                        $isPrivate = $chatRoom->type === 'private';
                        $otherUser = $isPrivate ? $chatRoom->getOtherParticipant(auth()->id()) : null;
                    @endphp
                    <div class="chat-item {{ $unreadCount > 0 ? 'unread' : '' }}" 
                         data-chat-id="{{ $chatRoom->id }}" 
                         data-chat-type="{{ $chatRoom->type }}">
                        <div class="chat-avatar">
                            @if($isPrivate && $otherUser && $otherUser->profile_picture)
                                <img src="{{ asset('storage/' . $otherUser->profile_picture) }}" 
                                     alt="{{ $otherUser->name }}" 
                                     class="rounded-circle">
                            @elseif(!$isPrivate && $chatRoom->avatar)
                                <img src="{{ asset('storage/' . $chatRoom->avatar) }}" 
                                     alt="{{ $chatRoom->name }}" 
                                     class="rounded-circle">
                            @else
                                <div class="avatar-placeholder {{ $isPrivate ? 'private' : 'group' }}">
                                    @if($isPrivate)
                                        <i class="fas fa-user"></i>
                                    @else
                                        <i class="fas fa-users"></i>
                                    @endif
                                </div>
                            @endif
                            @if($isPrivate)
                                <div class="online-indicator" id="online-{{ $otherUser?->id }}"></div>
                            @endif
                        </div>
                        <div class="chat-content">
                            <div class="chat-name">
                                {{ $chatRoom->getDisplayName(auth()->id()) }}
                                @if($isPrivate)
                                    <span class="chat-type-badge private">خاص</span>
                                @else
                                    <span class="chat-type-badge group">جماعي</span>
                                @endif
                            </div>
                            @if($chatRoom->lastMessage)
                                <div class="chat-preview">
                                    @if($chatRoom->lastMessage->type === 'image')
                                        <i class="fas fa-image me-1"></i>صورة
                                    @elseif($chatRoom->lastMessage->type === 'file')
                                        <i class="fas fa-file me-1"></i>ملف
                                    @elseif($chatRoom->lastMessage->type === 'contact')
                                        <i class="fas fa-user me-1"></i>جهة اتصال
                                    @else
                                        {{ Str::limit($chatRoom->lastMessage->message, 50) }}
                                    @endif
                                </div>
                            @else
                                <div class="chat-preview text-muted">لا توجد رسائل بعد</div>
                            @endif
                        </div>
                        <div class="chat-meta">
                            @if($chatRoom->lastMessage)
                                <div class="chat-time">
                                    {{ $chatRoom->lastMessage->created_at->diffForHumans() }}
                                </div>
                            @endif
                            @if($unreadCount > 0)
                                <span class="unread-badge">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>لا توجد دردشات بعد</p>
                        <button class="btn btn-primary" id="startNewChatBtn">
                            بدء دردشة جديدة
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Chat Window -->
        <div class="chat-main-window">
            <div class="chat-welcome" id="chatWelcome">
                <div class="welcome-content">
                    <i class="fas fa-comments fa-4x mb-4"></i>
                    <h4>مرحباً بك في الدردشة الداخلية</h4>
                    <p>اختر دردشة من القائمة الجانبية أو ابدأ دردشة جديدة</p>
                    <button class="btn btn-primary" id="startNewChatBtn2">
                        <i class="fas fa-plus me-2"></i>بدء دردشة جديدة
                    </button>
                </div>
            </div>

            <!-- Active Chat (Hidden by default) -->
            <div class="active-chat" id="activeChat" style="display: none;">
                <div class="chat-header-bar">
                    <div class="chat-partner-info">
                        <div class="partner-avatar" id="partnerAvatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="partner-details">
                            <h5 class="partner-name" id="partnerName">Courtney Henry</h5>
                            <span class="partner-status" id="partnerStatus">آخر ظهور منذ 5 دقائق</span>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="action-btn" title="مكالمة صوتية">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="action-btn" title="مكالمة فيديو">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="action-btn" title="معلومات الدردشة">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="messages-container" id="messagesContainer">
                    <!-- Messages will be loaded here -->
                </div>

                <div class="message-input-area">
                    <div class="input-controls">
                        <button class="control-btn" id="emojiBtn" title="إضافة إيموجي">
                            <i class="fas fa-smile"></i>
                        </button>
                        <button class="control-btn" id="attachBtn" title="إرفاق ملف">
                            <i class="fas fa-paperclip"></i>
                        </button>
                    </div>
                    <div class="message-input-wrapper">
                        <input type="text" class="message-input" id="messageInput" placeholder="اكتب رسالتك...">
                        <button class="send-btn" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">بدء دردشة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="chat-type-selection mb-4">
                    <div class="row">
                        <div class="col-6">
                            <div class="chat-type-card" data-type="private">
                                <div class="type-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h6>دردشة خاصة</h6>
                                <p>دردشة مع موظف واحد</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="chat-type-card" data-type="group">
                                <div class="type-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h6>دردشة جماعية</h6>
                                <p>دردشة مع عدة موظفين</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="user-selection" id="userSelection">
                    <div class="mb-3">
                        <label for="userSearch" class="form-label">البحث عن موظف</label>
                        <input type="text" class="form-control" id="userSearch" placeholder="اكتب اسم الموظف أو البريد الإلكتروني...">
                        <div id="userSearchResults" class="mt-2"></div>
                    </div>
                </div>

                <div class="group-selection" id="groupSelection" style="display: none;">
                    <div class="mb-3">
                        <label for="groupName" class="form-label">اسم المجموعة</label>
                        <input type="text" class="form-control" id="groupName" placeholder="أدخل اسم المجموعة...">
                    </div>
                    <div class="mb-3">
                        <label for="groupMembers" class="form-label">أعضاء المجموعة</label>
                        <div class="selected-members" id="selectedMembers"></div>
                        <input type="text" class="form-control" id="groupMemberSearch" placeholder="ابحث عن أعضاء...">
                        <div id="groupMemberResults" class="mt-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="createChatBtn" disabled>إنشاء الدردشة</button>
            </div>
        </div>
    </div>
</div>

<!-- File Upload Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">رفع ملف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="file-upload-area" id="fileUploadArea">
                    <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                    <p>اسحب الملفات هنا أو انقر للاختيار</p>
                    <input type="file" id="fileInput" multiple style="display: none;">
                </div>
                <div class="file-preview" id="filePreview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="uploadFilesBtn" disabled>رفع الملفات</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/chat.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modern chat functionality
    window.chatApp = new ModernChatApp({
        searchUrl: '{{ route("chat.search.users") }}',
        globalSearchUrl: '{{ route("chat.api.search.global") }}',
        sendMessageUrl: '{{ route("chat.messages.send-text") }}',
        sendContactUrl: '{{ route("chat.messages.send-contact") }}',
        uploadFileUrl: '{{ route("chat.files.upload") }}',
        loadMessagesUrl: '{{ route("chat.api.messages", ":id") }}',
        markAsReadUrl: '{{ route("chat.api.mark-read", ":id") }}',
        createChatUrl: '{{ route("chat.api.create") }}',
        conversationsUrl: '{{ route("chat.api.conversations") }}',
        csrfToken: '{{ csrf_token() }}',
        currentUserId: {{ auth()->id() ?? 0 }},
        assetUrl: '{{ asset("") }}'
    });

    // Initialize UI interactions
    initializeChatUI();
});

function initializeChatUI() {
    // Tab filtering
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterChatList(filter);
        });
    });

    // Chat type selection
    const chatTypeCards = document.querySelectorAll('.chat-type-card');
    const userSelection = document.getElementById('userSelection');
    const groupSelection = document.getElementById('groupSelection');
    const createChatBtn = document.getElementById('createChatBtn');

    chatTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            chatTypeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            
            const type = this.dataset.type;
            if (type === 'private') {
                userSelection.style.display = 'block';
                groupSelection.style.display = 'none';
            } else {
                userSelection.style.display = 'none';
                groupSelection.style.display = 'block';
            }
            
            createChatBtn.disabled = false;
        });
    });

    // Chat item selection
    const chatItems = document.querySelectorAll('.chat-item');
    chatItems.forEach(item => {
        item.addEventListener('click', function() {
            chatItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            const chatId = this.dataset.chatId;
            const chatType = this.dataset.chatType;
            loadChat(chatId, chatType);
        });
    });

    // Global search
    const globalSearch = document.getElementById('globalSearch');
    globalSearch.addEventListener('input', function() {
        const query = this.value;
        if (query.length > 2) {
            performGlobalSearch(query);
        }
    });

    // New chat buttons
    const newChatBtns = document.querySelectorAll('#addChatBtn, #startNewChatBtn, #startNewChatBtn2');
    newChatBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('newChatModal'));
            modal.show();
        });
    });

    // Send message
    const sendBtn = document.getElementById('sendBtn');
    const messageInput = document.getElementById('messageInput');
    
    sendBtn.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // File upload
    const attachBtn = document.getElementById('attachBtn');
    attachBtn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('fileUploadModal'));
        modal.show();
    });
}

function filterChatList(filter) {
    const chatItems = document.querySelectorAll('.chat-item');
    chatItems.forEach(item => {
        if (filter === 'all') {
            item.style.display = 'flex';
        } else if (filter === 'unread') {
            if (item.classList.contains('unread')) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

function loadChat(chatId, chatType) {
    // Hide welcome screen and show active chat
    document.getElementById('chatWelcome').style.display = 'none';
    document.getElementById('activeChat').style.display = 'block';
    
    // Load messages for this chat
    window.chatApp.loadMessages(chatId);
    
    // Update partner info
    updatePartnerInfo(chatId, chatType);
}

function updatePartnerInfo(chatId, chatType) {
    // This would be populated with real data from the selected chat
    const partnerName = document.getElementById('partnerName');
    const partnerStatus = document.getElementById('partnerStatus');
    
    if (chatType === 'private') {
        partnerName.textContent = 'Courtney Henry';
        partnerStatus.textContent = 'آخر ظهور منذ 5 دقائق';
    } else {
        partnerName.textContent = 'فريق العمل';
        partnerStatus.textContent = '5 أعضاء نشطين';
    }
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (message) {
        window.chatApp.sendMessage(message);
        messageInput.value = '';
    }
}

function performGlobalSearch(query) {
    // Implement global search functionality
    console.log('Searching for:', query);
    // This would call the backend search API
}
</script>
@endpush
