@extends('layouts.app')

@section('title', 'التكامل مع Snipe-IT')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">التكامل مع Snipe-IT</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-plug me-2"></i>
                    التكامل مع Snipe-IT
                </h4>
            </div>
        </div>
    </div>

    <!-- إحصائيات التكامل -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="إجمالي الأصول">إجمالي الأصول</h5>
                            <h3 class="my-2 py-1">{{ $stats['total_assets'] ?? 0 }}</h3>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="fas fa-laptop text-primary" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="الأصول المزامنة">الأصول المزامنة</h5>
                            <h3 class="my-2 py-1">{{ $stats['synced_assets'] ?? 0 }}</h3>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="fas fa-sync text-success" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="نسبة المزامنة">نسبة المزامنة</h5>
                            <h3 class="my-2 py-1">{{ $stats['sync_percentage'] ?? 0 }}%</h3>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="fas fa-percentage text-info" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="حالة الاتصال">حالة الاتصال</h5>
                            <h3 class="my-2 py-1">
                                <span class="badge {{ ($stats['connection_status'] ?? false) ? 'badge-success' : 'badge-danger' }}">
                                    {{ ($stats['connection_status'] ?? false) ? 'متصل' : 'غير متصل' }}
                                </span>
                            </h3>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="fas fa-wifi text-{{ ($stats['connection_status'] ?? false) ? 'success' : 'danger' }}" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أزرار التحكم -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        عمليات المزامنة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-outline-primary w-100" id="testConnectionBtn">
                                <i class="fas fa-wifi me-2"></i>
                                اختبار الاتصال
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-outline-success w-100" id="syncAssetsBtn">
                                <i class="fas fa-sync me-2"></i>
                                مزامنة الأصول
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-outline-info w-100" id="syncUsersBtn">
                                <i class="fas fa-users me-2"></i>
                                مزامنة المستخدمين
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-outline-warning w-100" id="syncCategoriesBtn">
                                <i class="fas fa-tags me-2"></i>
                                مزامنة الفئات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- آخر عمليات المزامنة -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر عمليات المزامنة
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="refreshLogsBtn">
                        <i class="fas fa-refresh me-1"></i>
                        تحديث
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="syncLogsTable">
                            <thead>
                                <tr>
                                    <th>النوع</th>
                                    <th>نوع المزامنة</th>
                                    <th>الحالة</th>
                                    <th>عدد المزامنة</th>
                                    <th>المدة</th>
                                    <th>تاريخ البدء</th>
                                    <th>المستخدم</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSyncs as $sync)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">{{ $sync['type_display'] ?? $sync['type'] }}</span>
                                    </td>
                                    <td>{{ $sync['sync_type_display'] ?? $sync['sync_type'] }}</td>
                                    <td>
                                        <span class="badge {{ $sync['status_badge_class'] ?? 'badge-secondary' }}">
                                            {{ $sync['status_display'] ?? $sync['status'] }}
                                        </span>
                                    </td>
                                    <td>{{ $sync['synced_count'] ?? 0 }}</td>
                                    <td>{{ $sync['formatted_duration'] ?? 'غير محدد' }}</td>
                                    <td>{{ $sync['started_at'] ? \Carbon\Carbon::parse($sync['started_at'])->format('Y-m-d H:i') : '-' }}</td>
                                    <td>{{ $sync['user']['full_name'] ?? 'غير محدد' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewSyncDetails({{ $sync['id'] }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">لا توجد عمليات مزامنة</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal لتفاصيل المزامنة -->
<div class="modal fade" id="syncDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل المزامنة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="syncDetailsContent">
                <!-- سيتم تحميل المحتوى هنا -->
            </div>
        </div>
    </div>
</div>

<!-- Modal لاختيار نوع المزامنة -->
<div class="modal fade" id="syncTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اختيار نوع المزامنة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="syncTypeForm">
                    <div class="mb-3">
                        <label class="form-label">نوع المزامنة</label>
                        <select class="form-select" name="sync_type" required>
                            <option value="incremental">مزامنة تدريجية</option>
                            <option value="full">مزامنة كاملة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">معرفات الأصول (اختياري)</label>
                        <textarea class="form-control" name="asset_ids" rows="3" placeholder="أدخل معرفات الأصول مفصولة بفواصل"></textarea>
                        <small class="form-text text-muted">اترك فارغاً لمزامنة جميع الأصول</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="confirmSyncBtn">تأكيد المزامنة</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentSyncType = '';

$(document).ready(function() {
    // اختبار الاتصال
    $('#testConnectionBtn').click(function() {
        testConnection();
    });

    // مزامنة الأصول
    $('#syncAssetsBtn').click(function() {
        currentSyncType = 'assets';
        $('#syncTypeModal').modal('show');
    });

    // مزامنة المستخدمين
    $('#syncUsersBtn').click(function() {
        syncUsers();
    });

    // مزامنة الفئات
    $('#syncCategoriesBtn').click(function() {
        syncCategories();
    });

    // تأكيد المزامنة
    $('#confirmSyncBtn').click(function() {
        const formData = new FormData(document.getElementById('syncTypeForm'));
        const syncType = formData.get('sync_type');
        const assetIds = formData.get('asset_ids') ? formData.get('asset_ids').split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id)) : [];

        $('#syncTypeModal').modal('hide');
        
        if (currentSyncType === 'assets') {
            syncAssets(syncType, assetIds);
        }
    });

    // تحديث السجلات
    $('#refreshLogsBtn').click(function() {
        location.reload();
    });
});

function testConnection() {
    showLoading('جاري اختبار الاتصال...');
    
    $.ajax({
        url: '{{ route("snipe-it.test-connection") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess('تم اختبار الاتصال بنجاح');
                // تحديث حالة الاتصال في الإحصائيات
                location.reload();
            } else {
                showError(response.message || 'فشل في اختبار الاتصال');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            showError(response?.message || 'حدث خطأ في اختبار الاتصال');
        }
    });
}

function syncAssets(syncType = 'incremental', assetIds = []) {
    showLoading('جاري مزامنة الأصول...');
    
    $.ajax({
        url: '{{ route("snipe-it.sync-assets") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            sync_type: syncType,
            asset_ids: assetIds
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(`تمت المزامنة بنجاح: ${response.data.synced_count} أصل`);
                location.reload();
            } else {
                showError(response.message || 'فشل في المزامنة');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            showError(response?.message || 'حدث خطأ في المزامنة');
        }
    });
}

