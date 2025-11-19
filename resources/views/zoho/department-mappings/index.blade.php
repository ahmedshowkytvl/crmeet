@extends('layouts.app')

@section('title', __('messages.zoho_department_mappings'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        {{ __('messages.zoho_department_mappings') }}
                    </h2>
                    <p class="text-muted mb-0">إدارة ربط أقسام Zoho بالأقسام المحلية</p>
                </div>
                <div>
                    <a href="{{ route('zoho.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للوحة التحكم
                    </a>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                        <i class="fas fa-upload me-2"></i>
                        تحديث مجمع
                    </button>
                    <a href="{{ route('zoho.department-mappings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        إضافة Mapping جديد
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-exchange-alt text-primary fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $mappings->count() }}</h4>
                    <small class="text-muted">إجمالي Mappings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $mappings->where('is_active', true)->count() }}</h4>
                    <small class="text-muted">Mappings مفعلة</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-pause-circle text-warning fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $mappings->where('is_active', false)->count() }}</h4>
                    <small class="text-muted">Mappings معطلة</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="fas fa-building text-info fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $departments->count() }}</h4>
                    <small class="text-muted">الأقسام المحلية</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Mappings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        قائمة {{ __('messages.zoho_department_mappings') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Zoho Department ID</th>
                                    <th>Zoho Department Name</th>
                                    <th>Local Department</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mappings as $mapping)
                                <tr>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">{{ $mapping->zoho_department_id }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $mapping->zoho_department_name }}</strong>
                                        @if($mapping->description)
                                            <br><small class="text-muted">{{ $mapping->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $mapping->local_department_name }}</span>
                                    </td>
                                    <td>
                                        @if($mapping->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                مفعل
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-pause me-1"></i>
                                                معطل
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $mapping->created_at->format('Y-m-d H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('zoho.department-mappings.show', $mapping) }}" class="btn btn-outline-info" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('zoho.department-mappings.edit', $mapping) }}" class="btn btn-outline-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('zoho.department-mappings.toggle-active', $mapping) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $mapping->is_active ? 'warning' : 'success' }}" title="{{ $mapping->is_active ? 'تعطيل' : 'تفعيل' }}">
                                                    <i class="fas fa-{{ $mapping->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('zoho.department-mappings.destroy', $mapping) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الـ mapping؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                                        <p>لا توجد mappings</p>
                                        <a href="{{ route('zoho.department-mappings.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>
                                            إضافة Mapping جديد
                                        </a>
                                    </td>
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

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تحديث Mappings مجمع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('zoho.department-mappings.bulk-update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        يمكنك إضافة أو تحديث عدة mappings دفعة واحدة. استخدم التنسيق التالي:
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mappings (JSON Format)</label>
                        <textarea class="form-control" name="mappings_json" rows="15" placeholder='[
  {
    "zoho_department_id": "766285000006092035",
    "zoho_department_name": "All Suppliers-Hotels",
    "local_department_id": 6,
    "description": "Mapping for All Suppliers-Hotels"
  },
  {
    "zoho_department_id": "766285000021972052",
    "zoho_department_name": "Contracting - EET Global",
    "local_department_id": 12,
    "description": "Mapping for Contracting - EET Global"
  }
]'></textarea>
                        <div class="form-text">
                            <strong>Local Department IDs:</strong><br>
                            @foreach($departments as $dept)
                                <span class="badge bg-light text-dark me-1">{{ $dept->id }}: {{ $dept->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تحديث Mappings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
