@extends('layouts.app')

@section('title', __('messages.add_new_user') . ' - ' . __('messages.system_title'))

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
    <h2><i class="fas fa-user-plus me-2"></i>{{ __('messages.add_new_user') }}</h2>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="name_ar" class="form-label">{{ __('messages.name_ar') }}</label>
                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" dir="rtl">
                    @error('name_ar')
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
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <label class="form-label">{{ __('messages.preview') }}:</label>
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
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">{{ __('messages.password') }} <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }} <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
                
                <div class="col-md-6 mb-3" id="department-field">
                    <label for="department_id" class="form-label">{{ __('messages.department') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                        <option value="">{{ __('messages.select_department') }}</option>
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
                    <label for="role_id" class="form-label">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                        <option value="">{{ __('messages.select_role') }}</option>
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
                    <label for="phone_work" class="form-label">{{ __('messages.work_phone') }}</label>
                    <input type="text" class="form-control @error('phone_work') is-invalid @enderror" id="phone_work" name="phone_work" value="{{ old('phone_work') }}">
                    @error('phone_work')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="phone_personal" class="form-label">{{ __('messages.mobile_phone') }}</label>
                    <input type="text" class="form-control @error('phone_personal') is-invalid @enderror" id="phone_personal" name="phone_personal" value="{{ old('phone_personal') }}">
                    @error('phone_personal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="work_email" class="form-label">{{ __('messages.work_email') }}</label>
                    <input type="email" class="form-control @error('work_email') is-invalid @enderror" id="work_email" name="work_email" value="{{ old('work_email') }}">
                    @error('work_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="avaya_extension" class="form-label">{{ __('messages.avaya_extension') }}</label>
                    <input type="text" class="form-control @error('avaya_extension') is-invalid @enderror" id="avaya_extension" name="avaya_extension" value="{{ old('avaya_extension') }}" placeholder="e.g., 1001">
                    <div class="form-text">{{ __('messages.avaya_extension_help') }}</div>
                    @error('avaya_extension')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="microsoft_teams_id" class="form-label">{{ __('messages.microsoft_teams_id') }}</label>
                    <input type="email" class="form-control @error('microsoft_teams_id') is-invalid @enderror" id="microsoft_teams_id" name="microsoft_teams_id" value="{{ old('microsoft_teams_id') }}" placeholder="e.g., john.doe@company.com">
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
                    <input type="text" class="form-control @error('zoho_agent_name') is-invalid @enderror" id="zoho_agent_name" name="zoho_agent_name" value="{{ old('zoho_agent_name') }}" placeholder="{{ __('messages.zoho_agent_name_placeholder') }}">
                    <div class="form-text">{{ __('messages.zoho_agent_name_help') }}</div>
                    @error('zoho_agent_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="zoho_agent_id" class="form-label">{{ __('messages.zoho_agent_id') }}</label>
                    <input type="text" class="form-control @error('zoho_agent_id') is-invalid @enderror" id="zoho_agent_id" name="zoho_agent_id" value="{{ old('zoho_agent_id') }}" placeholder="{{ __('messages.zoho_agent_id_placeholder') }}">
                    <div class="form-text">{{ __('messages.zoho_agent_id_help') }}</div>
                    @error('zoho_agent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="zoho_email" class="form-label">{{ __('messages.zoho_email') }}</label>
                    <input type="email" class="form-control @error('zoho_email') is-invalid @enderror" id="zoho_email" name="zoho_email" value="{{ old('zoho_email') }}" placeholder="{{ __('messages.zoho_email_placeholder') }}">
                    <div class="form-text">{{ __('messages.zoho_email_help') }}</div>
                    @error('zoho_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_zoho_enabled" name="is_zoho_enabled" value="1" {{ old('is_zoho_enabled') ? 'checked' : '' }}>
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
                    <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title', 'Employee') }}" placeholder="e.g., Software Developer">
                    @error('job_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="insurance_status" class="form-label">Insurance Status (حالة التأمين)</label>
                    <select class="form-select @error('insurance_status') is-invalid @enderror" id="insurance_status" name="insurance_status">
                        <option value="">{{ __('messages.not_specified') }}</option>
                        <option value="insured" {{ old('insurance_status') == 'insured' ? 'selected' : '' }}>Insured (مؤمن)</option>
                        <option value="not_insured" {{ old('insurance_status') == 'not_insured' ? 'selected' : '' }}>Not Insured (غير مؤمن)</option>
                    </select>
                    @error('insurance_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="company" class="form-label">{{ __('messages.company') }}</label>
                    <input type="text" class="form-control @error('company') is-invalid @enderror" id="company" name="company" value="{{ old('company', 'Egypt Express Travel') }}" placeholder="e.g., Egypt Express Travel">
                    @error('company')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3" id="manager-field">
                    <label for="manager_id" class="form-label">{{ __('messages.manager') }}</label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                        <option value="">{{ __('messages.select_manager') }}</option>
                        @foreach($users as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="office_address" class="form-label">{{ __('messages.office_address') }}</label>
                    <textarea value="94,shehab street,elmohandseen" class="form-control @error('office_address') is-invalid @enderror" id="office_address" name="office_address" rows="2">{{ old('office_address') }}</textarea>
                    @error('office_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                    <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}">
                    @error('linkedin_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="website_url" class="form-label">Website URL</label>
                    <input type="url" class="form-control @error('website_url') is-invalid @enderror" id="website_url" name="website_url" value="{{ old('website_url') }}">
                    @error('website_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="birthday" class="form-label">{{ __('messages.birthday') }}</label>
                    <input type="date" class="form-control @error('birthday') is-invalid @enderror" id="birthday" name="birthday" value="{{ old('birthday') }}">
                    @error('birthday')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="bio" class="form-label">{{ __('messages.bio') }}</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio') }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
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
    
    function toggleFields() {
        const selectedRole = roleSelect.value;
        const ceoRoleId = {{ \App\Models\Role::where('slug', 'ceo')->first()->id ?? 'null' }};
        
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
    
});
</script>
@endsection
