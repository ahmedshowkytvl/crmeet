@extends('layouts.app')

@section('title', 'بدء دردشة جديدة')

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
                @foreach($chatRooms as $room)
                    <div class="chat-item">
                        <a href="{{ route('chat.show', $room->id) }}" class="text-decoration-none">
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
                            <div class="chat-info">
                                <h6 class="mb-0">{{ $room->getDisplayName(auth()->id()) }}</h6>
                                @if($room->lastMessage)
                                    <p class="mb-0 text-muted small">{{ Str::limit($room->lastMessage->content, 30) }}</p>
                                @endif
                            </div>
                            <div class="chat-time">
                                @if($room->lastMessage)
                                    <small class="text-muted">{{ $room->lastMessage->created_at->diffForHumans() }}</small>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-8 col-lg-9">
            <div class="chat-main">
                <!-- Header -->
                <div class="chat-header">
                    <div class="chat-header-info">
                        <h4 class="mb-0">بدء دردشة جديدة</h4>
                        <p class="text-muted mb-0">اختر موظفاً للدردشة معه</p>
                    </div>
                </div>

                <!-- Search Section -->
                <div class="chat-content">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">البحث عن موظف</h5>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" id="employeeSearch" placeholder="ابحث عن موظف...">
                                    </div>
                                    <div id="searchResults" class="row">
                                        @foreach($employees as $employee)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card employee-card" data-user-id="{{ $employee->id }}">
                                                    <div class="card-body text-center">
                                                        <div class="employee-avatar mb-2">
                                                            @if($employee->profile_picture)
                                                                <img src="{{ asset('storage/' . $employee->profile_picture) }}" 
                                                                     alt="{{ $employee->name }}" 
                                                                     class="rounded-circle" 
                                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                            @else
                                                                <div class="avatar-placeholder rounded-circle mx-auto" 
                                                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                                                    <i class="fas fa-user fa-2x text-muted"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <h6 class="card-title mb-1">{{ $employee->name }}</h6>
                                                        <p class="card-text text-muted small mb-2">موظف</p>
                                                        <button class="btn btn-primary btn-sm start-chat-btn" 
                                                                data-user-id="{{ $employee->id }}" 
                                                                data-user-name="{{ $employee->name }}">
                                                            بدء دردشة
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2 mb-0">جاري إنشاء الدردشة...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
<style>
.employee-card {
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.employee-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.employee-card .card-body {
    padding: 1rem;
}

.employee-avatar img {
    border: 2px solid #e9ecef;
}

.start-chat-btn {
    width: 100%;
}

#searchResults {
    max-height: 500px;
    overflow-y: auto;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('employeeSearch');
    const searchResults = document.getElementById('searchResults');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            // Show all employees if search is too short
            document.querySelectorAll('.employee-card').forEach(card => {
                card.closest('.col-md-6').style.display = 'block';
            });
            return;
        }

        // Filter employees
        document.querySelectorAll('.employee-card').forEach(card => {
            const userName = card.querySelector('.card-title').textContent.toLowerCase();
            const userPosition = card.querySelector('.card-text').textContent.toLowerCase();
            const searchQuery = query.toLowerCase();
            
            if (userName.includes(searchQuery) || userPosition.includes(searchQuery)) {
                card.closest('.col-md-6').style.display = 'block';
            } else {
                card.closest('.col-md-6').style.display = 'none';
            }
        });
    });

    // Start chat functionality
    document.querySelectorAll('.start-chat-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            // Show loading modal
            loadingModal.show();
            
            // Send request to start chat
            fetch('{{ route("chat.start.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId
                })
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok');
            })
            .then(data => {
                loadingModal.hide();
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Handle error
                    alert('حدث خطأ أثناء إنشاء الدردشة');
                }
            })
            .catch(error => {
                loadingModal.hide();
                console.error('Error:', error);
                alert('حدث خطأ أثناء إنشاء الدردشة');
            });
        });
    });

    // Employee card click to start chat
    document.querySelectorAll('.employee-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.classList.contains('start-chat-btn')) {
                const btn = this.querySelector('.start-chat-btn');
                if (btn) {
                    btn.click();
                }
            }
        });
    });
});
</script>
@endpush
