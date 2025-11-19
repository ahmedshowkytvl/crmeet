@extends('layouts.app')

@section('title', 'سجل حركة الأصل - ' . $asset->display_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">سجل حركة الأصل</h1>
                    <p class="text-muted mb-0">{{ $asset->asset_code }} - {{ $asset->display_name }}</p>
                </div>
                <div>
                    <a href="{{ route('assets.movement.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للقائمة
                    </a>
                </div>
            </div>

            <!-- Asset Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">معلومات الأصل</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>كود الأصل:</strong><br>
                            <span class="text-primary">{{ $asset->asset_code }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>الاسم:</strong><br>
                            {{ $asset->display_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>الفئة:</strong><br>
                            {{ $asset->category->display_name ?? 'غير محدد' }}
                        </div>
                        <div class="col-md-3">
                            <strong>الرقم التسلسلي:</strong><br>
                            {{ $asset->serial_number ?? 'غير محدد' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">الوضع الحالي</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @switch($asset->current_availability_status)
                                        @case('available')
                                            <i class="fas fa-check-circle text-success fa-2x"></i>
                                            @break
                                        @case('checked_out')
                                            <i class="fas fa-user-check text-warning fa-2x"></i>
                                            @break
                                        @case('in_use')
                                            <i class="fas fa-clock text-info fa-2x"></i>
                                            @break
                                        @case('maintenance')
                                            <i class="fas fa-tools text-danger fa-2x"></i>
                                            @break
                                        @case('disposed')
                                            <i class="fas fa-trash text-secondary fa-2x"></i>
                                            @break
                                        @default
                                            <i class="fas fa-question-circle text-muted fa-2x"></i>
                                    @endswitch
                                </div>
                                <div>
                                    <h6 class="mb-1">حالة التوفر</h6>
                                    <span class="badge bg-{{ $asset->current_availability_status === 'available' ? 'success' : ($asset->current_availability_status === 'checked_out' ? 'warning' : ($asset->current_availability_status === 'maintenance' ? 'danger' : 'secondary')) }}">
                                        @switch($asset->current_availability_status)
                                            @case('available') متاح في المخزن @break
                                            @case('checked_out') مع موظف @break
                                            @case('in_use') غير متاح مؤقتاً @break
                                            @case('maintenance') في الصيانة @break
                                            @case('disposed') تم التصرف فيه @break
                                            @default غير محدد
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1">الموقع الحالي</h6>
                            <p class="mb-0">
                                @if($asset->currentCabinet && $asset->currentShelf)
                                    <i class="fas fa-warehouse text-primary"></i>
                                    {{ $asset->currentCabinet->warehouse->display_name ?? 'مخزن' }} - 
                                    دولاب {{ $asset->currentCabinet->cabinet_number }} - 
                                    رف {{ $asset->currentShelf->shelf_code }}
                                @elseif($asset->assignedTo)
                                    <i class="fas fa-user text-warning"></i>
                                    مع {{ $asset->assignedTo->name }}
                                @else
                                    <i class="fas fa-question-circle text-muted"></i>
                                    {{ $asset->current_location_description ?? 'غير محدد' }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1">آخر حركة</h6>
                            <p class="mb-0">
                                <i class="fas fa-clock text-muted"></i>
                                {{ $asset->last_movement_at ? $asset->last_movement_at->format('d/m/Y H:i') : 'غير محدد' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if($asset->current_availability_status === 'available')
                            <a href="{{ route('assets.movement.checkout', $asset) }}" class="btn btn-warning">
                                <i class="fas fa-user-check"></i> تسليم لموظف
                            </a>
                            <a href="{{ route('assets.movement.move', $asset) }}" class="btn btn-info">
                                <i class="fas fa-arrows-alt"></i> نقل
                            </a>
                        @elseif($asset->current_availability_status === 'checked_out')
                            <a href="{{ route('assets.movement.return', $asset) }}" class="btn btn-success">
                                <i class="fas fa-undo"></i> إرجاع للمخزن
                            </a>
                        @endif
                        
                        <button type="button" class="btn btn-danger" onclick="setMaintenance({{ $asset->id }})">
                            <i class="fas fa-tools"></i> وضع في الصيانة
                        </button>
                        
                        <a href="{{ route('assets.movement.export-history', $asset) }}" class="btn btn-secondary">
                            <i class="fas fa-download"></i> تصدير السجل
                        </a>
                    </div>
                </div>
            </div>

            <!-- Movement History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">سجل الحركة</h5>
                </div>
                <div class="card-body">
                    @if($movementHistory->count() > 0)
                        <div class="timeline">
                            @foreach($movementHistory as $log)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        @switch($log->action)
                                            @case('stored')
                                                <i class="fas fa-warehouse text-success"></i>
                                                @break
                                            @case('checked_out')
                                                <i class="fas fa-user-check text-warning"></i>
                                                @break
                                            @case('returned')
                                                <i class="fas fa-undo text-info"></i>
                                                @break
                                            @case('moved')
                                                <i class="fas fa-arrows-alt text-primary"></i>
                                                @break
                                            @case('maintenance')
                                                <i class="fas fa-tools text-danger"></i>
                                                @break
                                            @default
                                                <i class="fas fa-circle text-muted"></i>
                                        @endswitch
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $log->action_label }}</h6>
                                                <p class="mb-1 text-muted">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    {{ $log->location_description }}
                                                </p>
                                                @if($log->assignedToUser)
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-user"></i>
                                                        {{ $log->assignedToUser->name }}
                                                    </p>
                                                @endif
                                                @if($log->notes)
                                                    <p class="mb-1">
                                                        <i class="fas fa-sticky-note"></i>
                                                        {{ $log->notes }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    {{ $log->action_timestamp ? $log->action_timestamp->format('d/m/Y H:i') : $log->date }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    بواسطة {{ $log->user->name }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد حركات مسجلة لهذا الأصل</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">وضع الأصل في الصيانة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="maintenanceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="maintenance_notes" class="form-label">ملاحظات الصيانة</label>
                        <textarea class="form-control" id="maintenance_notes" name="notes" rows="3" placeholder="أدخل ملاحظات حول الصيانة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">وضع في الصيانة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 30px;
    height: 30px;
    background: #fff;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 35px;
    width: 2px;
    height: calc(100% + 15px);
    background: #dee2e6;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}
</style>
@endpush

@push('scripts')
<script>
function setMaintenance(assetId) {
    $('#maintenanceModal').modal('show');
}

$('#maintenanceForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const notes = formData.get('notes');
    
    fetch(`{{ route('assets.movement.maintenance', $asset) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('حدث خطأ: ' + (data.message || 'خطأ غير معروف'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
    });
});
</script>
@endpush
