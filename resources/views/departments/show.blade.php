@extends('layouts.app')

@php
    // Load translations directly to ensure new keys are available
    $ticketTranslations = [
        'total' => trans('messages.total', [], app()->getLocale()),
        'closed' => trans('messages.closed', [], app()->getLocale()),
        'open' => trans('messages.open', [], app()->getLocale()),
        'pending' => trans('messages.pending', [], app()->getLocale()),
        'in_progress' => trans('messages.in_progress', [], app()->getLocale()),
        'ticket_number' => trans('messages.ticket_number', [], app()->getLocale()),
        'subject' => trans('messages.subject', [], app()->getLocale()),
        'status' => trans('messages.status', [], app()->getLocale()),
        'handler' => trans('messages.handler', [], app()->getLocale()),
        'created_date' => trans('messages.created_date', [], app()->getLocale()),
        'closed_date' => trans('messages.closed_date', [], app()->getLocale()),
        'response_time' => trans('messages.response_time', [], app()->getLocale()),
        'conversations_count' => trans('messages.conversations_count', [], app()->getLocale()),
        'actions' => trans('messages.actions', [], app()->getLocale()),
        'search_ticket_number' => trans('messages.search_ticket_number', [], app()->getLocale()),
        'search' => trans('messages.search', [], app()->getLocale()),
        'clear' => trans('messages.clear', [], app()->getLocale()),
        'search_ticket_hint' => trans('messages.search_ticket_hint', [], app()->getLocale()),
        'results' => trans('messages.results', [], app()->getLocale()),
        'view_details' => trans('messages.view_details', [], app()->getLocale()),
        'conversation' => trans('messages.conversation', [], app()->getLocale()),
        'conversations' => trans('messages.conversations', [], app()->getLocale()),
    ];
    // Fallback to direct file read if translation not found
    foreach ($ticketTranslations as $key => $value) {
        if ($value === "messages.{$key}" || empty($value)) {
            $locale = app()->getLocale();
            $file = base_path("lang/{$locale}/messages.php");
            if (file_exists($file)) {
                $messages = require $file;
                $ticketTranslations[$key] = $messages[$key] ?? $key;
            }
        }
    }
@endphp

