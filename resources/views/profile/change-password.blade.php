@extends('layouts.app')

@section('title', __('messages.change_password'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-key me-2"></i>{{ __('messages.change_password') }}</h2>
    <a href="{{ route('profile.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>{{ __('messages.change_password') }}</h5>
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

                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">{{ __('messages.current_password') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control with-icon @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                <i class="fas fa-eye" id="toggleCurrentPasswordIcon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('messages.new_password') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control with-icon @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('messages.confirm_new_password') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control with-icon" 
                                   id="password_confirmation" name="password_confirmation" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="fas fa-eye" id="togglePasswordConfirmIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('messages.password_requirements') }}:</strong>
                        <ul class="mb-0 mt-2">
                            <li>{{ __('messages.password_min_length') }}</li>
                            <li>{{ __('messages.password_should_contain') }}</li>
                            <li>{{ __('messages.password_different_from_current') }}</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>{{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('messages.update_password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3ea8c4 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    border: none;
}

.input-group-text {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-right: none;
    border-radius: 10px 0 0 10px;
}

.form-control.with-icon {
    border-left: none;
    border-radius: 0;
}

.input-group .btn {
    border-radius: 0 10px 10px 0;
    border-left: none;
    border-color: #e9ecef;
}

.input-group .btn:hover {
    background-color: #e9ecef;
    border-color: #e9ecef;
}

.input-group .btn:focus {
    box-shadow: none;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3ea8c4 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #3ea8c4 0%, var(--primary-color) 100%);
}
</style>

<script>
// Toggle password visibility
document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
    const passwordField = document.getElementById('current_password');
    const icon = document.getElementById('toggleCurrentPasswordIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
    const passwordField = document.getElementById('password_confirmation');
    const icon = document.getElementById('togglePasswordConfirmIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>
@endsection
