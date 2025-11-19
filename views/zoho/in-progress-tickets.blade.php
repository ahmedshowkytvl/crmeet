@extends('layouts.app')

@section('title', 'تذاكر قيد التنفيذ')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-cog me-2 text-info"></i>
                        تذاكر قيد التنفيذ (In Progress)
                    </h2>
                    <p class="text-muted mb-0">عرض آخر {{ $tickets->count() }} تذكرة قيد التنفيذ</p>
                    
                    <!-- Instructions Alert -->
                    <div class="alert alert-warning alert-sm mt-3 mb-0" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>ملاحظة:</strong> هذه التذاكر تحتاج متابعة فورية. اضغط على زر 
                        <span class="badge bg-primary"><i class="fas fa-eye"></i></span> 
                        لعرض التفاصيل والمحادثات.
                    </div>
                </div>
                <div>
                    <a href="{{ route('zoho.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للوحة التحكم
                    </a>
                    <a href="{{ route('zoho.tickets') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>
                        جميع التذاكر
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-cog text-info fa-2x"></i>
                    </div>
                    <h4 class="mb-1 text-info">{{ $tickets->count() }}</h4>
                    <small class="text-muted">التذاكر المعروضة</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                    <h4 class="mb-1 text-warning">{{ $totalCount }}</h4>
                    <small class="text-muted">إجمالي التذاكر قيد التنفيذ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-users text-success fa-2x"></i>
                    </div>
                    <h4 class="mb-1 text-success">{{ $agents->count() }}</h4>
                    <small class="text-muted">الوكلاء النشطين</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-percentage text-primary fa-2x"></i>
                    </div>
                    <h4 class="mb-1 text-primary">{{ $stats['total_tickets'] > 0 ? round(($stats['in_progress_tickets'] / $stats['total_tickets']) * 100, 1) : 0 }}%</h4>
                    <small class="text-muted">نسبة التذاكر قيد التنفيذ</small>
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
                            <label class="form-label">فلترة حسب الوكيل</label>
                            <select class="form-select" id="agentFilter">
                                <option value="">جميع الوكلاء</option>
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
                            <label class="form-label">البحث في الموضوع</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="ابحث في موضوع التذكرة">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-filter me-2"></i>
                                تطبيق الفلاتر
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
                        قائمة التذاكر قيد التنفيذ
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="ticketsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم التذكرة</th>
                                    <th>الموضوع</th>
                                    <th>الوكيل</th>
                                    <th>القسم</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>تاريخ آخر تحديث</th>
                                    <th>وقت الاستجابة</th>
                                    <th>عدد المحادثات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                <tr data-agent="{{ $ticket->closed_by_name }}" data-department="{{ $ticket->department ? $ticket->department->name : '' }}" data-subject="{{ $ticket->subject }}">
                                    <td>
                                        <span class="badge bg-info">{{ $ticket->ticket_number }}</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $ticket->subject }}">
                                            {{ $ticket->subject }}
                                        </div>
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
                                        @if($ticket->updated_at)
                                            <small class="text-muted">{{ $ticket->updated_at->format('Y-m-d H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->response_time_minutes)
                                            <small class="text-muted">{{ number_format($ticket->response_time_minutes / 60, 1) }} ساعة</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->thread_count > 0)
                                            <span class="badge bg-info">{{ $ticket->thread_count }}</span>
                                        @else
                                            <span class="text-muted">0</span>
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
                                        <i class="fas fa-cog fa-3x mb-3 text-info"></i>
                                        <p>لا توجد تذاكر قيد التنفيذ</p>
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
                <h5 class="modal-title">تفاصيل التذكرة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
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
    const agentFilter = document.getElementById('agentFilter').value;
    const departmentFilter = document.getElementById('departmentFilter').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    
    const rows = document.querySelectorAll('#ticketsTable tbody tr');
    
    rows.forEach(row => {
        const agent = row.dataset.agent || '';
        const department = row.dataset.department || '';
        const subject = row.dataset.subject.toLowerCase();
        
        let show = true;
        
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
                        <h6>رقم التذكرة</h6>
                        <p>${data.ticket_number || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>الحالة</h6>
                        <p><span class="badge bg-info">${data.status || 'N/A'}</span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>الموضوع</h6>
                        <p>${data.subject || 'N/A'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>تاريخ الإنشاء</h6>
                        <p>${data.created_at_zoho || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>تاريخ آخر تحديث</h6>
                        <p>${data.updated_at || 'N/A'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>الوكيل</h6>
                        <p>${data.closed_by_name || 'غير محدد'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>وقت الاستجابة</h6>
                        <p>${data.response_time_minutes ? (data.response_time_minutes / 60).toFixed(1) + ' ساعة' : 'غير محدد'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>المحادثات</h6>
                        <div id="ticketThreads" class="mb-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadTicketThreads('${data.zoho_ticket_id}')">
                                <i class="fas fa-comments me-2"></i>
                                تحميل المحادثات
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>البيانات الخام</h6>
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
    fetch(`/api/zoho/tickets/in-progress/load-more?offset=${currentCount}&limit=500`, {
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
    row.setAttribute('data-agent', ticket.closed_by_name || '');
    row.setAttribute('data-subject', ticket.subject);
    
    row.innerHTML = `
        <td><span class="badge bg-info">${ticket.ticket_number || 'N/A'}</span></td>
        <td><div class="text-truncate" style="max-width: 200px;" title="${ticket.subject}">${ticket.subject || 'N/A'}</div></td>
        <td>${ticket.closed_by_name ? `<span class="badge bg-light text-dark">${ticket.closed_by_name}</span>` : '<span class="badge bg-secondary">غير محدد</span>'}</td>
        <td><small class="text-muted">${ticket.created_at_zoho ? new Date(ticket.created_at_zoho).toLocaleString('ar-SA') : '-'}</small></td>
        <td><small class="text-muted">${ticket.updated_at ? new Date(ticket.updated_at).toLocaleString('ar-SA') : '-'}</small></td>
        <td><small class="text-muted">${ticket.response_time_minutes ? (ticket.response_time_minutes / 60).toFixed(1) + ' ساعة' : '-'}</small></td>
        <td>${ticket.thread_count > 0 ? `<span class="badge bg-info">${ticket.thread_count}</span>` : '<span class="text-muted">0</span>'}</td>
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
            
            // Check if ticket is in progress
            const isInProgress = ticket.status === 'In Progress';
            const statusClass = isInProgress ? 'info' : 'secondary';
            const statusText = isInProgress ? 'قيد التنفيذ' : ticket.status;
            
            searchResult.innerHTML = `
                <div class="alert ${isInProgress ? 'alert-info' : 'alert-warning'}">
                    <h6><i class="fas fa-check-circle me-2"></i>تم العثور على التذكرة!</h6>
                    ${!isInProgress ? '<small>ملاحظة: هذه التذكرة ليست في حالة "قيد التنفيذ"</small>' : ''}
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
                                <th>تاريخ آخر تحديث</th>
                                <th>وقت الاستجابة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-info">${ticket.ticket_number || 'N/A'}</span></td>
                                <td><div class="text-truncate" style="max-width: 200px;" title="${ticket.subject}">${ticket.subject || 'N/A'}</div></td>
                                <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                                <td>${ticket.closed_by_name ? `<span class="badge bg-light text-dark">${ticket.closed_by_name}</span>` : '<span class="badge bg-secondary">غير محدد</span>'}</td>
                                <td><span class="badge bg-info">${ticket.department_name || 'غير محدد'}</span></td>
                                <td><small class="text-muted">${ticket.created_at_zoho ? new Date(ticket.created_at_zoho).toLocaleString('ar-SA') : '-'}</small></td>
                                <td><small class="text-muted">${ticket.updated_at ? new Date(ticket.updated_at).toLocaleString('ar-SA') : '-'}</small></td>
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
document.getElementById('agentFilter').addEventListener('change', applyFilters);
document.getElementById('departmentFilter').addEventListener('change', applyFilters);

// Global variable to store current ticket ID
let currentTicketId = null;

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
                         
                         <div class="bg-light p-3 rounded thread-content" id="content-${threadId}">
                             ${formatThreadContent(content)}
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
    
    /* In Progress specific styling */
    .badge.bg-info {
        background-color: #0dcaf0 !important;
    }
    
    .text-info {
        color: #0dcaf0 !important;
    }
</style>

@endpush
