@extends('layouts.app')

@section('title', $trans('add_contact') . ' - ' . $trans('system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-plus me-2"></i>{{ $trans('add_contact') }}</h2>
    <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>{{ $trans('back') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('contacts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Basic Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>{{ $trans('basic_information') }}
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ $trans('name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">{{ $trans('email') }} <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">{{ $trans('password') }} <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">{{ $trans('confirm_password') }} <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label">{{ $trans('role') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        <option value="">{{ $trans('select_role') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="department_id" class="form-label">{{ $trans('department') }}</label>
                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                        <option value="">{{ $trans('select_department') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="manager_id" class="form-label">{{ $trans('manager') }}</label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                        <option value="">{{ $trans('select_manager') }}</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Contact Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-phone me-2"></i>{{ $trans('contact_information') }}
                    </h5>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_work" class="form-label">{{ $trans('work_phone') }}</label>
                    <input type="text" class="form-control @error('phone_work') is-invalid @enderror" id="phone_work" name="phone_work" value="{{ old('phone_work') }}">
                    @error('phone_work')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_home" class="form-label">{{ $trans('phone_home') }}</label>
                    <input type="text" class="form-control @error('phone_home') is-invalid @enderror" id="phone_home" name="phone_home" value="{{ old('phone_home') }}">
                    @error('phone_home')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_personal" class="form-label">{{ $trans('phone_personal') }}</label>
                    <input type="text" class="form-control @error('phone_personal') is-invalid @enderror" id="phone_personal" name="phone_personal" value="{{ old('phone_personal') }}">
                    @error('phone_personal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_mobile" class="form-label">{{ $trans('phone_mobile') }}</label>
                    <input type="text" class="form-control @error('phone_mobile') is-invalid @enderror" id="phone_mobile" name="phone_mobile" value="{{ old('phone_mobile') }}">
                    @error('phone_mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_emergency" class="form-label">{{ $trans('phone_emergency') }}</label>
                    <input type="text" class="form-control @error('phone_emergency') is-invalid @enderror" id="phone_emergency" name="phone_emergency" value="{{ old('phone_emergency') }}">
                    @error('phone_emergency')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="extension" class="form-label">{{ $trans('extension') }}</label>
                    <input type="text" class="form-control @error('extension') is-invalid @enderror" id="extension" name="extension" value="{{ old('extension') }}">
                    @error('extension')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Social Media -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-share-alt me-2"></i>{{ $trans('social_links') }}
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="whatsapp" class="form-label">{{ $trans('whatsapp') }}</label>
                    <input type="text" class="form-control @error('whatsapp') is-invalid @enderror" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="رقم الهاتف">
                    @error('whatsapp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="telegram" class="form-label">{{ $trans('telegram') }}</label>
                    <input type="text" class="form-control @error('telegram') is-invalid @enderror" id="telegram" name="telegram" value="{{ old('telegram') }}" placeholder="اسم المستخدم">
                    @error('telegram')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="skype" class="form-label">{{ $trans('skype') }}</label>
                    <input type="text" class="form-control @error('skype') is-invalid @enderror" id="skype" name="skype" value="{{ old('skype') }}" placeholder="اسم المستخدم">
                    @error('skype')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="facebook" class="form-label">{{ $trans('facebook') }}</label>
                    <input type="url" class="form-control @error('facebook') is-invalid @enderror" id="facebook" name="facebook" value="{{ old('facebook') }}" placeholder="رابط الملف الشخصي">
                    @error('facebook')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="instagram" class="form-label">{{ $trans('instagram') }}</label>
                    <input type="text" class="form-control @error('instagram') is-invalid @enderror" id="instagram" name="instagram" value="{{ old('instagram') }}" placeholder="اسم المستخدم">
                    @error('instagram')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="linkedin_url" class="form-label">{{ $trans('linkedin') }}</label>
                    <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="رابط الملف الشخصي">
                    @error('linkedin_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Work Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-briefcase me-2"></i>{{ $trans('work_information') }}
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="job_title" class="form-label">{{ $trans('job_title') }}</label>
                    <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title') }}">
                    @error('job_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="employee_id" class="form-label">{{ $trans('employee_id') }}</label>
                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" value="{{ old('employee_id') }}">
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="hire_date" class="form-label">{{ $trans('hire_date') }}</label>
                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror" id="hire_date" name="hire_date" value="{{ old('hire_date') }}">
                    @error('hire_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="work_location" class="form-label">{{ $trans('work_location') }}</label>
                    <input type="text" class="form-control @error('work_location') is-invalid @enderror" id="work_location" name="work_location" value="{{ old('work_location') }}">
                    @error('work_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="office_room" class="form-label">{{ $trans('office_room') }}</label>
                    <input type="text" class="form-control @error('office_room') is-invalid @enderror" id="office_room" name="office_room" value="{{ old('office_room') }}">
                    @error('office_room')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="company" class="form-label">{{ $trans('company') }}</label>
                    <input type="text" class="form-control @error('company') is-invalid @enderror" id="company" name="company" value="{{ old('company') }}">
                    @error('company')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Personal Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>{{ $trans('personal_information') }}
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="birthday" class="form-label">{{ $trans('birthday') }}</label>
                    <input type="date" class="form-control @error('birthday') is-invalid @enderror" id="birthday" name="birthday" value="{{ old('birthday') }}">
                    @error('birthday')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nationality" class="form-label">{{ $trans('nationality') }}</label>
                    <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality" value="{{ old('nationality') }}">
                    @error('nationality')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label">{{ $trans('address') }}</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="city" class="form-label">{{ $trans('city') }}</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="country" class="form-label">{{ $trans('country') }}</label>
                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country') }}">
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="postal_code" class="form-label">{{ $trans('postal_code') }}</label>
                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                    @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>{{ $trans('additional_info') }}
                    </h5>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="bio" class="form-label">{{ $trans('bio') }}</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio') }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="skills" class="form-label">{{ $trans('skills') }}</label>
                    <textarea class="form-control @error('skills') is-invalid @enderror" id="skills" name="skills" rows="3" placeholder="مثال: PHP, Laravel, JavaScript">{{ old('skills') }}</textarea>
                    @error('skills')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="interests" class="form-label">{{ $trans('interests') }}</label>
                    <textarea class="form-control @error('interests') is-invalid @enderror" id="interests" name="interests" rows="3" placeholder="مثال: القراءة, الرياضة, السفر">{{ old('interests') }}</textarea>
                    @error('interests')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="languages" class="form-label">{{ $trans('languages') }}</label>
                    <textarea class="form-control @error('languages') is-invalid @enderror" id="languages" name="languages" rows="3" placeholder="مثال: العربية, الإنجليزية, الفرنسية">{{ old('languages') }}</textarea>
                    @error('languages')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="timezone" class="form-label">{{ $trans('timezone') }}</label>
                    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                        <option value="">{{ $trans('select_timezone') }}</option>
                        <option value="Asia/Riyadh" {{ old('timezone') == 'Asia/Riyadh' ? 'selected' : '' }}>الرياض (GMT+3)</option>
                        <option value="Asia/Dubai" {{ old('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>دبي (GMT+4)</option>
                        <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>لندن (GMT+0)</option>
                        <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>نيويورك (GMT-5)</option>
                    </select>
                    @error('timezone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="notes" class="form-label">{{ $trans('notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Profile Image -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-image me-2"></i>{{ $trans('profile_image') }}
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="profile_photo" class="form-label">{{ $trans('upload_photo') }}</label>
                    <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                    @error('profile_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-shield-alt me-2"></i>{{ $trans('privacy_settings') }}
                    </h5>
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_phone_work" name="show_phone_work" {{ old('show_phone_work', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_phone_work">{{ $trans('show_phone_work') }}</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_phone_personal" name="show_phone_personal" {{ old('show_phone_personal', false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_phone_personal">{{ $trans('show_phone_personal') }}</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_phone_mobile" name="show_phone_mobile" {{ old('show_phone_mobile', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_phone_mobile">{{ $trans('show_phone_mobile') }}</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_email" name="show_email" {{ old('show_email', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_email">{{ $trans('show_email') }}</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_address" name="show_address" {{ old('show_address', false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_address">{{ $trans('show_address') }}</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_social_media" name="show_social_media" {{ old('show_social_media', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_social_media">{{ $trans('show_social_media') }}</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>{{ $trans('create_contact') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // إضافة تأثيرات للفورم
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
    
    // تحسين تجربة المستخدم
    const roleSelect = document.getElementById('role');
    const managerSelect = document.getElementById('manager_id');
    
    roleSelect.addEventListener('change', function() {
        if (this.value === 'admin') {
            managerSelect.disabled = true;
            managerSelect.value = '';
        } else {
            managerSelect.disabled = false;
        }
    });
    
</script>
@endpush
