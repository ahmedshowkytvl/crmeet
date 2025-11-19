@extends('layouts.app')

@section('title', 'EET Life - Company Events & Celebrations')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-heart text-primary me-3"></i>
                    EET Life
                </h1>
                <p class="page-subtitle">Company Events, Celebrations & Team Highlights</p>
            </div>
        </div>
    </div>

    <!-- Announcements Ticker -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="announcements-ticker">
                <div class="ticker-content">
                    <i class="fas fa-bullhorn me-2"></i>
                    <span class="ticker-text">
                        @foreach($announcements as $announcement)
                            {{ $announcement }} • 
                        @endforeach
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Sections -->
    @can('events.manage')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-plus text-primary me-2"></i>
                        Events Management
                    </h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-1"></i> Add Event
                    </button>
                </div>
                <div class="card-body">
                    <div id="events-management-container">
                        <!-- Events will be loaded here via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading events...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('announcements.manage')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullhorn text-info me-2"></i>
                        Announcements Management
                    </h5>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                        <i class="fas fa-plus me-1"></i> Add Announcement
                    </button>
                </div>
                <div class="card-body">
                    <div id="announcements-management-container">
                        <!-- Announcements will be loaded here via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading announcements...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Upcoming Events Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        Upcoming & Recent Events
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row" id="events-container">
                        @forelse($upcomingEvents as $event)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="event-card">
                                    @if($event->image_url)
                                        <div class="event-image">
                                            <img src="{{ asset('storage/' . $event->image_url) }}" alt="{{ $event->title }}" class="img-fluid">
                                        </div>
                                    @endif
                                    <div class="event-content">
                                        <h6 class="event-title">{{ $event->title }}</h6>
                                        <div class="event-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $event->formatted_date }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $event->formatted_time }}
                                            </small>
                                            @if($event->location)
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $event->location }}
                                                </small>
                                            @endif
                                            <small class="text-muted d-block">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $event->organizer }}
                                            </small>
                                        </div>
                                        @if($event->description)
                                            <p class="event-description">{{ Str::limit($event->description, 100) }}</p>
                                        @endif
                                        <div class="event-actions">
                                            <button class="btn btn-sm btn-primary">View Details</button>
                                            @if($event->is_upcoming)
                                                <button class="btn btn-sm btn-outline-success">Join Event</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No upcoming events scheduled</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Employee Highlights Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Employee Highlights
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($employeeHighlights as $employee)
                            <div class="col-md-4 mb-3">
                                <div class="highlight-card">
                                    <div class="highlight-avatar">
                                        @if($employee->profile_picture)
                                            <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="{{ $employee->name }}" class="img-fluid">
                                        @else
                                            <div class="avatar-placeholder">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="highlight-content">
                                        <h6 class="highlight-name">{{ $employee->name }}</h6>
                                        <p class="highlight-role">{{ $employee->department->name ?? 'Employee' }}</p>
                                        <p class="highlight-achievement">Employee of the Month</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-4">
                                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No featured employees at the moment</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Birthdays This Month -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-birthday-cake text-danger me-2"></i>
                        Birthdays This Month
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($birthdaysThisMonth as $employee)
                        <div class="birthday-item">
                            <div class="birthday-avatar">
                                @if($employee->profile_picture)
                                    <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="{{ $employee->name }}" class="img-fluid">
                                @else
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="birthday-info">
                                <h6 class="birthday-name">{{ $employee->name }}</h6>
                                <p class="birthday-date">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ \Carbon\Carbon::parse($employee->birth_date)->format('M d') }}
                                </p>
                                <p class="birthday-department">{{ $employee->department->name ?? 'Employee' }}</p>
                            </div>
                            <div class="birthday-actions">
                                <button class="btn btn-sm btn-outline-primary">Send Greeting</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-birthday-cake fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No birthdays this month</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Shoutouts Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-comments text-info me-2"></i>
                        Team Shoutouts
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Shoutout Form -->
                    <div class="shoutout-form mb-3">
                        <form id="shoutout-form">
                            @csrf
                            <div class="mb-3">
                                <textarea class="form-control" name="message" placeholder="Write a shoutout..." rows="3" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <input type="text" class="form-control" name="recipient_name" placeholder="To (optional)">
                                </div>
                                <div class="col-6">
                                    <select class="form-control" name="type">
                                        <option value="general">General</option>
                                        <option value="birthday">Birthday</option>
                                        <option value="achievement">Achievement</option>
                                        <option value="thanks">Thanks</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">Post Shoutout</button>
                        </form>
                    </div>

                    <!-- Shoutouts List -->
                    <div class="shoutouts-list" id="shoutouts-list">
                        @forelse($recentShoutouts as $shoutout)
                            <div class="shoutout-item">
                                <div class="shoutout-avatar">
                                    @if($shoutout->user->profile_picture)
                                        <img src="{{ asset('storage/' . $shoutout->user->profile_picture) }}" alt="{{ $shoutout->user->name }}" class="img-fluid">
                                    @else
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="shoutout-content">
                                    <div class="shoutout-header">
                                        <strong>{{ $shoutout->user->name }}</strong>
                                        <span class="shoutout-type">{{ $shoutout->type_icon }}</span>
                                        <small class="text-muted">{{ $shoutout->formatted_created_at }}</small>
                                    </div>
                                    <p class="shoutout-message">{{ $shoutout->message }}</p>
                                    @if($shoutout->recipient_name)
                                        <small class="text-muted">To: {{ $shoutout->recipient_name }}</small>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-3">
                                <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No shoutouts yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
