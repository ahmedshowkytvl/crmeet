@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isRTL = $locale === 'ar';
    $dir = $isRTL ? 'rtl' : 'ltr';
@endphp

@section('title', $isRTL ? 'إدارة غرف الاجتماعات' : 'Meeting Rooms Management')

@section('content')
<div class="container-fluid py-4" x-data="roomManagement('{{ $locale }}', {{ $isRTL ? 'true' : 'false' }})" x-init="init()">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-door-open me-2"></i>
                    {{ $isRTL ? 'إدارة غرف الاجتماعات' : 'Meeting Rooms Management' }}
                </h2>
                <button class="btn btn-primary" @click="openCreateRoomModal()" x-show="canManageRooms">
                    <i class="fas fa-plus me-2"></i>
                    {{ $isRTL ? 'إضافة غرفة جديدة' : 'Add New Room' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Rooms Grid -->
    <div class="row" id="rooms-grid">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ $isRTL ? 'جارٍ التحميل...' : 'Loading...' }}</span>
                        </div>
                        <p class="mt-2 text-muted">{{ $isRTL ? 'جارٍ تحميل الغرف...' : 'Loading rooms...' }}</p>
                    </div>

                    <!-- Rooms List -->
                    <div x-show="!loading" class="row">
                        <template x-for="room in rooms" :key="room.id">
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm" :class="room.is_available ? 'border-success' : 'border-danger'">
                                    <div class="card-header d-flex justify-content-between align-items-center" :class="room.is_available ? 'bg-success text-white' : 'bg-danger text-white'">
                                        <h5 class="mb-0" x-text="room.name || (isRTL ? room.name_ar : room.name)"></h5>
                                        <span class="badge" :class="room.is_available ? 'bg-light text-dark' : 'bg-dark'">
                                            <i class="fas" :class="room.is_available ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                            <span x-text="room.is_available ? (isRTL ? 'متاحة' : 'Available') : (isRTL ? 'غير متاحة' : 'Unavailable')"></span>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <span x-text="room.location || (isRTL ? room.location_ar : room.location)"></span>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-users me-2"></i>
                                            <span x-text="room.capacity"></span> {{ $isRTL ? 'أشخاص' : 'people' }}
                                        </p>
                                        <div x-show="room.amenities && room.amenities.length > 0" class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-star me-2"></i>
                                                <template x-for="(amenity, index) in room.amenities" :key="index">
                                                    <span>
                                                        <span x-text="amenity"></span><span x-show="index < room.amenities.length - 1">, </span>
                                                    </span>
                                                </template>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-sm btn-outline-primary" @click="bookRoom(room.id)">
                                                <i class="fas fa-calendar-check me-1"></i>
                                                {{ $isRTL ? 'حجز' : 'Book' }}
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" @click="viewRoomCalendar(room.id)">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $isRTL ? 'التقويم' : 'Calendar' }}
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" @click="editRoom(room)" x-show="canManageRooms">
                                                <i class="fas fa-edit me-1"></i>
                                                {{ $isRTL ? 'تعديل' : 'Edit' }}
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="deleteRoom(room.id)" x-show="canManageRooms">
                                                <i class="fas fa-trash me-1"></i>
                                                {{ $isRTL ? 'حذف' : 'Delete' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && rooms.length === 0" class="text-center py-5">
                        <div class="alert alert-info mx-auto" style="max-width: 600px;">
                            <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
                            <h5>{{ $isRTL ? 'لا توجد غرف متاحة حالياً' : 'No Rooms Available' }}</h5>
                            <p class="mb-3">{{ $isRTL ? 'لم يتم إضافة أي غرف اجتماعات بعد. يمكنك إضافة غرفة جديدة باستخدام الزر "إضافة غرفة جديدة" أعلاه.' : 'No meeting rooms have been added yet. You can add a new room using the "Add New Room" button above.' }}</p>
                            <button class="btn btn-primary" @click="openCreateRoomModal()" x-show="canManageRooms">
                                <i class="fas fa-plus me-2"></i>
                                {{ $isRTL ? 'إضافة غرفة جديدة' : 'Add New Room' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Room Modal -->
    <div class="modal fade" id="room-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="editingRoom ? (isRTL ? 'تعديل الغرفة' : 'Edit Room') : (isRTL ? 'إضافة غرفة جديدة' : 'Add New Room')"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form @submit.prevent="saveRoom">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'اسم الغرفة' : 'Room Name' }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" x-model="roomForm.name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $isRTL ? 'اسم الغرفة (عربي)' : 'Room Name (Arabic)' }}</label>
                                <input type="text" class="form-control" x-model="roomForm.name_ar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'الموقع' : 'Location' }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" x-model="roomForm.location" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $isRTL ? 'الموقع (عربي)' : 'Location (Arabic)' }}</label>
                                <input type="text" class="form-control" x-model="roomForm.location_ar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'السعة' : 'Capacity' }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" x-model="roomForm.capacity" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $isRTL ? 'السعر بالساعة' : 'Hourly Rate' }}</label>
                                <input type="number" class="form-control" x-model="roomForm.hourly_rate" min="0" step="0.01">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'الوصف' : 'Description' }}</label>
                                <textarea class="form-control" x-model="roomForm.description" rows="3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" x-model="roomForm.is_available" id="is_available">
                                    <label class="form-check-label" for="is_available">
                                        {{ $isRTL ? 'متاحة للحجز' : 'Available for Booking' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ $isRTL ? 'إلغاء' : 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isRTL ? 'حفظ' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function roomManagement(locale, isRTL) {
    return {
        locale: locale || 'en',
        isRTL: isRTL || false,
        rooms: [],
        loading: true,
        roomForm: {
            name: '',
            name_ar: '',
            location: '',
            location_ar: '',
            capacity: 10,
            description: '',
            is_available: true,
            hourly_rate: null
        },
        editingRoom: null,
        canManageRooms: false,

        async init() {
            this.loading = true;
            await this.loadRooms();
            this.checkPermissions();
            this.loading = false;
        },

        async loadRooms() {
            try {
                // Use session-based request (no token needed)
                const response = await axios.get('/api/schedule/meeting-rooms', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    withCredentials: true
                });
                
                if (response.data && response.data.success !== false) {
                    this.rooms = response.data.data || [];
                    // Empty array is normal - no error
                } else {
                    this.rooms = [];
                }
            } catch (error) {
                // Silently handle - empty array is the normal state when no rooms exist
                console.log('Load rooms:', error.response?.status || 'network error');
                this.rooms = [];
                // Never show error alerts for empty data
            }
        },

        checkPermissions() {
            // Check if user can manage rooms
            this.canManageRooms = true; // For now, allow all authenticated users
        },

        openCreateRoomModal() {
            this.editingRoom = null;
            this.roomForm = {
                name: '',
                name_ar: '',
                location: '',
                location_ar: '',
                capacity: 10,
                description: '',
                is_available: true,
                hourly_rate: null
            };
            const modal = new bootstrap.Modal(document.getElementById('room-modal'));
            modal.show();
        },

        editRoom(room) {
            this.editingRoom = room;
            this.roomForm = {
                name: room.name || '',
                name_ar: room.name_ar || '',
                location: room.location || '',
                location_ar: room.location_ar || '',
                capacity: room.capacity || 10,
                description: room.description || '',
                is_available: room.is_available !== undefined ? room.is_available : true,
                hourly_rate: room.hourly_rate || null
            };
            const modal = new bootstrap.Modal(document.getElementById('room-modal'));
            modal.show();
        },

        async saveRoom() {
            try {
                const url = this.editingRoom 
                    ? `/api/schedule/meeting-rooms/${this.editingRoom.id}`
                    : '/api/schedule/meeting-rooms';
                const method = this.editingRoom ? 'put' : 'post';

                const response = await axios[method](url, this.roomForm, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSuccess(this.isRTL ? 'تم حفظ الغرفة بنجاح' : 'Room saved successfully');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('room-modal'));
                    modal.hide();
                    this.loading = true;
                    await this.loadRooms();
                    this.loading = false;
                } else {
                    alert(response.data.message || (this.isRTL ? 'فشل حفظ الغرفة' : 'Failed to save room'));
                }
            } catch (error) {
                console.error('Error saving room:', error);
                const errorMsg = error.response?.data?.message || (this.isRTL ? 'حدث خطأ أثناء حفظ الغرفة' : 'Error saving room');
                alert(errorMsg);
            }
        },

        async deleteRoom(roomId) {
            if (!confirm(this.isRTL ? 'هل أنت متأكد من حذف هذه الغرفة؟' : 'Are you sure you want to delete this room?')) {
                return;
            }

            try {
                const response = await axios.delete(`/api/schedule/meeting-rooms/${roomId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSuccess(this.isRTL ? 'تم حذف الغرفة بنجاح' : 'Room deleted successfully');
                    this.loading = true;
                    await this.loadRooms();
                    this.loading = false;
                } else {
                    alert(response.data.message || (this.isRTL ? 'فشل حذف الغرفة' : 'Failed to delete room'));
                }
            } catch (error) {
                console.error('Error deleting room:', error);
                const errorMsg = error.response?.data?.message || (this.isRTL ? 'حدث خطأ أثناء حذف الغرفة' : 'Error deleting room');
                alert(errorMsg);
            }
        },

        bookRoom(roomId) {
            window.location.href = `/schedule/rooms/book/${roomId}`;
        },

        viewRoomCalendar(roomId) {
            window.location.href = `/schedule?room=${roomId}`;
        },

        showSuccess(message) {
            // Show success notification
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            alertDiv.innerHTML = `
                <strong>${this.isRTL ? '✅ نجح!' : '✅ Success!'}</strong><br>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }
            }, 5000);
        }
    }
}
</script>
@endpush
@endsection
