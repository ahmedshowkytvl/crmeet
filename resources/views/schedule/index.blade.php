@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isRTL = $locale === 'ar';
    $dir = $isRTL ? 'rtl' : 'ltr';
@endphp

@section('title', $isRTL ? 'الجدولة والتقويم' : 'Schedule & Calendar')

@section('content')
<div class="container-fluid py-4" x-data="scheduleCalendar('{{ $locale }}', {{ $isRTL ? 'true' : 'false' }})" x-init="init()">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i>
                {{ $isRTL ? 'الجدولة والتقويم' : 'Schedule & Calendar' }}
            </h2>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="row">
        <div class="col-12 col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="schedule-calendar"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-3 mt-4 mt-lg-0">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ $isRTL ? 'إجراءات سريعة' : 'Quick Actions' }}
                    </h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary w-100 mb-2" @click="openCreateEventModal()">
                        <i class="fas fa-plus me-2"></i>
                        {{ $isRTL ? 'إنشاء حدث جديد' : 'Create New Event' }}
                    </button>
                    <a href="/schedule/rooms/book" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-door-open me-2"></i>
                        {{ $isRTL ? 'حجز غرفة اجتماع' : 'Book Meeting Room' }}
                    </a>
                    <a href="/schedule/rooms" class="btn btn-outline-info w-100 mb-2">
                        <i class="fas fa-building me-2"></i>
                        {{ $isRTL ? 'إدارة الغرف' : 'Manage Rooms' }}
                    </a>
                    <button class="btn btn-outline-info w-100" @click="refreshCalendar()">
                        <i class="fas fa-sync-alt me-2"></i>
                        {{ $isRTL ? 'تحديث التقويم' : 'Refresh Calendar' }}
                    </button>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        {{ $isRTL ? 'الأحداث القادمة' : 'Upcoming Events' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div id="upcoming-events-list">
                        <p class="text-muted text-center">{{ $isRTL ? 'جارٍ التحميل...' : 'Loading...' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div class="modal fade" id="create-event-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isRTL ? 'إنشاء حدث جديد' : 'Create New Event' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="create-event-form" @submit.prevent="createEvent">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'العنوان' : 'Title' }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'الوصف' : 'Description' }}</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'وقت البداية' : 'Start Time' }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $isRTL ? 'وقت النهاية' : 'End Time' }} <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control" name="end_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $isRTL ? 'نوع الحدث' : 'Event Type' }}</label>
                                <select class="form-select" name="event_type">
                                    <option value="event">{{ $isRTL ? 'حدث' : 'Event' }}</option>
                                    <option value="meeting">{{ $isRTL ? 'اجتماع' : 'Meeting' }}</option>
                                    <option value="reminder">{{ $isRTL ? 'تذكير' : 'Reminder' }}</option>
                                    <option value="task">{{ $isRTL ? 'مهمة' : 'Task' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $isRTL ? 'الأولوية' : 'Priority' }}</label>
                                <select class="form-select" name="priority">
                                    <option value="low">{{ $isRTL ? 'منخفضة' : 'Low' }}</option>
                                    <option value="medium" selected>{{ $isRTL ? 'متوسطة' : 'Medium' }}</option>
                                    <option value="high">{{ $isRTL ? 'عالية' : 'High' }}</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'المكان' : 'Location' }}</label>
                                <input type="text" class="form-control" name="location">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'غرفة الاجتماع' : 'Meeting Room' }}</label>
                                <select class="form-select" name="meeting_room_id">
                                    <option value="">{{ $isRTL ? 'لا يوجد' : 'None' }}</option>
                                    <template x-for="room in meetingRooms" :key="room.id">
                                        <option :value="room.id" x-text="room.name || (isRTL ? room.name_ar : room.name)"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'المشاركون' : 'Attendees' }}</label>
                                <select class="form-select" name="attendee_ids[]" multiple size="5">
                                    <template x-for="user in users" :key="user.id">
                                        <option :value="user.id" x-text="user.name"></option>
                                    </template>
                                </select>
                                <small class="text-muted">
                                    {{ $isRTL ? 'اضغط Ctrl/Cmd لتحديد عدة مستخدمين' : 'Press Ctrl/Cmd to select multiple users' }}
                                </small>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" @change="toggleRecurringOptions($event)">
                                    <label class="form-check-label" for="is_recurring">
                                        {{ $isRTL ? 'حدث متكرر' : 'Recurring Event' }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 mb-3" id="recurring-options" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">{{ $isRTL ? 'نمط التكرار' : 'Recurrence Pattern' }}</label>
                                        <select class="form-select" name="recurring_pattern">
                                            <option value="daily">{{ $isRTL ? 'يومي' : 'Daily' }}</option>
                                            <option value="weekly">{{ $isRTL ? 'أسبوعي' : 'Weekly' }}</option>
                                            <option value="monthly">{{ $isRTL ? 'شهري' : 'Monthly' }}</option>
                                            <option value="yearly">{{ $isRTL ? 'سنوي' : 'Yearly' }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">{{ $isRTL ? 'تاريخ الانتهاء' : 'End Date' }}</label>
                                        <input type="date" class="form-control" name="recurring_end_date">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ $isRTL ? 'اللون' : 'Color' }}</label>
                                <input type="color" class="form-control form-control-color" name="color" value="#3788d8">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ $isRTL ? 'إلغاء' : 'Cancel' }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isRTL ? 'إنشاء' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="event-details-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" data-event-title>{{ $isRTL ? 'تفاصيل الحدث' : 'Event Details' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>{{ $isRTL ? 'الوصف' : 'Description' }}:</strong> <span data-event-description></span></p>
                    <p><strong>{{ $isRTL ? 'المكان' : 'Location' }}:</strong> <span data-event-location></span></p>
                    <p><strong>{{ $isRTL ? 'البداية' : 'Start' }}:</strong> <span data-event-start></span></p>
                    <p><strong>{{ $isRTL ? 'النهاية' : 'End' }}:</strong> <span data-event-end></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ $isRTL ? 'إغلاق' : 'Close' }}
                    </button>
                    <button type="button" class="btn btn-primary" data-event-edit style="display: none;">
                        {{ $isRTL ? 'تعديل' : 'Edit' }}
                    </button>
                    <button type="button" class="btn btn-danger" data-event-delete style="display: none;">
                        {{ $isRTL ? 'حذف' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css' rel='stylesheet' />
<style>
    #schedule-calendar {
        padding: 20px;
        min-height: 600px;
    }
    
    .fc {
        direction: {{ $dir }};
    }
    
    @if($isRTL)
    .fc-toolbar {
        flex-direction: row-reverse;
    }
    
    .fc-button-group {
        flex-direction: row-reverse;
    }
    @endif
    
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
    }
    
    .fc-daygrid-event {
        white-space: normal;
    }
    
    .fc-event-title {
        font-weight: 500;
    }
    
    .fc-day-today {
        background-color: #fff3cd !important;
    }
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
// Make FullCalendar available globally
window.FullCalendar = FullCalendar;