@section('title', __('messages.department_details') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i>{{ __('messages.department_details') }}</h2>
    <div>
        <a href="{{ route('departments.edit', $department) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}
        </a>
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
        </a>
    </div>
</div>

<div class="row">
    <!-- Department Information -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('messages.department_details') }}</h5>
            </div>
            <div class="card-body">
                <h4>{{ $department->name }}</h4>
                <p class="text-muted">{{ $department->description ?? __('messages.no_description') }}</p>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('messages.department_manager') }}:</strong>
                        <p>{{ $department->manager->name ?? __('messages.not_specified') }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('messages.employee_count') }}:</strong>
                        <p>{{ $department->users->count() }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('messages.created_at') }}:</strong>
                        <p>{{ $department->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('messages.updated_at') }}:</strong>
                        <p>{{ $department->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Stats -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>{{ __('messages.department_stats') }}</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-users fa-3x text-primary"></i>
                </div>
                <h3 class="text-center">{{ $department->users->count() }}</h3>
                <p class="text-center text-muted">{{ __('messages.employee_count') }}</p>
                
                <hr>
                
                <div class="mb-2">
                    <strong>{{ __('messages.tasks') }}:</strong>
                    <span class="badge bg-info">{{ $department->tasks->count() }}</span>
                </div>
                
                <div class="mb-2">
                    <strong>{{ __('messages.requests') }}:</strong>
                    <span class="badge bg-warning">{{ $department->users->sum(function($user) { return $user->employeeRequests->count(); }) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Department Employees -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ __('messages.department_employees') }}</h5>
                <a href="{{ route('users.create', ['department_id' => $department->id, 'manager_id' => $department->manager_id]) }}" class="btn btn-sm btn-primary">{{ __('messages.add_user') }}</a>
            </div>
            <div class="card-body">
                @if($department->users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.name') }}</th>
                                    <th>{{ __('messages.email') }}</th>
                                    <th>{{ __('messages.role') }}</th>
                                    <th>{{ __('messages.work_phone') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($department->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($user->profile_picture)
                                                    <img src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}" class="user-avatar me-2" style="width: 30px; height: 30px;" onerror="this.style.display='none';">
                                                @endif
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->role && $user->role->slug == 'admin' ? 'danger' : ($user->role && $user->role->slug == 'manager' ? 'warning' : 'success') }}">
                                                {{ $user->role ? $user->role->name_ar : __('messages.not_specified') }}
                                            </span>
                                        </td>
                                        <td>{{ $user->phone_work ?? __('messages.not_specified') }}</td>
                                        <td>
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">{{ __('messages.no_departments_found') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>{{ __('messages.actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('departments.organizational-chart', $department) }}" class="btn btn-info w-100">
                            <i class="fas fa-sitemap me-2"></i>{{ __('messages.organizational_chart') }}
                        </a>
                    </div>
                    @if(isset($zohoTickets) && $zohoTickets->count() > 0)
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('departments.zoho-tickets', $department) }}" class="btn btn-success w-100">
                            <i class="fas fa-ticket-alt me-2"></i>تذاكر Zoho
                        </a>
                    </div>
                    @endif
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('departments.edit', $department) }}" class="btn btn-warning w-100">
                            <i class="fas fa-edit me-2"></i>{{ __('messages.edit_department') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('users.create', ['department_id' => $department->id, 'manager_id' => $department->manager_id]) }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>{{ __('messages.add_user') }}
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete_department') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>{{ __('messages.delete_department') }}
                            </button>
                        </form>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Zoho Tickets Section - Only show if there are tickets -->
@if(isset($zohoTickets) && $zohoTickets->count() > 0)
<div class="card" id="Zoho-department-tickets-section">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-ticket-alt me-2"></i>
            {{ __('messages.zoho_tickets') }} - {{ __('messages.department') }}
        </h6>
        <button type="button" id="refreshClosedByBtn" class="btn btn-light btn-sm" onclick="refreshClosedByData()">
            <i class="fas fa-sync-alt me-2"></i>{{ __('messages.updating') }}
        </button>
    </div>
    <div class="card-body">
        <!-- Info Alert: Data from cache only -->
        <div class="alert alert-info d-flex align-items-center mb-3">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <strong>{{ __('messages.cache_data_note') }}</strong> (<code>{{ __('messages.cache_table_name') }}</code> table)
                <br>
                <small>{{ __('messages.fetch_latest_from_zoho') }} <a href="{{ route('zoho.bulk-sync.index') }}" class="alert-link">Zoho Bulk Sync</a></small>
            </div>
        </div>
            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">{{ $ticketTranslations['total'] }}</h6>
                        <h4 class="text-primary mb-0">{{ $ticketStats['total_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">{{ $ticketTranslations['closed'] }}</h6>
                        <h4 class="text-success mb-0">{{ $ticketStats['closed_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">{{ $ticketTranslations['open'] }}</h6>
                        <h4 class="text-warning mb-0">{{ $ticketStats['open_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">{{ $ticketTranslations['pending'] }}</h6>
                        <h4 class="text-info mb-0">{{ $ticketStats['pending_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">{{ $ticketTranslations['in_progress'] }}</h6>
                        <h4 class="text-secondary mb-0">{{ $ticketStats['in_progress_tickets'] }}</h4>
                    </div>
                </div>
            </div>

            <!-- Search Box -->
            <div class="row mb-3">
                <div class="col-md-6 offset-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control form-control-lg" id="ticketSearchInput" 
                               placeholder="{{ $ticketTranslations['search_ticket_number'] }}" 
                               onkeyup="searchTicket()"
                               onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchTicket(); }">
                        <button class="btn btn-primary" type="button" onclick="searchTicket()">
                            <i class="fas fa-search me-2"></i>{{ $ticketTranslations['search'] }}
                        </button>
                        <button class="btn btn-secondary" type="button" onclick="resetSearch()">
                            <i class="fas fa-times me-2"></i>{{ $ticketTranslations['clear'] }}
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ $ticketTranslations['search_ticket_hint'] }}
                    </small>
                    <div class="text-center mt-2" id="searchResultsCount" style="display: none;">
                        <span class="badge bg-primary">{{ $ticketTranslations['results'] }}: <span id="resultsCount">0</span></span>
                    </div>
                </div>
            </div>

            <!-- جدول التذاكر -->
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="ticketsTable">
                    <thead class="table-light">
                        <tr>
                            <th>{{ $ticketTranslations['ticket_number'] }}</th>
                            <th>Department ID</th>
                            <th>{{ $ticketTranslations['subject'] }}</th>
                            <th>{{ $ticketTranslations['status'] }}</th>
                            <th>{{ $ticketTranslations['handler'] }}</th>
                            <th>{{ $ticketTranslations['created_date'] }}</th>
                            <th>{{ $ticketTranslations['closed_date'] }}</th>
                            <th>{{ $ticketTranslations['response_time'] }}</th>
                            <th>{{ $ticketTranslations['conversations_count'] }}</th>
                            <th>{{ $ticketTranslations['actions'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zohoTickets as $ticket)
                        <tr>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $ticket->ticket_number }}</code>
                            </td>
                            <td>
                                @php
                                    // Get departmentId from raw_data (Zoho Department ID)
                                    $zohoDepartmentId = null;
                                    if ($ticket->raw_data && is_array($ticket->raw_data) && isset($ticket->raw_data['departmentId'])) {
                                        $zohoDepartmentId = $ticket->raw_data['departmentId'];
                                    } else {
                                        $zohoDepartmentId = $ticket->department_id ?? $department->id ?? 'N/A';
                                    }
                                @endphp
                                <span class="badge bg-secondary" title="Zoho Department ID">{{ $zohoDepartmentId }}</span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $ticket->subject }}">
                                    {{ $ticket->subject }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'Open' => 'warning',
                                        'Closed' => 'success',
                                        'Pending' => 'info',
                                        'In Progress' => 'secondary',
                                        'Resolved' => 'primary'
                                    ];
                                    $statusColor = $statusColors[$ticket->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $handler = null;
                                    
                                    // First priority: Check closed_by_name column (if it has a value, don't change it)
                                    if ($ticket->closed_by_name && !empty($ticket->closed_by_name) && $ticket->closed_by_name !== 'غير محدد') {
                                        $handler = $ticket->closed_by_name;
                                    }
                                    // Second priority: Check raw_data for cf_closed_by
                                    elseif ($ticket->raw_data && isset($ticket->raw_data['cf']['cf_closed_by']) && !empty($ticket->raw_data['cf']['cf_closed_by'])) {
                                        $handler = $ticket->raw_data['cf']['cf_closed_by'];
                                    }
                                    // Third priority: Check raw_data for customFields['Closed By']
                                    elseif ($ticket->raw_data && isset($ticket->raw_data['customFields']['Closed By']) && !empty($ticket->raw_data['customFields']['Closed By'])) {
                                        $handler = $ticket->raw_data['customFields']['Closed By'];
                                    }
                                    // Fourth priority: Check if user exists
                                    elseif ($ticket->user) {
                                        $handler = $ticket->user->name;
                                    }
                                    
                                    // Format the handler display
                                    if ($handler) {
                                        if ($handler === 'Auto Close') {
                                            echo '<span class="badge bg-warning">Auto Close</span>';
                                        } elseif ($handler === 'Unknown Agent' || $handler === 'System') {
                                            echo '<span class="text-muted">' . trans('messages.not_specified') . '</span>';
                                        } else {
                                            echo '<span class="badge bg-info">' . htmlspecialchars($handler) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">' . trans('messages.not_specified') . '</span>';
                                    }
                                @endphp
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $ticket->created_at_zoho ? $ticket->created_at_zoho->format('Y-m-d H:i') : __('messages.not_specified') }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $ticket->closed_at_zoho ? $ticket->closed_at_zoho->format('Y-m-d H:i') : '-' }}
                                </small>
                            </td>
                            <td>
                                @if($ticket->response_time_minutes)
                                    <small class="text-success">
                                        {{ round($ticket->response_time_minutes / 60, 1) }} {{ __('messages.hours') }}
                                    </small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $ticket->thread_count }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            onclick="viewTicketDetails('{{ $ticket->zoho_ticket_id }}')"
                                            title="{{ $ticketTranslations['view_details'] }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($ticket->thread_count > 0)
                                        <span class="badge bg-info ms-1" title="{{ $ticket->thread_count }} {{ $ticket->thread_count == 1 ? $ticketTranslations['conversation'] : $ticketTranslations['conversations'] }}">
                                            <i class="fas fa-comments"></i> {{ $ticket->thread_count }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ trans('messages.showing_from_to_of_tickets', ['from' => $zohoTickets->firstItem(), 'to' => $zohoTickets->lastItem(), 'total' => $zohoTickets->total()]) }}
                    </small>
                </div>
                <div>
                    {{ $zohoTickets->links('pagination::bootstrap-5') }}
                </div>
            </div>
    </div>
</div>
@endif

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketDetailsModalLabel">{{ __('messages.ticket_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">{{ __('messages.loading_ticket_details') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store all tickets data for search (all {{ $allTicketsForSearch->count() }} tickets)
const allTicketsData = @json($allTicketsForSearch);

// Function to view ticket details
function viewTicketDetails(ticketId) {
    // Show modal and load ticket details
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    modal.show();
    
    // Load ticket details via AJAX
    loadTicketDetails(ticketId);
}

// Function to load ticket details from cache
function loadTicketDetails(ticketId) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    
    // Show loading state
    const loadingText = '{{ __('messages.loading_ticket_details') }}';
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">${loadingText}</p>
        </div>
    `;
    
    // استخدام الكاش فقط - لا استدعاء لـ Zoho API
    fetch(`/api/zoho/ticket-cache/${ticketId}`)
        .then(response => response.json())
        .then(cacheData => {
            if (cacheData.success) {
                displayTicketDetails(cacheData.data);
            } else {
                const notFoundText = '{{ __('messages.ticket_not_found_in_cache') }}';
                const fetchText = '{{ __('messages.fetch_latest_from_zoho') }}';
                contentDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        ${notFoundText}
                        <br><small>${fetchText} <a href="{{ route('zoho.bulk-sync.index') }}" class="alert-link">Zoho Bulk Sync</a></small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading ticket from cache:', error);
            const errorText = '{{ __('messages.error_loading_ticket') }}';
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${errorText}
                </div>
            `;
        });
}

// Function to display full ticket details from Zoho API
function displayTicketFullDetails(fullData) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    const ticket = fullData.ticket || {};
    const threads = fullData.threads || [];
    
    // Format dates
    const createdDate = ticket.createdTime ? new Date(ticket.createdTime).toLocaleString('ar-EG') : 'غير محدد';
    const closedDate = ticket.closedTime ? new Date(ticket.closedTime).toLocaleString('ar-EG') : 'N/A';
    
    // Status badge color
    const statusColors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary',
        'Resolved': 'primary'
    };
    const statusColor = statusColors[ticket.status] || 'secondary';
    
    // Build custom fields HTML
    let customFieldsHTML = '';
    if (ticket.customFields && Object.keys(ticket.customFields).length > 0) {
        customFieldsHTML = '<h6>الحقول المخصصة (Custom Fields):</h6><div class="table-responsive"><table class="table table-sm">';
        for (const [key, value] of Object.entries(ticket.customFields)) {
            if (value) {
                customFieldsHTML += `<tr><td><strong>${key}:</strong></td><td>${value}</td></tr>`;
            }
        }
        customFieldsHTML += '</table></div>';
    }
    
    // Build cf fields HTML
    let cfFieldsHTML = '';
    if (ticket.cf && Object.keys(ticket.cf).length > 0) {
        cfFieldsHTML = '<h6>الحقول المخصصة (CF):</h6><div class="table-responsive"><table class="table table-sm">';
        for (const [key, value] of Object.entries(ticket.cf)) {
            if (value) {
                const displayKey = key.replace('cf_', '').replace(/_/g, ' ');
                cfFieldsHTML += `<tr><td><strong>${displayKey}:</strong></td><td>${value}</td></tr>`;
            }
        }
        cfFieldsHTML += '</table></div>';
    }
    
    // Build threads HTML
    let threadsHTML = '';
    if (threads && threads.length > 0) {
        threadsHTML = `<h6>المحادثات (${threads.length}):</h6>`;
        threadsHTML += '<div class="accordion" id="fullTicketThreads">';
        threads.forEach((thread, index) => {
            const threadDate = thread.createdTime ? new Date(thread.createdTime).toLocaleString('ar-EG') : 'غير محدد';
            const content = thread.content || thread.summary || thread.body || 'لا يوجد محتوى';
            const direction = thread.direction || 'in';
            const directionText = direction === 'in' ? 'وارد' : 'صادر';
            const isHtml = thread.contentType === 'text/html' || content.includes('<');
            
            threadsHTML += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="fullHeading${index}">
                        <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#fullCollapse${index}">
                            <div class="d-flex align-items-center w-100">
                                <span class="badge bg-${direction === 'in' ? 'success' : 'primary'} me-2">${directionText}</span>
                                <span class="flex-grow-1">${thread.subject || 'بدون موضوع'}</span>
                                <small class="text-muted">${threadDate}</small>
                            </div>
                        </button>
                    </h2>
                    <div id="fullCollapse${index}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#fullTicketThreads">
                        <div class="accordion-body">
                            <p><strong>من:</strong> ${thread.fromEmailAddress || thread.author?.email || 'غير محدد'}</p>
                            <p><strong>إلى:</strong> ${thread.to || 'غير محدد'}</p>
                            <hr>
                            <div class="${isHtml ? '' : 'bg-light p-2 rounded'}">${isHtml ? content : content.replace(/\n/g, '<br>')}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        threadsHTML += '</div>';
    }
    
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>رقم التذكرة</h6>
                <p><code>${ticket.ticketNumber || ticket.id}</code></p>
            </div>
            <div class="col-md-6">
                <h6>الحالة</h6>
                <p><span class="badge bg-${statusColor}">${ticket.status || ticket.statusType}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>الموضوع</h6>
                <p>${ticket.subject || 'بدون موضوع'}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>تاريخ الإنشاء</h6>
                <p>${createdDate}</p>
            </div>
            <div class="col-md-6">
                <h6>تاريخ الإغلاق</h6>
                <p>${closedDate}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>{{ __('messages.department') }}</h6>
                <p><span class="badge bg-info">${ticket.layoutDetails?.layoutName || '{{ __('messages.not_specified') }}'}</span></p>
            </div>
            <div class="col-md-6">
                <h6>اللغة</h6>
                <p>${ticket.language || 'غير محدد'}</p>
            </div>
        </div>
        ${customFieldsHTML ? '<div class="row mt-3"><div class="col-12">' + customFieldsHTML + '</div></div>' : ''}
        ${cfFieldsHTML ? '<div class="row mt-3"><div class="col-12">' + cfFieldsHTML + '</div></div>' : ''}
        ${threadsHTML ? `
            <div class="row mt-3">
                <div class="col-12">
                    ${threadsHTML}
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button class="btn btn-primary btn-lg w-100" onclick="openFullThreadsPage(${ticket.id}, '${ticket.ticketNumber}')">
                        <i class="fas fa-window-maximize me-2"></i>
                        عرض جميع المحادثات في صفحة كاملة
                    </button>
                </div>
            </div>
        ` : ''}
        <div class="row mt-3">
            <div class="col-12">
                <h6>البيانات الخام (Raw Data)</h6>
                <pre class="bg-light p-3 rounded"><code>${JSON.stringify(ticket, null, 2)}</code></pre>
            </div>
        </div>
    `;
}

// Function to display ticket details in modal
function displayTicketDetails(ticket) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    
    // Format dates
    const createdDate = ticket.createdTime ? new Date(ticket.createdTime).toLocaleString('ar-EG') : 'غير محدد';
    const closedDate = ticket.closedTime ? new Date(ticket.closedTime).toLocaleString('ar-EG') : 'N/A';
    
    // Status badge color
    const statusColors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary',
        'Resolved': 'primary'
    };
    const statusColor = statusColors[ticket.status] || 'secondary';
    
    // Get translations
    const translations = {
        ticket_number: '{{ __('messages.ticket_number') }}',
        status: '{{ __('messages.status') }}',
        subject: '{{ __('messages.subject') }}',
        created_date: '{{ __('messages.created_date') }}',
        closed_date: '{{ __('messages.closed_date') }}',
        agent: '{{ __('messages.handler') }}',
        response_time: '{{ __('messages.response_time') }}'
    };
    
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>${translations.ticket_number}</h6>
                <p>${ticket.ticketNumber}</p>
            </div>
            <div class="col-md-6">
                <h6>${translations.status}</h6>
                <p><span class="badge bg-${statusColor}">${ticket.status}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>${translations.subject}</h6>
                <p>${ticket.subject}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>${translations.created_date}</h6>
                <p>${createdDate}</p>
            </div>
            <div class="col-md-6">
                <h6>${translations.closed_date}</h6>
                <p>${closedDate}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>${translations.agent}</h6>
                <p>${ticket.agent}</p>
            </div>
            <div class="col-md-6">
                <h6>{{ __('messages.department') }}</h6>
                <p><span class="badge bg-info">${ticket.department}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>${translations.response_time || '{{ __('messages.response_time') }}'}</h6>
                <p>${ticket.responseTime}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>zoho.common.threads</h6>
                <div id="ticketThreads" class="mb-3" data-ticket-id="${ticket.id}">
                    <button class="btn btn-sm btn-outline-primary" onclick="loadTicketThreads('${ticket.id}')">
                        <i class="fas fa-comments me-2"></i>
                        تحميل المحادثات
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Raw Data</h6>
                <pre class="bg-light p-3 rounded"><code>${JSON.stringify(ticket.rawData, null, 2)}</code></pre>
            </div>
        </div>
    `;
}

// Function to load ticket threads
function loadTicketThreads(ticketId) {
    const threadsDiv = document.getElementById('ticketThreads');
    
    // Show loading state
    threadsDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            <span>جاري تحميل المحادثات...</span>
        </div>
    `;
    
    // Fetch threads from API
    fetch(`/api/zoho/ticket/${ticketId}/threads`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTicketThreads(data.threads);
            } else {
                threadsDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.error || 'لا توجد محادثات متاحة'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading ticket threads:', error);
            threadsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    خطأ في تحميل المحادثات: ${error.message || 'خطأ غير معروف'}
                    <br>
                    <small class="text-muted">تأكد من اتصال الإنترنت وحاول مرة أخرى</small>
                </div>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadTicketThreads('${ticketId}')">
                    <i class="fas fa-redo me-1"></i>
                    إعادة المحاولة
                </button>
            `;
        });
}

// Function to load enhanced thread content
function loadEnhancedThreadContent(ticketId, threadId) {
    return new Promise((resolve, reject) => {
        // Try the new max-content endpoint first
        fetch(`/api/zoho/threads/${ticketId}/${threadId}/max-content`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    resolve(data.data);
                } else {
                    // Fallback to existing endpoint
                    return fetch(`/api/zoho/ticket/${ticketId}/threads`);
                }
            })
            .then(response => {
                if (response) {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                } else {
                    throw new Error('No response from fallback endpoint');
                }
            })
            .then(data => {
                if (data.success && data.threads) {
                    // Find the specific thread
                    const specificThread = data.threads.find(thread => thread.id === threadId);
                    if (specificThread) {
                        resolve(specificThread);
                    } else {
                        reject(new Error('Thread not found in response'));
                    }
                } else {
                    reject(new Error(data.error || 'No threads available'));
                }
            })
            .catch(error => {
                console.error('Error in loadEnhancedThreadContent:', error);
                reject(error);
            });
    });
}