@can('events.manage')
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEventForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="event_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_organizer" class="form-label">Organizer *</label>
                                <input type="text" class="form-control" id="event_organizer" name="organizer" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="event_date" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="event_location" name="location">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="event_description" class="form-label">Description</label>
                        <textarea class="form-control" id="event_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_status" class="form-label">Status</label>
                                <select class="form-control" id="event_status" name="status">
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="event_image" class="form-label">Event Image</label>
                                <input type="file" class="form-control" id="event_image" name="image" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="event_featured" name="is_featured" value="1">
                            <label class="form-check-label" for="event_featured">
                                Featured Event
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Add Announcement Modal -->
@can('announcements.manage')
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnnouncementModalLabel">Add New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAnnouncementForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="announcement_title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="announcement_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="announcement_message" class="form-label">Message *</label>
                        <textarea class="form-control" id="announcement_message" name="message" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="announcement_target_type" class="form-label">Target Audience *</label>
                                <select class="form-control" id="announcement_target_type" name="target_type" required>
                                    <option value="all">All Employees</option>
                                    <option value="selected">Selected Employees</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="announcement_event" class="form-label">Attached Event</label>
                                <select class="form-control" id="announcement_event" name="attached_event_id">
                                    <option value="">No Event</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="target_users_section" style="display: none;">
                        <label for="announcement_target_users" class="form-label">Select Employees</label>
                        <select class="form-control" id="announcement_target_users" name="target_ids[]" multiple>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Create Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Edit Event Modal -->
@can('events.manage')
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEventForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_event_id" name="event_id">
                <div class="modal-body">
                    <!-- Same fields as add event form -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_event_title" class="form-label">Event Title *</label>
                                <input type="text" class="form-control" id="edit_event_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_event_organizer" class="form-label">Organizer *</label>
                                <input type="text" class="form-control" id="edit_event_organizer" name="organizer" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_event_date" class="form-label">Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="edit_event_date" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_event_location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="edit_event_location" name="location">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_event_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_event_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_event_status" class="form-label">Status</label>
                                <select class="form-control" id="edit_event_status" name="status">
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_event_image" class="form-label">Event Image</label>
                                <input type="file" class="form-control" id="edit_event_image" name="image" accept="image/*">
                                <div id="current_image_preview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_event_featured" name="is_featured" value="1">
                            <label class="form-check-label" for="edit_event_featured">
                                Featured Event
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/eet-life.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/eet-life.js') }}"></script>
@endpush
