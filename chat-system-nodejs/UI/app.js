// Connect to Socket.IO server
const socket = io('http://localhost:3001');

let currentRoomId = null;
let currentUserId = null;

// Get user ID from localStorage or prompt
function getUserId() {
    let userId = localStorage.getItem('chatUserId');
    if (!userId) {
        userId = prompt('أدخل رقم المستخدم (User ID):');
        if (userId) {
            localStorage.setItem('chatUserId', userId);
        }
    }
    return userId;
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    currentUserId = getUserId();
    if (!currentUserId) {
        alert('يجب إدخال رقم المستخدم');
        return;
    }

    loadConversations();
    setupEventListeners();
});

// Load conversations
async function loadConversations() {
    try {
        const response = await fetch(`http://localhost:3001/api/conversations?userId=${currentUserId}`);
        const data = await response.json();
        
        if (data.conversations && data.conversations.length > 0) {
            renderConversations(data.conversations);
        } else {
            document.getElementById('conversationsList').innerHTML = 
                '<div class="loading">لا توجد محادثات</div>';
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
        document.getElementById('conversationsList').innerHTML = 
            '<div class="loading">خطأ في تحميل المحادثات</div>';
    }
}

// Render conversations
function renderConversations(conversations) {
    const list = document.getElementById('conversationsList');
    list.innerHTML = conversations.map(conv => `
        <div class="conversation-item" data-room-id="${conv.id}" onclick="openConversation(${conv.id}, '${conv.name || 'محادثة'}')">
            <div class="conversation-avatar">
                ${(conv.name || 'U').charAt(0).toUpperCase()}
            </div>
            <div class="conversation-info">
                <div class="conversation-name">${conv.name || 'محادثة'}</div>
                <div class="conversation-last-message">${conv.last_message || ''}</div>
            </div>
            <div class="conversation-meta">
                <div class="conversation-time">${formatTime(conv.last_message_time)}</div>
                ${conv.unread_count > 0 ? `<div class="unread-badge">${conv.unread_count}</div>` : ''}
            </div>
        </div>
    `).join('');
}

// Open conversation
function openConversation(roomId, userName) {
    currentRoomId = roomId;
    
    // Update UI
    document.getElementById('welcomeScreen').style.display = 'none';
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('messageInputContainer').style.display = 'flex';
    document.getElementById('chatUserName').textContent = userName;
    
    // Join room
    socket.emit('join-room', roomId);
    
    // Load messages
    loadMessages(roomId);
    
    // Update active conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-room-id="${roomId}"]`).classList.add('active');
}

// Load messages
async function loadMessages(roomId) {
    try {
        const response = await fetch(`http://localhost:3001/api/messages/${roomId}?userId=${currentUserId}`);
        const data = await response.json();
        
        renderMessages(data.messages || []);
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

// Render messages
function renderMessages(messages) {
    const container = document.getElementById('messagesContainer');
    container.innerHTML = messages.map(msg => {
        const isSent = msg.sender_id == currentUserId;
        return `
            <div class="message ${isSent ? 'sent' : 'received'}">
                <div class="message-avatar">
                    ${(msg.sender_name || 'U').charAt(0).toUpperCase()}
                </div>
                <div class="message-content">
                    <div class="message-text">${msg.content}</div>
                    <div class="message-time">${formatTime(msg.created_at)}</div>
                </div>
            </div>
        `;
    }).join('');
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

// Send message
function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content || !currentRoomId) return;
    
    socket.emit('send-message', {
        roomId: currentRoomId,
        senderId: currentUserId,
        receiverId: null, // Will be determined by server
        content: content,
        messageType: 'text'
    });
    
    input.value = '';
}

// Setup event listeners
function setupEventListeners() {
    // Send button
    document.getElementById('sendBtn').addEventListener('click', sendMessage);
    
    // Enter key
    document.getElementById('messageInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Socket events
    socket.on('new-message', (message) => {
        if (message.chat_room_id == currentRoomId) {
            const container = document.getElementById('messagesContainer');
            const isSent = message.sender_id == currentUserId;
            const messageHTML = `
                <div class="message ${isSent ? 'sent' : 'received'}">
                    <div class="message-avatar">
                        ${'U'}
                    </div>
                    <div class="message-content">
                        <div class="message-text">${message.content}</div>
                        <div class="message-time">${formatTime(message.created_at)}</div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', messageHTML);
            container.scrollTop = container.scrollHeight;
        }
        
        // Reload conversations to update last message
        loadConversations();
    });
    
    socket.on('conversation-updated', () => {
        loadConversations();
    });
}

// Format time
function formatTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    
    if (minutes < 1) return 'الآن';
    if (minutes < 60) return `منذ ${minutes} دقيقة`;
    if (minutes < 1440) return `منذ ${Math.floor(minutes / 60)} ساعة`;
    return date.toLocaleDateString('ar-EG');
}

// Make openConversation global
window.openConversation = openConversation;

