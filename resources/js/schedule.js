/**
 * Schedule Calendar System
 */

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import axios from 'axios';

window.ScheduleCalendar = class {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.options = options;
        this.calendar = null;
        this.apiBase = options.apiBase || '/api/schedule';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        this.init();
    }

    init() {
        const calendarEl = document.getElementById(this.elementId);
        if (!calendarEl) {
            console.error(`Calendar element #${this.elementId} not found`);
            return;
        }

        // Wait for FullCalendar to be loaded
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar is not loaded. Please include FullCalendar from CDN.');
            return;
        }

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: this.options.initialView || 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'ar',
            direction: 'rtl',
            firstDay: 6, // Saturday
            height: 'auto',
            allDaySlot: true,
            slotMinTime: '08:00:00',
            slotMaxTime: '20:00:00',
            events: (info, successCallback, failureCallback) => {
                this.fetchEvents(info.start, info.end, successCallback, failureCallback);
            },
            eventClick: (info) => {
                this.handleEventClick(info);
            },
            dateClick: (info) => {
                this.handleDateClick(info);
            },
            eventDrop: (info) => {
                this.handleEventDrop(info);
            },
            eventResize: (info) => {
                this.handleEventResize(info);
            },
            selectable: true,
            select: (info) => {
                this.handleSelect(info);
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            },
            ...this.options
        });

        this.calendar.render();
    }

    async fetchEvents(start, end, successCallback, failureCallback) {
        try {
            const response = await axios.get(`${this.apiBase}/events`, {
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
                    extendedProps: {
                        description: event.description,
                        location: event.location,
                        event_type: event.event_type,
                        status: event.status,
                        priority: event.priority,
                        meeting_room: event.meeting_room,
                        owner: event.owner,
                        attendees: event.attendees,
                        can_edit: event.can_edit,
                        can_delete: event.can_delete
                    }
                }));
                successCallback(events);
            } else {
                failureCallback(response.data.message || 'فشل تحميل الأحداث');
            }
        } catch (error) {
            console.error('Error fetching events:', error);
            failureCallback(error.message || 'حدث خطأ أثناء تحميل الأحداث');
        }
    }

    handleEventClick(info) {
        if (this.options.onEventClick) {
            this.options.onEventClick(info);
        } else {
            this.showEventDetails(info.event);
        }
    }

    handleDateClick(info) {
        if (this.options.onDateClick) {
            this.options.onDateClick(info);
        } else {
            this.createEvent(info.dateStr, info.allDay);
        }
    }

    handleSelect(selectInfo) {
        if (this.options.onSelect) {
            this.options.onSelect(selectInfo);
        } else {
            this.createEvent(
                selectInfo.startStr,
                selectInfo.allDay,
                selectInfo.endStr
            );
        }
    }

    async handleEventDrop(info) {
        try {
            await axios.put(`${this.apiBase}/events/${info.event.id}`, {
                start_time: info.event.start.toISOString(),
                end_time: info.event.end ? info.event.end.toISOString() : info.event.start.toISOString()
            }, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            this.showNotification('تم تحديث الحدث بنجاح', 'success');
        } catch (error) {
            info.revert();
            this.showNotification('فشل تحديث الحدث', 'error');
            console.error('Error updating event:', error);
        }
    }

    async handleEventResize(info) {
        try {
            await axios.put(`${this.apiBase}/events/${info.event.id}`, {
                start_time: info.event.start.toISOString(),
                end_time: info.event.end.toISOString()
            }, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            this.showNotification('تم تحديث الحدث بنجاح', 'success');
        } catch (error) {
            info.revert();
            this.showNotification('فشل تحديث الحدث', 'error');
            console.error('Error resizing event:', error);
        }
    }

    showEventDetails(event) {
        const props = event.extendedProps;
        const modal = document.getElementById('event-details-modal');
        if (modal) {
            // Populate modal with event details
            modal.querySelector('[data-event-title]').textContent = event.title;
            modal.querySelector('[data-event-description]').textContent = props.description || 'لا يوجد وصف';
            modal.querySelector('[data-event-location]').textContent = props.location || 'لا يوجد مكان';
            modal.querySelector('[data-event-start]').textContent = event.start.toLocaleString('ar');
            modal.querySelector('[data-event-end]').textContent = event.end ? event.end.toLocaleString('ar') : '';
            
            // Show edit/delete buttons based on permissions
            const editBtn = modal.querySelector('[data-event-edit]');
            const deleteBtn = modal.querySelector('[data-event-delete]');
            if (editBtn) editBtn.style.display = props.can_edit ? 'block' : 'none';
            if (deleteBtn) deleteBtn.style.display = props.can_delete ? 'block' : 'none';
            
            // Show modal
            modal.classList.remove('hidden');
        }
    }

    createEvent(startDate, allDay = false, endDate = null) {
        if (this.options.onCreateEvent) {
            this.options.onCreateEvent({ startDate, allDay, endDate });
        } else {
            // Default: open create event form
            const modal = document.getElementById('create-event-modal');
            if (modal) {
                modal.querySelector('[name="start_time"]').value = startDate;
                if (endDate) {
                    modal.querySelector('[name="end_time"]').value = endDate;
                }
                modal.classList.remove('hidden');
            }
        }
    }

    async createEventFromForm(formData) {
        try {
            const response = await axios.post(`${this.apiBase}/events`, formData, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (response.data.success) {
                this.calendar.refetchEvents();
                this.showNotification('تم إنشاء الحدث بنجاح', 'success');
                return true;
            } else {
                this.showNotification(response.data.message || 'فشل إنشاء الحدث', 'error');
                return false;
            }
        } catch (error) {
            console.error('Error creating event:', error);
            this.showNotification(error.response?.data?.message || 'حدث خطأ أثناء إنشاء الحدث', 'error');
            return false;
        }
    }

    async updateEvent(eventId, formData) {
        try {
            const response = await axios.put(`${this.apiBase}/events/${eventId}`, formData, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (response.data.success) {
                this.calendar.refetchEvents();
                this.showNotification('تم تحديث الحدث بنجاح', 'success');
                return true;
            } else {
                this.showNotification(response.data.message || 'فشل تحديث الحدث', 'error');
                return false;
            }
        } catch (error) {
            console.error('Error updating event:', error);
            this.showNotification(error.response?.data?.message || 'حدث خطأ أثناء تحديث الحدث', 'error');
            return false;
        }
    }

    async deleteEvent(eventId) {
        if (!confirm('هل أنت متأكد من حذف هذا الحدث؟')) {
            return false;
        }

        try {
            const response = await axios.delete(`${this.apiBase}/events/${eventId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.data.success) {
                this.calendar.refetchEvents();
                this.showNotification('تم حذف الحدث بنجاح', 'success');
                return true;
            } else {
                this.showNotification(response.data.message || 'فشل حذف الحدث', 'error');
                return false;
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            this.showNotification(error.response?.data?.message || 'حدث خطأ أثناء حذف الحدث', 'error');
            return false;
        }
    }

    getAuthToken() {
        // Get token from meta tag or localStorage
        const metaToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        if (metaToken) return metaToken;
        
        const storedToken = localStorage.getItem('auth_token');
        if (storedToken) return storedToken;
        
        return '';
    }

    showNotification(message, type = 'info') {
        // Simple notification - can be enhanced with a toast library
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' :
            type === 'error' ? 'bg-red-500' :
            'bg-blue-500'
        } text-white`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    refetch() {
        if (this.calendar) {
            this.calendar.refetchEvents();
        }
    }

    destroy() {
        if (this.calendar) {
            this.calendar.destroy();
        }
    }
};

// Meeting Room Booking Helper
window.MeetingRoomBooker = class {
    constructor(apiBase = '/api/schedule') {
        this.apiBase = apiBase;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    async getAvailableRooms(startTime, endTime, capacity = null) {
        try {
            const params = {
                start_time: startTime,
                end_time: endTime
            };
            if (capacity) params.capacity = capacity;

            const response = await axios.get(`${this.apiBase}/meeting-rooms/available`, {
                params,
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching available rooms:', error);
            return [];
        }
    }

    async getAvailableTimeSlots(roomId, date, duration = 60) {
        try {
            const response = await axios.get(`${this.apiBase}/meeting-rooms/${roomId}/available-time-slots`, {
                params: {
                    date: date,
                    duration: duration
                },
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`,
                    'Accept': 'application/json'
                }
            });

            return response.data.success ? response.data.data : [];
        } catch (error) {
            console.error('Error fetching time slots:', error);
            return [];
        }
    }

    getAuthToken() {
        const metaToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
        if (metaToken) return metaToken;
        
        const storedToken = localStorage.getItem('auth_token');
        if (storedToken) return storedToken;
        
        return '';
    }
};

export default window.ScheduleCalendar;

