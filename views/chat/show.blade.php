@extends('layouts.app')

@section('title', 'الدردشة - ' . $chatRoom->getDisplayName(auth()->id()))

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4 col-lg-3 chat-sidebar">
            <div class="chat-sidebar-header">
                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <h5 class="mb-0">الدردشات</h5>
            </div>

            <!-- Chat List -->
            <div class="chat-list">
                @php
                    $userChatRooms = auth()->user()->chatRooms()
                        ->wherePivot('is_archived', false)
                        ->with(['lastMessage', 'users' => function($query) {
                            $query->where('user_id', '!=', auth()->id());
                        }])
                        ->orderBy('last_message_at', 'desc')
                        ->get();
                @endphp
                
                @foreach($userChatRooms as $room)
                    <div class="chat-item {{ $room->id == $chatRoom->id ? 'active' : '' }}" 
                         data-chat-id="{{ $room->id }}">
                        <div class="chat-avatar">
                            @if($room->getDisplayAvatar(auth()->id()))
                                <img src="{{ asset('storage/' . $room->getDisplayAvatar(auth()->id())) }}" 
                                     alt="{{ $room->getDisplayName(auth()->id()) }}" 
                                     class="rounded-circle">
                            @else
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="chat-content">
                            <div class="chat-name">{{ $room->getDisplayName(auth()->id()) }}</div>
                            @if($room->lastMessage)
                                <div class="chat-preview">
                                    @if($room->lastMessage->message_type === 'image')
                                        <i class="fas fa-image me-1"></i>صورة
                                    @elseif($room->lastMessage->message_type === 'file')
                                        <i class="fas fa-file me-1"></i>ملف
                                    @elseif($room->lastMessage->message_type === 'contact')
                                        <i class="fas fa-user me-1"></i>جهة اتصال
                                    @else
                                        {{ Str::limit($room->lastMessage->content, 50) }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="chat-meta">
                            @if($room->lastMessage)
                                <div class="chat-time">
                                    {{ $room->lastMessage->created_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8 col-lg-9 chat-main">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <div class="chat-avatar">
                        @if($chatRoom->getDisplayAvatar(auth()->id()))
                            <img src="{{ asset('storage/' . $chatRoom->getDisplayAvatar(auth()->id())) }}" 
                                 alt="{{ $chatRoom->getDisplayName(auth()->id()) }}" 
                                 class="rounded-circle">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="chat-info">
                        <h6 class="mb-0">{{ $chatRoom->getDisplayName(auth()->id()) }}</h6>
                        <small class="text-muted">
                            @if($otherParticipants->count() > 0)
                                {{ $otherParticipants->first()->position ?? 'موظف' }}
                            @endif
                        </small>
                    </div>
                </div>
                <div class="chat-actions">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-secondary" id="searchMessagesBtn" title="البحث في الرسائل">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="chatInfoBtn" title="معلومات الدردشة">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="muteChatBtn">
                                    <i class="fas fa-volume-mute me-2"></i>كتم الصوت
                                </a></li>
                                <li><a class="dropdown-item" href="#" id="archiveChatBtn">
                                    <i class="fas fa-archive me-2"></i>أرشفة
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="chat-messages" id="chatMessages">
                @foreach($messages as $message)
                    <div class="message {{ $message->user_id == auth()->id() ? 'sent' : 'received' }}" 
                         data-message-id="{{ $message->id }}">
                        <div class="message-avatar">
                            @if($message->user && $message->user->profile_picture)
                                <img src="{{ asset('storage/' . $message->user->profile_picture) }}" 
                                     alt="{{ $message->user->name }}" 
                                     class="rounded-circle">
                            @else
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="sender-name">{{ $message->user->name ?? 'مستخدم' }}</span>
                                <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
                            </div>
                            <div class="message-body">
                                @if($message->type === 'text')
                                    <div class="message-text">{{ $message->message }}</div>
                                @elseif($message->type === 'image')
                                    <div class="message-image">
                                        <img src="{{ asset('storage/' . $message->attachment_url) }}" 
                                             alt="صورة" 
                                             class="img-fluid rounded">
                                    </div>
                                @elseif($message->type === 'file')
                                    <div class="message-file">
                                        <div class="file-info">
                                            <i class="fas fa-file me-2"></i>
                                            <span class="file-name">{{ $message->attachment_name }}</span>
                                            <span class="file-size">({{ $message->attachment_size_formatted }})</span>
                                        </div>
                                        <a href="{{ route('chat.files.download', $message->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> تحميل
                                        </a>
                                    </div>
                                @elseif($message->type === 'contact')
                                    <div class="message-contact">
                                        <div class="contact-card">
                                            <div class="contact-avatar">
                                                @if($message->contact_data['contact_avatar'])
                                                    <img src="{{ asset('storage/' . $message->contact_data['contact_avatar']) }}" 
                                                         alt="{{ $message->contact_data['contact_name'] }}" 
                                                         class="rounded-circle">
                                                @else
                                                    <div class="avatar-placeholder">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="contact-info">
                                                <h6 class="mb-1">{{ $message->contact_data['contact_name'] }}</h6>
                                                <small class="text-muted">{{ $message->contact_data['contact_position'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if($message->is_edited)
                                <div class="message-edited">
                                    <small class="text-muted">
                                        <i class="fas fa-edit me-1"></i>تم التعديل
                                    </small>
                                </div>
                            @endif
                            <div class="message-status">
                                @if($message->status === 'read')
                                    <i class="fas fa-check-double text-primary"></i>
                                @elseif($message->status === 'delivered')
                                    <i class="fas fa-check-double"></i>
                                @else
                                    <i class="fas fa-check"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div class="chat-input">
                <div class="input-group">
                    <button class="btn btn-outline-secondary" type="button" id="attachFileBtn">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <button class="btn btn-outline-secondary" type="button" id="attachContactBtn">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <input type="text" class="form-control" id="messageInput" placeholder="اكتب رسالتك...">
                    <button class="btn btn-primary" type="button" id="sendMessageBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <input type="file" id="fileInput" style="display: none;" multiple>
            </div>
        </div>
    </div>
</div>

<!-- Contact Selection Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اختر جهة اتصال</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="contactSearch" placeholder="البحث عن موظف...">
                </div>
                <div id="contactSearchResults"></div>
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
    // Set current user ID for message comparison
    window.currentUserId = {{ auth()->id() }};
    
    // Initialize chat functionality
    window.chatApp = new ChatApp({
        chatRoomId: {{ $chatRoom->id }},
        sendMessageUrl: '{{ route("chat.messages.send-text") }}',
        sendContactUrl: '{{ route("chat.messages.send-contact") }}',
        uploadFileUrl: '{{ route("chat.files.upload") }}',
        searchUsersUrl: '{{ route("chat.search.users") }}',
        csrfToken: '{{ csrf_token() }}'
    });
    
    // Mark messages as read
    window.chatApp.markAllAsRead();
});
</script>
@endpush
