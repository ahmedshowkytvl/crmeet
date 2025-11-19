/**
 * EET Life Page JavaScript
 * Handles interactions for events, shoutouts, and other dynamic content
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('EET Life page loaded');
    
    // Initialize components
    initShoutoutForm();
    initEventFilters();
    initBirthdayGreetings();
    initAnnouncementsTicker();
    initScrollAnimations();
    
    // Initialize management components
    initEventsManagement();
    initAnnouncementsManagement();
});

/**
 * Initialize shoutout form functionality
 */
function initShoutoutForm() {
    const form = document.getElementById('shoutout-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Show loading state
        submitBtn.textContent = 'Posting...';
        submitBtn.disabled = true;
        
        fetch('/eet-life/shoutouts', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: formData.get('message'),
                recipient_name: formData.get('recipient_name'),
                type: formData.get('type')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new shoutout to the list
                addShoutoutToList(data.shoutout);
                
                // Reset form
                form.reset();
                
                // Show success message
                showNotification('Shoutout posted successfully!', 'success');
            } else {
                showNotification('Error posting shoutout. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error posting shoutout. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

/**
 * Add new shoutout to the list
 */
function addShoutoutToList(shoutout) {
    const shoutoutsList = document.getElementById('shoutouts-list');
    if (!shoutoutsList) return;

    const shoutoutElement = createShoutoutElement(shoutout);
    
    // Add to top of list
    shoutoutsList.insertBefore(shoutoutElement, shoutoutsList.firstChild);
    
    // Add animation
    shoutoutElement.classList.add('fade-in');
    
    // Remove old shoutouts if list is too long
    const shoutoutItems = shoutoutsList.querySelectorAll('.shoutout-item');
    if (shoutoutItems.length > 10) {
        shoutoutItems[shoutoutItems.length - 1].remove();
    }
}

/**
 * Create shoutout element
 */
function createShoutoutElement(shoutout) {
    const div = document.createElement('div');
    div.className = 'shoutout-item';
    
    const avatarHtml = shoutout.user.profile_picture 
        ? `<img src="/storage/${shoutout.user.profile_picture}" alt="${shoutout.user.name}" class="img-fluid">`
        : `<div class="avatar-placeholder"><i class="fas fa-user"></i></div>`;
    
    const recipientHtml = shoutout.recipient_name 
        ? `<small class="text-muted">To: ${shoutout.recipient_name}</small>`
        : '';
    
    div.innerHTML = `
        <div class="shoutout-avatar">
            ${avatarHtml}
        </div>
        <div class="shoutout-content">
            <div class="shoutout-header">
                <strong>${shoutout.user.name}</strong>
                <span class="shoutout-type">${getTypeIcon(shoutout.type)}</span>
                <small class="text-muted">${shoutout.formatted_created_at}</small>
            </div>
            <p class="shoutout-message">${shoutout.message}</p>
            ${recipientHtml}
        </div>
    `;
    
    return div;
}

/**
 * Get type icon for shoutout
 */
function getTypeIcon(type) {
    const icons = {
        'birthday': '🎂',
        'achievement': '🏆',
        'thanks': '🙏',
        'general': '💬'
    };
    return icons[type] || '💬';
}

/**
 * Initialize event filters
 */
function initEventFilters() {
    // Add event filter buttons if needed
    const eventsContainer = document.getElementById('events-container');
    if (!eventsContainer) return;

    // Add click handlers for event buttons
    eventsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-primary')) {
            // Handle "View Details" click
            console.log('View details clicked');
            // You can implement modal or redirect here
        } else if (e.target.classList.contains('btn-outline-success')) {
            // Handle "Join Event" click
            console.log('Join event clicked');
            // You can implement join functionality here
        }
    });
}

/**
 * Initialize birthday greetings
 */
function initBirthdayGreetings() {
    const greetingBtns = document.querySelectorAll('.birthday-actions .btn');
    
    greetingBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const birthdayItem = this.closest('.birthday-item');
            const name = birthdayItem.querySelector('.birthday-name').textContent;
            
            // Show greeting modal or redirect to chat
            showGreetingModal(name);
        });
    });
}

/**
 * Show greeting modal
 */
