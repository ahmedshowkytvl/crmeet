@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isRTL = $locale === 'ar';
    $dir = $isRTL ? 'rtl' : 'ltr';
    $roomId = $roomId ?? null;
@endphp

@section('title', $isRTL ? 'حجز غرفة اجتماع' : 'Book Meeting Room')

@section('content')
<div class="container-fluid py-4" x-data="roomBooking('{{ $locale }}', {{ $isRTL ? 'true' : 'false' }}, {{ $roomId ?: 'null' }})" x-init="init()">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-calendar-check me-2"></i>
                {{ $isRTL ? 'حجز غرفة اجتماع' : 'Book Meeting Room' }}
            </h2>
        </div>
    </div>

    <div class="row">
        <!-- Booking Form -->
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $isRTL ? 'تفاصيل الحجز' : 'Booking Details' }}</h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="bookRoom">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'اختر الغرفة' : 'Select Room' }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" x-model="bookingForm.room_id" @change="loadRoomDetails" required>
                                    <option value="">{{ $isRTL ? '-- اختر الغرفة --' : '-- Select Room --' }}</option>
                                    <template x-for="room in availableRooms" :key="room.id">
                                        <option :value="room.id" x-text="room.name || (isRTL ? room.name_ar : room.name)"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'عنوان الاجتماع' : 'Meeting Title' }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" x-model="bookingForm.title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $isRTL ? 'الوصف' : 'Description' }}</label>
                                <input type="text" class="form-control" x-model="bookingForm.description">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'تاريخ ووقت البداية' : 'Start Date & Time' }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" x-model="bookingForm.start_time" @change="checkAvailability" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'تاريخ ووقت النهاية' : 'End Date & Time' }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" x-model="bookingForm.end_time" @change="checkAvailability" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'المشاركون' : 'Attendees' }}</label>
                                <select class="form-select" x-model="bookingForm.attendee_ids" multiple size="5">
                                    <template x-for="user in users" :key="user.id">
                                        <option :value="user.id" x-text="user.name"></option>
                                    </template>
                                </select>
                                <small class="text-muted">
                                    {{ $isRTL ? 'اضغط Ctrl/Cmd لتحديد عدة مستخدمين' : 'Press Ctrl/Cmd to select multiple users' }}
                                </small>
                            </div>
                            <div class="col-12" x-show="availabilityStatus">
                                <div class="alert" :class="availabilityStatus === 'available' ? 'alert-success' : 'alert-danger'">
                                    <i class="fas" :class="availabilityStatus === 'available' ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span x-text="availabilityMessage"></span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" @click="window.history.back()">
                                {{ $isRTL ? 'إلغاء' : 'Cancel' }}
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="availabilityStatus !== 'available'">
                                <i class="fas fa-calendar-check me-2"></i>
                                {{ $isRTL ? 'حجز الغرفة' : 'Book Room' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Room Details & Calendar -->
        <div class="col-12 col-lg-4">
            <!-- Room Details -->
            <div class="card shadow-sm mb-4" x-show="selectedRoom">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">{{ $isRTL ? 'تفاصيل الغرفة' : 'Room Details' }}</h5>
                </div>
                <div class="card-body" x-show="selectedRoom">
                    <h6 x-text="selectedRoom?.name || (isRTL ? selectedRoom?.name_ar : selectedRoom?.name)"></h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span x-text="selectedRoom?.location || (isRTL ? selectedRoom?.location_ar : selectedRoom?.location)"></span>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-users me-2"></i>
                        <span x-text="selectedRoom?.capacity"></span> {{ $isRTL ? 'أشخاص' : 'people' }}
                    </p>
                    <div x-show="selectedRoom?.amenities && selectedRoom.amenities.length > 0">
                        <small class="text-muted">
                            <i class="fas fa-star me-2"></i>
                            <template x-for="(amenity, index) in selectedRoom.amenities" :key="index">
                                <span>
                                    <span x-text="amenity"></span><span x-show="index < selectedRoom.amenities.length - 1">, </span>
                                </span>
                            </template>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Available Time Slots -->
            <div class="card shadow-sm" x-show="selectedRoom && availableTimeSlots.length > 0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">{{ $isRTL ? 'الأوقات المتاحة' : 'Available Time Slots' }}</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <template x-for="slot in availableTimeSlots" :key="slot.start">
                            <button type="button" class="list-group-item list-group-item-action" @click="selectTimeSlot(slot)">
                                <small x-text="formatTimeSlot(slot)"></small>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function roomBooking(locale, isRTL, roomId) {
    return {
        locale: locale || 'en',
        isRTL: isRTL || false,
        availableRooms: [],
        users: [],
        selectedRoom: null,
        availableTimeSlots: [],
        availabilityStatus: null,
        availabilityMessage: '',
        bookingForm: {
            room_id: roomId || '',
            title: '',
            description: '',
            start_time: '',
            end_time: '',
            attendee_ids: []
        },

        async init() {
            await this.loadRooms();
            await this.loadUsers();
            if (this.bookingForm.room_id) {
                await this.loadRoomDetails();
            }
        },

        async loadRooms() {
            try {
                const response = await axios.get('/api/schedule/meeting-rooms?available=true', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });
                if (response.data.success) {
                    this.availableRooms = response.data.data || [];
                    // Don't show error if no rooms - it's normal
                } else {
                    this.availableRooms = [];
                }
            } catch (error) {
                console.error('Error loading rooms:', error);
                // Only show error for actual server errors, not empty data
                if (error.response && error.response.status >= 500) {
                    this.showAlert(this.isRTL ? 'حدث خطأ في الخادم' : 'Server error', 'error');
                }
                this.availableRooms = [];
            }
        },

        async loadUsers() {
            try {
                const response = await axios.get('/api/users', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                }).catch(() => {
                    return axios.get('/users', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                });
                
                if (response.data && (response.data.success || Array.isArray(response.data))) {
                    this.users = Array.isArray(response.data) ? response.data : response.data.data;
                }
            } catch (error) {
                console.error('Error loading users:', error);
                this.users = [];
            }
        },

        async loadRoomDetails() {
            if (!this.bookingForm.room_id) return;

            try {
                const response = await axios.get(`/api/schedule/meeting-rooms/${this.bookingForm.room_id}`, {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });
                if (response.data.success) {
                    this.selectedRoom = response.data.data;
                    await this.loadAvailableTimeSlots();
                }
            } catch (error) {
                console.error('Error loading room details:', error);
            }
        },

        async loadAvailableTimeSlots() {
            if (!this.bookingForm.room_id || !this.bookingForm.start_time) return;

            try {
                const date = new Date(this.bookingForm.start_time).toISOString().split('T')[0];
                const response = await axios.get(`/api/schedule/meeting-rooms/${this.bookingForm.room_id}/available-time-slots`, {
                    params: {
                        date: date,
                        duration: 60
                    },
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });
                if (response.data.success) {
                    this.availableTimeSlots = response.data.data;
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
            }
        },

        async checkAvailability() {
            if (!this.bookingForm.room_id || !this.bookingForm.start_time || !this.bookingForm.end_time) {
                this.availabilityStatus = null;
                return;
            }

            try {
                const response = await axios.get('/api/schedule/meeting-rooms/available', {
                    params: {
                        start_time: this.bookingForm.start_time,
                        end_time: this.bookingForm.end_time,
                    },
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.success) {
                    const isAvailable = response.data.data.some(room => room.id == this.bookingForm.room_id);
                    if (isAvailable) {
                        this.availabilityStatus = 'available';
                        this.availabilityMessage = this.isRTL ? 'الغرفة متاحة في هذا الوقت' : 'Room is available at this time';
                    } else {
                        this.availabilityStatus = 'unavailable';
                        this.availabilityMessage = this.isRTL ? 'الغرفة غير متاحة في هذا الوقت' : 'Room is not available at this time';
                    }
                }
            } catch (error) {
                console.error('Error checking availability:', error);
                this.availabilityStatus = 'error';
                this.availabilityMessage = this.isRTL ? 'حدث خطأ أثناء التحقق من التوفر' : 'Error checking availability';
            }
        },

        selectTimeSlot(slot) {
            this.bookingForm.start_time = slot.start.replace(' ', 'T').substring(0, 16);
            this.bookingForm.end_time = slot.end.replace(' ', 'T').substring(0, 16);
            this.checkAvailability();
        },

        formatTimeSlot(slot) {
            const start = new Date(slot.start);
            const end = new Date(slot.end);
            return `${start.toLocaleTimeString(this.locale, { hour: '2-digit', minute: '2-digit' })} - ${end.toLocaleTimeString(this.locale, { hour: '2-digit', minute: '2-digit' })}`;
        },

        async bookRoom() {
            if (!this.bookingForm.room_id || !this.bookingForm.title || !this.bookingForm.start_time || !this.bookingForm.end_time) {
                this.showAlert(this.isRTL ? 'يرجى ملء جميع الحقول المطلوبة' : 'Please fill all required fields', 'error');
                return;
            }

            try {
                const data = {
                    title: this.bookingForm.title,
                    description: this.bookingForm.description,
                    start_time: this.bookingForm.start_time,
                    end_time: this.bookingForm.end_time,
                    attendee_ids: Array.isArray(this.bookingForm.attendee_ids) 
                        ? this.bookingForm.attendee_ids 
                        : [this.bookingForm.attendee_ids].filter(id => id)
                };

                const response = await axios.post(`/api/schedule/meeting-rooms/${this.bookingForm.room_id}/book`, data, {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (response.data.success) {
                    this.showAlert(
                        this.isRTL ? 'تم حجز الغرفة بنجاح' : 'Room booked successfully',
                        'success'
                    );
                    setTimeout(() => {
                        window.location.href = '/schedule';
                    }, 1500);
                } else {
                    this.showAlert(
                        response.data.message || (this.isRTL ? 'فشل حجز الغرفة' : 'Failed to book room'),
                        'error'
                    );
                }
            } catch (error) {
                console.error('Error booking room:', error);
                this.showAlert(
                    error.response?.data?.message || (this.isRTL ? 'حدث خطأ أثناء حجز الغرفة' : 'Error booking room'),
                    'error'
                );
            }
        },

        showAlert(message, type = 'info') {
            // Only show alerts for actual errors, not for empty data
            if (type === 'error') {
                console.error('Alert:', message);
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        },

        getAuthToken() {
            const metaToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
            if (metaToken) return metaToken;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            return csrfToken || '';
        }
    }
}
</script>
@endpush
@endsection

