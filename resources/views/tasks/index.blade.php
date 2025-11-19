@extends('layouts.app')

@section('title', __('messages.task_management') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title"><i class="fas fa-tasks me-2"></i>{{ __('messages.task_management') }}</h2>
    <div class="btn-group" role="group">
        <a href="{{ route('task-templates.index') }}" class="btn btn-success me-2">
            <i class="fas fa-file-alt me-2"></i>{{ __('messages.manage_task_templates') }}
        </a>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>{{ __('messages.add_new_task') }}
        </a>
        
        <!-- Language Toggle -->
        <div class="btn-group" role="group">
            <a href="{{ route('tasks.index', array_merge(request()->query(), ['locale' => 'ar'])) }}" 
               class="btn btn-outline-info {{ app()->getLocale() == 'ar' ? 'active' : '' }}" 
               title="{{ __('messages.arabic') }}">
                <i class="fas fa-language me-1"></i>ع
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->query(), ['locale' => 'en'])) }}" 
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
        <form method="GET" action="{{ route('tasks.index') }}" id="filtersForm">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">{{ __('messages.status') }}</label>
                    <select class="form-select modern-select" name="status" id="status_filter">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                        <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>{{ __('messages.on_hold') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="priority_filter" class="form-label">{{ __('messages.priority') }}</label>
                    <select class="form-select modern-select" name="priority" id="priority_filter">
                        <option value="">{{ __('messages.all_priorities') }}</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="repeat_type_filter" class="form-label">{{ __('messages.repeat_type') }}</label>
                    <select class="form-select modern-select" name="repeat_type" id="repeat_type_filter">
                        <option value="">{{ __('messages.all_types') }}</option>
                        <option value="one_time" {{ request('repeat_type') == 'one_time' ? 'selected' : '' }}>{{ __('messages.one_time') }}</option>
                        <option value="daily" {{ request('repeat_type') == 'daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                        <option value="quarterly" {{ request('repeat_type') == 'quarterly' ? 'selected' : '' }}>{{ __('messages.quarterly') }}</option>
                        <option value="yearly" {{ request('repeat_type') == 'yearly' ? 'selected' : '' }}>{{ __('messages.yearly') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label for="assigned_to_filter" class="form-label">{{ __('messages.assigned_to') }}</label>
                    <select class="form-select modern-select" name="assigned_to" id="assigned_to_filter">
                        <option value="">{{ __('messages.all_users') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'ar' ? ($user->name_ar ?: $user->name) : $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>{{ __('messages.apply_filters') }}
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>{{ __('messages.clear_filters') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tasks Table -->
<div class="card">
    <div class="card-body">
        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" data-view-route="tasks.show">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('messages.title') }}</th>
                            <th>{{ __('messages.assigned_to') }}</th>
                            <th>{{ __('messages.created_by') }}</th>
                            <th>{{ __('messages.department') }}</th>
                            <th>{{ __('messages.priority') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.repeat_type') }}</th>
                            <th>{{ __('messages.start_datetime') }}</th>
                            <th>{{ __('messages.end_datetime') }}</th>
                            <th>{{ __('messages.due_date') }}</th>
                            <th>{{ app()->getLocale() == 'ar' ? 'الوقت المستغرق' : 'Estimated Time' }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-1">{{ app()->getLocale() == 'ar' ? ($task->title_ar ?: $task->title) : $task->title }}</h6>
                                        <small class="text-muted">{{ Str::limit(app()->getLocale() == 'ar' ? ($task->description_ar ?: $task->description) : $task->description, 50) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($task->assignedTo)
                                            <a href="{{ route('users.show', $task->assignedTo->id) }}" class="text-decoration-none text-primary fw-medium user-link" title="{{ __('messages.view_user_profile') }}">
                                                {{ app()->getLocale() == 'ar' ? ($task->assignedTo->name_ar ?: $task->assignedTo->name) : $task->assignedTo->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ __('messages.no_data') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($task->createdBy)
                                        <a href="{{ route('users.show', $task->createdBy->id) }}" class="text-decoration-none text-primary fw-medium user-link" title="{{ __('messages.view_user_profile') }}">
                                            <small>{{ app()->getLocale() == 'ar' ? ($task->createdBy->name_ar ?: $task->createdBy->name) : $task->createdBy->name }}</small>
                                        </a>
                                    @else
                                        <span class="text-muted">{{ __('messages.no_data') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->department)
                                        <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? $task->department->name_ar : $task->department->name }}</span>
                                    @else
                                        <span class="text-muted">{{ __('messages.no_data') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                        {{ __('messages.' . $task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'primary' : ($task->status == 'on_hold' ? 'secondary' : 'warning')) }}">
                                        {{ __('messages.' . $task->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $task->repeat_type != 'one_time' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ __('messages.' . $task->repeat_type) }}
                                        @if($task->repeat_type != 'one_time' && $task->is_repeat_active)
                                            <i class="fas fa-sync-alt ms-1"></i>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($task->start_datetime)
                                        <span class="text-info">
                                            {{ $task->start_datetime->format('d/m/Y g:i A') }}
                                            @if($task->actual_start_datetime)
                                                <br><small class="text-success">✓ {{ __('messages.started') }} {{ $task->actual_start_datetime->format('g:i A') }}</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('messages.not_set') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->end_datetime)
                                        <span class="text-{{ $task->end_datetime < now() && $task->status !== 'completed' ? 'danger' : 'success' }}">
                                            {{ $task->end_datetime->format('d/m/Y g:i A') }}
                                            @if($task->end_datetime < now() && $task->status !== 'completed')
                                                <br><small class="text-danger">▲ {{ __('messages.overdue') }}</small>
                                            @endif
                                            @if($task->actual_end_datetime)
                                                <br><small class="text-success">✓ {{ __('messages.completed') }} {{ $task->actual_end_datetime->format('g:i A') }}</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('messages.not_set') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $task->due_date < now() && $task->status != 'completed' ? 'text-danger fw-bold' : '' }}">
                                        {{ $task->due_date->format('d/m/Y') }}
                                    </span>
                                    @if($task->due_date < now() && $task->status != 'completed')
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{ __('messages.overdue') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // حساب الوقت المقدر (Estimated Time)
                                        $estimatedHours = null;
                                        if ($task->estimated_time) {
                                            $estimatedHours = $task->estimated_time;
                                        } elseif ($task->start_datetime && $task->end_datetime) {
                                            $estimatedHours = $task->start_datetime->diffInHours($task->end_datetime);
                                        }
                                        
                                        // حساب الوقت الفعلي (Actual Time)
                                        $actualHours = null;
                                        if ($task->actual_start_datetime && $task->actual_end_datetime) {
                                            $actualHours = $task->actual_start_datetime->diffInHours($task->actual_end_datetime);
                                        }
                                    @endphp
                                    
                                    @if($estimatedHours)
                                        <div>
                                            <small class="text-info">
                                                <i class="fas fa-clock"></i> 
                                                {{ app()->getLocale() == 'ar' ? 'مقدر:' : 'Est:' }}
                                                <strong>{{ number_format($estimatedHours, 1) }}h</strong>
                                            </small>
                                        </div>
                                    @endif
                                    
                                    @if($actualHours)
                                        <div>
                                            <small class="text-success">
                                                <i class="fas fa-check-circle"></i> 
                                                {{ app()->getLocale() == 'ar' ? 'فعلي:' : 'Actual:' }}
                                                <strong>{{ number_format($actualHours, 1) }}h</strong>
                                            </small>
                                        </div>
                                    @elseif($estimatedHours)
                                        <div>
                                            <small class="text-muted">
                                                {{ app()->getLocale() == 'ar' ? 'لم يبدأ بعد' : 'Not started' }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ __('messages.not_set') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($task->created_by == auth()->id() || $task->assigned_to == auth()->id())
                                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning" title="{{ __('messages.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($task->created_by == auth()->id())
                                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline delete-form" data-message="{{ __('messages.confirm_delete_task') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $tasks->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('messages.no_tasks_found') }}</h5>
                <p class="text-muted">{{ __('messages.start_adding_tasks') }}</p>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ __('messages.add_task') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation
    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            if (confirm(message)) {
                this.submit();
            }
        });
    });
    
    // Auto-submit filters on change
    const filterSelects = document.querySelectorAll('.modern-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    });
});
</script>

<style>
/* تحسين مظهر روابط المستخدمين */
.user-link {
    transition: all 0.2s ease;
}

.user-link:hover {
    color: #0d6efd !important;
    text-decoration: underline !important;
    transform: translateY(-1px);
}

.user-link:active {
    transform: translateY(0);
}
</style>
@endpush
