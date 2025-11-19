@extends('layouts.app')

@section('title', 'إدارة حركة الأصول')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">إدارة حركة الأصول</h1>
                    <p class="text-muted mb-0">تتبع وإدارة حركة الأصول في المخازن</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="warehouse_id" class="form-label">المخزن</label>
                            <select class="form-select" id="warehouse_id" name="warehouse_id">
                                <option value="">جميع المخازن</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="availability_status" class="form-label">حالة التوفر</label>
                            <select class="form-select" id="availability_status" name="availability_status">
                                <option value="">جميع الحالات</option>
                                <option value="available" {{ request('availability_status') == 'available' ? 'selected' : '' }}>متاح في المخزن</option>
                                <option value="checked_out" {{ request('availability_status') == 'checked_out' ? 'selected' : '' }}>مع موظف</option>
                                <option value="in_use" {{ request('availability_status') == 'in_use' ? 'selected' : '' }}>غير متاح مؤقتاً</option>
                                <option value="maintenance" {{ request('availability_status') == 'maintenance' ? 'selected' : '' }}>في الصيانة</option>
                                <option value="disposed" {{ request('availability_status') == 'disposed' ? 'selected' : '' }}>تم التصرف فيه</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="cabinet_id" class="form-label">الدولاب</label>
                            <select class="form-select" id="cabinet_id" name="cabinet_id">
                                <option value="">جميع الدولاب</option>
                                @foreach($cabinets as $cabinet)
                                    <option value="{{ $cabinet->id }}" {{ request('cabinet_id') == $cabinet->id ? 'selected' : '' }}>
                                        {{ $cabinet->warehouse->display_name ?? 'مخزن' }} - دولاب {{ $cabinet->cabinet_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">البحث</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="كود الأصل أو الاسم...">
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                                <a href="{{ route('assets.movement.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> مسح
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assets List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">قائمة الأصول</h5>
                </div>
                <div class="card-body">
                    @if($assets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>كود الأصل</th>
                                        <th>الاسم</th>
                                        <th>الفئة</th>
                                        <th>الموقع الحالي</th>
                                        <th>حالة التوفر</th>
                                        <th>آخر حركة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assets as $asset)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $asset->asset_code }}</strong>
                                                @if($asset->serial_number)
                                                    <br><small class="text-muted">{{ $asset->serial_number }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $asset->display_name }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $asset->category->display_name ?? 'غير محدد' }}
                                                </span>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
                                                @switch($asset->current_availability_status)
                                                    @case('available')
                                                        <span class="badge bg-success">متاح في المخزن</span>
                                                        @break
                                                    @case('checked_out')
                                                        <span class="badge bg-warning">مع موظف</span>
                                                        @break
                                                    @case('in_use')
                                                        <span class="badge bg-info">غير متاح مؤقتاً</span>
                                                        @break
                                                    @case('maintenance')
                                                        <span class="badge bg-danger">في الصيانة</span>
                                                        @break
                                                    @case('disposed')
                                                        <span class="badge bg-secondary">تم التصرف فيه</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-muted">غير محدد</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($asset->last_movement_at)
                                                    <i class="fas fa-clock text-muted"></i>
                                                    {{ $asset->last_movement_at->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('assets.movement.show', $asset) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="عرض السجل">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                    
                                                    @if($asset->current_availability_status === 'available')
                                                        <a href="{{ route('assets.movement.checkout', $asset) }}" 
                                                           class="btn btn-sm btn-outline-warning" title="تسليم لموظف">
                                                            <i class="fas fa-user-check"></i>
                                                        </a>
                                                        <a href="{{ route('assets.movement.move', $asset) }}" 
                                                           class="btn btn-sm btn-outline-info" title="نقل">
                                                            <i class="fas fa-arrows-alt"></i>
                                                        </a>
                                                    @elseif($asset->current_availability_status === 'checked_out')
                                                        <a href="{{ route('assets.movement.return', $asset) }}" 
                                                           class="btn btn-sm btn-outline-success" title="إرجاع للمخزن">
                                                            <i class="fas fa-undo"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="setMaintenance({{ $asset->id }})" title="وضع في الصيانة">
                                                        <i class="fas fa-tools"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $assets->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد أصول</h5>
                            <p class="text-muted">لا توجد أصول تطابق معايير البحث</p>
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

@push('scripts')
<script>
let currentAssetId = null;

function setMaintenance(assetId) {
    currentAssetId = assetId;
    $('#maintenanceModal').modal('show');
}

$('#maintenanceForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!currentAssetId) return;
    
    const formData = new FormData(this);
    const notes = formData.get('notes');
    
    fetch(`/assets/movement/${currentAssetId}/maintenance`, {
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
