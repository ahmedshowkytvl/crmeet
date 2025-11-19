@extends('layouts.app')

@section('title', __('zoho.tickets.all_tickets'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        {{ __('zoho.tickets.all_tickets') }}
                    </h2>
                    <p class="text-muted mb-0">{{ __('zoho.tickets.showing_last_3000') }}</p>
                    <button class="btn btn-sm btn-success mt-2" onclick="refreshTicketsFromAPI()">
                        <i class="fas fa-sync me-2"></i>
                        تحديث من Zoho API (آخر 3000 تذكرة)
                    </button>
                    
                    <!-- Instructions Alert -->
                    <div class="alert alert-info alert-sm mt-3 mb-0" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> لرؤية المحادثات الكاملة مع تنسيق HTML، اضغط على زر 
                        <span class="badge bg-primary"><i class="fas fa-eye"></i></span> 
                        ثم اضغط "تحميل المحادثات" في نافذة التفاصيل.
                    </div>
                </div>
                <div>
                    <a href="{{ route('zoho.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        {{ __('zoho.common.back_to_dashboard') }}
                    </a>
                    <a href="{{ route('zoho.tickets.in-progress') }}" class="btn btn-outline-warning">
                        <i class="fas fa-cog me-2"></i>
                        تذاكر قيد التنفيذ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-ticket-alt text-primary fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['total_tickets'] }}</h4>
                    <small class="text-muted">{{ __('zoho.tickets.total_tickets') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['closed_tickets'] }}</h4>
                    <small class="text-muted">{{ __('zoho.tickets.closed_tickets') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-folder-open text-primary fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['open_tickets'] }}</h4>
                    <small class="text-muted">{{ __('zoho.tickets.open_tickets') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['pending_tickets'] }}</h4>
                    <small class="text-muted">{{ __('zoho.tickets.pending_tickets') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-cog text-info fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['in_progress_tickets'] }}</h4>
                    <small class="text-muted">{{ __('zoho.tickets.in_progress_tickets') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-users text-secondary fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $agents->count() }}</h4>
                    <small class="text-muted">{{ __('zoho.tickets.active_agents') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search for Specific Ticket -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-search me-2"></i>
                        البحث عن تذكرة معينة
                    </h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">رقم التذكرة</label>
                            <input type="text" class="form-control" id="ticketSearchInput" placeholder="أدخل رقم التذكرة (مثل: 2859543)">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button class="btn btn-success w-100" onclick="searchSpecificTicket()">
                                <i class="fas fa-search me-2"></i>
                                بحث في Zoho
                            </button>
                        </div>
                    </div>
                    <div id="searchResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('zoho.tickets.filter_by_status') }}</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('zoho.tickets.all_statuses') }}</option>
                                <option value="Closed">{{ __('zoho.ticket_status.closed') }}</option>
                                <option value="Open">{{ __('zoho.ticket_status.open') }}</option>
                                <option value="Pending">{{ __('zoho.ticket_status.pending') }}</option>
                                <option value="In Progress">{{ __('zoho.ticket_status.in_progress') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('zoho.tickets.filter_by_agent') }}</label>
                            <select class="form-select" id="agentFilter">
                                <option value="">{{ __('zoho.tickets.all_agents') }}</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent }}">{{ $agent }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">فلتر حسب القسم</label>
                            <select class="form-select" id="departmentFilter">
                                <option value="">جميع الأقسام</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}">{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('zoho.tickets.search') }}</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="{{ __('zoho.tickets.search_placeholder') }}">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-search me-2"></i>
                                {{ __('zoho.tickets.apply_filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('zoho.tickets.tickets_list') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="ticketsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('zoho.common.ticket_number') }}</th>
                                    <th>{{ __('zoho.common.subject') }}</th>
                                    <th>{{ __('zoho.common.status') }}</th>
                                    <th>{{ __('zoho.common.agent') }}</th>
                                    <th>القسم</th>
                                    <th>{{ __('zoho.common.created_date') }}</th>
                                    <th>{{ __('zoho.common.closed_date') }}</th>
                                    <th>{{ __('zoho.common.response_time') }}</th>
                                    <th>{{ __('zoho.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                <tr data-status="{{ $ticket->status }}" data-agent="{{ $ticket->closed_by_name }}" data-subject="{{ $ticket->subject }}" ondblclick="viewTicketDetails('{{ $ticket->zoho_ticket_id }}')">
                                    <td>
                                        <span class="badge bg-secondary">{{ $ticket->ticket_number }}</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $ticket->subject }}">
                                            {{ $ticket->subject }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($ticket->status) {
                                                'Closed' => 'success',
                                                'Open' => 'primary',
                                                'Pending' => 'warning',
                                                'In Progress' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ __('zoho.ticket_status.' . strtolower(str_replace(' ', '_', $ticket->status))) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ticket->closed_by_name)
                                            <span class="badge bg-light text-dark">{{ $ticket->closed_by_name }}</span>
                                        @else
                                            <span class="badge bg-secondary">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->department)
                                            <span class="badge bg-info">{{ $ticket->department->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->created_at_zoho)
                                            <small class="text-muted">{{ $ticket->created_at_zoho->format('Y-m-d H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->closed_at_zoho)
                                            <small class="text-muted">{{ $ticket->closed_at_zoho->format('Y-m-d H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->response_time_minutes)
                                            <small class="text-muted">{{ number_format($ticket->response_time_minutes / 60, 1) }} {{ __('zoho.dashboard.hours') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewTicketDetails('{{ $ticket->zoho_ticket_id }}')" title="عرض التفاصيل والمحادثات">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($ticket->thread_count > 0)
                                            <span class="badge bg-info ms-1" title="{{ $ticket->thread_count }} محادثة متاحة">
                                                <i class="fas fa-comments"></i> {{ $ticket->thread_count }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                        <p>{{ __('zoho.tickets.no_tickets_found') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Load More Button -->
                    @if($hasMore)
                    <div class="card-footer bg-light text-center">
                        <button class="btn btn-primary" id="loadMoreBtn" onclick="loadMoreTickets()">
                            <i class="fas fa-plus me-2"></i>
                            تحميل المزيد ({{ $totalCount - $tickets->count() }} تذكرة متبقية)
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('zoho.tickets.ticket_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">{{ __('zoho.common.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const agentFilter = document.getElementById('agentFilter').value;
    const departmentFilter = document.getElementById('departmentFilter').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    
    const rows = document.querySelectorAll('#ticketsTable tbody tr');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        const agent = row.dataset.agent || '';
        const department = row.dataset.department || '';
        const subject = row.dataset.subject.toLowerCase();
        
        let show = true;
        
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        if (departmentFilter && department !== departmentFilter) {
            show = false;
        }
        
        if (searchInput && !subject.includes(searchInput)) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function viewTicketDetails(ticketId) {
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    modal.show();
    
    // Load ticket details via AJAX
    fetch(`/api/zoho/ticket/${ticketId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('غير مصرح لك بالوصول. يرجى تسجيل الدخول مرة أخرى.');
                } else if (response.status === 403) {
                    throw new Error('غير مصرح لك بعرض هذه التذكرة.');
                } else if (response.status === 404) {
                    throw new Error('التذكرة غير موجودة.');
                } else {
                    throw new Error(`خطأ في الخادم: ${response.status} ${response.statusText}`);
                }
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('استجابة غير صالحة من الخادم');
            }
            
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            document.getElementById('ticketDetailsContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('zoho.common.ticket_number') }}</h6>
                        <p>${data.ticket_number || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('zoho.common.status') }}</h6>
                        <p><span class="badge bg-success">${data.status || 'N/A'}</span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>{{ __('zoho.common.subject') }}</h6>
                        <p>${data.subject || 'N/A'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('zoho.common.created_date') }}</h6>
                        <p>${data.created_at_zoho || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('zoho.common.closed_date') }}</h6>
                        <p>${data.closed_at_zoho || 'N/A'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('zoho.common.agent') }}</h6>
                        <p>${data.closed_by_name || 'غير محدد'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>القسم</h6>
                        <p><span class="badge bg-info">${data.department_name || 'غير محدد'}</span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('zoho.common.response_time') }}</h6>
                        <p>${data.response_time_minutes ? (data.response_time_minutes / 60).toFixed(1) + ' ساعة' : 'غير محدد'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>{{ __('zoho.common.threads') }}</h6>
                        <div id="ticketThreads" class="mb-3" data-ticket-id="${data.zoho_ticket_id}">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadTicketThreads('${data.zoho_ticket_id}')">
                                <i class="fas fa-comments me-2"></i>
                                تحميل المحادثات
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>{{ __('zoho.common.raw_data') }}</h6>
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(data.raw_data, null, 2)}</code></pre>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading ticket details:', error);
            document.getElementById('ticketDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <strong>خطأ في تحميل تفاصيل التذكرة:</strong><br>
                    ${error.message}
                </div>
            `;
        });
}

// Load more tickets function
function loadMoreTickets() {
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const originalText = loadMoreBtn.innerHTML;
    
    // Show loading state
    loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التحميل...';
    loadMoreBtn.disabled = true;
    
    // Get current number of tickets displayed
    const currentCount = {{ $tickets->count() }};
    
    // Fetch more tickets via AJAX
    fetch(`/api/zoho/tickets/load-more?offset=${currentCount}&limit=3000`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('غير مصرح لك بالوصول. يرجى تسجيل الدخول مرة أخرى.');
                } else if (response.status === 403) {
                    throw new Error('غير مصرح لك بتحميل المزيد من التذاكر.');
                } else {
                    throw new Error(`خطأ في الخادم: ${response.status} ${response.statusText}`);
                }
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('استجابة غير صالحة من الخادم');
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Add new tickets to the table
                const tbody = document.querySelector('#ticketsTable tbody');
                
                data.tickets.forEach(ticket => {
                    const row = createTicketRow(ticket);
                    tbody.appendChild(row);
                });
                
                // Update button or hide if no more tickets
                if (data.hasMore) {
                    loadMoreBtn.innerHTML = `<i class="fas fa-plus me-2"></i>تحميل المزيد (${data.totalCount - data.loaded} تذكرة متبقية)`;
                    loadMoreBtn.disabled = false;
                } else {
                    loadMoreBtn.parentElement.remove();
                }
            } else {
                throw new Error(data.error || 'Failed to load more tickets');
            }
        })
        .catch(error => {
            console.error('Error loading more tickets:', error);
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
            alert('حدث خطأ في تحميل المزيد من التذاكر: ' + error.message);
        });
}

// Create ticket row HTML
function createTicketRow(ticket) {
    const row = document.createElement('tr');
    row.setAttribute('data-status', ticket.status);
    row.setAttribute('data-agent', ticket.closed_by_name || '');
    row.setAttribute('data-department', ticket.department_name || '');
    row.setAttribute('data-subject', ticket.subject);
    
    // Status class mapping
    const statusClass = {
        'Closed': 'success',
        'Open': 'primary', 
        'Pending': 'warning',
        'In Progress': 'info'
    }[ticket.status] || 'secondary';
    
    row.innerHTML = `
        <td><span class="badge bg-secondary">${ticket.ticket_number || 'N/A'}</span></td>
        <td><div class="text-truncate" style="max-width: 200px;" title="${ticket.subject}">${ticket.subject || 'N/A'}</div></td>
        <td><span class="badge bg-${statusClass}">${ticket.status}</span></td>
        <td>${ticket.closed_by_name ? `<span class="badge bg-light text-dark">${ticket.closed_by_name}</span>` : '<span class="badge bg-secondary">غير محدد</span>'}</td>
        <td><span class="badge bg-info">${ticket.department_name || 'غير محدد'}</span></td>
        <td><small class="text-muted">${ticket.created_at_zoho ? new Date(ticket.created_at_zoho).toLocaleString('ar-SA') : '-'}</small></td>
        <td><small class="text-muted">${ticket.closed_at_zoho ? new Date(ticket.closed_at_zoho).toLocaleString('ar-SA') : '-'}</small></td>
        <td><small class="text-muted">${ticket.response_time_minutes ? (ticket.response_time_minutes / 60).toFixed(1) + ' ساعة' : '-'}</small></td>
        <td>
            <button class="btn btn-sm btn-outline-primary" onclick="viewTicketDetails('${ticket.zoho_ticket_id}')" title="عرض التفاصيل والمحادثات">
                <i class="fas fa-eye"></i>
            </button>
            ${ticket.thread_count > 0 ? `
                <span class="badge bg-info ms-1" title="${ticket.thread_count} محادثة متاحة">
                    <i class="fas fa-comments"></i> ${ticket.thread_count}
                </span>
            ` : ''}
        </td>
    `;
    
    return row;
}

// Search for specific ticket function
function searchSpecificTicket() {
    const ticketNumber = document.getElementById('ticketSearchInput').value.trim();
    const searchResult = document.getElementById('searchResult');
    
    if (!ticketNumber) {
        alert('يرجى إدخال رقم التذكرة');
        return;
    }
    
    // Show loading
    searchResult.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin me-2"></i>
            جاري البحث في Zoho...
        </div>
    `;
    searchResult.style.display = 'block';
    
    // Search via API
    fetch(`/api/zoho/tickets/search?ticket_number=${encodeURIComponent(ticketNumber)}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('التذكرة غير موجودة في Zoho');
            } else if (response.status === 401) {
                throw new Error('غير مصرح لك بالوصول');
            } else {
                throw new Error(`خطأ في الخادم: ${response.status} ${response.statusText}`);
            }
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('استجابة غير صالحة من الخادم');
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success && data.ticket) {
            const ticket = data.ticket;
            
            // Status class mapping
            const statusClass = {
                'Closed': 'success',
                'Open': 'primary', 
                'Pending': 'warning',
                'In Progress': 'info'
            }[ticket.status] || 'secondary';
            
            searchResult.innerHTML = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>تم العثور على التذكرة!</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>رقم التذكرة</th>
                                <th>الموضوع</th>
                                <th>الحالة</th>
                                <th>الوكيل</th>
                                <th>القسم</th>
                                <th>تاريخ الإنشاء</th>
                                <th>تاريخ الإغلاق</th>
                                <th>وقت الاستجابة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-secondary">${ticket.ticket_number || 'N/A'}</span></td>
                                <td><div class="text-truncate" style="max-width: 200px;" title="${ticket.subject}">${ticket.subject || 'N/A'}</div></td>
                                <td><span class="badge bg-${statusClass}">${ticket.status}</span></td>
                                <td>${ticket.closed_by_name ? `<span class="badge bg-light text-dark">${ticket.closed_by_name}</span>` : '<span class="badge bg-secondary">غير محدد</span>'}</td>
                                <td><span class="badge bg-info">${ticket.department_name || 'غير محدد'}</span></td>
                                <td><small class="text-muted">${ticket.created_at_zoho ? new Date(ticket.created_at_zoho).toLocaleString('ar-SA') : '-'}</small></td>
                                <td><small class="text-muted">${ticket.closed_at_zoho ? new Date(ticket.closed_at_zoho).toLocaleString('ar-SA') : '-'}</small></td>
                                <td><small class="text-muted">${ticket.response_time_minutes ? (ticket.response_time_minutes / 60).toFixed(1) + ' ساعة' : '-'}</small></td>
                                <td>
            <button class="btn btn-sm btn-outline-primary" onclick="viewTicketDetails('${ticket.zoho_ticket_id}')" title="عرض التفاصيل والمحادثات">
                <i class="fas fa-eye"></i>
            </button>
            ${ticket.thread_count > 0 ? `
                <span class="badge bg-info ms-1" title="${ticket.thread_count} محادثة متاحة">
                    <i class="fas fa-comments"></i> ${ticket.thread_count}
                </span>
            ` : ''}
        </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted mt-2">
                    <small><i class="fas fa-info-circle me-1"></i>تم البحث في Zoho API - ${data.source || 'zoho_api'}</small>
                </div>
            `;
        } else {
            throw new Error(data.error || 'فشل في البحث');
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        searchResult.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>خطأ في البحث:</strong><br>
                ${error.message}
                </div>
            `;
        });
}

// Auto-apply filters on input change
document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('agentFilter').addEventListener('change', applyFilters);
document.getElementById('departmentFilter').addEventListener('change', applyFilters);

// Global variable to store current ticket ID
let currentTicketId = null;

// Format thread content for display
function formatThreadContent(content, isHtml = false) {
    if (!content) return 'لا يوجد محتوى';
    
    if (isHtml) {
        // For HTML content, sanitize and display as-is
        const sanitized = content
            .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '') // Remove scripts
            .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '') // Remove iframes
            .replace(/on\w+="[^"]*"/gi, '') // Remove event handlers
            .replace(/javascript:/gi, ''); // Remove javascript: protocols
        
        return sanitized;
    } else {
        // For plain text, convert to HTML with proper formatting
        return content
            .replace(/\n/g, '<br>')
            .replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')
            .replace(/\s{2,}/g, '&nbsp;&nbsp;');
    }
}

// Clean HTML and decode entities
function cleanHtmlData(str) {
    if (!str) return '';
    
    return str
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&amp;/g, '&')
        .replace(/&#39;/g, "'")
        .replace(/<[^>]*>/g, '') // Remove HTML tags
        .replace(/&[a-zA-Z0-9#]+;/g, '') // Remove remaining HTML entities
        .trim();
}

// Extract email from messy string
    function extractEmail(str) {
        if (!str) return '';
        
        // Clean the string first
        const cleanStr = str.toString()
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&amp;/g, '&')
            .replace(/&#39;/g, "'")
            .replace(/<[^>]*>/g, '') // Remove HTML tags
            .trim();
        
        // Try different email patterns
        const patterns = [
            // Standard email pattern
            /([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g,
            // Email with angle brackets
            /<([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})>/g,
            // Email in quotes
            /"([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})"/g,
            // Email after "To:" or "From:"
            /(?:to|from|cc|bcc)\s*:?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/gi
        ];
        
        for (const pattern of patterns) {
            const matches = cleanStr.match(pattern);
            if (matches) {
                // Return the first valid email found
                for (const match of matches) {
                    const email = match.replace(/[<>"]/g, '').trim();
                    if (email && email.includes('@') && email.includes('.')) {
                        return email;
                    }
                }
            }
        }
        
        return '';
    }

// Get author display name from author object or string
function getAuthorDisplayName(author) {
    if (!author) return 'غير محدد';
    
    if (typeof author === 'object') {
        // Try different properties that might contain the name
        return author.name || 
               author.email || 
               author.firstName || 
               author.lastName ||
               (author.firstName && author.lastName ? `${author.firstName} ${author.lastName}` : null) ||
               'غير محدد';
    }
    
    return cleanHtmlData(author);
}

// Format thread content to display HTML properly
function formatThreadContent(content) {
    if (!content) return 'لا يوجد محتوى';
    
    // If content is already HTML, return it with proper formatting
    if (content.includes('<') && content.includes('>')) {
        // Clean and format HTML content
        return content
            .replace(/<style[^>]*>.*?<\/style>/gi, '') // Remove style tags
            .replace(/<script[^>]*>.*?<\/script>/gi, '') // Remove script tags
            .replace(/on\w+="[^"]*"/gi, '') // Remove event handlers
            .replace(/javascript:/gi, '') // Remove javascript: links
            .replace(/<iframe[^>]*>.*?<\/iframe>/gi, '') // Remove iframes
            .replace(/<object[^>]*>.*?<\/object>/gi, '') // Remove objects
            .replace(/<embed[^>]*>/gi, '') // Remove embeds
            .replace(/<link[^>]*>/gi, '') // Remove link tags
            .replace(/<meta[^>]*>/gi, '') // Remove meta tags
            .replace(/<title[^>]*>.*?<\/title>/gi, '') // Remove title tags
            .replace(/<head[^>]*>.*?<\/head>/gi, '') // Remove head tags
            .replace(/<html[^>]*>|<\/html>/gi, '') // Remove html tags
            .replace(/<body[^>]*>|<\/body>/gi, '') // Remove body tags
            .trim();
    }
    
    // Convert plain text to HTML with better formatting
    return content
        .replace(/\r\n/g, '\n') // Normalize line endings
        .replace(/\r/g, '\n') // Normalize line endings
        .replace(/\n{3,}/g, '\n\n') // Limit multiple line breaks
        .replace(/\n/g, '<br>') // Convert line breaks to HTML
        .replace(/&/g, '&amp;') // Escape ampersands
        .replace(/</g, '&lt;') // Escape less than
        .replace(/>/g, '&gt;') // Escape greater than
        .replace(/&lt;br&gt;/g, '<br>') // Restore line breaks
        .replace(/(https?:\/\/[^\s<>"]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>') // Make URLs clickable
        .replace(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g, '<a href="mailto:$1">$1</a>'); // Make emails clickable
}

// Load ticket threads function
function loadTicketThreads(ticketId) {
    currentTicketId = ticketId; // Store ticket ID globally
    const threadsContainer = document.getElementById('ticketThreads');
    const originalContent = threadsContainer.innerHTML;
    
    // Show loading
    threadsContainer.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
            <span>جاري تحميل المحادثات الكاملة...</span>
        </div>
        <div class="alert alert-info alert-sm mt-2">
            <i class="fas fa-info-circle me-2"></i>
            جاري جلب المحتوى الكامل للمحادثات من Zoho...
        </div>
    `;
    
    // Fetch threads via API
    fetch(`/api/zoho/ticket/${ticketId}/threads`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('لا توجد محادثات لهذه التذكرة');
            } else if (response.status === 401) {
                throw new Error('غير مصرح لك بالوصول');
            } else {
                throw new Error(`خطأ في الخادم: ${response.status} ${response.statusText}`);
            }
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('استجابة غير صالحة من الخادم');
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success && data.threads) {
            displayTicketThreads(data.threads, ticketId);
        } else {
            throw new Error(data.error || 'فشل في تحميل المحادثات');
        }
    })
    .catch(error => {
        console.error('Error loading threads:', error);
        threadsContainer.innerHTML = `
            <div class="alert alert-danger alert-sm">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>خطأ:</strong> ${error.message}
            </div>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadTicketThreads('${ticketId}')">
                <i class="fas fa-redo me-2"></i>
                إعادة المحاولة
            </button>
        `;
    });
}

// Function to load enhanced thread content - simplified approach
function loadEnhancedThreadContent(ticketId, threadId) {
    return new Promise((resolve, reject) => {
        // Use the working max-content endpoint
        fetch(`/api/zoho/threads/${ticketId}/${threadId}/max-content`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    console.log('Max-content endpoint success:', data.method || 'basic');
                    resolve(data.data);
                } else {
                    // Fallback to threads endpoint
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

// Hide ticket threads function
function hideTicketThreads() {
    const threadsContainer = document.getElementById('ticketThreads');
    if (currentTicketId) {
        threadsContainer.innerHTML = `
            <button class="btn btn-sm btn-outline-primary" onclick="loadTicketThreads('${currentTicketId}')">
                <i class="fas fa-comments me-2"></i>
                تحميل المحادثات
            </button>
        `;
    }
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

// Toggle full content for long threads
function toggleFullContent(threadId) {
    const contentElement = document.getElementById(`content-${threadId}`);
    const button = event.target.closest('button');
    
    if (contentElement.style.maxHeight === '500px' || !contentElement.style.maxHeight) {
        // Expand
        contentElement.style.maxHeight = 'none';
        button.innerHTML = '<i class="fas fa-compress-alt me-1"></i>عرض مختصر';
    } else {
        // Collapse
        contentElement.style.maxHeight = '500px';
        button.innerHTML = '<i class="fas fa-expand-alt me-1"></i>عرض كامل';
    }
}

// Display ticket threads
function displayTicketThreads(threads, ticketId) {
    const threadsContainer = document.getElementById('ticketThreads');
    
    if (!threads || threads.length === 0) {
        threadsContainer.innerHTML = `
            <div class="alert alert-info alert-sm">
                <i class="fas fa-info-circle me-2"></i>
                لا توجد محادثات لهذه التذكرة
            </div>
        `;
        return;
    }
    
    let threadsHtml = `
        <div class="alert alert-success alert-sm mb-3">
            <i class="fas fa-check-circle me-2"></i>
            تم العثور على ${threads.length} محادثة
        </div>
        <div class="accordion" id="threadsAccordion">
    `;
    
    threads.forEach((thread, index) => {
        const threadId = thread.id || `thread-${index}`;
        
        const content = thread.fullContent || thread.summary || thread.content || thread.body || thread.text || 'لا يوجد محتوى';
        const author = thread.author || thread.authorName || thread.from || thread.sender || 'غير محدد';
        const date = thread.createdTime ? new Date(thread.createdTime).toLocaleString('ar-SA') : 'غير محدد';
        const rawSubject = thread.fullSubject || thread.fromEmailAddress || thread.subject || thread.title || 'لا يوجد عنوان';
        const subject = cleanHtmlData(rawSubject);
        
        // Get email addresses - try multiple possible fields and clean them
        let fromEmail = extractEmail(thread.fromEmailAddress || thread.fromEmail || thread.from || thread.senderEmail || '');
        let toEmail = extractEmail(thread.toEmailAddress || thread.toEmail || thread.to || thread.recipientEmail || '');
        let ccEmail = extractEmail(thread.ccEmailAddress || thread.ccEmail || thread.cc || '');
        let bccEmail = extractEmail(thread.bccEmailAddress || thread.bccEmail || thread.bcc || '');
        
        // Try to extract emails from author object if it's an object
        if (typeof author === 'object' && author) {
            fromEmail = fromEmail || extractEmail(author.email || author.fromEmail || author.senderEmail || '');
            toEmail = toEmail || extractEmail(author.toEmail || author.recipientEmail || '');
        }
        
        // Try to extract from thread properties that might contain email info
        if (!toEmail) {
            toEmail = extractEmail(thread.recipient || thread.receiver || thread.destination || '');
        }
        
        // Try to extract from content if still empty - look for patterns like "To: email@domain.com"
        if (!toEmail && content) {
            const toMatch = content.match(/to\s*:?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i);
            if (toMatch) {
                toEmail = toMatch[1];
            }
        }
        
        // Try to extract from subject if it contains email info
        const cleanSubject = cleanHtmlData(subject);
        if (!fromEmail && cleanSubject) {
            fromEmail = extractEmail(cleanSubject);
        }
        
        // Try to extract from content if still empty
        if (!fromEmail && content) {
            fromEmail = extractEmail(content);
        }
        
        // If we have fromEmail but no toEmail, try to infer from content patterns
        if (fromEmail && !toEmail && content) {
            // Look for email patterns in content that are different from fromEmail
            const emailMatches = content.match(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g);
            if (emailMatches) {
                // Find an email that's different from fromEmail
                const differentEmail = emailMatches.find(email => 
                    email.toLowerCase() !== fromEmail.toLowerCase() && 
                    !email.toLowerCase().includes('eetglobal') &&
                    !email.toLowerCase().includes('callcenter')
                );
                if (differentEmail) {
                    toEmail = differentEmail;
                }
            }
        }
        
        // Determine direction based on email addresses and thread direction
        let isOutbound = thread.direction === 'out';
        
        // Smart direction detection based on email addresses
        if (fromEmail && toEmail) {
            const fromDomain = fromEmail.toLowerCase();
            const toDomain = toEmail.toLowerCase();
            
            // Define our company domains
            const ourDomains = ['eetglobal.net', 'eetglobal', 'callcenter', 'contracting'];
            
            // Check if FROM is our domain (outbound)
            const fromIsOurs = ourDomains.some(domain => fromDomain.includes(domain));
            
            // Check if TO is our domain (inbound)
            const toIsOurs = ourDomains.some(domain => toDomain.includes(domain));
            
            if (fromIsOurs && !toIsOurs) {
                isOutbound = true; // We sent it out
            } else if (!fromIsOurs && toIsOurs) {
                isOutbound = false; // We received it
            }
            // If both or neither are ours, keep the original direction from Zoho
        }
        
        const directionClass = isOutbound ? 'outbound' : 'inbound';
        const directionIcon = isOutbound ? 'fa-arrow-right text-primary' : 'fa-arrow-left text-success';
        const directionText = isOutbound ? 'صادر' : 'وارد';
        
        // Debug: Log thread data to console
        console.log('Thread data:', thread);
        console.log('From Email:', fromEmail);
        console.log('To Email:', toEmail);
        console.log('Original direction from Zoho:', thread.direction);
        console.log('Calculated direction:', isOutbound ? 'صادر' : 'وارد');
        
        threadsHtml += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-${threadId}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${threadId}">
                        <div class="d-flex align-items-center w-100">
                            <i class="fas ${directionIcon} me-2"></i>
                            <span class="badge bg-${isOutbound ? 'primary' : 'success'} me-2">${directionText}</span>
                            <span class="flex-grow-1 text-start">
                                <div>
                                    <strong>${getAuthorDisplayName(author)}</strong>
                                    <br>
                                    <small class="text-muted">${subject}</small>
                                </div>
                            </span>
                            <div class="text-end">
                                <small class="text-muted">المحادثة ${index + 1}</small>
                                <br>
                                <small class="text-muted">${date}</small>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse-${threadId}" class="accordion-collapse collapse" data-bs-parent="#threadsAccordion">
                     <div class="accordion-body">
                         <div class="mb-2">
                             <small class="text-muted">
                                 <i class="fas fa-user me-1"></i> ${getAuthorDisplayName(author)} | 
                                 <i class="fas fa-clock me-1"></i> ${date} |
                                 <i class="fas fa-exchange-alt me-1"></i> ${directionText}
                                 ${subject ? `| <i class="fas fa-envelope me-1"></i> ${subject}` : ''}
                             </small>
                         </div>
                         
                         <!-- Email Information -->
                         <div class="mb-3 email-info">
                             <div class="row">
                                 <div class="col-md-6 mb-2"><small class="text-muted"><i class="fas fa-paper-plane me-1"></i> <strong>من:</strong> ${fromEmail ? '<a href="mailto:' + fromEmail + '" class="text-decoration-none">' + fromEmail + '</a>' : '<span class="text-muted">غير متوفر</span>'}</small></div>
                                 <div class="col-md-6 mb-2"><small class="text-muted"><i class="fas fa-inbox me-1"></i> <strong>إلى:</strong> ${toEmail ? '<a href="mailto:' + toEmail + '" class="text-decoration-none">' + toEmail + '</a>' : '<span class="text-muted">غير متوفر</span>'}</small></div>
                                 ${ccEmail ? '<div class="col-md-6 mb-2"><small class="text-muted"><i class="fas fa-copy me-1"></i> <strong>نسخة:</strong> <a href="mailto:' + ccEmail + '" class="text-decoration-none">' + ccEmail + '</a></small></div>' : ''}
                                 ${bccEmail ? '<div class="col-md-6 mb-2"><small class="text-muted"><i class="fas fa-eye-slash me-1"></i> <strong>نسخة مخفية:</strong> <a href="mailto:' + bccEmail + '" class="text-decoration-none">' + bccEmail + '</a></small></div>' : ''}
                             </div>
                        </div>
                        
                        <div class="mb-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadEnhancedContent('${ticketId}', '${threadId}', '${threadId}')">
                                <i class="fas fa-expand-arrows-alt me-1"></i>
                                عرض تفاصيل المحادثة
                            </button>
                        </div>
                        
                        <div class="bg-light p-3 rounded thread-content" id="content-${threadId}">
                            ${formatThreadContent(content, thread.isHtml || false)}
                        </div>
                        ${content.length > 500 ? `
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullContent('${threadId}')">
                                    <i class="fas fa-expand-alt me-1"></i>
                                    عرض كامل
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    threadsHtml += `
        </div>
        <button class="btn btn-sm btn-outline-secondary mt-3" onclick="hideTicketThreads()">
            <i class="fas fa-eye-slash me-2"></i>
            إخفاء المحادثات
        </button>
    `;
    
    threadsContainer.innerHTML = threadsHtml;
}

// Allow Enter key to trigger search
document.getElementById('ticketSearchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchSpecificTicket();
    }
});

// Refresh tickets from Zoho API
function refreshTicketsFromAPI() {
    if (!confirm('هل تريد تحديث التذاكر من Zoho API؟ قد يستغرق هذا الأمر بضع دقائق.')) {
        return;
    }
    
    // Show loading
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info mt-2';
    alertDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري تحديث التذاكر من Zoho API...';
    document.querySelector('.d-flex.justify-content-between.align-items-center > div:first-child').appendChild(alertDiv);
    
    // Fetch from API
    window.location.href = '/zoho/tickets?refresh=1';
}
</script>

<style>
    /* Thread content styling */
    .thread-content {
        max-height: 500px;
        overflow-y: auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        line-height: 1.6;
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    
    .thread-content img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
        margin: 8px 0;
    }
    
    .thread-content a {
        color: #007bff;
        text-decoration: none;
        word-break: break-all;
    }
    
    .thread-content a:hover {
        text-decoration: underline;
    }
    
    .thread-content blockquote {
        border-left: 4px solid #dee2e6;
        padding-left: 1rem;
        margin: 1rem 0;
        color: #6c757d;
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 4px;
    }
    
    .thread-content p {
        margin-bottom: 1rem;
    }
    
    .thread-content strong, .thread-content b {
        font-weight: 600;
    }
    
    .thread-content em, .thread-content i {
        font-style: italic;
    }
    
    .thread-content ul, .thread-content ol {
        padding-left: 2rem;
        margin-bottom: 1rem;
    }
    
    .thread-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    
    .thread-content th, .thread-content td {
        border: 1px solid #dee2e6;
        padding: 8px 12px;
        text-align: left;
    }
    
    .thread-content th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .accordion-button {
        font-size: 0.9rem;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #e3f2fd;
        border-color: #bbdefb;
    }
    
    /* Thread toggle button styling */
    .thread-content {
        transition: max-height 0.3s ease;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Email information styling */
    .email-info {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .email-info a {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
    }
    
    .email-info a:hover {
        text-decoration: underline;
    }
    
    .email-info small {
        font-size: 0.85rem;
    }
    
    .email-info i {
        width: 16px;
        text-align: center;
    }
</style>

@endpush
