@extends('layouts.app')

@section('title', 'تذاكر Zoho - ' . $department->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-ticket-alt me-2"></i>تذاكر Zoho - {{ $department->name }}</h2>
    <div>
        <a href="{{ route('departments.show', $department) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>العودة للقسم
        </a>
    </div>
</div>

@if(isset($zohoTickets) && $zohoTickets->count() > 0)
<div class="card" id="Zoho-department-tickets-section">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="fas fa-ticket-alt me-2"></i>
            تذاكر Zoho - {{ $department->name }}
        </h6>
    </div>
    <div class="card-body">
        <!-- Info Alert: Data from cache only -->
        <div class="alert alert-info d-flex align-items-center mb-3">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <strong>ملاحظة هامة:</strong> هذه الصفحة تعرض البيانات المخزنة في الكاش فقط (<code>zoho_tickets_cached</code> table)
                <br>
                <small>لجلب أحدث التذاكر من Zoho، استخدم صفحة <a href="{{ route('zoho.bulk-sync.index') }}" class="alert-link">Zoho Bulk Sync</a></small>
            </div>
        </div>
        <!-- إحصائيات سريعة -->
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-2">
                <div class="bg-light p-2 rounded text-center">
                    <h6 class="text-muted mb-1">المجموع</h6>
                    <h4 class="text-primary mb-0">{{ $ticketStats['total_tickets'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="bg-light p-2 rounded text-center">
                    <h6 class="text-muted mb-1">مغلقة</h6>
                    <h4 class="text-success mb-0">{{ $ticketStats['closed_tickets'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="bg-light p-2 rounded text-center">
                    <h6 class="text-muted mb-1">مفتوحة</h6>
                    <h4 class="text-warning mb-0">{{ $ticketStats['open_tickets'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="bg-light p-2 rounded text-center">
                    <h6 class="text-muted mb-1">في الانتظار</h6>
                    <h4 class="text-info mb-0">{{ $ticketStats['pending_tickets'] }}</h4>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="bg-light p-2 rounded text-center">
                    <h6 class="text-muted mb-1">قيد التنفيذ</h6>
                    <h4 class="text-danger mb-0">{{ $ticketStats['in_progress_tickets'] }}</h4>
                </div>
            </div>
        </div>

        <!-- جدول التذاكر -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>رقم التذكرة</th>
                        <th>الموضوع</th>
                        <th>الحالة</th>
                        <th>المعالج</th>
                        <th>تاريخ الإنشاء</th>
                        <th>تاريخ الإغلاق</th>
                        <th>وقت الاستجابة</th>
                        <th>عدد المحادثات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zohoTickets as $ticket)
                        <tr style="cursor: pointer;" onclick="viewTicketDetails('{{ $ticket->id }}')">
                            <td><code>{{ $ticket->ticketNumber }}</code></td>
                            <td>{{ $ticket->subject }}</td>
                            <td>
                                @if($ticket->status == 'Closed')
                                    <span class="badge bg-success">Closed</span>
                                @elseif($ticket->status == 'Open')
                                    <span class="badge bg-warning">Open</span>
                                @elseif($ticket->status == 'Pending')
                                    <span class="badge bg-info">Pending</span>
                                @elseif($ticket->status == 'In Progress')
                                    <span class="badge bg-primary">In Progress</span>
                                @else
                                    <span class="badge bg-secondary">{{ $ticket->status }}</span>
                                @endif
                            </td>
                            <td>{{ $ticket->closed_by_name ?? 'غير محدد' }}</td>
                            <td>{{ $ticket->created_at_zoho ? \Carbon\Carbon::parse($ticket->created_at_zoho)->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $ticket->closed_at_zoho ? \Carbon\Carbon::parse($ticket->closed_at_zoho)->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $ticket->time_to_first_response ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $ticket->threadCount ?? 0 }}</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="event.stopPropagation(); viewTicketDetails('{{ $ticket->id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(($ticket->threadCount ?? 0) > 0)
                                        <span class="badge bg-info">
                                            <i class="fas fa-comments"></i> {{ $ticket->threadCount }} محادثة
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
                    عرض {{ $zohoTickets->firstItem() }} إلى {{ $zohoTickets->lastItem() }} من إجمالي {{ $zohoTickets->total() }} تذكرة
                </small>
            </div>
            <div>
                {{ $zohoTickets->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>لا توجد تذاكر Zoho لهذا القسم
</div>
@endif

<!-- Modal for Ticket Details -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketDetailsModalLabel">Ticket Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">جاري تحميل تفاصيل التذكرة...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Include all JavaScript functions from departments/show.blade.php for ticket viewing
// This will be added in the next step

let currentDepartmentId = {{ $department->id }};
let currentPage = {{ $zohoTickets->currentPage() }};
let refreshCounter = 0;

// Update current page when pagination link is clicked
document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('.pagination a');
    if (paginationLink && paginationLink.href.includes('page=')) {
        const urlParams = new URL(paginationLink.href);
        const page = urlParams.searchParams.get('page');
        if (page) {
            currentPage = parseInt(page);
            // Stop auto-refresh during page navigation
            stopAutoRefresh();
            // Restart after navigation completes
            setTimeout(() => {
                if (!document.hidden) {
                    startAutoRefresh();
                }
            }, 1000);
        }
    }
});

// Auto-refresh tickets every 15 seconds
let autoRefreshInterval;
let isAutoRefreshActive = false;

function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    isAutoRefreshActive = true;
    
    autoRefreshInterval = setInterval(() => {
        refreshCounter++;
        refreshTicketsTable();
    }, 15000); // 15 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshActive = false;
    }
}

function refreshTicketsTable() {
    const tbody = document.querySelector('#Zoho-department-tickets-section tbody');
    if (!tbody) return;
    
    console.log('Auto-refresh triggered at:', new Date().toLocaleTimeString());
    
    // Refresh from Zoho API every 12 cycles (every 3 minutes) to avoid timeout issues
    const refreshFromZoho = refreshCounter % 12 === 0;
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 120000);
    
    fetch(`/api/zoho/department/${currentDepartmentId}/tickets?per_page=20&page=${currentPage}&refresh=${refreshFromZoho}`, {
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
        return response.json();
    })
    .then(data => {
        if (refreshFromZoho) {
            console.log('✅ Refreshed from Zoho API and saved to cache');
        } else {
            console.log('📋 Using cached data');
        }
        if (data.success && data.tickets && data.tickets.data) {
            console.log('Updating with', data.tickets.data.length, 'tickets');
            // updateTableRows function would be implemented here
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

// Start auto-refresh when page is loaded and visible
document.addEventListener('DOMContentLoaded', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Handle visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Function to view ticket details
function viewTicketDetails(ticketId) {
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    modal.show();
    loadTicketDetails(ticketId);
}

// Function to load ticket details
function loadTicketDetails(ticketId) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">جاري تحميل تفاصيل التذكرة...</p>
        </div>
    `;
    
    fetch(`/api/zoho/ticket-full/${ticketId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                displayTicketFullDetails(data.data);
            } else {
                fetch(`/api/zoho/ticket-cache/${ticketId}`)
                    .then(response => response.json())
                    .then(cacheData => {
                        if (cacheData.success) {
                            displayTicketDetails(cacheData.data);
                        } else {
                            contentDiv.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    خطأ في تحميل تفاصيل التذكرة: ${cacheData.error || 'خطأ غير معروف'}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading ticket from cache:', error);
                        contentDiv.innerHTML = `<div class="alert alert-danger">خطأ في الاتصال بالخادم</div>`;
                    });
            }
        })
        .catch(error => {
            console.error('Error loading ticket:', error);
            contentDiv.innerHTML = `<div class="alert alert-danger">خطأ في تحميل التفاصيل</div>`;
        });
}

// Function to display full ticket details
function displayTicketFullDetails(fullData) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    const ticket = fullData.ticket || {};
    const threads = fullData.threads || [];
    
    const statusColors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary',
        'Resolved': 'primary'
    };
    const statusColor = statusColors[ticket.status] || 'secondary';
    
    let threadsHTML = '';
    if (threads && threads.length > 0) {
        threadsHTML = `<h6>المحادثات (${threads.length}):</h6><div class="accordion" id="fullTicketThreads">`;
        threads.forEach((thread, index) => {
            const content = thread.content || thread.summary || 'لا يوجد محتوى';
            threadsHTML += `
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}">محادثة ${index + 1}</button></h2>
                    <div id="collapse${index}" class="accordion-collapse collapse"><div class="accordion-body">${content}</div></div>
                </div>
            `;
        });
        threadsHTML += '</div>';
    }
    
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6"><h6>رقم التذكرة</h6><p><code>${ticket.ticketNumber || ticket.id}</code></p></div>
            <div class="col-md-6"><h6>الحالة</h6><p><span class="badge bg-${statusColor}">${ticket.status}</span></p></div>
        </div>
        <div class="row"><div class="col-12"><h6>الموضوع</h6><p>${ticket.subject || 'بدون موضوع'}</p></div></div>
        ${threadsHTML ? `<div class="row mt-3"><div class="col-12">${threadsHTML}</div></div>` : ''}
    `;
}

// Function to display ticket details from cache
function displayTicketDetails(ticket) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    const statusColors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary'
    };
    const statusColor = statusColors[ticket.status] || 'secondary';
    
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6"><h6>Ticket Number</h6><p>${ticket.ticketNumber}</p></div>
            <div class="col-md-6"><h6>Status</h6><p><span class="badge bg-${statusColor}">${ticket.status}</span></p></div>
        </div>
        <div class="row"><div class="col-12"><h6>Subject</h6><p>${ticket.subject}</p></div></div>
    `;
}
</script>
@endpush
