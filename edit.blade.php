@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', __('messages.edit_user') . ' - ' . $user->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/image-cropper.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="{{ asset('js/image-cropper.js') }}"></script>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit me-2"></i>{{ __('messages.edit_user') }}</h2>
    <div class="btn-group">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
        </a>
        <a href="{{ route('users.contact-card', $user) }}" class="btn btn-info">
            <i class="fas fa-id-card me-2"></i>{{ __('messages.contact_card') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="name_ar" class="form-label">{{ __('messages.name_ar') }}</label>
                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar', $user->name_ar) }}" dir="rtl">
                    @error('name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="employee_id" class="form-label">{{ __('messages.employee_id') }} ({{ __('messages.employee_code') }})</label>
                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" placeholder="{{ __('messages.employee_code_example') }}">
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="id_number" class="form-label">ID Number (رقم الهوية الوطنية)</label>
                    <input type="text" class="form-control @error('id_number') is-invalid @enderror" id="id_number" name="id_number" value="{{ old('id_number', $user->id_number) }}" placeholder="e.g., 29012345678901">
                    @error('id_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="profile_picture" class="form-label">{{ __('messages.profile_picture') }}</label>
                    <div class="input-group">
                        <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" id="profile_picture" name="profile_picture" accept="image/*">
                        <button type="button" class="btn btn-outline-secondary" id="cropImageBtn" disabled>
                            <i class="fas fa-crop me-1"></i>قص الصورة
                        </button>
                    </div>
                    <div class="form-text">{{ __('messages.profile_picture_help') }}</div>
                    @error('profile_picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    <!-- Current Image -->
                    @if($user->profile_picture)
                        <div class="mt-3">
                            <label class="form-label">{{ __('messages.current_picture') }}:</label>
                            <div>
                                <img src="{{ Storage::url($user->profile_picture) }}" alt="Current Profile Picture" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" onerror="this.src='{{ asset('images/default-avatar.png') }}'; this.onerror=null;">
                            </div>
                        </div>
                    @endif
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <label class="form-label">{{ __('messages.new_picture_preview') }}:</label>
                        <div>
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="cropPreviewBtn">
                                <i class="fas fa-crop me-1"></i>قص هذه الصورة
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="removePreviewBtn">
                                <i class="fas fa-times me-1"></i>إزالة
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">{{ __('messages.password') }}</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                    <small class="form-text text-muted">{{ __('messages.leave_blank_to_keep_current') }}</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>
                
                <div class="col-md-6 mb-3" id="department-field">
                    <label for="department_id" class="form-label">{{ __('messages.department') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                        <option value="">{{ __('messages.select_department') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        <option value="">{{ __('messages.select_role') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">{{ __('messages.work_phone') }}</label>
                    <div id="workPhonesContainer">
                        @php
                            $workPhones = $user->phones->filter(function($phone) {
                                return $phone->phoneType && $phone->phoneType->slug === 'work';
                            });
                            // If no work phones exist but phone_work field has value, create a temporary entry
                            if ($workPhones->isEmpty() && $user->phone_work) {
                                $workPhones = collect([(object)[
                                    'id' => 'temp_0',
                                    'phone_number' => $user->phone_work,
                                    'is_primary' => true
                                ]]);
                            }
                        @endphp
                        
                        @if($workPhones->isEmpty())
                            <div class="work-phone-row mb-2">
                                <div class="row g-2">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control work-phone-input" name="work_phones[0][number]" placeholder="{{ __('messages.work_phone') }}" value="{{ old('work_phones.0.number') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input main-phone-radio" type="radio" name="main_work_phone" value="0" id="main_phone_0" checked>
                                            <label class="form-check-label" for="main_phone_0">
                                                Main
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @foreach($workPhones as $index => $phone)
                                <div class="work-phone-row mb-2">
                                    <div class="row g-2">
                                        <div class="col-md-10">
                                            <input type="hidden" name="work_phones[{{ $index }}][id]" value="{{ $phone->id }}">
                                            <input type="text" class="form-control work-phone-input" name="work_phones[{{ $index }}][number]" placeholder="{{ __('messages.work_phone') }}" value="{{ old("work_phones.$index.number", $phone->phone_number) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input main-phone-radio" type="radio" name="main_work_phone" value="{{ $index }}" id="main_phone_{{ $index }}" {{ $phone->is_primary || ($index == 0 && !$workPhones->contains('is_primary', true)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="main_phone_{{ $index }}">
                                                    Main
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger remove-phone-btn mt-1">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addWorkPhoneBtn">
                        <i class="fas fa-plus"></i> إضافة رقم آخر
                    </button>
                    @error('work_phones.*.number')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="phone_personal" class="form-label">{{ __('messages.mobile_phone') }}</label>
                    <input type="text" class="form-control @error('phone_personal') is-invalid @enderror" id="phone_personal" name="phone_personal" value="{{ old('phone_personal', $user->phone_personal) }}">
                    @error('phone_personal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="work_email" class="form-label">{{ __('messages.work_email') }}</label>
                    <input type="email" class="form-control @error('work_email') is-invalid @enderror" id="work_email" name="work_email" value="{{ old('work_email', $user->work_email) }}">
                    @error('work_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="avaya_extension" class="form-label">{{ __('messages.avaya_extension') }}</label>
                    <input type="text" class="form-control @error('avaya_extension') is-invalid @enderror" id="avaya_extension" name="avaya_extension" value="{{ old('avaya_extension', $user->avaya_extension) }}" placeholder="e.g., 1001">
                    <div class="form-text">{{ __('messages.avaya_extension_help') }}</div>
                    @error('avaya_extension')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="microsoft_teams_id" class="form-label">{{ __('messages.microsoft_teams_id') }}</label>
                    <input type="email" class="form-control @error('microsoft_teams_id') is-invalid @enderror" id="microsoft_teams_id" name="microsoft_teams_id" value="{{ old('microsoft_teams_id', $user->microsoft_teams_id) }}" placeholder="e.g., john.doe@company.com">
                    <div class="form-text">{{ __('messages.microsoft_teams_id_help') }}</div>
                    @error('microsoft_teams_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Zoho Integration Fields -->
                <div class="col-12 mb-3">
                    <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-cloud me-2"></i>{{ __('messages.zoho_integration') }}</h6>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="zoho_agent_name" class="form-label">{{ __('messages.zoho_agent_name') }}</label>
                    <input type="text" class="form-control @error('zoho_agent_name') is-invalid @enderror" id="zoho_agent_name" name="zoho_agent_name" value="{{ old('zoho_agent_name', $user->zoho_agent_name) }}" placeholder="{{ __('messages.zoho_agent_name_placeholder') }}">
                    <div class="form-text">{{ __('messages.zoho_agent_name_help') }}</div>
                    @error('zoho_agent_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="zoho_agent_id" class="form-label">{{ __('messages.zoho_agent_id') }}</label>
                    <input type="text" class="form-control @error('zoho_agent_id') is-invalid @enderror" id="zoho_agent_id" name="zoho_agent_id" value="{{ old('zoho_agent_id', $user->zoho_agent_id) }}" placeholder="{{ __('messages.zoho_agent_id_placeholder') }}">
                    <div class="form-text">{{ __('messages.zoho_agent_id_help') }}</div>
                    @error('zoho_agent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="zoho_email" class="form-label">{{ __('messages.zoho_email') }}</label>
                    <input type="email" class="form-control @error('zoho_email') is-invalid @enderror" id="zoho_email" name="zoho_email" value="{{ old('zoho_email', $user->zoho_email) }}" placeholder="{{ __('messages.zoho_email_placeholder') }}">
                    <div class="form-text">{{ __('messages.zoho_email_help') }}</div>
                    @error('zoho_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_zoho_enabled" name="is_zoho_enabled" value="1" {{ old('is_zoho_enabled', $user->is_zoho_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_zoho_enabled">
                            {{ __('messages.enable_zoho_integration') }}
                        </label>
                    </div>
                    <div class="form-text">{{ __('messages.zoho_enabled_help') }}</div>
                    @error('is_zoho_enabled')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="job_title" class="form-label">{{ __('messages.job_title') }}</label>
                    <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title', $user->job_title ?: 'Employee') }}" placeholder="e.g., Software Developer">
                    @error('job_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="insurance_status" class="form-label">Insurance Status (حالة التأمين)</label>
                    <select class="form-select @error('insurance_status') is-invalid @enderror" id="insurance_status" name="insurance_status">
                        <option value="">{{ __('messages.not_specified') }}</option>
                        <option value="insured" {{ old('insurance_status', $user->insurance_status) == 'insured' ? 'selected' : '' }}>Insured (مؤمن)</option>
                        <option value="not_insured" {{ old('insurance_status', $user->insurance_status) == 'not_insured' ? 'selected' : '' }}>Not Insured (غير مؤمن)</option>
                    </select>
                    @error('insurance_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="company" class="form-label">{{ __('messages.company') }}</label>
                    <input type="text" class="form-control @error('company') is-invalid @enderror" id="company" name="company" value="{{ old('company', $user->company ?: 'Egypt Express Travel') }}" placeholder="e.g., Egypt Express Travel">
                    @error('company')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3" id="manager-field">
                    <label for="manager_id" class="form-label">{{ __('messages.manager') }}</label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                        <option value="">{{ __('messages.select_manager') }}</option>
                        @foreach($users as $manager)
                            @if($manager->id != $user->id)
                            <option value="{{ $manager->id }}" {{ old('manager_id', $user->manager_id) == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="office_address" class="form-label">{{ __('messages.office_address') }}</label>
                    <textarea class="form-control @error('office_address') is-invalid @enderror" id="office_address" name="office_address" rows="2">{{ old('office_address', $user->office_address) }}</textarea>
                    @error('office_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                    <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $user->linkedin_url) }}">
                    @error('linkedin_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="website_url" class="form-label">Website URL</label>
                    <input type="url" class="form-control @error('website_url') is-invalid @enderror" id="website_url" name="website_url" value="{{ old('website_url', $user->website_url) }}">
                    @error('website_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="birthday" class="form-label">{{ __('messages.birthday') }}</label>
                    <input type="text" class="form-control @error('birthday') is-invalid @enderror" id="birthday" name="birthday" value="{{ old('birthday', $user->birthday?->format('d-m-Y')) }}" placeholder="DD-MM-YYYY" pattern="\d{2}-\d{2}-\d{4}">
                    <small class="form-text text-muted">{{ __('messages.date_format_help') }}: DD-MM-YYYY (مثال: 25-12-1990)</small>
                    @error('birthday')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="birth_date" class="form-label">{{ __('messages.birth_date') }}</label>
                    <input type="text" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('d-m-Y')) }}" placeholder="DD-MM-YYYY" pattern="\d{2}-\d{2}-\d{4}">
                    <small class="form-text text-muted">{{ __('messages.date_format_help') }}: DD-MM-YYYY (مثال: 25-12-1990)</small>
                    @error('birth_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nationality" class="form-label">{{ __('messages.nationality') }}</label>
                    <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality" value="{{ old('nationality', $user->nationality) }}" placeholder="{{ __('messages.nationality_example') }}">
                    @error('nationality')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="city" class="form-label">{{ __('messages.city') }}</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="{{ __('messages.city_example') }}">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="country" class="form-label">{{ __('messages.country') }}</label>
                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $user->country) }}" placeholder="{{ __('messages.country_example') }}">
                    @error('country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="bio" class="form-label">{{ __('messages.bio') }}</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $user->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>{{ __('messages.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const departmentField = document.getElementById('department-field');
    const managerField = document.getElementById('manager-field');
    const departmentSelect = document.getElementById('department_id');
    const managerSelect = document.getElementById('manager_id');
    
    // Date input formatting
    const birthdayInput = document.getElementById('birthday');
    const birthDateInput = document.getElementById('birth_date');
    
    // Add date formatting to birthday inputs
    [birthdayInput, birthDateInput].forEach(input => {
        if (input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '-' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 9);
                }
                e.target.value = value;
            });
            
            input.addEventListener('blur', function(e) {
                const value = e.target.value;
                if (value && !/^\d{2}-\d{2}-\d{4}$/.test(value)) {
                    e.target.classList.add('is-invalid');
                } else {
                    e.target.classList.remove('is-invalid');
                }
            });
        }
    });
    
    function toggleFields() {
        const selectedRole = roleSelect.value;
        const ceoRoleId = @json(\App\Models\Role::where('slug', 'ceo')->first()?->id ?? null);
        
        if (selectedRole == ceoRoleId) {
            // Hide department and manager fields for CEO
            departmentField.style.display = 'none';
            managerField.style.display = 'none';
            departmentSelect.removeAttribute('required');
            departmentSelect.value = '';
            managerSelect.value = '';
        } else {
            // Show department and manager fields for other roles
            departmentField.style.display = 'block';
            managerField.style.display = 'block';
            departmentSelect.setAttribute('required', 'required');
        }
    }
    
    // Initial check
    toggleFields();
    
    // Listen for role changes
    roleSelect.addEventListener('change', toggleFields);
    
    // Image preview and cropping functionality
    const profilePictureInput = document.getElementById('profile_picture');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const cropImageBtn = document.getElementById('cropImageBtn');
    const cropPreviewBtn = document.getElementById('cropPreviewBtn');
    const removePreviewBtn = document.getElementById('removePreviewBtn');
    let currentFile = null;
    
    profilePictureInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            currentFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                cropImageBtn.disabled = false;
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
            cropImageBtn.disabled = true;
            currentFile = null;
        }
    });
    
    // Crop image button functionality
    cropImageBtn.addEventListener('click', function() {
        if (currentFile) {
            cropImage(currentFile, function(croppedFile) {
                // Update the file input with cropped file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                profilePictureInput.files = dataTransfer.files;
                
                // Update preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(croppedFile);
                
                currentFile = croppedFile;
            }, {
                aspectRatio: 1, // مربع
                viewMode: 1
            });
        }
    });
    
    // Crop preview button functionality
    cropPreviewBtn.addEventListener('click', function() {
        if (currentFile) {
            cropImage(currentFile, function(croppedFile) {
                // Update the file input with cropped file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                profilePictureInput.files = dataTransfer.files;
                
                // Update preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(croppedFile);
                
                currentFile = croppedFile;
            }, {
                aspectRatio: 1, // مربع
                viewMode: 1
            });
        }
    });
    
    // Remove preview button functionality
    removePreviewBtn.addEventListener('click', function() {
        profilePictureInput.value = '';
        imagePreview.style.display = 'none';
        cropImageBtn.disabled = true;
        currentFile = null;
    });
    
    // Auto-fill Microsoft Teams ID with work email
    const workEmailInput = document.getElementById('work_email');
    const microsoftTeamsIdInput = document.getElementById('microsoft_teams_id');
    
    workEmailInput.addEventListener('blur', function() {
        if (workEmailInput.value && !microsoftTeamsIdInput.value) {
            microsoftTeamsIdInput.value = workEmailInput.value;
        }
    });
    
    // Also auto-fill when work email changes
    workEmailInput.addEventListener('input', function() {
        if (workEmailInput.value && !microsoftTeamsIdInput.value) {
            microsoftTeamsIdInput.value = workEmailInput.value;
        }
    });
    
    // Work phones management
    const workPhonesContainer = document.getElementById('workPhonesContainer');
    const addWorkPhoneBtn = document.getElementById('addWorkPhoneBtn');
    
    // Initialize index based on existing rows
    let workPhoneIndex = workPhonesContainer.querySelectorAll('.work-phone-row').length;
    
    // Add new work phone
    addWorkPhoneBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'work-phone-row mb-2';
        newRow.innerHTML = `
            <div class="row g-2">
                <div class="col-md-10">
                    <input type="text" class="form-control work-phone-input" name="work_phones[${workPhoneIndex}][number]" placeholder="{{ __('messages.work_phone') }}">
                </div>
                <div class="col-md-2">
                    <div class="form-check">
                        <input class="form-check-input main-phone-radio" type="radio" name="main_work_phone" value="${workPhoneIndex}" id="main_phone_${workPhoneIndex}">
                        <label class="form-check-label" for="main_phone_${workPhoneIndex}">
                            Main
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-phone-btn mt-1">
                <i class="fas fa-trash"></i> حذف
            </button>
        `;
        workPhonesContainer.appendChild(newRow);
        workPhoneIndex++;
    });
    
    // Remove work phone
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-phone-btn')) {
            const row = e.target.closest('.work-phone-row');
            // Ensure at least one phone remains
            const allRows = workPhonesContainer.querySelectorAll('.work-phone-row');
            if (allRows.length > 1) {
                row.remove();
                // Re-index remaining phones
                updatePhoneIndexes();
            } else {
                alert('يجب أن يكون هناك رقم واحد على الأقل');
            }
        }
    });
    
    function updatePhoneIndexes() {
        const rows = workPhonesContainer.querySelectorAll('.work-phone-row');
        rows.forEach((row, index) => {
            const input = row.querySelector('input[type="text"]');
            const radio = row.querySelector('input[type="radio"]');
            const label = row.querySelector('label');
            
            if (input) {
                input.name = `work_phones[${index}][number]`;
            }
            if (radio) {
                radio.value = index;
                radio.id = `main_phone_${index}`;
            }
            if (label) {
                label.setAttribute('for', `main_phone_${index}`);
            }
        });
        workPhoneIndex = rows.length;
    }
});
</script>
@endsection