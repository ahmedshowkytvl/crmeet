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
                        <li class="breadcrumb-item"><a href="{{ route('zoho.department-mappings.show', $departmentMapping) }}">عرض التفاصيل</a></li>
                        <li class="breadcrumb-item active">تعديل Mapping</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('messages.edit_department_mapping') }}
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        تعديل بيانات الـ Mapping
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('zoho.department-mappings.update', $departmentMapping) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="zoho_department_id" class="form-label">
                                    Zoho Department ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('zoho_department_id') is-invalid @enderror" 
                                       id="zoho_department_id" 
                                       name="zoho_department_id" 
                                       value="{{ old('zoho_department_id', $departmentMapping->zoho_department_id) }}"
                                       placeholder="مثال: 766285000151839183"
                                       required>
                                @error('zoho_department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">معرف القسم في Zoho Desk</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="zoho_department_name" class="form-label">
                                    Zoho Department Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('zoho_department_name') is-invalid @enderror" 
                                       id="zoho_department_name" 
                                       name="zoho_department_name" 
                                       value="{{ old('zoho_department_name', $departmentMapping->zoho_department_name) }}"
                                       placeholder="مثال: EET Global - Customers"
                                       required>
                                @error('zoho_department_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">اسم القسم في Zoho Desk</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="local_department_id" class="form-label">
                                    Local Department <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('local_department_id') is-invalid @enderror" 
                                        id="local_department_id" 
                                        name="local_department_id" 
                                        required>
                                    <option value="">اختر القسم المحلي</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('local_department_id', $departmentMapping->local_department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('local_department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">القسم المقابل في النظام المحلي</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $departmentMapping->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        مفعل
                                    </label>
                                </div>
                                <div class="form-text">هل يجب أن يكون هذا الـ mapping نشط؟</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="وصف اختياري لهذا الـ mapping">{{ old('description', $departmentMapping->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('zoho.department-mappings.show', $departmentMapping) }}" 
                                           class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            إلغاء
                                        </a>
                                        
                                        <a href="{{ route('zoho.department-mappings.index') }}" 
                                           class="btn btn-outline-secondary">
                                            <i class="fas fa-list me-2"></i>
                                            العودة للقائمة
                                        </a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        حفظ التغييرات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات إضافية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h6 class="text-muted mb-1">تاريخ الإنشاء</h6>
                                <small class="text-primary">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $departmentMapping->created_at->format('Y-m-d H:i:s') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h6 class="text-muted mb-1">آخر تحديث</h6>
                                <small class="text-primary">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $departmentMapping->updated_at->format('Y-m-d H:i:s') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <h6 class="text-muted mb-1">التذاكر المرتبطة</h6>
                                <small class="text-primary">
                                    <i class="fas fa-ticket-alt me-1"></i>
                                    {{ \App\Models\ZohoTicketCache::where('department_id', $departmentMapping->local_department_id)->count() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill local department name
    const localDeptSelect = document.getElementById('local_department_id');
    const localDeptNameInput = document.createElement('input');
    localDeptNameInput.type = 'hidden';
    localDeptNameInput.name = 'local_department_name';
    localDeptNameInput.id = 'local_department_name';
    localDeptSelect.parentNode.appendChild(localDeptNameInput);
    
    localDeptSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            localDeptNameInput.value = selectedOption.text;
        }
    });
    
    // Initialize with current value
    const currentOption = localDeptSelect.options[localDeptSelect.selectedIndex];
    if (currentOption.value) {
        localDeptNameInput.value = currentOption.text;
    }
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const zohoId = document.getElementById('zoho_department_id').value.trim();
        const zohoName = document.getElementById('zoho_department_name').value.trim();
        const localDept = document.getElementById('local_department_id').value;
        
        if (!zohoId || !zohoName || !localDept) {
            e.preventDefault();
            alert('يرجى ملء جميع الحقول المطلوبة');
            return false;
        }
        
        // Confirm changes
        if (!confirm('هل أنت متأكد من حفظ التغييرات؟')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