function showGreetingModal(name) {
    // Simple alert for now - you can implement a proper modal
    const message = prompt(`Send a birthday greeting to ${name}:`, `Happy Birthday ${name}! 🎉`);
    
    if (message && message.trim()) {
        // Here you can send the greeting via chat or email
        console.log(`Sending greeting to ${name}: ${message}`);
        showNotification('Birthday greeting sent!', 'success');
    }
}

/**
 * Initialize announcements ticker
 */
function initAnnouncementsTicker() {
    const ticker = document.querySelector('.ticker-content');
    if (!ticker) return;

    // Pause ticker on hover
    ticker.addEventListener('mouseenter', function() {
        this.style.animationPlayState = 'paused';
    });

    ticker.addEventListener('mouseleave', function() {
        this.style.animationPlayState = 'running';
    });
}

/**
 * Initialize scroll animations
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observe cards for animation
    const cards = document.querySelectorAll('.card, .event-card, .highlight-card');
    cards.forEach(card => {
        observer.observe(card);
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

/**
 * Refresh shoutouts list
 */
function refreshShoutouts() {
    fetch('/eet-life/shoutouts')
        .then(response => response.json())
        .then(data => {
            const shoutoutsList = document.getElementById('shoutouts-list');
            if (!shoutoutsList) return;

            // Clear existing shoutouts
            shoutoutsList.innerHTML = '';

            // Add new shoutouts
            data.forEach(shoutout => {
                const shoutoutElement = createShoutoutElement(shoutout);
                shoutoutsList.appendChild(shoutoutElement);
            });
        })
        .catch(error => {
            console.error('Error refreshing shoutouts:', error);
        });
}

/**
 * Refresh events list
 */
function refreshEvents(type = 'upcoming') {
    fetch(`/eet-life/events?type=${type}`)
        .then(response => response.json())
        .then(data => {
            const eventsContainer = document.getElementById('events-container');
            if (!eventsContainer) return;

            // Update events container with new data
            // This is a simplified version - you might want to implement more sophisticated updating
            console.log('Events refreshed:', data);
        })
        .catch(error => {
            console.error('Error refreshing events:', error);
        });
}

/**
 * Initialize Events Management
 */
function initEventsManagement() {
    // Load events on page load
    loadEvents();
    
    // Initialize event form
    initEventForm();
    
    // Initialize edit event form
    initEditEventForm();
}

/**
 * Load events for management
 */
function loadEvents() {
    const container = document.getElementById('events-management-container');
    if (!container) return;

    fetch('/events')
        .then(response => response.json())
        .then(data => {
            if (data.data) {
                renderEventsGrid(data.data);
            } else {
                renderEventsGrid(data);
            }
        })
        .catch(error => {
            console.error('Error loading events:', error);
            container.innerHTML = '<div class="text-center py-4"><p class="text-danger">Error loading events</p></div>';
        });
}

/**
 * Render events grid
 */
