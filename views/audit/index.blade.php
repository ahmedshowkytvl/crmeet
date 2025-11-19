@extends('layouts.app')

@section('title', 'إدارة العمليات - EET Global Management System')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-list me-2"></i>
                        إدارة العمليات
                    </h1>
                    <p class="text-muted mb-0">مراقبة وتحليل جميع العمليات في النظام</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="toggleAutoRefresh()">
                        <i class="fas fa-sync-alt me-1"></i>
                        <span id="autoRefreshText">تفعيل التحديث التلقائي</span>
                    </button>
                    <a href="{{ route('audit.export', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>
                        تصدير البيانات
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي العمليات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_actions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                العمليات الناجحة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['successful_actions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                العمليات الفاشلة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['failed_actions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                أكثر المستخدمين نشاطاً
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['most_active_user']->user_name ?? 'غير محدد' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>
                فلاتر البحث
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">البحث</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="ابحث في العمليات...">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="user_id" class="form-label">المستخدم</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">جميع المستخدمين</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="action_type" class="form-label">نوع العملية</label>
                        <select class="form-select" id="action_type" name="action_type">
                            <option value="">جميع الأنواع</option>
                            @foreach($actionTypes as $actionType)
                                <option value="{{ $actionType }}" {{ request('action_type') == $actionType ? 'selected' : '' }}>
                                    {{ $actionType }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="module" class="form-label">الوحدة</label>
                        <select class="form-select" id="module" name="module">
                            <option value="">جميع الوحدات</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                    {{ $module }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">الحالة</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">جميع الحالات</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>نجح</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشل</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                تطبيق الفلاتر
                            </button>
                            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                إعادة تعيين
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>
                سجل العمليات
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="auditTable">
                    <thead class="table-dark">
                        <tr>
                            <th>التاريخ والوقت</th>
                            <th>اسم المستخدم</th>
                            <th>الدور</th>
                            <th>نوع العملية</th>
                            <th>الوحدة</th>
                            <th>معرف السجل</th>
                            <th>اسم السجل</th>
                            <th>عنوان IP</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="text-nowrap">
                                        {{ $log->created_at->format('Y-m-d') }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ $log->created_at->format('H:i:s') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ substr($log->user_name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $log->user_name ?? 'غير محدد' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $log->user_role ?? 'غير محدد' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $log->formatted_action_type }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->formatted_module }}</span>
                                </td>
                                <td>
                                    @if($log->record_id)
                                        <span class="text-muted">#{{ $log->record_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $log->record_name }}">
                                        {{ $log->record_name ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $log->status_badge_class }}">
                                        {{ $log->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewLogDetails({{ $log->id }})"
                                            title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>لا توجد عمليات مسجلة</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    تفاصيل العملية
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let autoRefreshInterval = null;
let isAutoRefreshEnabled = false;

function toggleAutoRefresh() {
    if (isAutoRefreshEnabled) {
        clearInterval(autoRefreshInterval);
        document.getElementById('autoRefreshText').textContent = 'تفعيل التحديث التلقائي';
        isAutoRefreshEnabled = false;
    } else {
        autoRefreshInterval = setInterval(function() {
            location.reload();
        }, 30000); // Refresh every 30 seconds
        document.getElementById('autoRefreshText').textContent = 'إيقاف التحديث التلقائي';
        isAutoRefreshEnabled = true;
    }
}

function viewLogDetails(logId) {
    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    const content = document.getElementById('logDetailsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">جاري التحميل...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch log details
    fetch(`/audit/${logId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const log = data.data;
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">معلومات المستخدم</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>اسم المستخدم:</strong></td>
                                    <td>${log.user_name || 'غير محدد'}</td>
                                </tr>
                                <tr>
                                    <td><strong>الدور:</strong></td>
                                    <td>${log.user_role || 'غير محدد'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">معلومات العملية</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>نوع العملية:</strong></td>
                                    <td><span class="badge bg-primary">${log.action_type}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>الوحدة:</strong></td>
                                    <td><span class="badge bg-secondary">${log.module}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td><span class="badge ${log.status_class}">${log.status}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-primary">معلومات السجل</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>معرف السجل:</strong></td>
                                    <td>${log.record_id || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>اسم السجل:</strong></td>
                                    <td>${log.record_name || '-'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">معلومات الشبكة</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>عنوان IP:</strong></td>
                                    <td>${log.ip_address || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>معلومات الجهاز:</strong></td>
                                    <td>${log.device_info || '-'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary">التفاصيل</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0">${JSON.stringify(log.details, null, 2)}</pre>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary">التوقيت</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>التاريخ والوقت:</strong></td>
                                    <td>${log.created_at}</td>
                                </tr>
                                <tr>
                                    <td><strong>منذ:</strong></td>
                                    <td>${log.created_at_formatted}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        حدث خطأ في تحميل التفاصيل
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    حدث خطأ في الاتصال
                </div>
            `;
        });
}

// Auto-refresh on page load if enabled
document.addEventListener('DOMContentLoaded', function() {
    // Check if auto-refresh was enabled in a previous session
    if (localStorage.getItem('autoRefreshEnabled') === 'true') {
        toggleAutoRefresh();
    }
});

// Save auto-refresh state
window.addEventListener('beforeunload', function() {
    localStorage.setItem('autoRefreshEnabled', isAutoRefreshEnabled);
});
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.badge-success {
    background-color: #28a745 !important;
}

.badge-danger {
    background-color: #dc3545 !important;
}

.table th {
    background-color: #343a40;
    color: white;
    border: none;
}

.table td {
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}
</style>
@endpush