// Function to display ticket threads
function displayTicketThreads(threads) {
    const threadsDiv = document.getElementById('ticketThreads');
    const ticketId = threadsDiv.getAttribute('data-ticket-id');
    
    if (!threads || threads.length === 0) {
        threadsDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                لا توجد محادثات لهذه التذكرة
            </div>
        `;
        return;
    }
    
    // Success message
    let threadsHTML = `
        <div class="alert alert-success alert-sm mb-3">
            <i class="fas fa-check-circle me-2"></i>
            تم العثور على ${threads.length} محادثة
        </div>
        <div class="accordion" id="threadsAccordion">
    `;
    
    threads.forEach((thread, index) => {
        const threadDate = thread.createdTime ? new Date(thread.createdTime).toLocaleString('ar-EG', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        }) : 'غير محدد';
        
        const content = thread.fullContent || thread.summary || 'لا يوجد محتوى';
        const direction = thread.direction || 'in';
        const directionText = direction === 'in' ? 'وارد' : 'صادر';
        const directionIcon = direction === 'in' ? 'arrow-left' : 'arrow-right';
        const directionColor = direction === 'in' ? 'success' : 'primary';
        
        // Extract sender information
        const senderName = thread.senderName || thread.from || 'غير محدد';
        const senderEmail = thread.senderEmail || thread.fromEmail || '';
        const toEmail = thread.toEmail || 'غير متوفر';
        
        const threadId = thread.id || `thread-${index}`;
        const collapseId = `collapse-${threadId}`;
        const headingId = `heading-${threadId}`;
        
        threadsHTML += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="${headingId}">
                    <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${index === 0 ? 'true' : 'false'}">
                        <div class="d-flex align-items-center w-100">
                            <i class="fas fa-${directionIcon} text-${directionColor} me-2"></i>
                            <span class="badge bg-${directionColor} me-2">${directionText}</span>
                            <span class="flex-grow-1 text-start">
                                <div>
                                    <strong>${senderName}</strong>
                                    <br>
                                    <small class="text-muted">"${senderName}"</small>
                                </div>
                            </span>
                            <div class="text-end">
                                <small class="text-muted">المحادثة ${index + 1}</small>
                                <br>
                                <small class="text-muted">${threadDate}</small>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#threadsAccordion">
                    <div class="accordion-body">
                               <div class="mb-2">
                                   <small class="text-muted">
                                       <i class="fas fa-user me-1"></i> ${senderName} | 
                                       <i class="fas fa-clock me-1"></i> ${threadDate} |
                                       <i class="fas fa-exchange-alt me-1"></i> ${directionText}
                                       | <i class="fas fa-envelope me-1"></i> "${senderName}"
                                       ${thread.isHtml ? '| <i class="fas fa-code me-1"></i> HTML' : '| <i class="fas fa-file-text me-1"></i> نص'}
                                   </small>
                               </div>
                        
                        <!-- Email Information -->
                        <div class="mb-3 email-info">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-paper-plane me-1"></i> <strong>من:</strong> 
                                        ${senderEmail ? `<a href="mailto:${senderEmail}" class="text-decoration-none">${senderEmail}</a>` : '<span class="text-muted">غير متوفر</span>'}
                                    </small>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-inbox me-1"></i> <strong>إلى:</strong> 
                                        <span class="text-muted">${toEmail}</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadEnhancedContent('${ticketId}', '${threadId}', '${threadId}')">
                                <i class="fas fa-expand-arrows-alt me-1"></i>
                                عرض تفاصيل المحادثة
                            </button>
                        </div>
                        
                        <div class="bg-light p-3 rounded thread-content" id="content-${threadId}">
                            <div class="thread-html-content" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; word-wrap: break-word;">
                                ${formatThreadContent(content, thread.isHtml || false)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    threadsHTML += `
        </div>
        <button class="btn btn-sm btn-outline-secondary mt-3" onclick="hideTicketThreads()">
            <i class="fas fa-eye-slash me-2"></i>
            إخفاء المحادثات
        </button>
    `;
    
    threadsDiv.innerHTML = threadsHTML;
}

// Function to load enhanced content for a specific thread
function loadEnhancedContent(ticketId, threadId, contentId) {
    const contentDiv = document.getElementById(`content-${contentId}`);
    const button = event.target;
    
    // Show loading state
    const originalContent = contentDiv.innerHTML;
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span class="ms-2">جاري تحميل المحتوى المحسن...</span>
        </div>
    `;
    
    // Try to load enhanced content
    loadEnhancedThreadContent(ticketId, threadId)
        .then(enhancedData => {
            // Update content with enhanced data
            const enhancedContent = enhancedData.fullContent || enhancedData.body || enhancedData.content || enhancedData.summary || 'لا يوجد محتوى محسن';
            const isHtml = enhancedData.isHtml || false;
            
            contentDiv.innerHTML = `
                <div class="thread-html-content" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; word-wrap: break-word;">
                    ${formatThreadContent(enhancedContent, isHtml)}
                </div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        تم تحميل تفاصيل المحادثة
                    </small>
                </div>
            `;
            
            // Update button
            button.innerHTML = '<i class="fas fa-check me-1"></i> تم التحميل';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-success');
            button.disabled = true;
        })
        .catch(error => {
            console.error('Error loading enhanced content:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    المحتوى الحالي متاح فقط
                    <br>
                    <small class="text-muted">تفاصيل المحادثة محدودة في الوقت الحالي</small>
                </div>
                ${originalContent}
            `;
            
            // Reset button
            button.innerHTML = '<i class="fas fa-info me-1"></i> المحتوى الحالي';
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-info');
            button.disabled = true;
        });
}

// Function to format thread content as HTML
function formatThreadContent(content, isHtml = false) {
    if (!content) return 'لا يوجد محتوى';
    
    // If content is already HTML, return it directly (with basic sanitization)
    if (isHtml) {
        // Basic HTML sanitization - remove potentially dangerous tags
        let sanitizedContent = content
            .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
            .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
            .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '')
            .replace(/on\w+="[^"]*"/gi, '')
            .replace(/javascript:/gi, '');
        
        return sanitizedContent;
    }
    
    // Convert plain text to HTML while preserving formatting
    let htmlContent = content
        // Convert line breaks to <br> tags
        .replace(/\n/g, '<br>')
        // Convert multiple spaces to non-breaking spaces
        .replace(/  +/g, (match) => '&nbsp;'.repeat(match.length))
        // Convert email addresses to clickable links
        .replace(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g, '<a href="mailto:$1" class="text-primary text-decoration-none">$1</a>')
        // Convert URLs to clickable links
        .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-primary text-decoration-none">$1</a>')
        // Convert phone numbers to clickable links
        .replace(/(\+?[0-9\s\-\(\)]{10,})/g, '<a href="tel:$1" class="text-success text-decoration-none">$1</a>')
        // Convert dates to highlighted format
        .replace(/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/g, '<span class="badge bg-info text-dark">$1</span>')
        // Convert times to highlighted format
        .replace(/(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|am|pm)?)/g, '<span class="badge bg-warning text-dark">$1</span>')
        // Convert booking references to highlighted format
        .replace(/\b(?:Booking|booking|Reference|reference|Ref|ref)\s*:?\s*([A-Z0-9\-]{5,})/gi, '<span class="badge bg-secondary text-white">$1</span>')
        // Convert status words to highlighted format
        .replace(/\b(?:Status|status|Confirmed|confirmed|Pending|pending|Cancelled|cancelled)\b/g, '<span class="badge bg-primary text-white">$1</span>')
        // Convert important keywords to bold
        .replace(/\b(?:Important|important|Urgent|urgent|ASAP|asap|Please|please|Regards|regards|Thank you|thank you)\b/g, '<strong class="text-primary">$1</strong>');
    
    return htmlContent;
}

// Function to hide ticket threads
function hideTicketThreads() {
    const threadsDiv = document.getElementById('ticketThreads');
    const ticketId = threadsDiv.getAttribute('data-ticket-id');
    threadsDiv.innerHTML = `
        <button class="btn btn-sm btn-outline-primary" onclick="loadTicketThreads('${ticketId}')">
            <i class="fas fa-comments me-2"></i>
            تحميل المحادثات
        </button>
    `;
}

// Function to open full threads page
function openFullThreadsPage(ticketId, ticketNumber) {
    // Get threads from current modal content
    const threadsContainer = document.querySelector('#fullTicketThreads');
    if (!threadsContainer) {
        alert('لم يتم تحميل المحادثات بعد');
        return;
    }
    
    // Extract threads data from the page
    const accordionItems = threadsContainer.querySelectorAll('.accordion-item');
    const threads = Array.from(accordionItems).map((item, index) => {
        const body = item.querySelector('.accordion-body');
        const fromText = body.querySelector('p:nth-child(1)')?.textContent || '';
        const toText = body.querySelector('p:nth-child(2)')?.textContent || '';
        const contentDiv = body.querySelector('div');
        
        return {
            index: index + 1,
            from: fromText.replace('من:', '').trim(),
            to: toText.replace('إلى:', '').trim(),
            content: contentDiv?.textContent || contentDiv?.innerHTML || ''
        };
    });
    
    // Create new window
    const newWindow = window.open('', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    
    // Build HTML content
    let htmlContent = '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
    htmlContent += '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    htmlContent += '<title>المحادثات الكاملة - Ticket #' + ticketNumber + '</title>';
    htmlContent += '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
    htmlContent += '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
    htmlContent += '<style>';
    htmlContent += 'body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; padding: 20px; }';
    htmlContent += '.thread-container { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
    htmlContent += '.thread-header { border-bottom: 2px solid #007bff; padding-bottom: 15px; margin-bottom: 15px; }';
    htmlContent += '.thread-meta { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; }';
    htmlContent += '.thread-meta-item { margin-bottom: 8px; }';
    htmlContent += '.thread-content { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; line-height: 1.8; word-wrap: break-word; }';
    htmlContent += '.print-btn { position: fixed; bottom: 30px; left: 30px; z-index: 1000; }';
    htmlContent += '@media print { body { padding: 0; } .print-btn { display: none; } }';
    htmlContent += '</style></head><body><div class="container-fluid">';
    htmlContent += '<div class="row mb-4"><div class="col-12">';
    htmlContent += '<h1 class="text-center mb-3"><i class="fas fa-envelope-open-text text-primary me-2"></i> المحادثات الكاملة - Ticket #' + ticketNumber + '</h1>';
    htmlContent += '<p class="text-center text-muted">عدد المحادثات: ' + threads.length + '</p></div></div>';
    htmlContent += '<div class="row"><div class="col-12">';
    
    threads.forEach(thread => {
        htmlContent += '<div class="thread-container">';
        htmlContent += '<div class="thread-header"><h4 class="mb-0">المحادثة ' + thread.index + '</h4></div>';
        htmlContent += '<div class="thread-meta">';
        htmlContent += '<div class="thread-meta-item"><strong><i class="fas fa-user me-2 text-success"></i>من:</strong> ' + thread.from + '</div>';
        htmlContent += '<div class="thread-meta-item"><strong><i class="fas fa-paper-plane me-2 text-info"></i>إلى:</strong> ' + thread.to + '</div>';
        htmlContent += '</div>';
        htmlContent += '<div class="thread-content">' + thread.content + '</div>';
        htmlContent += '</div>';
    });
    
    htmlContent += '</div></div></div>';
    htmlContent += '<button class="btn btn-primary print-btn" onclick="window.print()"><i class="fas fa-print me-2"></i>طباعة</button>';
    htmlContent += '</body></html>';
    
    newWindow.document.write(htmlContent);
    newWindow.document.close();
}

// Auto-refresh tickets every 15 seconds
let autoRefreshInterval;
let isAutoRefreshActive = false;
let currentDepartmentId = {{ $department->id }};
let currentPage = {{ $zohoTickets->currentPage() }};
let refreshCounter = 0; // Counter to track refresh cycles

function startAutoRefresh() {
    // Auto-refresh disabled on main department page to avoid timeouts
    // Use the dedicated Zoho Tickets page (/departments/{id}/zoho-tickets) for auto-refresh
    console.log('Auto-refresh disabled on main department page');
    return;
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
    isAutoRefreshActive = false;
}

function refreshTicketsTable() {
    const tbody = document.querySelector('#Zoho-department-tickets-section tbody');
    if (!tbody) return;
    
    console.log('Auto-refresh triggered at:', new Date().toLocaleTimeString());
    
    // استخدام الكاش فقط - لا استدعاء لـ Zoho API
    // Fetch tickets from cache only
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 seconds timeout
    
    fetch(`/api/zoho/department/${currentDepartmentId}/tickets?per_page=20&page=${currentPage}&refresh=false&cache_only=true`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        console.log('Cache API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Cache API response data:', data);
        console.log('📋 Using cached data only');
        if (data.success && data.tickets && data.tickets.data) {
            console.log('Updating with', data.tickets.data.length, 'tickets from cache');
            updateTableRows(data.tickets.data);
        } else {
            console.log('No data to update from cache');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
            console.warn('Request timeout - skipping this refresh cycle');
        } else {
            console.error('Error refreshing tickets:', error);
        }
    });
}

function updateTableRows(newTickets) {
    const tbody = document.querySelector('#Zoho-department-tickets-section tbody');
    if (!tbody) return;
    
    const currentRows = Array.from(tbody.querySelectorAll('tr'));
    
    console.log('Updating table rows. Total tickets:', newTickets.length);
    
    newTickets.forEach(newTicket => {
        // Find existing row by ticket ID
        const existingRow = currentRows.find(row => {
            const codeElement = row.querySelector('code');
            return codeElement && codeElement.textContent.trim() === newTicket.ticket_number;
        });
        
        if (existingRow) {
            // Update existing row
            const statusCell = existingRow.querySelector('td:nth-child(4)'); // Status column (after Department ID)
            const statusBadge = statusCell ? statusCell.querySelector('span.badge') : null;
            const handlerCell = existingRow.querySelector('td:nth-child(5)'); // Handler column
            const closedDateCell = existingRow.querySelector('td:nth-child(7)'); // Closed date column
            
            // Get current values
            const currentStatus = existingRow.getAttribute('data-status') || '';
            const currentClosedBy = existingRow.getAttribute('data-closed-by') || '';
            
            // Get new values
            const newStatus = newTicket.status || '';
            const newClosedBy = newTicket.closed_by_name || newTicket.cf_closed_by || '';
            
            // Check for changes
            const statusChanged = currentStatus !== newStatus;
            const closedByChanged = currentClosedBy !== newClosedBy;
            
            console.log(`Ticket ${newTicket.ticket_number}:`, {
                currentStatus,
                newStatus,
                statusChanged,
                currentClosedBy,
                newClosedBy,
                closedByChanged
            });
            
            // Update status
            if (statusChanged) {
                console.log(`Status changed for ticket ${newTicket.ticket_number}: ${currentStatus} -> ${newStatus}`);
                existingRow.setAttribute('data-status', newStatus);
                const statusColor = getStatusColor(newStatus);
                if (statusBadge) {
                    statusBadge.textContent = newStatus;
                    statusBadge.className = `badge bg-${statusColor}`;
                }
            }
            
            // Update handler
            if (closedByChanged && handlerCell) {
                console.log(`Handler changed for ticket ${newTicket.ticket_number}: ${currentClosedBy} -> ${newClosedBy}`);
                existingRow.setAttribute('data-closed-by', newClosedBy);
                handlerCell.innerHTML = formatHandler(newTicket);
            }
            
            // Update closed date
            if (closedDateCell && newTicket.closed_at_zoho) {
                closedDateCell.innerHTML = formatClosedDate(newTicket.closed_at_zoho);
            }
            
            // Add highlight animation if anything changed
            if (statusChanged || closedByChanged) {
                console.log(`Highlighting row for ticket ${newTicket.ticket_number}`);
                existingRow.style.transition = 'background-color 0.3s ease';
                existingRow.style.backgroundColor = '#fff3cd';
                setTimeout(() => {
                    existingRow.style.backgroundColor = '';
                }, 2000);
            }
        }
    });
}

function getStatusColor(status) {
    const colors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary',
        'Resolved': 'primary'
    };
    return colors[status] || 'secondary';
}

function formatHandler(ticket) {
    const handler = ticket.closed_by_name || ticket.cf_closed_by || ticket.user?.name || '';
    if (!handler || handler === 'Auto Close') {
        return '<span class="badge bg-warning">Auto Close</span>';
    }
    if (handler === 'غير محدد') {
        return '<span class="text-muted">غير محدد</span>';
    }
    return `<span class="badge bg-info">${handler}</span>`;
}

function formatClosedDate(dateString) {
    if (!dateString) return '<small class="text-muted">-</small>';
    const date = new Date(dateString);
    return `<small class="text-muted">${date.toLocaleDateString('ar-EG')} ${date.toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit'})}</small>`;
}

// Function to search tickets by ticket number
function searchTicket() {
    const searchValue = document.getElementById('ticketSearchInput').value.trim();
    const table = document.getElementById('ticketsTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const resultsCountDiv = document.getElementById('searchResultsCount');
    const resultsCount = document.getElementById('resultsCount');
    
    // إذا كان البحث فارغاً، عرض جميع الصفوف
    if (searchValue === '') {
        // عرض جميع الصفوف من الجدول الأصلي
        const rows = tbody.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            rows[i].style.display = '';
        }
        resultsCountDiv.style.display = 'none';
        return;
    }
    
    // البحث في جميع البيانات (allTicketsData)
    const matchingTickets = allTicketsData.filter(ticket => {
        return ticket.ticket_number && ticket.ticket_number.includes(searchValue);
    });
    
    // إخفاء جميع الصفوف الحالية
    const rows = tbody.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        rows[i].style.display = 'none';
    }
    
    // عرض الصفوف المطابقة
    let displayedCount = 0;
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        if (cells.length > 0) {
            const ticketNumber = cells[0].querySelector('code')?.textContent || '';
            const ticket = matchingTickets.find(t => t.ticket_number === ticketNumber);
            
            if (ticket) {
                rows[i].style.display = '';
                displayedCount++;
            }
        }
    }
    
    // تحديث عداد النتائج
    resultsCount.textContent = matchingTickets.length;
    resultsCountDiv.style.display = matchingTickets.length > 0 ? 'block' : 'none';
    
    // إظهار رسالة إذا لم يتم العثور على نتائج
    if (matchingTickets.length === 0 && searchValue !== '') {
        const notFoundMsg = '{{ __('messages.ticket_not_found', ['number' => '']) }}';
        const availableMsg = '{{ __('messages.tickets_available') }}';
        alert('⚠️ ' + notFoundMsg.replace(':number', searchValue) + '\n\n' + allTicketsData.length + ' ' + availableMsg);
    }
}

// Function to reset search
function resetSearch() {
    document.getElementById('ticketSearchInput').value = '';
    searchTicket(); // هذا سيعرض جميع الصفوف
}

// Function to refresh closed by data from Zoho API
function refreshClosedByData() {
    const btn = document.getElementById('refreshClosedByBtn');
    const originalHtml = btn.innerHTML;
    
    // Disable button and show loading state
    btn.disabled = true;
    const updatingText = '{{ __('messages.updating') }}';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + updatingText;
    
    // Send AJAX request
    fetch('{{ route("departments.refresh-closed-by", $department) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('✅ ' + data.message);
            // Reload page to show updated data
            window.location.reload();
        } else {
            alert('❌ حدث خطأ: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ حدث خطأ أثناء التحديث');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// Add double-click event to table rows
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('#Zoho-department-tickets-section tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('dblclick', function() {
            const eyeButton = this.querySelector('button[onclick*="viewTicketDetails"]');
            if (eyeButton) {
                const onclickAttr = eyeButton.getAttribute('onclick');
                const ticketIdMatch = onclickAttr.match(/viewTicketDetails\('([^']+)'\)/);
                if (ticketIdMatch) {
                    viewTicketDetails(ticketIdMatch[1]);
                }
            }
        });
        
        // Add hover effect for double-click indication
        row.style.cursor = 'pointer';
        const doubleClickText = '{{ __('messages.double_click_to_view') }}';
        row.title = doubleClickText;
        
        // Store current values for comparison
        const statusBadge = row.querySelector('span.badge');
        if (statusBadge) {
            row.setAttribute('data-status', statusBadge.textContent.trim());
        }
        
        const handlerCell = row.querySelector('td:nth-child(5)'); // Handler column (after Department ID)
        if (handlerCell) {
            const badge = handlerCell.querySelector('span.badge');
            row.setAttribute('data-closed-by', badge ? badge.textContent.trim() : '');
        }
    });
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Stop auto-refresh when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
});
</script>
@endsection
