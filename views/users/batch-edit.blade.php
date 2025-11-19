@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'التعديل المجمع للموظفين - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <h2 class="section-title me-4"><i class="fas fa-edit me-2"></i>التعديل المجمع للموظفين</h2>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>العودة لقائمة الموظفين
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            يمكنك استخدام هذه الصفحة لتعديل عدة موظفين في نفس الوقت. اختر الموظفين المطلوبين من الجدول أدناه.
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="modern-search-container">
                    <form method="GET" action="{{ route('users.batch-edit') }}" class="modern-search-form">
                        <input type="text" 
                               class="modern-search-input" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="البحث عن الموظفين..."
                               autocomplete="off">
                        <button class="modern-search-btn" type="submit" title="البحث">
                            <i class="fas fa-search search-icon"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('users.batch-edit') }}" class="modern-search-clear" title="مسح البحث">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="selectAllBtn">
                        <i class="fas fa-check-square me-2"></i>تحديد الكل
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="deselectAllBtn">
                        <i class="fas fa-square me-2"></i>إلغاء التحديد
                    </button>
                </div>
            </div>
        </div>

        <!-- Batch Edit Form -->
        <form id="batchEditForm" method="POST" action="{{ route('users.batch-update') }}">
            @csrf
            @method('PUT')
            
            <!-- Batch Edit Controls -->
            <div class="card mb-4" id="batchEditControls" style="display: none;">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل <span id="selectedCount">0</span> موظف
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="batch_department" class="form-label">القسم</label>
                            <select class="form-select" name="department_id" id="batch_department">
                                <option value="">لا تغيير</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">
                                        {{ app()->getLocale() == 'ar' ? $department->name_ar : $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="batch_role" class="form-label">المنصب</label>
                            <select class="form-select" name="role_id" id="batch_role">
                                <option value="">لا تغيير</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">
                                        {{ app()->getLocale() == 'ar' ? $role->name_ar : $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="batch_manager" class="form-label">المدير</label>
                            <select class="form-select" name="manager_id" id="batch_manager">
                                <option value="">لا تغيير</option>
                                <option value="0">إزالة المدير</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="batch_location" class="form-label">الموقع</label>
                            <input type="text" class="form-control" name="office_address" id="batch_location" placeholder="عنوان المكتب">
                        </div>
                        <div class="col-md-6">
                            <label for="batch_company" class="form-label">الشركة</label>
                            <input type="text" class="form-control" name="company" id="batch_company" placeholder="اسم الشركة">
                        </div>
                        <div class="col-md-6">
                            <label for="batch_job_title" class="form-label">المسمى الوظيفي</label>
                            <input type="text" class="form-control" name="job_title" id="batch_job_title" placeholder="المسمى الوظيفي">
                        </div>
                        <div class="col-md-6">
                            <label for="batch_status" class="form-label">الحالة</label>
                            <select class="form-select" name="is_active" id="batch_status">
                                <option value="">لا تغيير</option>
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning me-2">
                                <i class="fas fa-save me-2"></i>تطبيق التغييرات
                            </button>
                            <button type="button" class="btn btn-danger me-2" id="batchDeleteBtn">
                                <i class="fas fa-trash me-2"></i>حذف المحدد
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearFormBtn">
                                <i class="fas fa-times me-2"></i>مسح النموذج
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
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
                            <th>{{ __('messages.manager') }}</th>
                            <th>{{ __('messages.work_phone') }}</th>
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
                                                <img src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}" class="user-avatar me-2" onerror="this.style.display='none';">
                                            @endif
                                            {{ $user->name }}
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->department)
                                            <span class="badge bg-info">{{ $user->department->name }}</span>
                                        @else
                                            <span class="text-muted">لا يوجد</span>
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
                                        @if($user->job_title)
                                            <span class="badge bg-info">{{ $user->job_title }}</span>
                                        @else
                                            <span class="text-muted">{{ __('messages.no_data') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->manager)
                                            {{ $user->manager->name }}
                                        @else
                                            <span class="text-muted">{{ __('messages.no_data') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->phone_work)
                                            {{ $user->phone_work }}
                                        @else
                                            <span class="text-muted">لا يوجد</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center">
                    {{ $users->appends(request()->query())->links('pagination.bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا يوجد موظفين</h5>
                    <p class="text-muted">لم يتم العثور على أي موظفين للعرض</p>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Batch Delete Form (Hidden) -->
<form id="batchDeleteForm" method="POST" action="{{ route('users.batch-delete') }}" style="display: none;">
    @csrf
    @method('DELETE')
    <div id="batchDeleteInputs"></div>
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الحذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تحذير!</strong> هذا الإجراء لا يمكن التراجع عنه.
                </div>
                <p>هل أنت متأكد من أنك تريد حذف <strong><span id="deleteCount">0</span></strong> موظف؟</p>
                <p class="text-muted">سيتم حذف جميع بيانات هؤلاء الموظفين نهائياً من النظام.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>إلغاء
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>تأكيد الحذف
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const batchEditControls = document.getElementById('batchEditControls');
    const selectedCountSpan = document.getElementById('selectedCount');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const clearFormBtn = document.getElementById('clearFormBtn');
    const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBatchEditControls();
    });
    
    // Individual checkboxes
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateBatchEditControls();
        });
    });
    
    // Select All button
    selectAllBtn.addEventListener('click', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAllCheckbox.checked = true;
        updateBatchEditControls();
    });
    
    // Deselect All button
    deselectAllBtn.addEventListener('click', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        updateBatchEditControls();
    });
    
    // Clear form button
    clearFormBtn.addEventListener('click', function() {
        document.getElementById('batchEditForm').reset();
    });
    
    // Batch delete button
    batchDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('يرجى اختيار موظف واحد على الأقل للحذف');
            return;
        }
        
        document.getElementById('deleteCount').textContent = checkedBoxes.length;
        deleteConfirmationModal.show();
    });
    
    // Confirm delete button
    confirmDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        // Add user IDs to delete form
        const deleteInputs = document.getElementById('batchDeleteInputs');
        deleteInputs.innerHTML = '';
        
        userIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            deleteInputs.appendChild(input);
        });
        
        // Submit delete form
        document.getElementById('batchDeleteForm').submit();
    });
    
    function updateSelectAllState() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        selectAllCheckbox.checked = checkedBoxes.length === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < userCheckboxes.length;
    }
    
    function updateBatchEditControls() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            batchEditControls.style.display = 'block';
            selectedCountSpan.textContent = count;
            
            // Add hidden inputs for selected user IDs
            const form = document.getElementById('batchEditForm');
            const existingInputs = form.querySelectorAll('input[name="user_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
        } else {
            batchEditControls.style.display = 'none';
        }
    }
    
    // Form submission
    document.getElementById('batchEditForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('يرجى اختيار موظف واحد على الأقل للتعديل');
            return false;
        }
        
        const hasChanges = Array.from(this.elements).some(element => {
            if (element.type === 'text' || element.type === 'select-one') {
                return element.value.trim() !== '';
            }
            return false;
        });
        
        if (!hasChanges) {
            e.preventDefault();
            alert('يرجى تحديد تغيير واحد على الأقل لتطبيقه');
            return false;
        }
        
        if (!confirm(`هل أنت متأكد من تطبيق التغييرات على ${checkedBoxes.length} موظف؟`)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
