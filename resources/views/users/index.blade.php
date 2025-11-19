@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', __('messages.user_management') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <h2 class="section-title me-4"><i class="fas fa-users me-2"></i>{{ __('messages.user_management') }}</h2>
        <div class="modern-search-container">
            <form method="GET" action="{{ route('users.index') }}" class="modern-search-form">
                <input type="text" 
                       class="modern-search-input" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('messages.search_users') }}..."
                       autocomplete="off">
                <button class="modern-search-btn" type="submit" title="{{ __('messages.search') }}">
                    <i class="fas fa-search search-icon"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('users.index') }}" class="modern-search-clear" title="{{ __('messages.clear_search') }}">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>{{ __('messages.add_new_user') }}
        </a>
        <a href="{{ route('users.batch-edit') }}" class="btn btn-warning" id="batchEditBtn" style="display: none;">
            <i class="fas fa-edit me-2"></i>{{ __('messages.batch_edit') }}
        </a>
        <a href="{{ route('users.archived') }}" class="btn btn-outline-secondary">
            <i class="fas fa-archive me-2"></i>{{ __('messages.archive') }}
        </a>
        
        <!-- Language Toggle -->
        <div class="btn-group" role="group">
            <a href="{{ route('users.index', array_merge(request()->query(), ['locale' => 'ar'])) }}" 
               class="btn btn-outline-info {{ app()->getLocale() == 'ar' ? 'active' : '' }}" 
               title="{{ __('messages.arabic') }}">
                <i class="fas fa-language me-1"></i>ع
            </a>
            <a href="{{ route('users.index', array_merge(request()->query(), ['locale' => 'en'])) }}" 
               class="btn btn-outline-info {{ app()->getLocale() == 'en' ? 'active' : '' }}" 
               title="{{ __('messages.english') }}">
                <i class="fas fa-language me-1"></i>EN
            </a>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" id="filtersForm">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="department_filter" class="form-label">{{ __('messages.department') }}</label>
                    <select class="form-select modern-select" name="department" id="department_filter">
                        <option value="">{{ __('messages.all_departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? $department->name_ar : $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="role_filter" class="form-label">{{ __('messages.role') }}</label>
                    <select class="form-select modern-select" name="role" id="role_filter">
                        <option value="">{{ __('messages.all_roles') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? $role->name_ar : $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="job_title_filter" class="form-label">{{ __('messages.job_title') }}</label>
                    <select class="form-select modern-select" name="job_title" id="job_title_filter">
                        <option value="">{{ __('messages.all_job_titles') }}</option>
                        @foreach($jobTitles as $jobTitle)
                            <option value="{{ $jobTitle }}" {{ request('job_title') == $jobTitle ? 'selected' : '' }}>
                                {{ $jobTitle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="manager_filter" class="form-label">{{ __('messages.manager') }}</label>
                    <select class="form-select modern-select" name="manager" id="manager_filter">
                        <option value="">{{ __('messages.all_managers') }}</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ request('manager') == $manager->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? ($manager->name_ar ?: $manager->name) : $manager->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">{{ __('messages.status') }}</label>
                    <select class="form-select modern-select" name="status" id="status_filter">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="colleagues_filter" class="form-label">{{ app()->getLocale() == 'ar' ? 'الفلترة' : 'Filter' }}</label>
                    <select class="form-select modern-select" name="colleagues_only" id="colleagues_filter">
                        <option value="">{{ app()->getLocale() == 'ar' ? 'جميع المستخدمين' : 'All Users' }}</option>
                        <option value="1" {{ request('colleagues_only') == '1' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'زملائي فقط' : 'My Colleagues Only' }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="hire_date_from" class="form-label">{{ __('messages.hire_date') }} ({{ __('messages.from') }})</label>
                    <input type="date" class="form-control custom-date-input" id="hire_date_from" name="hire_date_from" value="{{ request('hire_date_from') }}" data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" style="text-align: center; font-weight: 500;">
                </div>
                <div class="col-md-2">
                    <label for="hire_date_to" class="form-label">{{ __('messages.hire_date') }} ({{ __('messages.to') }})</label>
                    <input type="date" class="form-control custom-date-input" id="hire_date_to" name="hire_date_to" value="{{ request('hire_date_to') }}" data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" style="text-align: center; font-weight: 500;">
                </div>
                <div class="col-md-2">
                    <label for="birthday_from" class="form-label">{{ __('messages.birthday') }} ({{ __('messages.from') }})</label>
                    <input type="date" class="form-control custom-date-input" id="birthday_from" name="birthday_from" value="{{ request('birthday_from') }}" data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" style="text-align: center; font-weight: 500;">
                </div>
                <div class="col-md-2">
                    <label for="birthday_to" class="form-label">{{ __('messages.birthday') }} ({{ __('messages.to') }})</label>
                    <input type="date" class="form-control custom-date-input" id="birthday_to" name="birthday_to" value="{{ request('birthday_to') }}" data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" style="text-align: center; font-weight: 500;">
                </div>
                <div class="col-md-2">
                    <label for="per_page_filter" class="form-label">{{ __('messages.per_page') }}</label>
                    <select class="form-select modern-select" name="per_page" id="per_page_filter">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.all') }}</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>{{ __('messages.apply_filters') }}
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>{{ __('messages.clear_filters') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($users->count() > 0)
            @if(request('search'))
                <div class="search-results-info">
                    <div class="alert">
                        <i class="fas fa-search me-2"></i>
                        {{ __('messages.search_results_for') }}: "<strong>{{ request('search') }}</strong>" 
                        ({{ $users->total() }} {{ __('messages.results') }})
                    </div>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable" data-view-route="users.show">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.department') }}</th>
                            <th>{{ __('messages.role') }}</th>
                            <th>{{ __('messages.job_title') }}</th>
                            <th>{{ __('messages.work_phone') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($user->profile_picture)
                                        <img src="{{ Storage::url($user->profile_picture) }}" 
                                             alt="{{ $user->name }}" 
                                             class="user-avatar me-2 profile-image-clickable" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             data-image-src="{{ Storage::url($user->profile_picture) }}"
                                             data-image-alt="{{ $user->name }}"
                                             onerror="this.src='{{ asset('images/default-avatar.png') }}'" 
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; cursor: pointer;">
                                        @else
                                        <img src="{{ asset('images/default-avatar.png') }}" 
                                             alt="{{ $user->name }}" 
                                             class="user-avatar me-2 profile-image-clickable" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             data-image-src="{{ asset('images/default-avatar.png') }}"
                                             data-image-alt="{{ $user->name }}"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; cursor: pointer;">
                                        @endif
                                        {{ app()->getLocale() == 'ar' ? ($user->name_ar ?: $user->name) : $user->name }}
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->department)
                                        <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? ($user->department->name_ar ?: $user->department->name) : $user->department->name }}</span>
                                    @else
                                        <span class="text-muted">{{ __('messages.no_data') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role)
                                        <span class="badge bg-{{ $user->role->slug == 'software_developer' ? 'danger' : ($user->role->slug == 'head_manager' ? 'warning' : ($user->role->slug == 'team_leader' ? 'info' : 'success')) }}">
                                            {{ app()->getLocale() == 'ar' ? $user->role->name_ar : $user->role->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('messages.no_data') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->position || $user->job_title)
                                        <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? ($user->position_ar ?: $user->position ?: $user->job_title) : ($user->position ?: $user->job_title) }}</span>
                                    @else
                                        <span class="text-muted">{{ __('messages.no_data') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Get primary work phone
                                        $primaryWorkPhone = $user->phones->filter(function($phone) {
                                            return $phone->phoneType && $phone->phoneType->slug === 'work' && $phone->is_primary;
                                        })->first();
                                        
                                        // Fallback to phone_work field if no primary phone found
                                        $displayPhone = $primaryWorkPhone ? $primaryWorkPhone->phone_number : ($user->phone_work ?? null);
                                    @endphp
                                    @if($displayPhone)
                                        <span>{{ $displayPhone }}</span>
                                        @if($primaryWorkPhone && $user->phones->filter(function($phone) {
                                            return $phone->phoneType && $phone->phoneType->slug === 'work';
                                        })->count() > 1)
                                            <span class="badge bg-info ms-1" title="يوجد {{ $user->phones->filter(function($phone) {
                                                return $phone->phoneType && $phone->phoneType->slug === 'work';
                                            })->count() }} أرقام عمل">
                                                +{{ $user->phones->filter(function($phone) {
                                                    return $phone->phoneType && $phone->phoneType->slug === 'work';
                                                })->count() - 1 }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">{{ __('messages.no_data') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.contact-card', $user) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.contact_card') }}">
                                            <i class="fas fa-id-card"></i>
                                        </a>
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($user->department)
                                            <a href="{{ route('users.create', ['department_id' => $user->department->id, 'manager_id' => $user->department->manager_id]) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="إضافة زميل في {{ $user->department->name }}">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.archive', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_archive_user') }}')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.archive') }}">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" data-confirm="{{ __('messages.confirm_delete_user') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.cannot_delete_self') }}" disabled>
                                            <i class="fas fa-archive"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.cannot_delete_self') }}" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages() || request('per_page') == 'all')
            <div class="d-flex justify-content-center mt-4">
                {{ $users->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
            @endif
        @else
            <div class="text-center py-5">
                @if(request('search'))
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('messages.no_search_results') }}</h5>
                    <p class="text-muted">{{ __('messages.try_different_search') }}</p>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('messages.show_all_users') }}
                    </a>
                @else
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('messages.no_users_found') }}</h5>
                    <p class="text-muted">{{ __('messages.start_adding_users') }}</p>
                    <div class="btn-group" role="group">
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>{{ __('messages.add_user') }}
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Batch Edit Modal -->
<div class="modal fade" id="batchEditModal" tabindex="-1" aria-labelledby="batchEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchEditModalLabel">{{ __('messages.batch_edit_employees') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="batchEditForm" method="POST" action="{{ route('users.batch-update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.will_apply_changes_to') }} <span id="selectedCount">0</span> {{ __('messages.employees') }}
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="batch_department" class="form-label">{{ __('messages.department') }}</label>
                            <select class="form-select" name="department_id" id="batch_department">
                                <option value="">{{ __('messages.no_change') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">
                                        {{ app()->getLocale() == 'ar' ? $department->name_ar : $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="batch_role" class="form-label">{{ __('messages.role') }}</label>
                            <select class="form-select" name="role_id" id="batch_role">
                                <option value="">{{ __('messages.no_change') }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">
                                        {{ app()->getLocale() == 'ar' ? $role->name_ar : $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="batch_manager" class="form-label">{{ __('messages.manager') }}</label>
                            <select class="form-select" name="manager_id" id="batch_manager">
                                <option value="">{{ __('messages.no_change') }}</option>
                                <option value="0">{{ __('messages.remove_manager') }}</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ app()->getLocale() == 'ar' ? ($manager->name_ar ?: $manager->name) : $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="batch_location" class="form-label">{{ __('messages.location') }}</label>
                            <input type="text" class="form-control" name="office_address" id="batch_location" placeholder="{{ __('messages.office_address') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="batch_company" class="form-label">{{ __('messages.company') }}</label>
                            <input type="text" class="form-control" name="company" id="batch_company" placeholder="{{ __('messages.company_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="batch_job_title" class="form-label">{{ __('messages.job_title') }}</label>
                            <input type="text" class="form-control" name="job_title" id="batch_job_title" placeholder="{{ __('messages.job_title') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.apply_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const batchEditBtn = document.getElementById('batchEditBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    // Select All checkbox
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBatchEditButton();
    });
    
    // Individual checkboxes
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateBatchEditButton();
        });
    });
    
    function updateSelectAllState() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        selectAllCheckbox.checked = checkedBoxes.length === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < userCheckboxes.length;
    }
    
    function updateBatchEditButton() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            batchEditBtn.style.display = 'inline-block';
            selectedCountSpan.textContent = count;
        } else {
            batchEditBtn.style.display = 'none';
        }
    }
    
    // Batch Edit Modal
    const batchEditModal = new bootstrap.Modal(document.getElementById('batchEditModal'));
    
    batchEditBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        // Add hidden inputs for selected user IDs
        const form = document.getElementById('batchEditForm');
        const existingInputs = form.querySelectorAll('input[name="user_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        userIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        batchEditModal.show();
    });
    
    // Auto-submit filters on change
    const filterSelects = document.querySelectorAll('.modern-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    });
    
    // Auto-submit date filters on change
    const dateInputs = document.querySelectorAll('.custom-date-input');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    });
    
    // Handle delete confirmation
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
/* Image Modal Styles */
.profile-image-clickable {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-image-clickable:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.image-modal .modal-dialog {
    max-width: 90vw;
    max-height: 90vh;
}

.image-modal .modal-content {
    background: transparent;
    border: none;
    box-shadow: none;
}

.image-modal .modal-body {
    padding: 0;
    text-align: center;
}

.image-modal img {
    max-width: 100%;
    max-height: 80vh;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.image-modal .btn-close {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 1050;
    background: rgba(0,0,0,0.7);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    opacity: 1;
}

.image-modal .btn-close:hover {
    background: rgba(0,0,0,0.9);
}

/* Custom Date Input Styles */
.custom-date-input::placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input::-webkit-input-placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input::-moz-placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input:-ms-input-placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input:-moz-placeholder {
    color: #fff !important;
    opacity: 1;
}
</style>
@endpush

<!-- Image Modal -->
<div class="modal fade image-modal" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-times text-white"></i>
            </button>
            <div class="modal-body">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle image modal
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    imageModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imageSrc = button.getAttribute('data-image-src');
        const imageAlt = button.getAttribute('data-image-alt');
        
        modalImage.src = imageSrc;
        modalImage.alt = imageAlt;
    });
    
    // Add click effect to profile images
    const profileImages = document.querySelectorAll('.profile-image-clickable');
    profileImages.forEach(function(img) {
        img.addEventListener('click', function() {
            // Add a subtle click effect
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1.1)';
            }, 100);
        });
    });
});
</script>
@endpush
