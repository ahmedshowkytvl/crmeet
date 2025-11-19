@extends('layouts.app')

@section('title', $passwordAccount->display_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-key me-2"></i>{{ $passwordAccount->display_name }}
    </h2>
    <div class="d-flex gap-2">
        @can('update', $passwordAccount)
        <a href="{{ route('password-accounts.edit', $passwordAccount) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>{{ __('Edit') }}
        </a>
        @endcan
        <a href="{{ route('password-accounts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Account Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Account Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('Account Name') }}</h6>
                        <p class="text-muted">{{ $passwordAccount->name }}</p>
                        
                        @if($passwordAccount->name_ar)
                            <h6>{{ __('Account Name (Arabic)') }}</h6>
                            <p class="text-muted">{{ $passwordAccount->name_ar }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('Email/Username') }}</h6>
                        <p class="text-muted">{{ $passwordAccount->email ?: '-' }}</p>
                        
                        @if($passwordAccount->url)
                            <h6>{{ __('Login URL') }}</h6>
                            <p class="text-muted">
                                <a href="{{ $passwordAccount->url }}" target="_blank" class="text-decoration-none">
                                    {{ $passwordAccount->url }} <i class="fas fa-external-link-alt"></i>
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('Category') }}</h6>
                        @if($passwordAccount->category)
                            <span class="badge" style="background-color: {{ $passwordAccount->category->color }};">
                                {{ $passwordAccount->category->display_name }}
                            </span>
                        @else
                            <span class="badge bg-secondary">{{ $passwordAccount->display_category ?: '-' }}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('Status') }}</h6>
                        @if($passwordAccount->isExpired())
                            <span class="badge bg-danger">{{ __('Expired') }}</span>
                        @elseif($passwordAccount->isExpiringSoon())
                            <span class="badge bg-warning">{{ __('Expiring Soon') }}</span>
                        @elseif($passwordAccount->is_shared)
                            <span class="badge bg-info">{{ __('Shared') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('Active') }}</span>
                        @endif
                    </div>
                </div>
                
                @if($passwordAccount->display_notes)
                    <hr>
                    <h6>{{ __('Notes') }}</h6>
                    <p class="text-muted">{{ $passwordAccount->display_notes }}</p>
                @endif
                
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <h6>{{ __('Requires 2FA') }}</h6>
                        <p class="text-muted">
                            @if($passwordAccount->requires_2fa)
                                <i class="fas fa-check text-success"></i> {{ __('Yes') }}
                            @else
                                <i class="fas fa-times text-danger"></i> {{ __('No') }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6>{{ __('Shared Account') }}</h6>
                        <p class="text-muted">
                            @if($passwordAccount->is_shared)
                                <i class="fas fa-check text-success"></i> {{ __('Yes') }}
                            @else
                                <i class="fas fa-times text-danger"></i> {{ __('No') }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6>{{ __('Expires At') }}</h6>
                        <p class="text-muted">
                            @if($passwordAccount->expires_at)
                                {{ $passwordAccount->expires_at->format('Y-m-d') }}
                                @if($passwordAccount->isExpiringSoon())
                                    <i class="fas fa-exclamation-triangle text-warning ms-1"></i>
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Password Section -->
        @can('viewPassword', $passwordAccount)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Password') }}</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('This password is encrypted and can only be viewed by authorized users.') }}
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" id="passwordField" 
                           value="{{ $passwordAccount->password }}" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                    <button class="btn btn-outline-primary" type="button" onclick="copyPassword()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                @if($passwordAccount->requires_2fa)
                    <small class="text-warning mt-2 d-block">
                        <i class="fas fa-shield-alt me-1"></i>{{ __('This account requires 2FA') }}
                    </small>
                @endif
            </div>
        </div>
        @endcan
        
        <!-- Assigned Users -->
        @if($passwordAccount->assignments->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Assigned Users') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Access Level') }}</th>
                                <th>{{ __('Permissions') }}</th>
                                <th>{{ __('Assigned At') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($passwordAccount->assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($assignment->user->avatar)
                                                <img src="{{ $assignment->user->avatar }}" alt="{{ $assignment->user->name }}" 
                                                     class="me-2 rounded-circle" style="width: 32px; height: 32px;">
                                            @else
                                                <div class="me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px;">
                                                    {{ substr($assignment->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $assignment->user->name }}</strong>
                                                @if($assignment->user->department)
                                                    <br><small class="text-muted">{{ $assignment->user->department->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($assignment->access_level) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($assignment->can_view_password)
                                                <span class="badge bg-success">{{ __('View Password') }}</span>
                                            @endif
                                            @if($assignment->can_edit_password)
                                                <span class="badge bg-warning">{{ __('Edit Password') }}</span>
                                            @endif
                                            @if($assignment->can_delete_account)
                                                <span class="badge bg-danger">{{ __('Delete Account') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $assignment->assigned_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <!-- Account Preview -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Account Preview') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    @if($passwordAccount->icon)
                        <img src="{{ $passwordAccount->icon }}" alt="{{ $passwordAccount->display_name }}" 
                             class="me-3" style="width: 40px; height: 40px;">
                    @else
                        <div class="me-3 d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px; background-color: {{ $passwordAccount->category->color ?? '#6c757d' }}; border-radius: 8px;">
                            <i class="fas fa-key text-white"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $passwordAccount->display_name }}</h6>
                        <span class="badge" style="background-color: {{ $passwordAccount->category->color ?? '#6c757d' }};">
                            {{ $passwordAccount->category->display_name ?? $passwordAccount->display_category }}
                        </span>
                    </div>
                </div>
                
                @if($passwordAccount->display_notes)
                    <p class="text-muted small mb-0">
                        {{ Str::limit($passwordAccount->display_notes, 100) }}
                    </p>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('viewPassword', $passwordAccount)
                        <button type="button" class="btn btn-outline-success" onclick="showPassword()">
                            <i class="fas fa-key me-2"></i>{{ __('View Password') }}
                        </button>
                    @endcan
                    
                    @can('update', $passwordAccount)
                        <a href="{{ route('password-accounts.edit', $passwordAccount) }}" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-edit me-2"></i>{{ __('Edit Account') }}
                        </a>
                    @endcan
                    
                    @if($passwordAccount->url)
                        <a href="{{ $passwordAccount->url }}" target="_blank" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>{{ __('Visit Website') }}
                        </a>
                    @endif
                    
                    <a href="{{ route('password-accounts.index') }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>{{ __('View All Accounts') }}
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Account Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Account Statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $passwordAccount->assignments->count() }}</h4>
                        <small class="text-muted">{{ __('Assigned Users') }}</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ $passwordAccount->auditLogs->count() }}</h4>
                        <small class="text-muted">{{ __('Audit Logs') }}</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-warning">{{ $passwordAccount->passwordHistory->count() }}</h4>
                        <small class="text-muted">{{ __('Password Changes') }}</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $passwordAccount->created_at->diffInDays() }}</h4>
                        <small class="text-muted">{{ __('Days Old') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('View Password') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('This password is encrypted and can only be viewed by authorized users.') }}
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" id="modalPasswordField" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="toggleModalPassword()">
                        <i class="fas fa-eye" id="modalToggleIcon"></i>
                    </button>
                    <button class="btn btn-outline-primary" type="button" onclick="copyModalPassword()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const passwordField = document.getElementById('passwordField');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

function copyPassword() {
    const passwordField = document.getElementById('passwordField');
    passwordField.select();
    document.execCommand('copy');
    
    // Show success message
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-primary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
    }, 2000);
}

function showPassword() {
    document.getElementById('modalPasswordField').value = '{{ $passwordAccount->password }}';
    const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
    modal.show();
}

function toggleModalPassword() {
    const passwordField = document.getElementById('modalPasswordField');
    const toggleIcon = document.getElementById('modalToggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

function copyModalPassword() {
    const passwordField = document.getElementById('modalPasswordField');
    passwordField.select();
    document.execCommand('copy');
    
    // Show success message
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-primary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
    }, 2000);
}
</script>
@endpush