function syncUsers() {
    showLoading('جاري مزامنة المستخدمين...');
    
    $.ajax({
        url: '{{ route("snipe-it.sync-users") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(`تمت مزامنة المستخدمين بنجاح: ${response.data.synced_count} مستخدم`);
                location.reload();
            } else {
                showError(response.message || 'فشل في مزامنة المستخدمين');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            showError(response?.message || 'حدث خطأ في مزامنة المستخدمين');
        }
    });
}

function syncCategories() {
    showLoading('جاري مزامنة الفئات...');
    
    $.ajax({
        url: '{{ route("snipe-it.sync-categories") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess(`تمت مزامنة الفئات بنجاح: ${response.data.synced_count} فئة`);
                location.reload();
            } else {
                showError(response.message || 'فشل في مزامنة الفئات');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            showError(response?.message || 'حدث خطأ في مزامنة الفئات');
        }
    });
}

function viewSyncDetails(syncId) {
    // يمكن إضافة منطق لعرض تفاصيل المزامنة
    showInfo('تفاصيل المزامنة ستكون متاحة قريباً');
}

function showLoading(message) {
    // إظهار مؤشر التحميل
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

function hideLoading() {
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'نجح!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'خطأ!',
            text: message
        });
    } else {
        alert(message);
    }
}

function showInfo(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'معلومات',
            text: message
        });
    } else {
        alert(message);
    }
}
</script>
@endpush
