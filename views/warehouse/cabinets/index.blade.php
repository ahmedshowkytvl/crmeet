@extends('layouts.app')

@section('title', 'إدارة الدولاب والرفوف')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">إدارة الدولاب والرفوف</h1>
                    <p class="text-muted mb-0">إدارة تنظيم المخازن والدولاب والرفوف</p>
                </div>
                <div>
                    <a href="{{ route('warehouse.cabinets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة دولاب جديد
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label for="search" class="form-label">البحث</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="رقم الدولاب أو الاسم...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                                <a href="{{ route('warehouse.cabinets.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> مسح
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cabinets List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">قائمة الدولاب</h5>
                </div>
                <div class="card-body">
                    @if($cabinets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>رقم الدولاب</th>
                                        <th>الاسم</th>
                                        <th>المخزن</th>
                                        <th>الموقع</th>
                                        <th>عدد الرفوف</th>
                                        <th>الاستخدام</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cabinets as $cabinet)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $cabinet->cabinet_number }}</strong>
                                            </td>
                                            <td>{{ $cabinet->display_name }}</td>
                                            <td>
                                                <i class="fas fa-warehouse text-muted"></i>
                                                {{ $cabinet->warehouse->display_name ?? 'غير محدد' }}
                                            </td>
                                            <td>
                                                @if($cabinet->location_in_warehouse)
                                                    <i class="fas fa-map-marker-alt text-muted"></i>
                                                    {{ $cabinet->location_in_warehouse }}
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $cabinet->shelves->count() }} / {{ $cabinet->total_shelves }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $cabinet->getUsagePercentage() }}%"
                                                         aria-valuenow="{{ $cabinet->getUsagePercentage() }}" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ number_format($cabinet->getUsagePercentage(), 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($cabinet->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('warehouse.cabinets.show', $cabinet) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('warehouse.cabinets.shelves', $cabinet) }}" 
                                                       class="btn btn-sm btn-outline-info" title="إدارة الرفوف">
                                                        <i class="fas fa-layer-group"></i>
                                                    </a>
                                                    <a href="{{ route('warehouse.cabinets.edit', $cabinet) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteCabinet({{ $cabinet->id }})" title="حذف">
                                                        <i class="fas fa-trash"></i>
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
                            {{ $cabinets->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد دولاب</h5>
                            <p class="text-muted">ابدأ بإضافة دولاب جديد لإدارة المخازن</p>
                            <a href="{{ route('warehouse.cabinets.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة دولاب جديد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف هذا الدولاب؟</p>
                <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteCabinet(cabinetId) {
    const form = document.getElementById('deleteForm');
    form.action = `/assets/cabinets/${cabinetId}`;
    $('#deleteModal').modal('show');
}
</script>
@endpush
