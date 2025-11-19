@extends('layouts.app')

@section('title', __('passwords.password_management'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-key me-2"></i>{{ __('passwords.password_management') }}
    </h2>
    <div class="d-flex gap-2">
        @can('create', App\Models\PasswordAccount::class)
        <a href="{{ route('password-accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>{{ __('passwords.add_new_credentials') }}
        </a>
        @endcan
        
        @can('create', App\Models\PasswordAccount::class)
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="batchActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-tasks me-2"></i>{{ __('passwords.batch_actions') }}
            </button>
            <ul class="dropdown-menu" aria-labelledby="batchActionsDropdown">
                <li><a class="dropdown-item" href="#" onclick="selectAllAccounts()">
                    <i class="fas fa-check-square me-2"></i>{{ __('passwords.select_all') }}
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="deselectAllAccounts()">
                    <i class="fas fa-square me-2"></i>{{ __('passwords.deselect_all') }}
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="batchDelete()">
                    <i class="fas fa-trash me-2 text-danger"></i>{{ __('passwords.batch_delete') }}
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="batchExport()">
                    <i class="fas fa-download me-2 text-info"></i>{{ __('passwords.batch_export') }}
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="batchAssign()">
                    <i class="fas fa-user-plus me-2 text-success"></i>{{ __('passwords.batch_assign') }}
                </a></li>
            </ul>
        </div>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $allAccounts->count() }}</h4>
                        <p class="mb-0">{{ __('passwords.total_accounts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $allAccounts->where('is_active', true)->count() }}</h4>
                        <p class="mb-0">{{ __('passwords.active_accounts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $allAccounts->where('expires_at', '<=', now()->addDays(7))->where('expires_at', '>', now())->count() }}</h4>
                        <p class="mb-0">{{ __('passwords.expiring_soon_accounts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $allAccounts->where('is_shared', true)->count() }}</h4>
                        <p class="mb-0">{{ __('passwords.shared_accounts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-share-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('password-accounts.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">{{ __('passwords.search_accounts') }}</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="{{ __('passwords.search_accounts') }}">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">{{ __('passwords.filter_by_category') }}</label>
                <select class="form-select" id="category" name="category">
                    <option value="">{{ __('passwords.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category['value'] }}" 
                                {{ request('category') == $category['value'] ? 'selected' : '' }}>
                            {{ $category['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">{{ __('passwords.filter_by_status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('passwords.all_statuses') }}</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>
                        {{ __('passwords.expired') }}
                    </option>
                    <option value="expiring_soon" {{ request('status') == 'expiring_soon' ? 'selected' : '' }}>
                        {{ __('passwords.expiring_soon') }}
                    </option>
                    <option value="shared" {{ request('status') == 'shared' ? 'selected' : '' }}>
                        {{ __('passwords.shared') }}
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="employee" class="form-label">{{ __('Filter by Employee') }}</label>
                <select class="form-select" id="employee" name="employee">
                    <option value="">{{ __('All Employees') }}</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                                {{ request('employee') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>{{ __('Search') }}
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status', 'employee']))
                        <a href="{{ route('password-accounts.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>{{ __('Clear') }}
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Accounts Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('passwords.password_accounts') }}</h5>
    </div>
    <div class="card-body">
        @if($accounts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" data-view-route="password-accounts.show">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllAccounts(this)">
                            </th>
                            <th>{{ __('passwords.account_name') }}</th>
                            <th>{{ __('passwords.email_username') }}</th>
                            <th>{{ __('passwords.category') }}</th>
                            <th>{{ __('passwords.status') }}</th>
                            <th>{{ __('passwords.expires_at') }}</th>
                            <th>{{ __('passwords.assigned_users') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td>
                                    <input type="checkbox" class="account-checkbox" value="{{ $account->id }}" onchange="updateBatchActions()">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($account->icon)
                                            <img src="{{ $account->icon }}" alt="{{ $account->display_name }}" 
                                                 class="me-2" style="width: 24px; height: 24px;">
                                        @else
                                            <i class="fas fa-key me-2 text-muted"></i>
                                        @endif
                                        <div>
                                            <strong>{{ $account->display_name }}</strong>
                                            @if($account->requires_2fa)
                                                <i class="fas fa-shield-alt text-warning ms-1" 
                                                   title="{{ __('passwords.requires_2fa') }}"></i>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $account->email ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $account->display_category ?: '-' }}</span>
                                </td>
                                <td>
                                    @if($account->isExpired())
                                        <span class="badge bg-danger">{{ __('passwords.expired') }}</span>
                                    @elseif($account->isExpiringSoon())
                                        <span class="badge bg-warning">{{ __('passwords.expiring_soon') }}</span>
                                    @elseif($account->is_shared)
                                        <span class="badge bg-info">{{ __('passwords.shared') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('passwords.active') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($account->expires_at)
                                        {{ $account->expires_at->format('Y-m-d') }}
                                        @if($account->isExpiringSoon())
                                            <i class="fas fa-exclamation-triangle text-warning ms-1"></i>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($account->assignments->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($account->assignments->take(3) as $assignment)
                                                <span class="badge bg-primary" title="{{ $assignment->user->name }}">
                                                    {{ $assignment->user->name }}
                                                </span>
                                            @endforeach
                                            @if($account->assignments->count() > 3)
                                                <span class="badge bg-secondary">
                                                    +{{ $account->assignments->count() - 3 }} {{ __('more') }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">{{ __('Not assigned') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @can('view', $account)
                                            <a href="{{ route('password-accounts.show', $account) }}" 
                                               class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        
                                        @can('viewPassword', $account)
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    data-account-id="{{ $account->id }}"
                                                    onclick="showPassword(this.dataset.accountId)" 
                                                    title="{{ __('passwords.view_password') }}">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        @endcan
                                        
                                        @can('update', $account)
                                            <a href="{{ route('password-accounts.edit', $account) }}" 
                                               class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        
                                        @can('delete', $account)
                                            <form method="POST" action="{{ route('password-accounts.destroy', $account) }}" 
                                                  class="d-inline" onsubmit="return confirm('{{ __("Are you sure?") }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('passwords.delete_account') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $accounts->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-key fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('passwords.no_accounts_found') }}</h5>
                @can('create', App\Models\PasswordAccount::class)
                    <a href="{{ route('password-accounts.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>{{ __('passwords.create_account') }}
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('passwords.view_password') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('passwords.view_password_warning') }}
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" id="passwordField" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                    <button class="btn btn-outline-primary" type="button" onclick="copyPassword()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                    </button>
                </div>
                <div id="passwordInfo" class="mt-3"></div>
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
function showPassword(accountId) {
    fetch(`/password-accounts/${accountId}/password`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('passwordField').value = data.password;
        document.getElementById('passwordInfo').innerHTML = 
            data.requires_2fa ? '<small class="text-warning"><i class="fas fa-shield-alt me-1"></i>{{ __("passwords.requires_2fa") }}</small>' : '';
        
        const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("Error loading password") }}');
    });
}

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

// Batch Actions Functions
function toggleAllAccounts(selectAllCheckbox) {
    const accountCheckboxes = document.querySelectorAll('.account-checkbox');
    accountCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateBatchActions();
}

function selectAllAccounts() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = true;
    toggleAllAccounts(selectAllCheckbox);
}

function deselectAllAccounts() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = false;
    toggleAllAccounts(selectAllCheckbox);
}

function updateBatchActions() {
    const selectedAccounts = document.querySelectorAll('.account-checkbox:checked');
    const batchActionsDropdown = document.getElementById('batchActionsDropdown');
    
    if (selectedAccounts.length > 0) {
        batchActionsDropdown.classList.remove('btn-outline-secondary');
        batchActionsDropdown.classList.add('btn-primary');
        batchActionsDropdown.innerHTML = `<i class="fas fa-tasks me-2"></i>{{ __('passwords.batch_actions') }} (${selectedAccounts.length})`;
    } else {
        batchActionsDropdown.classList.remove('btn-primary');
        batchActionsDropdown.classList.add('btn-outline-secondary');
        batchActionsDropdown.innerHTML = `<i class="fas fa-tasks me-2"></i>{{ __('passwords.batch_actions') }}`;
    }
}

function batchDelete() {
    const selectedAccounts = Array.from(document.querySelectorAll('.account-checkbox:checked')).map(cb => cb.value);
    
    if (selectedAccounts.length === 0) {
        alert('{{ __("passwords.no_accounts_selected") }}');
        return;
    }
    
    if (confirm(`{{ __('passwords.confirm_batch_delete') }} ${selectedAccounts.length} {{ __('passwords.accounts') }}?`)) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("password-accounts.batch-delete") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method override
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Add selected accounts
        selectedAccounts.forEach(accountId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'account_ids[]';
            input.value = accountId;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

function batchExport() {
    const selectedAccounts = Array.from(document.querySelectorAll('.account-checkbox:checked')).map(cb => cb.value);
    
    if (selectedAccounts.length === 0) {
        alert('{{ __("passwords.no_accounts_selected") }}');
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("password-accounts.batch-export") }}';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add selected accounts
    selectedAccounts.forEach(accountId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'account_ids[]';
        input.value = accountId;
        form.appendChild(input);
    });
    
    // Add export format
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = 'csv';
    form.appendChild(formatInput);
    
    document.body.appendChild(form);
    form.submit();
}

function batchAssign() {
    const selectedAccounts = Array.from(document.querySelectorAll('.account-checkbox:checked')).map(cb => cb.value);
    
    if (selectedAccounts.length === 0) {
        alert('{{ __("passwords.no_accounts_selected") }}');
        return;
    }
    
    // Show user selection modal
    showBatchAssignModal(selectedAccounts);
}

function showBatchAssignModal(accountIds) {
    // This would open a modal to select users for batch assignment
    // For now, we'll redirect to a batch assign page
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '{{ route("password-accounts.batch-assign") }}';
    
    accountIds.forEach(accountId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'account_ids[]';
        input.value = accountId;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
