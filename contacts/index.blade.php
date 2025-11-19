@extends('layouts.app')

@section('title', $trans('contacts') . ' - ' . $trans('system_title'))

@section('content')
<style>

/* Toggle Switch Styles */
.toggle {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Custom Toggle Switch */
.custom-toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    margin: 0;
}

.custom-toggle input[type="checkbox"] {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.custom-toggle .slider {
    position: relative;
    width: 50px;
    height: 24px;
    background-color: #6c757d;
    border-radius: 24px;
    transition: all 0.3s ease;
    margin-left: 10px;
}

.custom-toggle .slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.custom-toggle input:checked + .slider {
    background-color: #28a745;
}

.custom-toggle input:checked + .slider:before {
    transform: translateX(26px);
}

.custom-toggle .toggle-text {
    font-size: 0.9rem;
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
}

.custom-toggle:hover .slider {
    box-shadow: 0 0 8px rgba(0,0,0,0.2);
}

/* RTL Support */
[dir="rtl"] .custom-toggle .slider {
    margin-left: 0;
    margin-right: 10px;
}


</style>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <h2><i class="fas fa-address-book me-2"></i>{{ $trans('contacts') }}</h2>
        <div class="ms-3 d-flex align-items-center">
            <label class="custom-toggle">
                <input type="checkbox" id="hideEmployeesToggle" checked>
                <span class="slider"></span>
                <span class="toggle-text">{{ $trans('hide_employees') }}</span>
            </label>
            <small class="ms-2 text-muted" id="filterStatus">
                <i class="fas fa-info-circle me-1"></i>
                {{ request('hide_employees', '1') === '1' ? $trans('suppliers_only') : $trans('all_contacts') }}
                <span class="badge bg-secondary ms-1">{{ $contacts->total() }}</span>
            </small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('contacts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>{{ $trans('add_contact') }}
        </a>
        <a href="{{ route('image-editor') }}" class="btn btn-info">
            <i class="fas fa-image me-2"></i>محرر الصور
        </a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="fas fa-download me-2"></i>{{ $trans('export') }}
        </button>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('contacts.index') }}" class="row g-3" id="contactsFilterForm">
            <input type="hidden" id="hideEmployees" name="hide_employees" value="{{ request('hide_employees', '1') }}">
            <div class="col-md-4">
                <label for="search" class="form-label">{{ $trans('search') }}</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="{{ $trans('search_contacts') }}">
            </div>
            <div class="col-md-3" id="departmentFilter">
                <label for="department_id" class="form-label">{{ $trans('department') }}</label>
                <select class="form-select" id="department_id" name="department_id">
                    <option value="">{{ $trans('all_departments') }}</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3" id="roleFilter">
                <label for="role_id" class="form-label">{{ $trans('role') }}</label>
                <select class="form-select" id="role_id" name="role_id">
                    <option value="">{{ $trans('all_roles') }}</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>{{ $trans('apply') }}
                    </button>
                    <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>{{ $trans('clear') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Contacts Grid -->
<div class="row">
    @forelse($contacts as $contact)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 contact-card" data-entity-id="{{ $contact->id }}" data-view-route="contacts.show">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        @if($contact->profile_picture)
                            <img src="{{ asset($contact->profile_picture) }}" 
                                 alt="{{ $contact->name }}" 
                                 class="avatar rounded-circle me-3" 
                                 style="width: 50px; height: 50px; object-fit: cover;"
                                 onerror="this.src='{{ asset('images/default-avatar.png')}}'">
                        @else
                            <img src="{{ asset('images/default-avatar.png') }}" 
                                 alt="{{ $contact->name }}" 
                                 class="avatar rounded-circle me-3" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        @endif
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">{{ $contact->name }}</h5>
                            <p class="text-muted mb-1">{{ $contact->job_title ?? $trans('not_specified') }}</p>
                            @if($contact->department)
                                <span class="badge bg-info">{{ $contact->department->name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="contact-info">
                        @if($contact->email)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:{{ $contact->email }}" class="text-decoration-none">
                                    {{ $contact->email }}
                                </a>
                            </div>
                        @endif

                        @if($contact->phone_work)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-phone text-success me-2"></i>
                                <a href="tel:{{ $contact->phone_work }}" class="text-decoration-none">
                                    {{ $contact->phone_work }}
                                </a>
                            </div>
                        @endif

                        @if(isset($contact->phone_mobile) && $contact->phone_mobile)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-mobile-alt text-info me-2"></i>
                                <a href="tel:{{ $contact->phone_mobile }}" class="text-decoration-none">
                                    {{ $contact->phone_mobile }}
                                </a>
                            </div>
                        @endif

                        @if($contact->manager)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user-tie text-warning me-2"></i>
                                <span class="text-muted">{{ $trans('reports_to') }}: {{ $contact->manager->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="btn-group">
                            <a href="{{ route('users.contact-card', $contact) }}" class="btn btn-sm btn-outline-primary" 
                               title="{{ $trans('contact_card') }}">
                                <i class="fas fa-id-card"></i>
                            </a>
                            <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-info" 
                               title="{{ $trans('view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        <small class="text-muted">
                            {{ $contact->created_at ? $contact->created_at->diffForHumans() : $trans('not_available') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ $trans('no_contacts_found') }}</h5>
                <p class="text-muted">{{ $trans('no_contacts_description') }}</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($contacts->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $contacts->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
@endif

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $trans('export') }} {{ $trans('contacts') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ $trans('export_description') }}</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ $trans('export_format_info') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $trans('cancel') }}</button>
                <a href="{{ route('contacts.export') }}" class="btn btn-success">
                    <i class="fas fa-download me-2"></i>{{ $trans('export') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('hideEmployeesToggle');
    const hideEmployeesInput = document.getElementById('hideEmployees');
    const statusText = document.getElementById('filterStatus');
    const departmentFilter = document.getElementById('departmentFilter');
    const roleFilter = document.getElementById('roleFilter');
    
    // Set initial state based on URL parameter
    const hideEmployees = hideEmployeesInput.value;
    toggle.checked = hideEmployees === '1';
    updateToggleText();
    updateFilterVisibility();
    
    // Handle toggle change
    toggle.addEventListener('change', function() {
        const isChecked = this.checked;
        const hideEmployeesValue = isChecked ? '1' : '0';
        hideEmployeesInput.value = hideEmployeesValue;
        
        updateToggleText();
        updateFilterVisibility();
        
        // Clear department and role filters when hiding employees
        if (isChecked) {
            document.getElementById('department_id').value = '';
            document.getElementById('role_id').value = '';
        }
        
        // Submit form to apply filter
        document.getElementById('contactsFilterForm').submit();
    });
    
    function updateToggleText() {
        const toggleText = document.querySelector('.toggle-text');
        if (toggle.checked) {
            toggleText.textContent = '{{ $trans("hide_employees") }}';
            statusText.innerHTML = '<i class="fas fa-info-circle me-1"></i>{{ $trans("suppliers_only") }} <span class="badge bg-secondary ms-1">...</span>';
        } else {
            toggleText.textContent = '{{ $trans("show_employees") }}';
            statusText.innerHTML = '<i class="fas fa-info-circle me-1"></i>{{ $trans("all_contacts") }} <span class="badge bg-secondary ms-1">...</span>';
        }
    }
    
    function updateFilterVisibility() {
        const isChecked = toggle.checked;
        if (isChecked) {
            // Hide employee-related filters when showing suppliers only
            departmentFilter.style.display = 'none';
            roleFilter.style.display = 'none';
        } else {
            // Show all filters when showing all contacts
            departmentFilter.style.display = 'block';
            roleFilter.style.display = 'block';
        }
    }
    
    // Quick search functionality
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (this.value.length >= 2) {
                    // يمكن إضافة AJAX search هنا
                }
            }.bind(this), 300);
        });
    }
});
</script>
@endpush
