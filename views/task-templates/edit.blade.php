@extends('layouts.app')

@section('title', __('messages.edit_template'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('messages.edit_template') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('task-templates.update', $taskTemplate) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.template_name') }} ({{ __('messages.english') }}) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $taskTemplate->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('messages.template_name_ar') }}</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $taskTemplate->name_ar) }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">{{ __('messages.template_department') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('department') is-invalid @enderror" 
                                            id="department" name="department" required>
                                        <option value="">{{ __('messages.select_department') }}</option>
                                        @foreach($departments as $key => $name)
                                            <option value="{{ $key }}" {{ old('department', $taskTemplate->department) == $key ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estimated_time" class="form-label">{{ __('messages.estimated_time_hours') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.001" min="0" class="form-control @error('estimated_time') is-invalid @enderror" 
                                           id="estimated_time" name="estimated_time" value="{{ old('estimated_time', $taskTemplate->estimated_time) }}" required>
                                    @error('estimated_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('messages.time_example') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">الوصف (إنجليزي)</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $taskTemplate->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description_ar" class="form-label">الوصف (عربي)</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" name="description_ar" rows="3">{{ old('description_ar', $taskTemplate->description_ar) }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $taskTemplate->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    قالب نشط
                                </label>
                            </div>
                            <div class="form-text">القوالب النشطة فقط ستظهر في قائمة اختيار القوالب عند إنشاء مهام جديدة</div>
                        </div>

                        <!-- معلومات إضافية -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>معلومات القالب</h6>
                            <ul class="mb-0">
                                <li><strong>تاريخ الإنشاء:</strong> {{ $taskTemplate->created_at->format('Y-m-d H:i') }}</li>
                                <li><strong>آخر تحديث:</strong> {{ $taskTemplate->updated_at->format('Y-m-d H:i') }}</li>
                                <li><strong>عدد الاستخدامات:</strong> {{ $taskTemplate->usage_count }} مهمة</li>
                                @if($taskTemplate->estimated_time > 0)
                                    <li><strong>الوقت المقدر:</strong> {{ $taskTemplate->estimated_time_in_minutes }} دقيقة</li>
                                @endif
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('task-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
