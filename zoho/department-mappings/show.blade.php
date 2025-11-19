@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('zoho.dashboard') }}">Zoho</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('zoho.department-mappings.index') }}">إدارة الأقسام</a></li>
                        <li class="breadcrumb-item active">عرض تفاصيل Mapping</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-eye me-2"></i>
                    {{ __('messages.view_department_mapping') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        تفاصيل الـ Mapping
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Zoho Department ID:</label>
                            <div class="bg-light p-2 rounded">
                                <code class="text-primary">{{ $departmentMapping->zoho_department_id }}</code>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Zoho Department Name:</label>
                            <div class="bg-light p-2 rounded">
                                <strong>{{ $departmentMapping->zoho_department_name }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Local Department:</label>
                            <div class="bg-light p-2 rounded">
                                <span class="badge bg-info fs-6">
                                    {{ $departmentMapping->local_department_name }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الحالة:</label>
                            <div class="bg-light p-2 rounded">
                                @if($departmentMapping->is_active)
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check me-1"></i>
                                        مفعل
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6">
                                        <i class="fas fa-pause me-1"></i>
                                        معطل
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($departmentMapping->description)
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">الوصف:</label>
                            <div class="bg-light p-3 rounded">
                                {{ $departmentMapping->description }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الإنشاء:</label>
                            <div class="bg-light p-2 rounded">
                                <i class="fas fa-calendar me-2"></i>
                                {{ $departmentMapping->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">آخر تحديث:</label>
                            <div class="bg-light p-2 rounded">
                                <i class="fas fa-clock me-2"></i>
                                {{ $departmentMapping->updated_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        الإجراءات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('zoho.department-mappings.edit', $departmentMapping) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            تعديل الـ Mapping
                        </a>

                        <form action="{{ route('zoho.department-mappings.toggle-active', $departmentMapping) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-{{ $departmentMapping->is_active ? 'warning' : 'success' }} w-100">
                                <i class="fas fa-{{ $departmentMapping->is_active ? 'pause' : 'play' }} me-2"></i>
                                {{ $departmentMapping->is_active ? 'تعطيل' : 'تفعيل' }} الـ Mapping
                            </button>
                        </form>

                        <button type="button" 
                                class="btn btn-danger" 
                                onclick="confirmDelete()">
                            <i class="fas fa-trash me-2"></i>
                            حذف الـ Mapping
                        </button>

                        <a href="{{ route('zoho.department-mappings.index') }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-2">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-muted mb-1">التذاكر المرتبطة</h6>
                                <h4 class="text-primary mb-0">
                                    {{ \App\Models\ZohoTicketCache::where('department_id', $departmentMapping->local_department_id)->count() }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من أنك تريد حذف هذا الـ mapping؟</p>
                <div class="alert alert-warning">
                    <strong>تحذير:</strong> هذا الإجراء لا يمكن التراجع عنه!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('zoho.department-mappings.destroy', $departmentMapping) }}" 
                      method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        حذف نهائي
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete() {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection
