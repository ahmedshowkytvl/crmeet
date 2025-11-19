@extends('layouts.app')

@section('title', __('messages.add_department') . ' - ' . __('messages.system_title'))

@section('content')
@php
    $trans = function($key) {
        return __('messages.' . $key);
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title"><i class="fas fa-plus me-2"></i>{{ $trans('add_department') }}</h2>
    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>{{ $trans('back_to_list') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('departments.store') }}" method="POST">
            @csrf
            <div class="row">
                <!-- اسم القسم بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ $trans('department_name_en') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- اسم القسم بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="name_ar" class="form-label">{{ $trans('department_name_ar') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                    @error('name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- مدير القسم -->
                <div class="col-md-6 mb-3">
                    <label for="manager_id" class="form-label">{{ $trans('department_manager') }}</label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                        <option value="">{{ $trans('select_manager') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->department->name ?? $trans('not_specified') }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                
                <!-- وصف القسم بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">{{ $trans('department_description_en') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- وصف القسم بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="description_ar" class="form-label">{{ $trans('department_description_ar') }}</label>
                    <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="4">{{ old('description_ar') }}</textarea>
                    @error('description_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                
                <!-- Zoho IDs -->
                <div class="col-md-6 mb-3">
                    <label for="zoho_id" class="form-label">{{ $trans('zoho_ids') }}</label>
                    <textarea class="form-control @error('zoho_id') is-invalid @enderror" 
                              id="zoho_id" 
                              name="zoho_id" 
                              rows="3" 
                              placeholder="{{ $trans('zoho_id_placeholder') }}">{{ old('zoho_id') }}</textarea>
                    <div class="form-text text-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ $trans('zoho_id_note') }}
                    </div>
                    @error('zoho_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
            </div>
            
            <!-- معاينة القسم -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-eye me-2"></i>{{ $trans('preview') }}
                    </h5>
                    
                    <!-- معاينة باللغة الإنجليزية -->
                    <div class="card mb-3" id="department-preview-en">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-flag me-2"></i>{{ $trans('english_preview') }}</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-2" id="preview-name-en">{{ $trans('department_name') }}</h5>
                            <p class="text-muted mb-0" id="preview-description-en">{{ $trans('department_description') }}</p>
                        </div>
                    </div>
                    
                    <!-- معاينة باللغة العربية -->
                    <div class="card" id="department-preview-ar" dir="rtl">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-flag me-2"></i>{{ $trans('arabic_preview') }}</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-2" id="preview-name-ar">{{ $trans('department_name') }}</h5>
                            <p class="text-muted mb-0" id="preview-description-ar">{{ $trans('department_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>{{ $trans('save') }}
                </button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>{{ $trans('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const nameArInput = document.getElementById('name_ar');
    const descriptionInput = document.getElementById('description');
    const descriptionArInput = document.getElementById('description_ar');
    const zohoIdInput = document.getElementById('zoho_id');
    
    // تحديث الاسم بالإنجليزية
    nameInput.addEventListener('input', function() {
        document.getElementById('preview-name-en').textContent = this.value || '{{ $trans("department_name") }}';
    });
    
    // تحديث الاسم بالعربية
    nameArInput.addEventListener('input', function() {
        document.getElementById('preview-name-ar').textContent = this.value || '{{ $trans("department_name") }}';
    });
    
    // تحديث الوصف بالإنجليزية
    descriptionInput.addEventListener('input', function() {
        document.getElementById('preview-description-en').textContent = this.value || '{{ $trans("department_description") }}';
    });
    
    // تحديث الوصف بالعربية
    descriptionArInput.addEventListener('input', function() {
        document.getElementById('preview-description-ar').textContent = this.value || '{{ $trans("department_description") }}';
    });
    
    // التحقق من صحة Zoho IDs (أرقام فقط)
    zohoIdInput.addEventListener('input', function() {
        const value = this.value;
        const ids = value.split(',').map(id => id.trim()).filter(id => id);
        
        let isValid = true;
        for (let id of ids) {
            if (id && !/^\d+$/.test(id)) {
                isValid = false;
                break;
            }
        }
        
        if (value && !isValid) {
            this.classList.add('is-invalid');
            let errorDiv = this.parentNode.querySelector('.zoho-id-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'zoho-id-error invalid-feedback';
                errorDiv.textContent = '{{ $trans("zoho_id_numeric_error") }}';
                this.parentNode.appendChild(errorDiv);
            }
        } else {
            this.classList.remove('is-invalid');
            const errorDiv = this.parentNode.querySelector('.zoho-id-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    });
});
</script>
@endpush
@endsection
