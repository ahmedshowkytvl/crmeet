@extends('layouts.app')

@section('title', __('passwords.batch_assign'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-user-plus me-2"></i>{{ __('passwords.batch_assign') }}
    </h2>
    <a href="{{ route('password-accounts.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('passwords.select_users_to_assign') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password-accounts.batch-assign-store') }}">
                    @csrf
                    
                    <!-- Hidden field for account IDs -->
                    @foreach($accountIds as $accountId)
                        <input type="hidden" name="account_ids[]" value="{{ $accountId }}">
                    @endforeach
                    
                    <!-- Access Level -->
                    <div class="mb-4">
                        <label for="access_level" class="form-label">{{ __('passwords.access_level') }}</label>
                        <select class="form-select" id="access_level" name="access_level" required>
                            <option value="read-only">{{ __('passwords.read_only') }}</option>
                            <option value="manage">{{ __('passwords.manage') }}</option>
                        </select>
                    </div>
                    
                    <!-- Users Selection -->
                    <div class="mb-4">
                        <label class="form-label">{{ __('passwords.select_users') }}</label>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllUsers()">
                                    <i class="fas fa-check-square me-1"></i>{{ __('passwords.select_all') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllUsers()">
                                    <i class="fas fa-square me-1"></i>{{ __('passwords.deselect_all') }}
                                </button>
                            </div>
                            
                            @foreach($users as $user)
                                <div class="form-check mb-2">
                                    <input class="form-check-input user-checkbox" type="checkbox" 
                                           name="user_ids[]" value="{{ $user->id }}" 
                                           id="user_{{ $user->id }}">
                                    <label class="form-check-label d-flex align-items-center" for="user_{{ $user->id }}">
                                        @if($user->profile_picture)
                                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 14px;">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>{{ __('passwords.assign_accounts') }}
                        </button>
                        <a href="{{ route('password-accounts.index') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('passwords.selected_accounts') }}</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($accounts as $account)
                        <div class="list-group-item d-flex align-items-center">
                            @if($account->icon)
                                <img src="{{ $account->icon }}" alt="{{ $account->name }}" 
                                     class="me-2" style="width: 24px; height: 24px;">
                            @else
                                <i class="fas fa-key me-2 text-muted"></i>
                            @endif
                            <div>
                                <div class="fw-bold">{{ $account->name }}</div>
                                <small class="text-muted">{{ $account->email }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('passwords.batch_assign_info', ['count' => $accounts->count()]) }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectAllUsers() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllUsers() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
    
    if (selectedUsers.length === 0) {
        e.preventDefault();
        alert('{{ __("passwords.please_select_at_least_one_user") }}');
        return false;
    }
});
</script>
@endpush