// FullCalendar Arabic locale
if (typeof FullCalendar !== 'undefined') {
    // Arabic locale
    FullCalendar.globalLocales.push(function () {
        'use strict';
        return {
            code: 'ar',
            week: {
                dow: 6,
                doy: 12
            },
            direction: 'rtl',
            buttonText: {
                prev: 'السابق',
                next: 'التالي',
                today: 'اليوم',
                month: 'شهر',
                week: 'أسبوع',
                day: 'يوم',
                list: 'قائمة'
            },
            weekText: 'أسبوع',
            allDayText: 'اليوم كله',
            moreLinkText: 'أخرى',
            noEventsText: 'لا توجد أحداث',
            weekTextLong: 'أسبوع'
        };
    }());
    
    // English locale is already included in FullCalendar by default
}

function scheduleCalendar(locale, isRTL) {
    return {
        locale: locale || 'en',
        isRTL: isRTL || false,
        calendar: null,
        meetingRooms: [],
        users: [],
        selectedEvent: null,

        async init() {
            await this.loadMeetingRooms();
            await this.loadUsers();
            this.initCalendar();
            this.loadUpcomingEvents();
        },

        initCalendar() {
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar is not loaded');
                return;
            }

            const calendarEl = document.getElementById('schedule-calendar');
            if (!calendarEl) {
                console.error('Calendar element not found');
                return;
            }

            // Calendar configuration
            const calendarConfig = {
                initialView: 'dayGridMonth',
                locale: this.locale,
                direction: this.isRTL ? 'rtl' : 'ltr',
                firstDay: this.isRTL ? 6 : 0, // Saturday for Arabic, Sunday for English
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: this.isRTL ? {
                    today: 'اليوم',
                    month: 'شهر',
                    week: 'أسبوع',
                    day: 'يوم',
                    list: 'قائمة'
                } : undefined,
                allDayText: this.isRTL ? 'اليوم كله' : 'All Day',
                noEventsText: this.isRTL ? 'لا توجد أحداث' : 'No events to display',
                moreLinkText: this.isRTL ? 'أخرى' : 'more',
                events: (info, successCallback, failureCallback) => {
                    this.fetchEvents(info.start, info.end, successCallback, failureCallback);
                },
                eventClick: (info) => {
                    this.showEventDetails(info.event);
                },
                dateClick: (info) => {
                    this.openCreateEventModal({
                        startDate: info.dateStr + 'T09:00',
                        endDate: info.dateStr + 'T10:00'
                    });
                },
                selectable: true,
                select: (selectInfo) => {
                    this.openCreateEventModal({
                        startDate: selectInfo.startStr,
                        endDate: selectInfo.endStr
                    });
                }
            };

            this.calendar = new FullCalendar.Calendar(calendarEl, calendarConfig);
            this.calendar.render();
        },

        async fetchEvents(start, end, successCallback, failureCallback) {
            try {
                const response = await axios.get('/api/schedule/events', {
                    params: {
                        start: start.toISOString(),
                        end: end.toISOString()
                    },
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.data.success) {
                    const events = response.data.data.map(event => ({
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end,
                        color: event.color,
                        extendedProps: event
                    }));
                    successCallback(events);
                } else {
                    failureCallback(this.isRTL ? 'فشل تحميل الأحداث' : 'Failed to load events');
                }
            } catch (error) {
                console.error('Error fetching events:', error);
                failureCallback(this.isRTL ? 'حدث خطأ أثناء تحميل الأحداث' : 'Error loading events');
            }
        },

        async loadMeetingRooms() {
            try {
                const response = await axios.get('/api/schedule/meeting-rooms', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });
                if (response.data.success) {
                    this.meetingRooms = response.data.data;
                }
            } catch (error) {
                console.error('Error loading meeting rooms:', error);
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

        async loadUpcomingEvents() {
            try {
                const response = await axios.get('/api/schedule/events', {
                    params: {
                        start: new Date().toISOString(),
                        status: 'scheduled'
                    },
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'Accept': 'application/json'
                    }
                });
                if (response.data.success) {
                    const events = response.data.data.slice(0, 5);
                    this.renderUpcomingEvents(events);
                }
            } catch (error) {
                console.error('Error loading upcoming events:', error);
            }
        },

        renderUpcomingEvents(events) {
            const container = document.getElementById('upcoming-events-list');
            if (!container) return;
            
            if (events.length === 0) {
                container.innerHTML = `<p class="text-muted text-center">${this.isRTL ? 'لا توجد أحداث قادمة' : 'No upcoming events'}</p>`;
                return;
            }
            
            const dateOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            container.innerHTML = events.map(event => {
                const date = new Date(event.start);
                const dateStr = date.toLocaleString(this.locale, dateOptions);
                return `
                    <div class="border-bottom pb-2 mb-2">
                        <strong>${event.title}</strong><br>
                        <small class="text-muted">${dateStr}</small>
                    </div>
                `;
            }).join('');
        },

        openCreateEventModal(data = {}) {
            const modalEl = document.getElementById('create-event-modal');
            if (!modalEl) return;
            
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('create-event-form');
            
            if (data.startDate) {
                const startInput = form.querySelector('[name="start_time"]');
                if (startInput) {
                    const date = new Date(data.startDate);
                    startInput.value = date.toISOString().slice(0, 16);
                }
            }
            if (data.endDate) {
                const endInput = form.querySelector('[name="end_time"]');
                if (endInput) {
                    const date = new Date(data.endDate);
                    endInput.value = date.toISOString().slice(0, 16);
                }
            }
            
            modal.show();
        },

        async createEvent(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            const data = {
                title: formData.get('title'),
                description: formData.get('description'),
                start_time: formData.get('start_time'),
                end_time: formData.get('end_time'),
                event_type: formData.get('event_type'),
                priority: formData.get('priority'),
                location: formData.get('location'),
                meeting_room_id: formData.get('meeting_room_id') || null,
                attendee_ids: Array.from(formData.getAll('attendee_ids[]')).map(id => parseInt(id)).filter(id => !isNaN(id)),
                is_recurring: formData.has('is_recurring'),
                recurring_pattern: formData.get('recurring_pattern') || null,
                recurring_end_date: formData.get('recurring_end_date') || null,
                color: formData.get('color')
            };

            try {
                const response = await axios.post('/api/schedule/events', data, {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (response.data.success) {
                    this.calendar.refetchEvents();
                    this.loadUpcomingEvents();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('create-event-modal'));
                    modal.hide();
                    form.reset();
                    alert(this.isRTL ? 'تم إنشاء الحدث بنجاح' : 'Event created successfully');
                } else {
                    alert(response.data.message || (this.isRTL ? 'فشل إنشاء الحدث' : 'Failed to create event'));
                }
            } catch (error) {
                console.error('Error creating event:', error);
                alert(error.response?.data?.message || (this.isRTL ? 'حدث خطأ أثناء إنشاء الحدث' : 'Error creating event'));
            }
        },

        showEventDetails(event) {
            this.selectedEvent = event;
            const modalEl = document.getElementById('event-details-modal');
            if (!modalEl) return;
            
            const modal = new bootstrap.Modal(modalEl);
            const props = event.extendedProps;
            
            modalEl.querySelector('[data-event-title]').textContent = event.title;
            modalEl.querySelector('[data-event-description]').textContent = props.description || (this.isRTL ? 'لا يوجد وصف' : 'No description');
            modalEl.querySelector('[data-event-location]').textContent = props.location || (this.isRTL ? 'لا يوجد مكان' : 'No location');
            
            const dateOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            modalEl.querySelector('[data-event-start]').textContent = event.start.toLocaleString(this.locale, dateOptions);
            modalEl.querySelector('[data-event-end]').textContent = event.end ? event.end.toLocaleString(this.locale, dateOptions) : '';
            
            modal.show();
        },

        openRoomBookingModal() {
            window.location.href = '/schedule/rooms/book';
        },

        refreshCalendar() {
            if (this.calendar) {
                this.calendar.refetchEvents();
                this.loadUpcomingEvents();
            }
        },

        toggleRecurringOptions(event) {
            const options = document.getElementById('recurring-options');
            if (options) {
                options.style.display = event.target.checked ? 'block' : 'none';
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
