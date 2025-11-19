@extends('layouts.app')

@section('title', __('messages.edit_department') . ' - ' . __('messages.system_title'))

@section('content')
@php
    $trans = function($key) {
        return __('messages.' . $key);
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title"><i class="fas fa-edit me-2"></i>{{ $trans('edit_department') }}</h2>
    <a href="{{ route('departments.show', $department) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>{{ $trans('back') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('departments.update', $department) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- اسم القسم بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">{{ $trans('department_name_en') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $department->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- اسم القسم بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="name_ar" class="form-label">{{ $trans('department_name_ar') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar', $department->name_ar) }}" required>
                    @error('name_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- مدير القسم -->
                <div class="col-md-6 mb-3">
                    <label for="manager_id" class="form-label">{{ $trans('department_manager') }}</label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                        <option value="">{{ $trans('select_manager') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id', $department->manager_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->department->name ?? $trans('not_specified') }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                
                <!-- وصف القسم بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">{{ $trans('department_description_en') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $department->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- وصف القسم بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="description_ar" class="form-label">{{ $trans('department_description_ar') }}</label>
                    <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="4">{{ old('description_ar', $department->description_ar) }}</textarea>
                    @error('description_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                
                <!-- Zoho IDs -->
                <div class="col-md-6 mb-3">
                    <label for="zoho_id" class="form-label">{{ $trans('zoho_ids') }}</label>
                    <textarea class="form-control @error('zoho_id') is-invalid @enderror" 
                              id="zoho_id" 
                              name="zoho_id" 
                              rows="3" 
                              placeholder="{{ $trans('zoho_id_placeholder') }}">{{ old('zoho_id', is_array($department->zoho_id) ? implode(', ', $department->zoho_id) : $department->zoho_id) }}</textarea>
                    <div class="form-text text-warning">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ $trans('zoho_id_note') }}
                    </div>
                    @error('zoho_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Zoho Department Mappings -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-exchange-alt me-2"></i>
                                {{ __('messages.zoho_department_mappings') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($zohoMappings->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Zoho Department ID</th>
                                                <th>Zoho Department Name</th>
                                                <th>Local Department</th>
                                                <th>Status</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($zohoMappings as $mapping)
                                            <tr>
                                                <td>
                                                    <code class="bg-light px-2 py-1 rounded">{{ $mapping->zoho_department_id }}</code>
                                                </td>
                                                <td>
                                                    <strong>{{ $mapping->zoho_department_name }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $mapping->local_department_name }}</span>
                                                </td>
                                                <td>
                                                    @if($mapping->is_active)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>
                                                            مفعل
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-pause me-1"></i>
                                                            معطل
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $mapping->description ?? 'لا يوجد وصف' }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('zoho.department-mappings.edit', $mapping) }}" class="btn btn-outline-primary" title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('zoho.department-mappings.toggle-active', $mapping) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-{{ $mapping->is_active ? 'warning' : 'success' }}" title="{{ $mapping->is_active ? 'تعطيل' : 'تفعيل' }}">
                                                                <i class="fas fa-{{ $mapping->is_active ? 'pause' : 'play' }}"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="{{ route('zoho.department-mappings.index') }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-cogs me-2"></i>
                                        إدارة جميع الـ Mappings
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                                    <p>{{ __('messages.no_department_mappings') }}</p>
                                    <a href="{{ route('zoho.department-mappings.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-2"></i>
                                        إضافة Mapping جديد
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- معاينة القسم -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-eye me-2"></i>{{ $trans('preview') }}
                    </h5>
                    
                    <!-- معاينة باللغة الإنجليزية -->
                    <div class="card mb-3" id="department-preview-en">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-flag me-2"></i>{{ $trans('english_preview') }}</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-2" id="preview-name-en">{{ $department->name ?: $trans('department_name') }}</h5>
                            <p class="text-muted mb-0" id="preview-description-en">{{ $department->description ?: $trans('department_description') }}</p>
                        </div>
                    </div>
                    
                    <!-- معاينة باللغة العربية -->
                    <div class="card" id="department-preview-ar" dir="rtl">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-flag me-2"></i>{{ $trans('arabic_preview') }}</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-2" id="preview-name-ar">{{ $department->name_ar ?: $trans('department_name') }}</h5>
                            <p class="text-muted mb-0" id="preview-description-ar">{{ $department->description_ar ?: $trans('department_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>{{ $trans('save_changes') }}
                </button>
                <a href="{{ route('departments.show', $department) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>{{ $trans('cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Zoho Tickets Section - Only show if there are tickets -->
@if(isset($zohoTickets) && $zohoTickets->count() > 0)
<div class="card" id="Zoho-department-tickets-section">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="fas fa-ticket-alt me-2"></i>
            آخر 200 تذكرة Zoho للقسم
        </h6>
    </div>
    <div class="card-body">
        <!-- Debug Info -->
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            isset($zohoTickets): {{ isset($zohoTickets) ? 'true' : 'false' }}<br>
            @if(isset($zohoTickets))
                count: {{ $zohoTickets->count() }}<br>
                isset($ticketStats): {{ isset($ticketStats) ? 'true' : 'false' }}
            @endif
        </div>
            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">المجموع</h6>
                        <h4 class="text-primary mb-0">{{ $ticketStats['total_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">مغلقة</h6>
                        <h4 class="text-success mb-0">{{ $ticketStats['closed_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">مفتوحة</h6>
                        <h4 class="text-warning mb-0">{{ $ticketStats['open_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">في الانتظار</h6>
                        <h4 class="text-info mb-0">{{ $ticketStats['pending_tickets'] }}</h4>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="bg-light p-2 rounded text-center">
                        <h6 class="text-muted mb-1">قيد التنفيذ</h6>
                        <h4 class="text-secondary mb-0">{{ $ticketStats['in_progress_tickets'] }}</h4>
                    </div>
                </div>
            </div>

            <!-- جدول التذاكر -->
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>رقم التذكرة</th>
                            <th>الموضوع</th>
                            <th>الحالة</th>
                            <th>المعالج</th>
                            <th>تاريخ الإنشاء</th>
                            <th>تاريخ الإغلاق</th>
                            <th>وقت الاستجابة</th>
                            <th>عدد المحادثات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zohoTickets as $ticket)
                        <tr>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $ticket->ticket_number }}</code>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $ticket->subject }}">
                                    {{ $ticket->subject }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'Open' => 'warning',
                                        'Closed' => 'success',
                                        'Pending' => 'info',
                                        'In Progress' => 'secondary',
                                        'Resolved' => 'primary'
                                    ];
                                    $statusColor = $statusColors[$ticket->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->user)
                                    <span class="badge bg-info">{{ $ticket->user->name }}</span>
                                @elseif($ticket->closed_by_name)
                                    <span class="badge bg-secondary">{{ $ticket->closed_by_name }}</span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $ticket->created_at_zoho ? $ticket->created_at_zoho->format('Y-m-d H:i') : 'غير محدد' }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $ticket->closed_at_zoho ? $ticket->closed_at_zoho->format('Y-m-d H:i') : '-' }}
                                </small>
                            </td>
                            <td>
                                @if($ticket->response_time_minutes)
                                    <small class="text-success">
                                        {{ round($ticket->response_time_minutes / 60, 1) }} ساعة
                                    </small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $ticket->thread_count }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            onclick="viewTicketDetails('{{ $ticket->zoho_ticket_id }}')"
                                            title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($ticket->thread_count > 0)
                                        <span class="badge bg-info ms-1" title="{{ $ticket->thread_count }} محادثة">
                                            <i class="fas fa-comments"></i> {{ $ticket->thread_count }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    عرض آخر {{ $zohoTickets->count() }} تذكرة من إجمالي التذاكر المرتبطة بهذا القسم
                </small>
            </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const nameArInput = document.getElementById('name_ar');
    const descriptionInput = document.getElementById('description');
    const descriptionArInput = document.getElementById('description_ar');
    const zohoIdInput = document.getElementById('zoho_id');
    
    // تحديث الاسم بالإنجليزية
    nameInput.addEventListener('input', function() {
        document.getElementById('preview-name-en').textContent = this.value || '{{ $trans("department_name") }}';
    });
    
    // تحديث الاسم بالعربية
    nameArInput.addEventListener('input', function() {
        document.getElementById('preview-name-ar').textContent = this.value || '{{ $trans("department_name") }}';
    });
    
    // تحديث الوصف بالإنجليزية
    descriptionInput.addEventListener('input', function() {
        document.getElementById('preview-description-en').textContent = this.value || '{{ $trans("department_description") }}';
    });
    
    // تحديث الوصف بالعربية
    descriptionArInput.addEventListener('input', function() {
        document.getElementById('preview-description-ar').textContent = this.value || '{{ $trans("department_description") }}';
    });
    
    // التحقق من صحة Zoho IDs (أرقام فقط)
    zohoIdInput.addEventListener('input', function() {
        const value = this.value;
        const ids = value.split(',').map(id => id.trim()).filter(id => id);
        
        let isValid = true;
        for (let id of ids) {
            if (id && !/^\d+$/.test(id)) {
                isValid = false;
                break;
            }
        }
        
        if (value && !isValid) {
            this.classList.add('is-invalid');
            let errorDiv = this.parentNode.querySelector('.zoho-id-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'zoho-id-error invalid-feedback';
                errorDiv.textContent = '{{ $trans("zoho_id_numeric_error") }}';
                this.parentNode.appendChild(errorDiv);
            }
        } else {
            this.classList.remove('is-invalid');
            const errorDiv = this.parentNode.querySelector('.zoho-id-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    });
});

// Function to view ticket details
function viewTicketDetails(ticketId) {
    // Redirect to Zoho tickets page with the specific ticket
    window.open(`{{ route('zoho.tickets') }}?search=${ticketId}`, '_blank');
}
</script>
@endpush
@endsection
