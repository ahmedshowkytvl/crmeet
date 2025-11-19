@extends('layouts.app')

@section('title', __('passwords.edit_account'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-edit me-2"></i>{{ __('passwords.edit_account') }}
    </h2>
    <a href="{{ route('password-accounts.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('passwords.password_account') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password-accounts.update', $passwordAccount) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Account Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                {{ __('passwords.account_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $passwordAccount->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Arabic Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name_ar" class="form-label">{{ __('passwords.account_name_ar') }}</label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" name="name_ar" value="{{ old('name_ar', $passwordAccount->name_ar) }}">
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Email/Username -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">{{ __('passwords.email_username') }}</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $passwordAccount->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">{{ __('passwords.category') }}</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id">
                                <option value="">{{ __('Select Category') }}</option>
                                @foreach($categories as $key => $value)
                                    <option value="{{ $key }}" {{ old('category_id', $passwordAccount->category_id) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <a href="{{ route('password-categories.create') }}" target="_blank">
                                    <i class="fas fa-plus me-1"></i>{{ __('Create New Category') }}
                                </a>
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">{{ __('passwords.password') }}</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="{{ __('Leave blank to keep current password') }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" onclick="generatePassword()">
                                    <i class="fas fa-random"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Leave blank to keep current password') }}</small>
                        </div>
                        
                        <!-- Login URL -->
                        <div class="col-md-6 mb-3">
                            <label for="url" class="form-label">{{ __('passwords.login_url') }}</label>
                            <input type="url" class="form-control @error('url') is-invalid @enderror" 
                                   id="url" name="url" value="{{ old('url', $passwordAccount->url) }}" 
                                   placeholder="https://example.com/login">
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Expires At -->
                        <div class="col-md-6 mb-3">
                            <label for="expires_at" class="form-label">{{ __('passwords.expires_at') }}</label>
                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" 
                                   id="expires_at" name="expires_at" value="{{ old('expires_at', $passwordAccount->expires_at?->format('Y-m-d')) }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Icon URL -->
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">{{ __('passwords.icon') }}</label>
                            <input type="url" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon', $passwordAccount->icon) }}" 
                                   placeholder="https://example.com/icon.png">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('passwords.notes') }}</label>
                        <div class="input-group">
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $passwordAccount->notes) }}</textarea>
                            <button type="button" class="btn btn-outline-primary" id="generateNotesBtn">
                                <i class="fas fa-magic me-1"></i>
                                {{ __('passwords.generate_ai_notes') }}
                            </button>
                        </div>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            {{ __('passwords.ai_notes_description') }}
                        </small>
                    </div>
                    
                    <!-- Arabic Notes -->
                    <div class="mb-3">
                        <label for="notes_ar" class="form-label">{{ __('passwords.notes_ar') }}</label>
                        <textarea class="form-control @error('notes_ar') is-invalid @enderror" 
                                  id="notes_ar" name="notes_ar" rows="3">{{ old('notes_ar', $passwordAccount->notes_ar) }}</textarea>
                        @error('notes_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Checkboxes -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requires_2fa" 
                                       name="requires_2fa" value="1" {{ old('requires_2fa', $passwordAccount->requires_2fa) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_2fa">
                                    {{ __('passwords.requires_2fa') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_shared" 
                                       name="is_shared" value="1" {{ old('is_shared', $passwordAccount->is_shared) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_shared">
                                    {{ __('passwords.is_shared') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', $passwordAccount->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('passwords.is_active') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('password-accounts.index') }}" class="btn btn-secondary me-2">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('Update Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Assignment Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('passwords.assign_to_users') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="user_search" class="form-label">{{ __('passwords.search_users') }}</label>
                    <input type="text" class="form-control" id="user_search" 
                           placeholder="{{ __('passwords.search_users') }}">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('passwords.available_users') }}</label>
                    <div id="users_list" style="max-height: 300px; overflow-y: auto;">
                        @foreach($users as $user)
                            <div class="form-check user-item" data-name="{{ strtolower($user->name) }}">
                                <input class="form-check-input" type="checkbox" 
                                       id="user_{{ $user->id }}" name="assigned_users[]" 
                                       value="{{ $user->id }}" {{ in_array($user->id, $assignedUsers) ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_{{ $user->id }}">
                                    {{ $user->name }}
                                    @if($user->department)
                                        <small class="text-muted">({{ $user->department->name }})</small>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Security Tips') }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Use strong, unique passwords') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Enable 2FA when available') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Set expiration dates for sensitive accounts') }}
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Use descriptive names and categories') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'ToggleIcon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function generatePassword() {
    const length = 16;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    document.getElementById('password').value = password;
}

// User search functionality
document.getElementById('user_search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const userItems = document.querySelectorAll('.user-item');
    
    userItems.forEach(item => {
        const userName = item.getAttribute('data-name');
        if (userName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// AI Notes Generation
document.getElementById('generateNotesBtn').addEventListener('click', function() {
    const accountName = document.getElementById('name').value;
    const categoryId = document.getElementById('category_id').value;
    const email = document.getElementById('email').value;
    const url = document.getElementById('url').value;
    const notesTextarea = document.getElementById('notes');
    
    if (!accountName.trim()) {
        alert('{{ __("passwords.please_enter_account_name_first") }}');
        return;
    }
    
    // Show loading state
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("passwords.generating") }}';
    this.disabled = true;
    
    // Make API request
    fetch('{{ route("password-accounts.generate-notes") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            account_name: accountName,
            category_id: categoryId || null,
            email: email || null,
            url: url || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notesTextarea.value = data.notes;
            // Show success message
            showNotification('{{ __("passwords.ai_notes_generated_successfully") }}', 'success');
        } else {
            showNotification(data.message || '{{ __("passwords.failed_to_generate_notes") }}', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('{{ __("passwords.an_error_occurred_while_generating_notes") }}', 'error');
    })
    .finally(() => {
        // Restore button state
        this.innerHTML = originalText;
        this.disabled = false;
    });
});

// Notification function
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>
@endpush
