@extends('layouts.app')

@section('title', __('messages.task_details') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title"><i class="fas fa-tasks me-2"></i>{{ __('messages.task_details') }}</h2>
    <div class="btn-group" role="group">
        @if($task->created_by == auth()->id() || $task->assigned_to == auth()->id())
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}
            </a>
        @endif
        @if($task->created_by == auth()->id())
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline delete-form" data-message="{{ __('messages.confirm_delete_task') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>{{ __('messages.delete') }}
                </button>
            </form>
        @endif
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} me-2"></i>{{ __('messages.back_to_list') }}
        </a>
    </div>
</div>

<!-- معلومات المهمة الأساسية -->
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('messages.task_information') }}</h5>
            </div>
            <div class="card-body">
                <!-- العنوان -->
                <div class="mb-4">
                    <h4 class="text-primary">{{ app()->getLocale() == 'ar' ? ($task->title_ar ?: $task->title) : $task->title }}</h4>
                    @if(app()->getLocale() == 'ar' && $task->title_ar && $task->title)
                        <small class="text-muted">{{ $task->title }}</small>
                    @elseif(app()->getLocale() == 'en' && $task->title_ar)
                        <small class="text-muted">{{ $task->title_ar }}</small>
                    @endif
                </div>
                
                <!-- الوصف -->
                <div class="mb-4">
                    <h6 class="text-muted">{{ __('messages.description') }}</h6>
                    <p class="lead">{{ app()->getLocale() == 'ar' ? ($task->description_ar ?: $task->description) : $task->description }}</p>
                    @if(app()->getLocale() == 'ar' && $task->description_ar && $task->description)
                        <small class="text-muted border-top pt-2 d-block">{{ $task->description }}</small>
                    @elseif(app()->getLocale() == 'en' && $task->description_ar)
                        <small class="text-muted border-top pt-2 d-block">{{ $task->description_ar }}</small>
                    @endif
                </div>
                
                <!-- الفئة -->
                @if($task->category)
                <div class="mb-3">
                    <h6 class="text-muted">{{ __('messages.category') }}</h6>
                    <span class="badge bg-secondary fs-6">{{ $task->category }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- معلومات الحالة والأولوية -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>{{ __('messages.status_priority') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted mb-2">{{ __('messages.status') }}</label>
                    <br>
                    <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'primary' : ($task->status == 'on_hold' ? 'secondary' : 'warning')) }} fs-6">
                        {{ __('messages.' . $task->status) }}
                    </span>
                    
                    <!-- تحديث سريع للحالة -->
                    @if($task->canUserUpdateStatus(auth()->id()))
                        <div class="mt-3">
                            <form action="{{ route('tasks.update-status', $task) }}" method="POST" id="statusUpdateForm">
                                @csrf
                                @method('PATCH')
                                <label for="quick_status" class="form-label small">{{ __('messages.quick_status_update') }}</label>
                                <select class="form-select form-select-sm" id="quick_status" name="status">
                                    <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                                    <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                                    <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                    <option value="on_hold" {{ $task->status == 'on_hold' ? 'selected' : '' }}>{{ __('messages.on_hold') }}</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary mt-2 w-100">
                                    <i class="fas fa-sync-alt me-1"></i>{{ __('messages.update_status') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label class="text-muted mb-2">{{ __('messages.priority') }}</label>
                    <br>
                    <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }} fs-6">
                        {{ __('messages.' . $task->priority) }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted mb-2">{{ __('messages.due_date') }}</label>
                    <br>
                    <span class="fs-5 {{ $task->due_date < now() && $task->status != 'completed' ? 'text-danger fw-bold' : '' }}">
                        <i class="fas fa-calendar-alt me-2"></i>{{ $task->due_date->format('d/m/Y') }}
                    </span>
                    @if($task->due_date < now() && $task->status != 'completed')
                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{ __('messages.overdue') }}</small>
                    @endif
                </div>
                
                <div class="mb-0">
                    <label class="text-muted mb-2">{{ __('messages.repeat_type') }}</label>
                    <br>
                    <span class="badge {{ $task->repeat_type != 'one_time' ? 'bg-info' : 'bg-secondary' }} fs-6">
                        {{ __('messages.' . $task->repeat_type) }}
                        @if($task->repeat_type != 'one_time' && $task->is_repeat_active)
                            <i class="fas fa-sync-alt ms-1"></i>
                        @endif
                    </span>
                    
                    @if($task->repeat_type != 'one_time')
                        <div class="mt-3 p-2 bg-light rounded">
                            <small>
                                @if($task->next_repeat_at)
                                    <strong>{{ __('messages.next_repeat') }}:</strong><br>
                                    {{ $task->next_repeat_at->format('d/m/Y') }}
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- معلومات التكليف -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ __('messages.assignment') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted mb-2">{{ __('messages.assigned_to') }}</label>
                    <a href="{{ route('users.show', $task->assignedTo->id) }}" class="text-decoration-none text-dark user-link-card">
                        <div class="d-flex align-items-center">
                            @if($task->assignedTo->profile_picture)
                                <img src="{{ Storage::url($task->assignedTo->profile_picture) }}" alt="{{ $task->assignedTo->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @endif
                            <div>
                                <strong>{{ app()->getLocale() == 'ar' ? ($task->assignedTo->name_ar ?: $task->assignedTo->name) : $task->assignedTo->name }}</strong>
                                @if($task->assignedTo->position)
                                    <br><small class="text-muted">{{ app()->getLocale() == 'ar' ? ($task->assignedTo->position_ar ?: $task->assignedTo->position) : $task->assignedTo->position }}</small>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted mb-2">{{ __('messages.created_by') }}</label>
                    <a href="{{ route('users.show', $task->createdBy->id) }}" class="text-decoration-none text-dark user-link-card">
                        <div class="d-flex align-items-center">
                            @if($task->createdBy->profile_picture)
                                <img src="{{ Storage::url($task->createdBy->profile_picture) }}" alt="{{ $task->createdBy->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @endif
                            <div>
                                <strong>{{ app()->getLocale() == 'ar' ? ($task->createdBy->name_ar ?: $task->createdBy->name) : $task->createdBy->name }}</strong>
                                @if($task->createdBy->position)
                                    <br><small class="text-muted">{{ app()->getLocale() == 'ar' ? ($task->createdBy->position_ar ?: $task->createdBy->position) : $task->createdBy->position }}</small>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                
                @if($task->department)
                <div class="mb-0">
                    <label class="text-muted mb-2">{{ __('messages.department') }}</label>
                    <br>
                    <a href="{{ route('departments.show', $task->department->id) }}" class="text-decoration-none">
                        <span class="badge bg-info fs-6 department-link">{{ app()->getLocale() == 'ar' ? $task->department->name_ar : $task->department->name }}</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- معلومات التوقيت -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>{{ __('messages.timeline') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted mb-1 small">{{ __('messages.created_at') }}</label>
                    <br>
                    <span class="text-dark">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                    <br>
                    <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                </div>
                
                <div class="mb-0">
                    <label class="text-muted mb-1 small">{{ __('messages.last_updated') }}</label>
                    <br>
                    <span class="text-dark">{{ $task->updated_at->format('d/m/Y H:i') }}</span>
                    <br>
                    <small class="text-muted">{{ $task->updated_at->diffForHumans() }}</small>
                </div>
                
                @if($task->repeat_type != 'one_time' && $task->last_repeated_at)
                <div class="mt-3">
                    <label class="text-muted mb-1 small">{{ __('messages.last_repeated') }}</label>
                    <br>
                    <span class="text-dark">{{ $task->last_repeated_at->format('d/m/Y H:i') }}</span>
                    <br>
                    <small class="text-muted">{{ $task->last_repeated_at->diffForHumans() }}</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation
    const deleteForm = document.querySelector('.delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            if (confirm(message)) {
                this.submit();
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.comment-item:last-child {
    border-bottom: none !important;
}

/* تحسين مظهر روابط المستخدمين في البطاقات */
.user-link-card {
    transition: all 0.3s ease;
    border-radius: 8px;
    padding: 8px;
    margin: -8px;
}

.user-link-card:hover {
    background-color: rgba(13, 110, 253, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.user-link-card:active {
    transform: translateY(0);
}

/* تحسين مظهر رابط القسم */
.department-link {
    transition: all 0.3s ease;
    cursor: pointer;
}

.department-link:hover {
    background-color: #0dcaf0 !important;
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.department-link:active {
    transform: scale(1.02);
}
</style>
@endpush
