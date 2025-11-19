    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>الدردشة - النسخة الثابتة</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Laravel Echo & Pusher JS for WebSockets -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.19.0/dist/echo.iife.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }


        /* Left Sidebar */
        .left-sidebar {
            width: 350px;
            background: white;
            border-left: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        /* System Administrator Header */
        .system-admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4ecdc4, #ff6b6b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .admin-avatar-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .admin-name {
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        .admin-actions {
            display: flex;
            gap: 0.5rem;
        }

        .admin-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .admin-btn.notification-btn .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .admin-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #4ecdc4;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .system-status {
            margin: 1rem 0;
            text-align: center;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-indicator.online {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .status-indicator.offline {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .status-indicator i {
            font-size: 0.8rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Image Popup Styles */
        .image-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            cursor: pointer;
        }

        .image-popup-overlay.show {
            display: flex;
        }

        .image-popup-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .image-popup-content img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .image-popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            z-index: 10000;
        }

        .image-popup-close:hover {
            background: rgba(0, 0, 0, 0.9);
        }

        /* Make avatars clickable */
        .conversation-avatar img,
        .message-avatar img,
        .chat-partner-avatar img {
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .conversation-avatar img:hover,
        .message-avatar img:hover,
        .chat-partner-avatar img:hover {
            transform: scale(1.05);
        }

        /* Tooltip Styles */
        .conversation-avatar,
        .message-avatar,
        .chat-partner-avatar {
            position: relative;
        }

        .conversation-avatar img,
        .message-avatar img,
        .chat-partner-avatar img {
            position: relative;
        }

        .avatar-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .avatar-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.8);
        }

        .conversation-avatar:hover .avatar-tooltip,
        .message-avatar:hover .avatar-tooltip,
        .chat-partner-avatar:hover .avatar-tooltip {
            opacity: 1;
            visibility: visible;
        }

        /* New Chat Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #a0aec0;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: #f7fafc;
            color: #4a5568;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3182ce;
        }

        .users-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-bottom: 0.5rem;
        }

        .user-item:hover {
            background: #f7fafc;
        }

        .user-item.selected {
            background: #ebf8ff;
            border: 2px solid #3182ce;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .user-role {
            font-size: 0.875rem;
            color: #718096;
        }

        .user-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #48bb78;
        }

        .status-text {
            font-size: 0.75rem;
            color: #718096;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-primary {
            background: #3182ce;
            color: white;
        }

        .btn-primary:hover {
            background: #2c5aa0;
        }

        .btn-primary:disabled {
            background: #a0aec0;
            cursor: not-allowed;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .chat-type-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            background: #f7fafc;
            padding: 0.5rem;
            border-radius: 12px;
        }

        .chat-type-tab {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            background: transparent;
            color: #718096;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            position: relative;
        }

        .chat-type-tab i {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }

        .chat-type-tab span:not(.tab-count) {
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .chat-type-tab .tab-count {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .chat-type-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .chat-type-tab.active .tab-count {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-type-tab:hover:not(.active) {
            background: white;
            color: #4a5568;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #718096;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .filter-tab:hover:not(.active) {
            background: #e2e8f0;
            color: #4a5568;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            border: 2px solid transparent;
        }

        .conversation-item:hover {
            background: #f7fafc;
            transform: translateX(-2px);
        }

        .conversation-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-left: 1rem;
            position: relative;
            flex-shrink: 0;
        }

        .conversation-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .conversation-avatar .default-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .conversation-info {
            flex: 1;
            min-width: 0;
        }

        .conversation-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-last-message {
            color: #718096;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-item.active .conversation-last-message {
            color: rgba(255, 255, 255, 0.8);
        }

        .conversation-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }

        .conversation-time {
            font-size: 0.75rem;
            color: #a0aec0;
        }

        .conversation-item.active .conversation-time {
            color: rgba(255, 255, 255, 0.7);
        }

        .unread-badge {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .conversation-item.active .unread-badge {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-type-badge {
            background: #4ecdc4;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .conversation-item.active .chat-type-badge {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Chat Area */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }

        .chat-welcome {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .welcome-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 2rem;
        }

        .welcome-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            max-width: 600px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-description {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .active-chat {
            display: none;
            flex: 1;
            flex-direction: column;
            height: 100vh;
            max-height: 100vh;
        }

        .active-chat.show {
            display: flex;
        }

        .chat-header-info {
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-partner-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chat-partner-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            overflow: hidden;
        }

        .chat-partner-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .chat-partner-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .chat-partner-status {
            font-size: 0.875rem;
            color: #718096;
        }

        .chat-actions {
            display: flex;
            gap: 0.5rem;
        }

        .chat-action-btn {
            background: #f7fafc;
            border: none;
            color: #718096;
            padding: 0.75rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-action-btn:hover {
            background: #e2e8f0;
            color: #4a5568;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #f8fafc;
            max-height: calc(100vh - 200px);
            min-height: 0;
        }

        .message {
            display: flex;
            margin-bottom: 1rem;
            align-items: flex-end;
        }

        .message.own {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin: 0 0.5rem;
            flex-shrink: 0;
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .message-avatar .default-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .message-content {
            max-width: 70%;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .message.own .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message-text {
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 0.25rem;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            text-align: right;
        }

        .message.own .message-time {
            text-align: left;
        }

        .message-status {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .message-status .status-icon {
            font-size: 0.7rem;
        }

        .message-status .status-icon.sending {
            color: #a0aec0;
            animation: spin 1s linear infinite;
        }

        .message-status .status-icon.sent {
            color: #718096;
        }

        .message-status .status-icon.read {
            color: #3182ce;
        }

        .message-status .status-icon.error {
            color: #e53e3e;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .message-status .status-icon i {
            font-size: 0.6rem;
        }

        .status-icon {
            font-size: 0.7rem;
        }

        .input-area {
            background: white;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .message-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .message-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-actions {
            display: flex;
            gap: 0.5rem;
        }

        .input-btn {
            background: #f7fafc;
            border: none;
            color: #718096;
            padding: 0.75rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .input-btn:hover {
            background: #e2e8f0;
            color: #4a5568;
        }

        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* Scrollbar Styling */
        .conversations-list::-webkit-scrollbar,
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .conversations-list::-webkit-scrollbar-track,
        .messages-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .conversations-list::-webkit-scrollbar-thumb,
        .messages-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .conversations-list::-webkit-scrollbar-thumb:hover,
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .left-sidebar {
                width: 100%;
                position: absolute;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .left-sidebar.show {
                transform: translateX(0);
            }

            .main-chat {
                width: 100%;
            }

            .search-container {
                width: 200px;
            }

            .welcome-features {
                grid-template-columns: 1fr;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Chat Info Modal Styles */
        .chat-info-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .chat-info-modal.show {
            display: flex;
        }

        .chat-info-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .chat-info-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-info-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .chat-info-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-info-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-info-body {
            padding: 1.5rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .info-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-section-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #718096;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: #f7fafc;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .info-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-left: 1rem;
        }

        .info-item-content {
            flex: 1;
        }

        .info-item-label {
            font-size: 0.75rem;
            color: #718096;
            margin-bottom: 0.25rem;
        }

        .info-item-value {
            font-size: 1rem;
            font-weight: 500;
            color: #2d3748;
        }

        .participants-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .participant-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: #f7fafc;
            border-radius: 8px;
        }

        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 0.75rem;
            overflow: hidden;
        }

        .participant-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .participant-avatar .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .participant-info {
            flex: 1;
        }

        .participant-name {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .participant-role {
            font-size: 0.875rem;
            color: #718096;
        }

        .delete-chat-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ff4757 0%, #e84118 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .delete-chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
        }
    </style>
</head>
<body>
    <div class="chat-container">

        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <!-- System Administrator Header -->
            <div class="system-admin-header">
                <div class="admin-info">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                             alt="{{ auth()->user()->name }}" 
                             class="admin-avatar-img"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="admin-avatar" style="display: none;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="admin-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <span class="admin-name">{{ auth()->user()->name }}</span>
                </div>
                <div class="admin-actions">
                    <button class="admin-btn notification-btn" title="الإشعارات" style="display: none;">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">{{ $unreadMessages > 0 ? $unreadMessages : '' }}</span>
                    </button>
                    <button class="admin-btn" title="المستخدمون النشطون: {{ $onlineUsers }}" style="display: none;">
                        <i class="fas fa-bolt"></i>
                        <span class="admin-badge">{{ $onlineUsers }}</span>
                    </button>
                    <button class="admin-btn" title="إجمالي الرسائل: {{ $totalMessages }}" style="display: none;">
                        <i class="fas fa-th"></i>
                        <span class="admin-badge">{{ $totalMessages }}</span>
                    </button>
                    <button class="admin-btn" id="new-chat-btn" title="إجمالي المحادثات: {{ $conversations->count() }}">
                        <i class="fas fa-plus"></i>
                        <span class="admin-badge">{{ $conversations->count() }}</span>
                    </button>
                </div>
            </div>
            
            <div class="sidebar-header">
                <h2 class="sidebar-title">الرسائل</h2>
                <div class="chat-type-tabs">
                    <button class="chat-type-tab active" data-chat-type="all">
                        <i class="fas fa-comments"></i>
                        <span>الكل</span>
                        <span class="tab-count" id="all-count">{{ $conversations->count() }}</span>
                    </button>
                    <button class="chat-type-tab" data-chat-type="private">
                        <i class="fas fa-user"></i>
                        <span>محادثات خاصة</span>
                        <span class="tab-count" id="private-count">{{ $conversations->where('type', 'private')->count() }}</span>
                    </button>
                    <button class="chat-type-tab" data-chat-type="group">
                        <i class="fas fa-users"></i>
                        <span>مجموعات</span>
                        <span class="tab-count" id="group-count">{{ $conversations->where('type', 'group')->count() }}</span>
                    </button>
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">الكل</button>
                    <button class="filter-tab" data-filter="unread">غير مقروء</button>
                </div>
            </div>
            <div class="conversations-list" id="conversations-list">
                @forelse($conversations as $conversation)
                <div class="conversation-item {{ ($firstConversation && $firstConversation['id'] == $conversation['id']) ? 'active' : '' }}" 
                     data-conversation-id="{{ $conversation['id'] }}"
                     data-type="{{ $conversation['type'] }}">
                    <div class="conversation-avatar">
                        @if($conversation['other_participant'] && $conversation['other_participant']->profile_picture)
                            <img src="{{ asset('storage/' . $conversation['other_participant']->profile_picture) }}" 
                                 alt="{{ $conversation['display_name'] }}">
                        @else
                            <div class="default-avatar">
                                {{ substr($conversation['display_name'], 0, 1) }}
                            </div>
                        @endif
                        <div class="avatar-tooltip">{{ $conversation['display_name'] }}</div>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-name">{{ $conversation['display_name'] }}</div>
                        <div class="conversation-last-message">
                            @if($conversation['last_message'])
                                {{ $conversation['last_message']->content }}
                            @else
                                لا توجد رسائل
                            @endif
                        </div>
                        <div class="chat-type-badge">
                            {{ $conversation['type'] === 'private' ? 'خاص' : 'جماعي' }}
                        </div>
                    </div>
                    <div class="conversation-meta">
                        <div class="conversation-time">
                            @if($conversation['last_message_at'])
                                {{ \Carbon\Carbon::parse($conversation['last_message_at'])->diffForHumans() }}
                            @endif
                        </div>
                        @if($conversation['unread_count'] > 0)
                            <div class="unread-badge">{{ $conversation['unread_count'] }}</div>
                        @endif
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 2rem; color: #718096;">
                    <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>لا توجد محادثات</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="main-chat">
            @if($conversations->isEmpty() || !$firstConversation)
            <!-- Welcome Screen -->
            <div class="chat-welcome">
                <div class="welcome-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h1 class="welcome-title">مرحباً بك في نظام الدردشة</h1>
                <p class="welcome-subtitle">لديك {{ $conversations->count() }} محادثة نشطة مع {{ $totalUsers }} مستخدم في النظام</p>
                <div class="system-status">
                    <div class="status-indicator {{ $onlineUsers > 0 ? 'online' : 'offline' }}">
                        <i class="fas fa-circle"></i>
                        <span>{{ $onlineUsers > 0 ? 'النظام نشط' : 'النظام غير متصل' }}</span>
                    </div>
                </div>
                <div class="welcome-features">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h3 class="feature-title">دردشة فورية</h3>
                        <p class="feature-description">{{ $totalMessages }} رسالة تم إرسالها في النظام</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">دردشات جماعية</h3>
                        <p class="feature-description">{{ $onlineUsers }} مستخدم نشط الآن</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <h3 class="feature-title">مشاركة الملفات</h3>
                        <p class="feature-description">{{ $unreadMessages }} رسالة غير مقروءة</p>
                    </div>
                </div>
            </div>
            @else
            <!-- Active Chat -->
            <div class="active-chat show" id="active-chat">
                <div class="chat-header-info">
                    <div class="chat-partner-info">
                        <div class="chat-partner-avatar">
                            @if($firstConversation['other_participant'] && $firstConversation['other_participant']->profile_picture)
                                <img src="{{ asset('storage/' . $firstConversation['other_participant']->profile_picture) }}" 
                                     alt="{{ $firstConversation['display_name'] }}">
                            @else
                                {{ substr($firstConversation['display_name'], 0, 1) }}
                            @endif
                            <div class="avatar-tooltip">{{ $firstConversation['display_name'] }}</div>
                        </div>
                        <div class="chat-partner-details">
                            <h3>{{ $firstConversation['display_name'] }}</h3>
                            <div class="chat-partner-status">
                                @if($firstConversation['type'] === 'private')
                                    آخر ظهور منذ 5 دقائق
                                @else
                                    {{ $firstConversation['participant_count'] }} أعضاء
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-action-btn" id="chat-info-btn" title="معلومات المحادثة">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="messages-container" id="messages-container">
                    <!-- Debug: Messages count: {{ count($messages) }} -->
                    @forelse($messages as $message)
                    <div class="message {{ $message['is_own'] ? 'own' : '' }} fade-in">
                        <div class="message-avatar">
                            @if($message['user_avatar'] && !str_contains($message['user_avatar'], 'ui-avatars.com'))
                                <img src="{{ $message['user_avatar'] }}" alt="{{ $message['user_name'] }}">
                            @else
                                <div class="default-avatar">
                                    {{ substr($message['user_name'], 0, 1) }}
                                </div>
                            @endif
                            <div class="avatar-tooltip">{{ $message['user_name'] }}</div>
                        </div>
                        <div class="message-content">
                            <div class="message-text">{{ $message['content'] }}</div>
                            <div class="message-time">{{ $message['time'] }}</div>
                            @if($message['is_own'])
                            <div class="message-status">
                                @if(isset($message['read_at']) && $message['read_at'])
                                    <div class="status-icon read">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                @else
                                    <div class="status-icon sent">
                                        <i class="fas fa-check"></i>
                                    </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div id="empty-messages-placeholder" style="text-align: center; padding: 2rem; color: #718096;">
                        <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>لا توجد رسائل في هذه المحادثة</p>
                    </div>
                    @endforelse
                </div>
                <div class="input-area">
                    <input type="text" class="message-input" placeholder="اكتب رسالتك هنا..." id="message-input">
                    <div class="input-actions">
                        <button class="input-btn" title="إيموجي">
                            <i class="fas fa-smile"></i>
                        </button>
                        <button class="input-btn" title="مرفق">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <button class="input-btn send-btn" title="إرسال">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Image Popup -->
    <div class="image-popup-overlay" id="image-popup">
        <div class="image-popup-content">
            <button class="image-popup-close" id="close-popup">&times;</button>
            <img id="popup-image" src="" alt="">
        </div>
    </div>

    <!-- New Chat Modal -->
    <div class="modal-overlay" id="new-chat-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">بدء محادثة جديدة</h3>
                <button class="modal-close" id="close-new-chat-modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" class="search-input" id="user-search" placeholder="ابحث عن شخص للدردشة معه...">
                <div class="users-list" id="users-list">
                    <!-- Users will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-new-chat">إلغاء</button>
                <button class="btn btn-primary" id="start-chat" disabled>بدء المحادثة</button>
            </div>
        </div>
    </div>

    <!-- Chat Info Modal -->
    @if($firstConversation)
    <div class="chat-info-modal" id="chat-info-modal">
        <div class="chat-info-content">
            <div class="chat-info-header">
                <h3 class="chat-info-title">معلومات المحادثة</h3>
                <button class="chat-info-close" id="close-chat-info">&times;</button>
            </div>
            <div class="chat-info-body">
                <!-- تاريخ الإنشاء -->
                <div class="info-section">
                    <div class="info-section-title">تفاصيل المحادثة</div>
                    <div class="info-item">
                        <div class="info-item-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-item-content">
                            <div class="info-item-label">تاريخ الإنشاء</div>
                            <div class="info-item-value">
                                {{ \Carbon\Carbon::parse($firstConversation['created_at'])->format('Y-m-d h:i A') }}
                            </div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="info-item-content">
                            <div class="info-item-label">نوع المحادثة</div>
                            <div class="info-item-value">
                                {{ $firstConversation['type'] === 'private' ? 'محادثة خاصة' : 'محادثة جماعية' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- المشاركون -->
                <div class="info-section">
                    <div class="info-section-title">المشاركون ({{ $firstConversation['participant_count'] ?? 1 }})</div>
                    <div class="participants-list">
                        @if($firstConversation['type'] === 'private' && $firstConversation['other_participant'])
                            <div class="participant-item">
                                <div class="participant-avatar">
                                    @if($firstConversation['other_participant']->profile_picture)
                                        <img src="{{ asset('storage/' . $firstConversation['other_participant']->profile_picture) }}" 
                                             alt="{{ $firstConversation['other_participant']->name }}">
                                    @else
                                        <div class="default-avatar">
                                            {{ substr($firstConversation['other_participant']->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="participant-info">
                                    <div class="participant-name">{{ $firstConversation['other_participant']->name }}</div>
                                    <div class="participant-role">{{ $firstConversation['other_participant']->email }}</div>
                                </div>
                            </div>
                        @endif
                        <!-- إضافة المستخدم الحالي -->
                        <div class="participant-item">
                            <div class="participant-avatar">
                                @if(auth()->user()->profile_picture)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                                         alt="{{ auth()->user()->name }}">
                                @else
                                    <div class="default-avatar">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="participant-info">
                                <div class="participant-name">{{ auth()->user()->name }} (أنت)</div>
                                <div class="participant-role">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- زر حذف المحادثة -->
                <div class="info-section">
                    <button class="delete-chat-btn" id="delete-chat-btn" data-conversation-id="{{ $firstConversation['id'] }}">
                        <i class="fas fa-trash"></i>
                        حذف المحادثة بالكامل
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Initialize Laravel Echo with Reverb
        window.Pusher = Pusher;
        
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: @json(env('REVERB_APP_KEY', '')),
            wsHost: @json(env('REVERB_HOST', 'localhost')),
            wsPort: @json(env('REVERB_PORT', 8080)),
            wssPort: @json(env('REVERB_PORT', 8080)),
            forceTLS: @json(env('REVERB_SCHEME', 'http') === 'https'),
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            },
        });
        
        // Log Echo connection status
        if (Echo.connector && Echo.connector.pusher) {
            Echo.connector.pusher.connection.bind('connected', function() {
                console.log('✅ Echo connected successfully');
            });
            
            Echo.connector.pusher.connection.bind('error', function(err) {
                console.error('❌ Echo connection error:', err);
            });
            
            Echo.connector.pusher.connection.bind('disconnected', function() {
                console.warn('⚠️ Echo disconnected');
            });
            
            Echo.connector.pusher.connection.bind('state_change', function(states) {
                console.log('Echo connection state:', states.current);
            });
        }
        
        // Global variables for chat type and filter
        let currentChatType = 'all';
        let currentFilter = 'all';
        let echoChannel = null;
        
        // Store current conversation ID from backend or URL
        @if($firstConversation && isset($firstConversation['id']))
        let currentConversationId = {{ $firstConversation['id'] }};
        @else
        let currentConversationId = null;
        @endif
        
        // Static Chat JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Update currentConversationId from URL if available
            const urlParams = new URLSearchParams(window.location.search);
            const urlConversationId = urlParams.get('conversation');
            if (urlConversationId) {
                currentConversationId = parseInt(urlConversationId);
            }
            
            // Setup Echo listener for current conversation after a short delay
            // Wait for Echo to be fully initialized
            setTimeout(function() {
                if (urlConversationId) {
                    console.log('Setting up Echo listener for conversation from URL:', urlConversationId);
                    setupEchoListener(parseInt(urlConversationId));
                } else if (currentConversationId) {
                    console.log('Setting up Echo listener for current conversation:', currentConversationId);
                    setupEchoListener(currentConversationId);
                }
            }, 1000); // Wait 1 second for Echo to initialize
            
            // Also update when conversation item is clicked
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', function() {
                    const conversationId = this.dataset.conversationId;
                    if (conversationId) {
                        const roomId = parseInt(conversationId);
                        currentConversationId = roomId;
                        // Setup Echo listener for the clicked conversation
                        setupEchoListener(roomId);
                    }
                });
            });
            // Auto scroll to bottom on page load
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Image popup functionality
            const imagePopup = document.getElementById('image-popup');
            const popupImage = document.getElementById('popup-image');
            const closePopup = document.getElementById('close-popup');

            // Add click event to all images
            function addImageClickListeners() {
                const images = document.querySelectorAll('.conversation-avatar img, .message-avatar img, .chat-partner-avatar img');
                images.forEach(img => {
                    img.addEventListener('click', function(e) {
                        e.stopPropagation();
                        popupImage.src = this.src;
                        popupImage.alt = this.alt;
                        imagePopup.classList.add('show');
                        document.body.style.overflow = 'hidden'; // Prevent background scrolling
                    });
                });
            }

            // Close popup
            function closeImagePopup() {
                imagePopup.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }

            // Event listeners
            closePopup.addEventListener('click', closeImagePopup);
            imagePopup.addEventListener('click', function(e) {
                if (e.target === imagePopup) {
                    closeImagePopup();
                }
            });

            // Close with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && imagePopup.classList.contains('show')) {
                    closeImagePopup();
                }
            });

            // Initialize image click listeners
            addImageClickListeners();

            // New Chat Modal functionality
            const newChatModal = document.getElementById('new-chat-modal');
            const closeNewChatModal = document.getElementById('close-new-chat-modal');
            const cancelNewChat = document.getElementById('cancel-new-chat');
            const startChatBtn = document.getElementById('start-chat');
            const userSearch = document.getElementById('user-search');
            const usersList = document.getElementById('users-list');
            let selectedUser = null;

            // Real users data from API
            let realUsers = [];

            // Load users from API
            async function loadUsersFromAPI() {
                try {
                    const response = await fetch('{{ route("chat.api.users") }}', {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        realUsers = await response.json();
                        loadUsers(realUsers);
                    } else {
                        console.error('Error loading users:', response.statusText);
                        loadUsers([]);
                    }
                } catch (error) {
                    console.error('Error loading users:', error);
                    loadUsers([]);
                }
            }

            // Load users into modal
            function loadUsers(users = realUsers) {
                usersList.innerHTML = '';
                users.forEach(user => {
                    const userItem = document.createElement('div');
                    userItem.className = 'user-item';
                    userItem.dataset.userId = user.id;
                    
                    const avatarHtml = user.avatar ? 
                        `<img src="${user.avatar}" alt="${user.name || 'User'}">` :
                        `<div class="default-avatar">${(user.name || 'U').charAt(0)}</div>`;
                    
                    userItem.innerHTML = `
                        <div class="user-avatar">
                            ${avatarHtml}
                        </div>
                        <div class="user-info">
                            <div class="user-name">${user.name || 'Unknown User'}</div>
                            <div class="user-role">${user.role || 'No Role'}</div>
                        </div>
                        <div class="user-status">
                            <div class="status-dot ${user.status}"></div>
                            <span class="status-text">${getStatusText(user.status)}</span>
                        </div>
                    `;
                    
                    userItem.addEventListener('click', () => selectUser(userItem, user));
                    usersList.appendChild(userItem);
                });
            }

            function getStatusText(status) {
                const statusMap = {
                    'online': 'متصل',
                    'offline': 'غير متصل',
                    'away': 'بعيد'
                };
                return statusMap[status] || 'غير معروف';
            }

            function selectUser(userItem, user) {
                // Remove previous selection
                document.querySelectorAll('.user-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                // Select current user
                userItem.classList.add('selected');
                selectedUser = user;
                startChatBtn.disabled = false;
            }

            function openNewChatModal() {
                newChatModal.classList.add('show');
                loadUsersFromAPI(); // Load real users from API
                selectedUser = null;
                startChatBtn.disabled = true;
                userSearch.value = '';
            }

            function closeModal() {
                newChatModal.classList.remove('show');
                selectedUser = null;
                startChatBtn.disabled = true;
                userSearch.value = '';
            }

            async function startNewChat() {
                if (selectedUser) {
                    try {
                        // إنشاء محادثة جديدة مع المستخدم المحدد
                        const response = await fetch('{{ route("chat.api.create") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                type: 'private',
                                users: [selectedUser.id]
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            alert(`تم بدء محادثة جديدة مع ${selectedUser.name}`);
                            closeModal();
                            
                            // إعادة تحميل الصفحة لعرض المحادثة الجديدة
                            window.location.reload();
                        } else {
                            alert('حدث خطأ في إنشاء المحادثة');
                        }
                    } catch (error) {
                        console.error('Error creating chat:', error);
                        alert('حدث خطأ في إنشاء المحادثة');
                    }
                }
            }

            // Search functionality
            function searchUsers(query) {
                const filteredUsers = realUsers.filter(user => 
                    user.name.toLowerCase().includes(query.toLowerCase()) ||
                    user.role.toLowerCase().includes(query.toLowerCase())
                );
                loadUsers(filteredUsers);
            }

            // Event listeners
            document.getElementById('new-chat-btn').addEventListener('click', openNewChatModal);
            closeNewChatModal.addEventListener('click', closeModal);
            cancelNewChat.addEventListener('click', closeModal);
            startChatBtn.addEventListener('click', startNewChat);
            
            userSearch.addEventListener('input', (e) => {
                searchUsers(e.target.value);
            });

            // Close modal when clicking outside
            newChatModal.addEventListener('click', (e) => {
                if (e.target === newChatModal) {
                    closeModal();
                }
            });
            
            // Chat Info Modal functionality
            const chatInfoBtn = document.getElementById('chat-info-btn');
            const chatInfoModal = document.getElementById('chat-info-modal');
            const closeChatInfo = document.getElementById('close-chat-info');
            const deleteChatBtn = document.getElementById('delete-chat-btn');

            // Open chat info modal
            if (chatInfoBtn) {
                chatInfoBtn.addEventListener('click', function() {
                    chatInfoModal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }

            // Close chat info modal
            function closeChatInfoModal() {
                if (chatInfoModal) {
                    chatInfoModal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            }

            if (closeChatInfo) {
                closeChatInfo.addEventListener('click', closeChatInfoModal);
            }

            // Close modal when clicking outside
            if (chatInfoModal) {
                chatInfoModal.addEventListener('click', function(e) {
                    if (e.target === chatInfoModal) {
                        closeChatInfoModal();
                    }
                });
            }

            // Delete chat functionality
            if (deleteChatBtn) {
                deleteChatBtn.addEventListener('click', async function() {
                    const conversationId = this.dataset.conversationId;
                    
                    if (confirm('هل أنت متأكد من حذف هذه المحادثة بالكامل؟ لا يمكن التراجع عن هذا الإجراء.')) {
                        try {
                            // Show loading state on button
                            deleteChatBtn.disabled = true;
                            deleteChatBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';
                            
                            const response = await fetch(`/chat/api/conversations/${conversationId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            const data = await response.json();
                            
                            if (response.ok && data.success) {
                                // Close modal
                                closeChatInfoModal();
                                
                                // Redirect to chat main page
                                window.location.href = '{{ route("chat.static") }}';
                            } else {
                                // Show error and restore button
                                deleteChatBtn.disabled = false;
                                deleteChatBtn.innerHTML = '<i class="fas fa-trash"></i> حذف المحادثة بالكامل';
                                
                                // Show error in console
                                console.error('فشل حذف المحادثة:', data.message);
                            }
                        } catch (error) {
                            console.error('خطأ في حذف المحادثة:', error);
                            
                            // Restore button state
                            deleteChatBtn.disabled = false;
                            deleteChatBtn.innerHTML = '<i class="fas fa-trash"></i> حذف المحادثة بالكامل';
                        }
                    }
                });
            }

            // Close chat info modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && chatInfoModal && chatInfoModal.classList.contains('show')) {
                    closeChatInfoModal();
                }
            });
            
        // Setup Echo listener for new messages
        function setupEchoListener(roomId) {
            // Leave previous channel if exists
            if (echoChannel && currentConversationId) {
                console.log('Leaving previous channel: chat.' + currentConversationId);
                Echo.leave('chat.' + currentConversationId);
                echoChannel = null;
            }
            
            if (!roomId) {
                console.log('No room ID provided for Echo listener');
                return;
            }
            
            // Check if Echo is available
            if (!window.Echo) {
                console.error('Echo is not initialized yet');
                setTimeout(function() {
                    setupEchoListener(roomId);
                }, 500);
                return;
            }
            
            currentConversationId = roomId;
            console.log('Setting up Echo listener for room:', roomId);
            
            try {
                // Join the presence channel (لتمكين toOthers() في Broadcast)
                echoChannel = Echo.join('chat.' + roomId)
                    .here((users) => {
                        console.log('Users currently in channel:', users.length, users);
                    })
                    .joining((user) => {
                        console.log('User joined channel:', user.name);
                    })
                    .leaving((user) => {
                        console.log('User left channel:', user.name);
                    })
                    .listen('.message.sent', (data) => {
                        console.log('New message received via WebSocket:', data);
                        
                        // Add the message to the UI
                        const message = data;
                        const messagesContainer = document.getElementById('messages-container');
                        
                        if (messagesContainer) {
                            // Hide empty placeholder
                            const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
                            if (emptyPlaceholder) {
                                emptyPlaceholder.style.display = 'none';
                            }
                            
                            // Check if message already exists (prevent duplicates)
                            const existingMessage = messagesContainer.querySelector(`[data-message-id="${message.id}"]`);
                            if (!existingMessage) {
                                // Set is_own based on current user
                                const currentUserId = {{ auth()->id() }};
                                message.is_own = (message.user_id || message.sender_id) == currentUserId;
                                
                                const messageElement = createMessageElement(message);
                                messagesContainer.appendChild(messageElement);
                                
                                // Scroll to bottom
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                                
                                console.log('Message added to UI:', message.id);
                            } else {
                                console.log('Message already exists, skipping:', message.id);
                            }
                        }
                        
                        // Update conversations list
                        if (typeof loadConversationsList === 'function') {
                            loadConversationsList(currentChatType, currentFilter);
                        }
                    })
                    .error((error) => {
                        console.error('Echo channel error:', error);
                    });
                
                console.log('Echo listener setup completed for room:', roomId);
            } catch (error) {
                console.error('Error setting up Echo listener:', error);
            }
        }
        
            // Update time display every minute
            setInterval(function() {
                updateTimeDisplays();
            }, 60000);

            function updateTimeDisplays() {
                // Update last seen times
                const timeElements = document.querySelectorAll('.last-seen');
                timeElements.forEach(element => {
                    const timeText = element.textContent;
                    if (timeText.includes('دقيقة')) {
                        const minutes = parseInt(timeText.match(/\d+/)[0]);
                        if (minutes < 60) {
                            element.textContent = `آخر ظهور منذ ${minutes + 1} دقائق`;
                        }
                    }
                });
            }
            // Chat Type Tabs functionality
            const chatTypeTabs = document.querySelectorAll('.chat-type-tab');
            chatTypeTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    chatTypeTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    currentChatType = this.dataset.chatType;
                    loadConversationsList(currentChatType, currentFilter);
                });
            });

            // Filter tabs functionality
            const filterTabs = document.querySelectorAll('.filter-tab');
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    currentFilter = this.dataset.filter;
                    filterConversations(currentFilter);
                });
            });

            // Conversation selection - Use event delegation for dynamic content
            const conversationsList = document.getElementById('conversations-list');
            if (conversationsList) {
                conversationsList.addEventListener('click', function(e) {
                    const conversationItem = e.target.closest('.conversation-item');
                    if (conversationItem) {
                        // Remove active class from all items
                        document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
                        conversationItem.classList.add('active');
                        
                        const conversationId = conversationItem.dataset.conversationId;
                        if (conversationId) {
                            currentConversationId = parseInt(conversationId);
                            console.log('Updated currentConversationId to:', currentConversationId);
                            loadConversation(conversationId);
                        }
                    }
                });
            }
            
            // Also handle direct click on conversation items (for initial page load)
            function attachConversationClickListeners() {
                const conversationItems = document.querySelectorAll('.conversation-item');
                conversationItems.forEach(item => {
                    // Only add listener if not already handled by delegation
                    if (!item.dataset.listenerAttached) {
                        item.dataset.listenerAttached = 'true';
                        item.addEventListener('click', function(e) {
                            // Let event delegation handle it, but update currentConversationId
                            const conversationId = this.dataset.conversationId;
                            if (conversationId) {
                                currentConversationId = parseInt(conversationId);
                                console.log('Updated currentConversationId to:', currentConversationId);
                            }
                        });
                    }
                });
            }
            
            // Attach listeners initially (event delegation will handle most cases)
            attachConversationClickListeners();

            // Message input functionality
            const messageInput = document.getElementById('message-input');
            const sendBtn = document.querySelector('.send-btn');
            
            if (messageInput && sendBtn) {
                messageInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
                
                sendBtn.addEventListener('click', sendMessage);
            }

            // Search functionality
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    searchConversations(query);
                });
            }
        });

        // Load conversations list from API (Global function)
        async function loadConversationsList(type = 'all', filter = 'all') {
            try {
                const response = await fetch(`{{ route("chat.api.conversations") }}?type=${type}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    renderConversations(data.conversations, filter);
                    updateTabCounts(data.counts);
                    // Event delegation handles new elements automatically
                }
            } catch (error) {
                console.error('Error loading conversations:', error);
            }
        }
        
        // Render conversations HTML
        function renderConversations(conversations, filter = 'all') {
            const conversationsList = document.getElementById('conversations-list');
            const urlConversationId = new URLSearchParams(window.location.search).get('conversation');
            
            // Update global currentConversationId from URL if available
            if (urlConversationId) {
                currentConversationId = parseInt(urlConversationId);
            }
            
            if (!conversationsList) return;
            
            // Filter conversations
            let filteredConversations = conversations;
            if (filter === 'unread') {
                filteredConversations = conversations.filter(conv => conv.unread_count > 0);
            }
            
            if (filteredConversations.length === 0) {
                conversationsList.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #718096;">
                        <i class="fas fa-comments" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>لا توجد محادثات</p>
                    </div>
                `;
                return;
            }
            
            conversationsList.innerHTML = filteredConversations.map(conversation => {
                const avatarHtml = conversation.other_participant && conversation.other_participant.profile_picture ?
                    `<img src="{{ asset('storage/') }}/${conversation.other_participant.profile_picture}" alt="${conversation.display_name}">` :
                    `<div class="default-avatar">${conversation.display_name.charAt(0)}</div>`;
                
                const lastMessage = conversation.last_message ? conversation.last_message.content : 'لا توجد رسائل';
                
                // Format time ago in Arabic
                let lastMessageTime = '';
                if (conversation.last_message_at) {
                    const now = new Date();
                    const messageDate = new Date(conversation.last_message_at);
                    const diffMs = now - messageDate;
                    const diffMins = Math.floor(diffMs / 60000);
                    const diffHours = Math.floor(diffMs / 3600000);
                    const diffDays = Math.floor(diffMs / 86400000);
                    
                    if (diffMins < 1) {
                        lastMessageTime = 'الآن';
                    } else if (diffMins < 60) {
                        lastMessageTime = `منذ ${diffMins} دقيقة`;
                    } else if (diffHours < 24) {
                        lastMessageTime = `منذ ${diffHours} ساعة`;
                    } else if (diffDays < 7) {
                        lastMessageTime = `منذ ${diffDays} يوم`;
                    } else {
                        lastMessageTime = messageDate.toLocaleDateString('ar-EG');
                    }
                }
                
                const isActive = currentConversationId == conversation.id ? 'active' : '';
                const unreadBadge = conversation.unread_count > 0 ? `<div class="unread-badge">${conversation.unread_count}</div>` : '';
                const chatTypeBadge = conversation.type === 'private' ? 'خاص' : 'جماعي';
                
                return `
                    <div class="conversation-item ${isActive}" 
                         data-conversation-id="${conversation.id}"
                         data-type="${conversation.type}">
                        <div class="conversation-avatar">
                            ${avatarHtml}
                            <div class="avatar-tooltip">${conversation.display_name}</div>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name">${conversation.display_name}</div>
                            <div class="conversation-last-message">${lastMessage}</div>
                            <div class="chat-type-badge">${chatTypeBadge}</div>
                        </div>
                        <div class="conversation-meta">
                            <div class="conversation-time">${lastMessageTime}</div>
                            ${unreadBadge}
                        </div>
                    </div>
                `;
            }).join('');
            // Event delegation handles click events automatically
        }
        
        // Update tab counts
        function updateTabCounts(counts) {
            document.getElementById('all-count').textContent = counts.all || 0;
            document.getElementById('private-count').textContent = counts.private || 0;
            document.getElementById('group-count').textContent = counts.group || 0;
        }

        function filterConversations(filter) {
            loadConversationsList(currentChatType, filter);
        }

        function searchConversations(query) {
            const conversations = document.querySelectorAll('.conversation-item');
            conversations.forEach(conversation => {
                const name = conversation.querySelector('.conversation-name').textContent.toLowerCase();
                const lastMessage = conversation.querySelector('.conversation-last-message').textContent.toLowerCase();
                
                if (name.includes(query) || lastMessage.includes(query)) {
                    conversation.style.display = 'flex';
                } else {
                    conversation.style.display = 'none';
                }
            });
        }

        function loadConversation(conversationId) {
            console.log('Loading conversation:', conversationId);
            
            // Setup Echo listener for the new conversation
            setupEchoListener(parseInt(conversationId));
            
            // Reload the page to show the conversation with real data
            window.location.href = '{{ route("chat.static") }}?conversation=' + conversationId;
        }

        // Load messages for current conversation
        async function loadCurrentConversationMessages() {
            const urlParams = new URLSearchParams(window.location.search);
            const conversationId = urlParams.get('conversation');
            
            if (!conversationId) return;
            
            try {
                const response = await fetch(`{{ route("chat.api.messages", ":id") }}`.replace(':id', conversationId), {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success && data.messages) {
                    // تحديث عرض الرسائل
                    updateMessagesDisplay(data.messages);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }
        
        // Render all messages (used when switching conversations)
        function renderAllMessages(messages) {
            const messagesContainer = document.getElementById('messages-container');
            if (!messagesContainer) return;
            
            // إخفاء placeholder
            const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
            if (emptyPlaceholder) {
                emptyPlaceholder.style.display = 'none';
            }
            
            // مسح الرسائل الحالية
            messagesContainer.innerHTML = '';
            
            // عرض جميع الرسائل
            if (messages && messages.length > 0) {
                messages.forEach(message => {
                    const messageElement = createMessageElement(message);
                    messagesContainer.appendChild(messageElement);
                });
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } else {
                // عرض placeholder إذا لم تكن هناك رسائل
                messagesContainer.innerHTML = `
                    <div id="empty-messages-placeholder" style="text-align: center; padding: 2rem; color: #718096;">
                        <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>لا توجد رسائل في هذه المحادثة</p>
                    </div>
                `;
            }
        }

        // Polling removed - using WebSockets instead via Laravel Echo

        // Update messages display without reloading
        function updateMessagesDisplay(messages) {
            const messagesContainer = document.getElementById('messages-container');
            if (!messagesContainer) return;
            
            // الحصول على معرفات الرسائل الحالية
            const existingMessageIds = Array.from(messagesContainer.querySelectorAll('.message'))
                .map(msg => parseInt(msg.dataset.messageId))
                .filter(id => !isNaN(id));
            
            // الحصول على معرفات الرسائل الجديدة
            const newMessageIds = messages.map(msg => msg.id);
            
            // العثور على الرسائل الجديدة (غير موجودة في القائمة الحالية)
            const messagesToAdd = messages.filter(msg => !existingMessageIds.includes(msg.id));
            
            // إضافة الرسائل الجديدة فقط
            if (messagesToAdd.length > 0) {
                // إخفاء placeholder إذا كان موجوداً
                const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
                if (emptyPlaceholder) {
                    emptyPlaceholder.style.display = 'none';
                }
                
                messagesToAdd.forEach(message => {
                    const messageElement = createMessageElement(message);
                    messagesContainer.appendChild(messageElement);
                });
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            // إذا لم تكن هناك رسائل حالية وكانت الرسائل الجديدة فارغة، أعرض placeholder
            if (existingMessageIds.length === 0 && messages.length === 0) {
                const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
                if (emptyPlaceholder) {
                    emptyPlaceholder.style.display = 'block';
                } else {
                    messagesContainer.innerHTML = `
                        <div id="empty-messages-placeholder" style="text-align: center; padding: 2rem; color: #718096;">
                            <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد رسائل في هذه المحادثة</p>
                        </div>
                    `;
                }
            }
        }

        // Create message element
        function createMessageElement(message) {
            const messageElement = document.createElement('div');
            // استخدام user_id أو sender_id أو is_own حسب ما هو متاح
            const currentUserId = {{ auth()->id() }};
            const messageUserId = message.sender_id || message.user_id;
            // إذا كان is_own غير محدد، احسبه بناءً على user_id
            const isOwn = message.is_own !== undefined ? message.is_own : (messageUserId == currentUserId);
            messageElement.className = `message ${isOwn ? 'own' : ''} fade-in`;
            messageElement.dataset.messageId = message.id;
            
            // استخدام sender أو user حسب ما هو متاح
            const sender = message.sender || message.user;
            // استخدام content أولاً، ثم message
            const messageContent = message.content || message.message || '';
            
            // معالجة avatar
            let avatarHtml = '';
            const senderName = sender ? (sender.name || 'Unknown') : 'Unknown';
            if (sender && sender.profile_picture) {
                avatarHtml = `<img src="{{ asset('storage/') }}/${sender.profile_picture}" alt="${senderName}">`;
            } else {
                const initial = senderName.charAt(0).toUpperCase();
                avatarHtml = `<div class="default-avatar">${initial}</div>`;
            }
            
            const timeAgo = getTimeAgo(message.created_at);
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    ${avatarHtml}
                    <div class="avatar-tooltip">${senderName}</div>
                </div>
                <div class="message-content">
                    <div class="message-text">${escapeHtml(messageContent)}</div>
                    <div class="message-time">${timeAgo}</div>
                    ${isOwn ? `
                    <div class="message-status">
                        <div class="status-icon sent">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            return messageElement;
        }

        // Get time ago in Arabic
        function getTimeAgo(dateString) {
            const now = new Date();
            const messageDate = new Date(dateString);
            const diffMs = now - messageDate;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) {
                return 'الآن';
            } else if (diffMins < 60) {
                return `منذ ${diffMins} دقيقة`;
            } else if (diffHours < 24) {
                return `منذ ${diffHours} ساعة`;
            } else if (diffDays < 7) {
                return `منذ ${diffDays} يوم`;
            } else {
                return messageDate.toLocaleDateString('ar-EG');
            }
        }

        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            if (!messageInput || !messageInput.value.trim()) return;
            
            const message = messageInput.value.trim();
            messageInput.value = '';
            
            console.log('Sending message:', message);
            
            // Get current chat room ID from global variable, URL, or active conversation item
            let chatRoomId = currentConversationId;
            
            // Try to get from URL if not set
            if (!chatRoomId) {
                const urlParams = new URLSearchParams(window.location.search);
                const urlConversationId = urlParams.get('conversation');
                if (urlConversationId) {
                    chatRoomId = parseInt(urlConversationId);
                }
            }
            
            // Try to get from active conversation item
            if (!chatRoomId) {
                const activeConversation = document.querySelector('.conversation-item.active');
                if (activeConversation) {
                    const conversationId = activeConversation.dataset.conversationId;
                    if (conversationId) {
                        chatRoomId = parseInt(conversationId);
                    }
                }
            }
            
            // Validate chatRoomId
            if (!chatRoomId || isNaN(chatRoomId)) {
                console.error('No valid conversation ID found');
                alert('خطأ: لم يتم العثور على معرف المحادثة. يرجى اختيار محادثة أولاً.');
                return;
            }
            
            // Hide empty messages placeholder if exists
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
                if (emptyPlaceholder) {
                    emptyPlaceholder.style.display = 'none';
                }
            }
                
            // Send to server
            fetch('{{ route("chat.static.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    chat_room_id: chatRoomId
                })
            })
            .then(async response => {
                // Check if response is OK
                if (!response.ok) {
                    let errorData = {};
                    try {
                        errorData = await response.json();
                    } catch (e) {
                        // If response is not JSON, use status text
                        errorData = { error: `خطأ ${response.status}: ${response.statusText}` };
                    }
                    
                    // Create error object with status and data
                    const error = new Error(`HTTP error! status: ${response.status}`);
                    error.status = response.status;
                    error.data = errorData;
                    throw error;
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Message sent successfully:', data.message);
                    
                    // Add message to UI immediately (optimistic update)
                    const messagesContainer = document.getElementById('messages-container');
                    if (messagesContainer) {
                        const emptyPlaceholder = document.getElementById('empty-messages-placeholder');
                        if (emptyPlaceholder) {
                            emptyPlaceholder.style.display = 'none';
                        }
                        
                        // Check if message already exists (from WebSocket)
                        const existingMessage = messagesContainer.querySelector(`[data-message-id="${data.message.id}"]`);
                        if (!existingMessage) {
                            const messageElement = createMessageElement(data.message);
                            messagesContainer.appendChild(messageElement);
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    }
                    
                    // Update conversations list
                    if (typeof loadConversationsList === 'function') {
                        loadConversationsList(currentChatType, currentFilter);
                    }
                    // WebSocket will also broadcast the message to other users
                } else {
                    console.error('Failed to send message:', data);
                    const errorMsg = data.error || data.message || 'حدث خطأ في إرسال الرسالة';
                    alert(errorMsg);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                
                // Try to extract error message from error object
                let errorMessage = 'حدث خطأ في إرسال الرسالة';
                
                if (error.data) {
                    // Handle validation errors
                    if (error.data.errors) {
                        const errorMessages = Object.values(error.data.errors).flat();
                        errorMessage = errorMessages.join('\n');
                    } else if (error.data.error) {
                        errorMessage = error.data.error;
                    } else if (error.data.message) {
                        errorMessage = error.data.message;
                    }
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                alert(errorMessage);
            });
        }
        
        // دالة تنظيف HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
