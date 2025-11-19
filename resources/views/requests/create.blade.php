@extends('layouts.app')

@section('title', 'إضافة طلب - نظام إدارة الموظفين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus me-2"></i>إضافة طلب جديد</h2>
    <a href="{{ route('requests.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('requests.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="employee_id" class="form-label">الموظف <span class="text-danger">*</span></label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                        <option value="">اختر الموظف</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('employee_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->department->name ?? 'غير محدد' }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="requested_by" class="form-label">مقدم الطلب <span class="text-danger">*</span></label>
                    <select class="form-select @error('requested_by') is-invalid @enderror" id="requested_by" name="requested_by" required>
                        <option value="">اختر مقدم الطلب</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('requested_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->department->name ?? 'غير محدد' }}
                            </option>
                        @endforeach
                    </select>
                    @error('requested_by')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="manager_id" class="form-label">المدير المسؤول <span class="text-danger">*</span></label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id" required>
                        <option value="">اختر المدير</option>
                        @foreach($users->where('role', 'manager') as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->department->name ?? 'غير محدد' }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">اختر الحالة</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>موافق عليه</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="title" class="form-label">عنوان الطلب <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="description" class="form-label">وصف الطلب <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>حفظ
                </button>
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