function renderEventsGrid(events) {
    const container = document.getElementById('events-management-container');
    if (!container) return;

    if (events.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h5>No Events Found</h5>
                <p>Start by creating your first event!</p>
            </div>
        `;
        return;
    }

    const eventsHtml = events.map(event => `
        <div class="event-management-card">
            ${event.image_url ? `
                <div class="event-management-image">
                    <img src="/storage/${event.image_url}" alt="${event.title}">
                </div>
            ` : ''}
            <div class="event-management-content">
                <h6 class="event-management-title">
                    ${event.title}
                    ${event.is_featured ? '<span class="featured-badge">Featured</span>' : ''}
                </h6>
                <div class="event-management-meta">
                    <small><i class="fas fa-calendar me-1"></i> ${formatDate(event.date)}</small>
                    <small><i class="fas fa-clock me-1"></i> ${formatTime(event.date)}</small>
                    ${event.location ? `<small><i class="fas fa-map-marker-alt me-1"></i> ${event.location}</small>` : ''}
                    <small><i class="fas fa-user me-1"></i> ${event.organizer}</small>
                    <small><i class="fas fa-user-plus me-1"></i> ${event.creator ? event.creator.name : 'Unknown'}</small>
                </div>
                <div class="event-management-actions">
                    <button class="btn btn-sm btn-view" onclick="viewEvent(${event.id})">
                        <i class="fas fa-eye me-1"></i> View
                    </button>
                    <button class="btn btn-sm btn-edit" onclick="editEvent(${event.id})">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="deleteEvent(${event.id})">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                    <button class="btn btn-sm ${event.is_featured ? 'btn-warning' : 'btn-featured'}" onclick="toggleFeatured(${event.id})">
                        <i class="fas fa-star me-1"></i> ${event.is_featured ? 'Unfeature' : 'Feature'}
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = `<div class="events-grid">${eventsHtml}</div>`;
}

/**
 * Initialize event form
 */
function initEventForm() {
    const form = document.getElementById('addEventForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Creating...';
        submitBtn.disabled = true;
        
        fetch('/events', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Event created successfully!', 'success');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                loadEvents();
            } else {
                showNotification('Error creating event: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error creating event. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

/**
 * Initialize edit event form
 */
function initEditEventForm() {
    const form = document.getElementById('editEventForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const eventId = document.getElementById('edit_event_id').value;
        const formData = new FormData(form);
        formData.append('_method', 'PUT');
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        
        fetch(`/events/${eventId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Event updated successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editEventModal')).hide();
                loadEvents();
            } else {
                showNotification('Error updating event: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating event. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

/**
 * View event details
 */
function viewEvent(eventId) {
    fetch(`/events/${eventId}`)
        .then(response => response.json())
        .then(data => {
            // You can implement a view modal here
            console.log('Event details:', data);
            showNotification('Event details loaded', 'info');
        })
        .catch(error => {
            console.error('Error loading event:', error);
            showNotification('Error loading event details', 'error');
        });
}

/**
 * Edit event
 */
function editEvent(eventId) {
    fetch(`/events/${eventId}`)
        .then(response => response.json())
        .then(data => {
            // Populate edit form
            document.getElementById('edit_event_id').value = data.id;
            document.getElementById('edit_event_title').value = data.title;
            document.getElementById('edit_event_organizer').value = data.organizer;
            document.getElementById('edit_event_date').value = formatDateTimeForInput(data.date);
            document.getElementById('edit_event_location').value = data.location || '';
            document.getElementById('edit_event_description').value = data.description || '';
            document.getElementById('edit_event_status').value = data.status;
            document.getElementById('edit_event_featured').checked = data.is_featured;
            
            // Show current image if exists
            const imagePreview = document.getElementById('current_image_preview');
            if (data.image_url) {
                imagePreview.innerHTML = `
                    <small class="text-muted">Current image:</small><br>
                    <img src="/storage/${data.image_url}" class="image-preview" alt="Current image">
                `;
            } else {
                imagePreview.innerHTML = '';
            }
            
            // Show modal
            new bootstrap.Modal(document.getElementById('editEventModal')).show();
        })
        .catch(error => {
            console.error('Error loading event:', error);
            showNotification('Error loading event for editing', 'error');
        });
}

/**
 * Delete event
 */
function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        fetch(`/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Event deleted successfully!', 'success');
                loadEvents();
            } else {
                showNotification('Error deleting event: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting event. Please try again.', 'error');
        });
    }
}

/**
 * Toggle featured status
 */
function toggleFeatured(eventId) {
    fetch(`/events/${eventId}/toggle-featured`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Event featured status updated!', 'success');
            loadEvents();
        } else {
            showNotification('Error updating featured status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating featured status', 'error');
    });
}

/**
 * Initialize Announcements Management
 */
function initAnnouncementsManagement() {
    // Load announcements on page load
    loadAnnouncements();
    
    // Initialize announcement form
    initAnnouncementForm();
    
    // Load events for dropdown
    loadEventsForDropdown();
    
    // Load users for target selection
    loadUsersForTarget();
}

/**
 * Load announcements for management
 */
function loadAnnouncements() {
    const container = document.getElementById('announcements-management-container');
    if (!container) return;

    fetch('/announcements')
        .then(response => response.json())
        .then(data => {
            if (data.data) {
                renderAnnouncementsList(data.data);
            } else {
                renderAnnouncementsList(data);
            }
        })
        .catch(error => {
            console.error('Error loading announcements:', error);
            container.innerHTML = '<div class="text-center py-4"><p class="text-danger">Error loading announcements</p></div>';
        });
}

/**
 * Render announcements list
 */
function renderAnnouncementsList(announcements) {
    const container = document.getElementById('announcements-management-container');
    if (!container) return;

    if (announcements.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-bullhorn"></i>
                <h5>No Announcements Found</h5>
                <p>Start by creating your first announcement!</p>
            </div>
        `;
        return;
    }

    const announcementsHtml = announcements.map(announcement => `
        <div class="announcement-management-item">
            <div class="announcement-management-header">
                <h6 class="announcement-management-title">${announcement.title}</h6>
                <div class="announcement-management-actions">
                    <button class="btn btn-sm btn-view" onclick="viewAnnouncement(${announcement.id})">
                        <i class="fas fa-eye me-1"></i> View
                    </button>
                    <button class="btn btn-sm btn-edit" onclick="editAnnouncement(${announcement.id})">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="deleteAnnouncement(${announcement.id})">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="announcement-management-meta">
                <span><i class="fas fa-user me-1"></i> ${announcement.creator ? announcement.creator.name : 'Unknown'}</span>
                <span><i class="fas fa-clock me-1"></i> ${announcement.formatted_created_at}</span>
                <span class="announcement-target-badge">${announcement.target_audience}</span>
                ${announcement.event ? `<a href="#" class="announcement-event-link">📅 ${announcement.event.title}</a>` : ''}
            </div>
            <div class="announcement-management-message">${announcement.message_preview}</div>
        </div>
    `).join('');

    container.innerHTML = `<div class="announcements-list">${announcementsHtml}</div>`;
}

/**
 * Initialize announcement form
 */
function initAnnouncementForm() {
    const form = document.getElementById('addAnnouncementForm');
    if (!form) return;

    // Handle target type change
    const targetTypeSelect = document.getElementById('announcement_target_type');
    const targetUsersSection = document.getElementById('target_users_section');
    
    targetTypeSelect.addEventListener('change', function() {
        if (this.value === 'selected') {
            targetUsersSection.style.display = 'block';
        } else {
            targetUsersSection.style.display = 'none';
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = 'Creating...';
        submitBtn.disabled = true;
        
        fetch('/announcements', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Announcement created successfully!', 'success');
                form.reset();
                targetUsersSection.style.display = 'none';
                bootstrap.Modal.getInstance(document.getElementById('addAnnouncementModal')).hide();
                loadAnnouncements();
            } else {
                showNotification('Error creating announcement: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error creating announcement. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

/**
 * Load events for dropdown
 */
function loadEventsForDropdown() {
    fetch('/events-dropdown')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('announcement_event');
            if (select) {
                select.innerHTML = '<option value="">No Event</option>' + 
                    data.map(event => `<option value="${event.id}">${event.title} - ${formatDate(event.date)}</option>`).join('');
            }
        })
        .catch(error => {
            console.error('Error loading events for dropdown:', error);
        });
}

/**
 * Load users for target selection
 */
function loadUsersForTarget() {
    fetch('/announcements-users')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('announcement_target_users');
            if (select) {
                select.innerHTML = data.map(user => `<option value="${user.id}">${user.name} (${user.email})</option>`).join('');
            }
        })
        .catch(error => {
            console.error('Error loading users for target:', error);
        });
}

/**
 * View announcement details
 */
function viewAnnouncement(announcementId) {
    fetch(`/announcements/${announcementId}`)
        .then(response => response.json())
        .then(data => {
            // You can implement a view modal here
            console.log('Announcement details:', data);
            showNotification('Announcement details loaded', 'info');
        })
        .catch(error => {
            console.error('Error loading announcement:', error);
            showNotification('Error loading announcement details', 'error');
        });
}

/**
 * Edit announcement
 */
function editAnnouncement(announcementId) {
    // You can implement edit functionality here
    console.log('Edit announcement:', announcementId);
    showNotification('Edit functionality coming soon', 'info');
}

/**
 * Delete announcement
 */
function deleteAnnouncement(announcementId) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        fetch(`/announcements/${announcementId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Announcement deleted successfully!', 'success');
                loadAnnouncements();
            } else {
                showNotification('Error deleting announcement: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting announcement. Please try again.', 'error');
        });
    }
}

/**
 * Utility functions
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function formatDateTimeForInput(dateString) {
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Export functions for global access
window.EetLife = {
    refreshShoutouts,
    refreshEvents,
    showNotification,
    loadEvents,
    loadAnnouncements
};
