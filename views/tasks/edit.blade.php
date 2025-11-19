@extends('layouts.app')

@section('title', __('messages.edit_task') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title"><i class="fas fa-edit me-2"></i>{{ __('messages.edit_task') }}</h2>
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }} me-2"></i>{{ __('messages.back_to_list') }}
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('tasks.update', $task) }}" method="POST" id="taskForm">
            @csrf
            @method('PUT')
            
            <!-- معلومات المهمة الأساسية -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>{{ __('messages.basic_information') }}</h5>
                </div>
                
                <!-- العنوان بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">{{ __('messages.task_title') }} (English) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $task->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- العنوان بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="title_ar" class="form-label">{{ __('messages.task_title') }} (عربي)</label>
                    <input type="text" class="form-control @error('title_ar') is-invalid @enderror" id="title_ar" name="title_ar" value="{{ old('title_ar', $task->title_ar) }}" dir="rtl">
                    @error('title_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الوصف بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">{{ __('messages.task_description') }} (English) <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الوصف بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="description_ar" class="form-label">{{ __('messages.task_description') }} (عربي)</label>
                    <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="4" dir="rtl">{{ old('description_ar', $task->description_ar) }}</textarea>
                    @error('description_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- التكليف والجدولة -->
            <div class="row mt-4">
                <div class="col-12 mb-4">
                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-user-clock me-2"></i>{{ __('messages.assignment_and_schedule') }}</h5>
                </div>
                
                <!-- المكلف بالمهمة -->
                <div class="col-md-6 mb-3">
                    <label for="assigned_to" class="form-label">{{ __('messages.assigned_to') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to" required>
                        <option value="">{{ __('messages.select_employee') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }} data-department="{{ $user->department_id }}">
                                {{ app()->getLocale() == 'ar' ? ($user->name_ar ?: $user->name) : $user->name }}
                                @if($user->department)
                                    - {{ app()->getLocale() == 'ar' ? $user->department->name_ar : $user->department->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الأولوية (فقط منشئ المهمة يمكنه تعديلها) -->
                @if($task->canUserUpdatePriority(auth()->id()))
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">{{ __('messages.priority') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        <option value="">{{ __('messages.select_priority') }}</option>
                        <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
                        <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                        <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @else
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">{{ __('messages.priority') }}</label>
                    <input type="text" class="form-control" value="{{ __('messages.' . $task->priority) }}" readonly>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> {{ __('messages.only_creator_can_change_priority') }}
                    </small>
                </div>
                @endif
                
                <!-- الحالة (المكلف بالمهمة أو منشئها إذا كانت له) -->
                @if($task->canUserUpdateStatus(auth()->id()))
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">{{ __('messages.select_status') }}</option>
                        <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                        <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                        <option value="on_hold" {{ old('status', $task->status) == 'on_hold' ? 'selected' : '' }}>{{ __('messages.on_hold') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @else
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">{{ __('messages.status') }}</label>
                    <input type="text" class="form-control" value="{{ __('messages.' . $task->status) }}" readonly>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> {{ __('messages.only_assignee_can_change_status') }}
                    </small>
                </div>
                @endif
                
                <!-- الفئة -->
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">{{ __('messages.category') }}</label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category', $task->category) }}" placeholder="{{ __('messages.category_placeholder') }}">
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- خيارات SLA -->
                <div class="col-12 mb-4">
                    <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-clock me-2"></i>{{ __('messages.sla_settings') }}</h6>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sla_type" id="sla_datetime" value="datetime" {{ !$task->sla_hours ? 'checked' : '' }}>
                                <label class="form-check-label" for="sla_datetime">
                                    {{ __('messages.specific_datetime') }}
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sla_type" id="sla_hours" value="hours" {{ $task->sla_hours ? 'checked' : '' }}>
                                <label class="form-check-label" for="sla_hours">
                                    {{ __('messages.hours_from_now') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تحديد تاريخ ووقت محدد -->
                    <div id="datetime_section" class="row" style="{{ $task->sla_hours ? 'display: none;' : '' }}">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">{{ __('messages.due_date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $task->due_date->format('Y-m-d')) }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="due_time" class="form-label">{{ __('messages.due_time') }}</label>
                            <input type="time" class="form-control @error('due_time') is-invalid @enderror" id="due_time" name="due_time" value="{{ old('due_time', $task->due_time) }}">
                            @error('due_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-info">
                                <i class="fas fa-info-circle"></i> 
                                عند تغيير هذا الوقت، سيتم تحديث تلقائي لـ Start Date & Time (الآن) و End Date & Time (الآن + الوقت المحدد)
                            </small>
                        </div>
                    </div>
                    
                    <!-- تحديد عدد الساعات -->
                    <div id="hours_section" class="row" style="{{ !$task->sla_hours ? 'display: none;' : '' }}">
                        <div class="col-md-6 mb-3">
                            <label for="sla_hours_input" class="form-label">{{ __('messages.number_of_hours') }}</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('sla_hours') is-invalid @enderror" id="sla_hours_input" name="sla_hours" value="{{ old('sla_hours', $task->sla_hours) }}" min="1">
                                <span class="input-group-text">{{ __('messages.hours') }}</span>
                            </div>
                            <small class="form-text text-info">
                                <i class="fas fa-info-circle"></i> 
                                أدخل عدد الساعات وسيتم عرض التاريخ والوقت المحسوب أدناه
                            </small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('messages.calculated_due_datetime') }}</label>
                            <div class="alert alert-info mb-2" id="current_datetime">
                                <i class="fas fa-clock text-primary"></i> <strong>الوقت الحالي:</strong> <span id="current_time_display"></span>
                            </div>
                            <div class="alert alert-success mb-0" id="calculated_datetime">
                                @if($task->sla_hours && $task->due_datetime)
                                    <i class="fas fa-calendar-check text-success"></i> 
                                    <strong>التاريخ والوقت الحالي للانتهاء:</strong><br>
                                    {{ $task->due_datetime->format('d/m/Y H:i') }}
                                @else
                                    <i class="fas fa-calendar-check text-success"></i> {{ __('messages.enter_hours_to_calculate') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- تواريخ البداية والانتهاء -->
                <div class="col-12 mb-4">
                    <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-calendar-alt me-2"></i>{{ __('messages.planned_schedule') }}</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_datetime" class="form-label">{{ __('messages.start_datetime') }}</label>
                            <input type="datetime-local" class="form-control @error('start_datetime') is-invalid @enderror" id="start_datetime" name="start_datetime" value="{{ old('start_datetime', $task->start_datetime ? $task->start_datetime->format('Y-m-d\TH:i') : '') }}">
                            @error('start_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($task->actual_start_datetime)
                                <small class="form-text text-success">
                                    <i class="fas fa-check-circle"></i> {{ __('messages.actually_started') }}: {{ $task->actual_start_datetime->format('d/m/Y H:i') }}
                                </small>
                            @endif
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_datetime" class="form-label">{{ __('messages.end_datetime') }}</label>
                            <input type="datetime-local" class="form-control @error('end_datetime') is-invalid @enderror" id="end_datetime" name="end_datetime" value="{{ old('end_datetime', $task->end_datetime ? $task->end_datetime->format('Y-m-d\TH:i') : '') }}">
                            @error('end_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($task->actual_end_datetime)
                                <small class="form-text text-success">
                                    <i class="fas fa-check-circle"></i> {{ __('messages.actually_completed') }}: {{ $task->actual_end_datetime->format('d/m/Y H:i') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- نوع التكرار -->
                <div class="col-md-6 mb-3">
                    <label for="repeat_type" class="form-label">{{ __('messages.repeat_type') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('repeat_type') is-invalid @enderror" id="repeat_type" name="repeat_type" required>
                        <option value="one_time" {{ old('repeat_type', $task->repeat_type) == 'one_time' ? 'selected' : '' }}>{{ __('messages.one_time') }}</option>
                        <option value="daily" {{ old('repeat_type', $task->repeat_type) == 'daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                        <option value="quarterly" {{ old('repeat_type', $task->repeat_type) == 'quarterly' ? 'selected' : '' }}>{{ __('messages.quarterly') }}</option>
                        <option value="yearly" {{ old('repeat_type', $task->repeat_type) == 'yearly' ? 'selected' : '' }}>{{ __('messages.yearly') }}</option>
                    </select>
                    @error('repeat_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted" id="repeatInfo">
                        <i class="fas fa-info-circle"></i> <span id="repeatInfoText"></span>
                    </small>
                </div>
                
                <!-- معلومات إضافية عن التكرار -->
                @if($task->repeat_type != 'one_time')
                <div class="col-12 mb-3">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-sync-alt me-2"></i>{{ __('messages.repeat_information') }}</h6>
                        <ul class="mb-0">
                            @if($task->last_repeated_at)
                                <li><strong>{{ __('messages.last_repeated') }}:</strong> {{ $task->last_repeated_at->format('d/m/Y H:i') }}</li>
                            @endif
                            @if($task->next_repeat_at)
                                <li><strong>{{ __('messages.next_repeat') }}:</strong> {{ $task->next_repeat_at->format('d/m/Y') }}</li>
                            @endif
                            <li><strong>{{ __('messages.repeat_status') }}:</strong> 
                                <span class="badge bg-{{ $task->is_repeat_active ? 'success' : 'secondary' }}">
                                    {{ $task->is_repeat_active ? __('messages.active') : __('messages.inactive') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- معلومات المهمة -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-info-circle me-2"></i>{{ __('messages.task_information') }}</h6>
                        <ul class="mb-0">
                            <li><strong>{{ __('messages.created_by') }}:</strong> {{ app()->getLocale() == 'ar' ? ($task->createdBy->name_ar ?: $task->createdBy->name) : $task->createdBy->name }}</li>
                            <li><strong>{{ __('messages.created_at') }}:</strong> {{ $task->created_at->format('d/m/Y H:i') }}</li>
                            <li><strong>{{ __('messages.last_updated') }}:</strong> {{ $task->updated_at->format('d/m/Y H:i') }}</li>
                            @if($task->department)
                                <li><strong>{{ __('messages.department') }}:</strong> {{ app()->getLocale() == 'ar' ? $task->department->name_ar : $task->department->name }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>{{ __('messages.update') }}
                </button>
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-info me-2">
                    <i class="fas fa-eye me-2"></i>{{ __('messages.view') }}
                </a>
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>{{ __('messages.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const repeatTypeSelect = document.getElementById('repeat_type');
    const repeatInfoText = document.getElementById('repeatInfoText');
    
    // SLA elements
    const slaDatetimeRadio = document.getElementById('sla_datetime');
    const slaHoursRadio = document.getElementById('sla_hours');
    const datetimeSection = document.getElementById('datetime_section');
    const hoursSection = document.getElementById('hours_section');
    const dueDateInput = document.getElementById('due_date');
    const dueTimeInput = document.getElementById('due_time');
    const slaHoursInput = document.getElementById('sla_hours_input');
    
    // تحديث معلومات التكرار
    function updateRepeatInfo() {
        const repeatType = repeatTypeSelect ? repeatTypeSelect.value : '';
        let infoText = '';
        
        switch(repeatType) {
            case 'one_time':
                infoText = '{{ __("messages.one_time_task_info") }}';
                break;
            case 'daily':
                infoText = '{{ __("messages.daily_task_info") }}';
                break;
            case 'quarterly':
                infoText = '{{ __("messages.quarterly_task_info") }}';
                break;
            case 'yearly':
                infoText = '{{ __("messages.yearly_task_info") }}';
                break;
        }
        
        if (repeatInfoText) {
            repeatInfoText.textContent = infoText;
        }
    }
    
    // التبديل بين خيارات SLA
    function toggleSlaOptions() {
        console.log('toggleSlaOptions called (edit page)');
        
        const slaDatetimeRadio = document.getElementById('sla_datetime');
        const slaHoursRadio = document.getElementById('sla_hours');
        const datetimeSection = document.getElementById('datetime_section');
        const hoursSection = document.getElementById('hours_section');
        const dueDateInput = document.getElementById('due_date');
        const slaHoursInput = document.getElementById('sla_hours_input');
        
        console.log('Elements found (edit page):', {
            slaDatetimeRadio: !!slaDatetimeRadio,
            slaHoursRadio: !!slaHoursRadio,
            datetimeSection: !!datetimeSection,
            hoursSection: !!hoursSection,
            dueDateInput: !!dueDateInput,
            slaHoursInput: !!slaHoursInput
        });
        
        if (slaDatetimeRadio && slaDatetimeRadio.checked) {
            console.log('DateTime radio is checked (edit page)');
            if (datetimeSection) {
                datetimeSection.style.display = 'block';
                console.log('DateTime section shown (edit page)');
            }
            if (hoursSection) {
                hoursSection.style.display = 'none';
                console.log('Hours section hidden (edit page)');
            }
            if (dueDateInput) dueDateInput.required = true;
            if (slaHoursInput) {
                slaHoursInput.required = false;
                slaHoursInput.value = '';
            }
        } else if (slaHoursRadio && slaHoursRadio.checked) {
            console.log('Hours radio is checked (edit page)');
            if (datetimeSection) {
                datetimeSection.style.display = 'none';
                console.log('DateTime section hidden (edit page)');
            }
            if (hoursSection) {
                hoursSection.style.display = 'block';
                console.log('Hours section shown (edit page)');
            }
            if (dueDateInput) dueDateInput.required = false;
            if (slaHoursInput) slaHoursInput.required = true;
        } else {
            console.log('No radio button is checked (edit page)');
        }
        
        console.log('SLA toggle - DateTime checked (edit page):', slaDatetimeRadio?.checked);
        console.log('SLA toggle - Hours checked (edit page):', slaHoursRadio?.checked);
        console.log('SLA toggle - DateTime section display (edit page):', datetimeSection?.style.display);
        console.log('SLA toggle - Hours section display (edit page):', hoursSection?.style.display);
    }
    
    // تحديث الوقت الحالي
    function updateCurrentTime() {
        const now = new Date();
        const currentTimeDisplay = document.getElementById('current_time_display');
        
        if (currentTimeDisplay) {
            const dateStr = now.toLocaleDateString('ar-EG', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const timeStr = now.toLocaleTimeString('ar-EG', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            
            currentTimeDisplay.textContent = `${dateStr} - ${timeStr}`;
        }
    }
    
    // حساب التاريخ والوقت المتوقع
    function calculateDueDateTime() {
        console.log('calculateDueDateTime called (edit page)');
        
        if (!slaHoursInput) {
            console.log('slaHoursInput not found (edit page)');
            return;
        }
        
        const hours = parseInt(slaHoursInput.value);
        console.log('Hours value (edit page):', hours);
        
        if (!calculatedDatetime) {
            console.log('calculatedDatetime element not found (edit page)');
            return;
        }
        
        if (!hours || hours <= 0) {
            calculatedDatetime.innerHTML = '<i class="fas fa-calendar-check text-success"></i> أدخل عدد الساعات لحساب التاريخ والوقت';
            return;
        }
        
        const now = new Date();
        const dueDate = new Date(now.getTime() + (hours * 60 * 60 * 1000));
        
        const dateStr = dueDate.toLocaleDateString('ar-EG', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        const timeStr = dueDate.toLocaleTimeString('ar-EG', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        calculatedDatetime.innerHTML = `
            <i class="fas fa-calendar-check text-success"></i> 
            <strong>التاريخ والوقت المخطط للانتهاء:</strong><br>
            ${dateStr} - ${timeStr}
        `;
        
        console.log('Calculated datetime updated (edit page):', `${dateStr} - ${timeStr}`);
    }
    
    // تحديث start_datetime و end_datetime عند تغيير due_time
    function updateStartEndDateTime() {
        const startDatetimeInput = document.getElementById('start_datetime');
        const endDatetimeInput = document.getElementById('end_datetime');
        
        if (!dueTimeInput || !startDatetimeInput || !endDatetimeInput) return;
        
        const dueTime = dueTimeInput.value;
        if (!dueTime) return;
        
        const now = new Date();
        
        // تحويل due_time إلى ساعات
        const [hours, minutes] = dueTime.split(':');
        const totalHours = parseInt(hours) + (parseInt(minutes) / 60);
        
        // start_datetime = الآن
        const startDateTime = new Date(now);
        startDatetimeInput.value = startDateTime.toISOString().slice(0, 16);
        
        // end_datetime = الآن + عدد الساعات من due_time
        const endDateTime = new Date(now.getTime() + (totalHours * 60 * 60 * 1000));
        endDatetimeInput.value = endDateTime.toISOString().slice(0, 16);
        
        console.log('Updated start_datetime:', startDatetimeInput.value);
        console.log('Updated end_datetime:', endDatetimeInput.value);
        console.log('Due time hours:', totalHours);
    }
    
    // تحديث start_datetime و end_datetime بناءً على عدد الساعات (SLA Hours)
    function updateStartEndDateTimeFromHours() {
        const slaHoursInput = document.getElementById('sla_hours_input');
        const startDatetimeInput = document.getElementById('start_datetime');
        const endDatetimeInput = document.getElementById('end_datetime');
        
        if (!slaHoursInput || !startDatetimeInput || !endDatetimeInput) return;
        
        const hours = parseInt(slaHoursInput.value);
        if (!hours || hours <= 0) return;
        
        const now = new Date();
        
        // start_datetime = الآن
        const startDateTime = new Date(now);
        
        // تحويل إلى تنسيق datetime-local مع التوقيت المحلي
        const startYear = startDateTime.getFullYear();
        const startMonth = String(startDateTime.getMonth() + 1).padStart(2, '0');
        const startDay = String(startDateTime.getDate()).padStart(2, '0');
        const startHours = String(startDateTime.getHours()).padStart(2, '0');
        const startMinutes = String(startDateTime.getMinutes()).padStart(2, '0');
        
        startDatetimeInput.value = `${startYear}-${startMonth}-${startDay}T${startHours}:${startMinutes}`;
        
        // end_datetime = الآن + عدد الساعات المحدد
        const endDateTime = new Date(now.getTime() + (hours * 60 * 60 * 1000));
        
        // تحويل إلى تنسيق datetime-local مع التوقيت المحلي
        const endYear = endDateTime.getFullYear();
        const endMonth = String(endDateTime.getMonth() + 1).padStart(2, '0');
        const endDay = String(endDateTime.getDate()).padStart(2, '0');
        const endHours = String(endDateTime.getHours()).padStart(2, '0');
        const endMinutes = String(endDateTime.getMinutes()).padStart(2, '0');
        
        endDatetimeInput.value = `${endYear}-${endMonth}-${endDay}T${endHours}:${endMinutes}`;
        
        console.log('=== SLA Hours Calculation ===');
        console.log('Current time:', now.toLocaleString());
        console.log('Hours to add:', hours);
        console.log('Calculated end time:', endDateTime.toLocaleString());
        console.log('Updated start_datetime from SLA hours:', startDatetimeInput.value);
        console.log('Updated end_datetime from SLA hours:', endDatetimeInput.value);
        console.log('Time difference in hours:', (endDateTime.getTime() - now.getTime()) / (1000 * 60 * 60));
    }
    
    // تحويل الوقت إلى تنسيق 24 ساعة قبل إرسال النموذج
    function convertTimeTo24Hour(timeString) {
        if (!timeString) return '';
        
        // إذا كان التنسيق يحتوي على AM/PM
        if (timeString.includes('AM') || timeString.includes('PM')) {
            const [time, period] = timeString.split(' ');
            const [hours, minutes] = time.split(':');
            
            let hour24 = parseInt(hours);
            
            if (period === 'AM' && hour24 === 12) {
                hour24 = 0;
            } else if (period === 'PM' && hour24 !== 12) {
                hour24 += 12;
            }
            
            return `${hour24.toString().padStart(2, '0')}:${minutes}`;
        }
        
        return timeString;
    }
    
    // إضافة event listener للنموذج لتحويل الوقت قبل الإرسال
    const taskForm = document.querySelector('form');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            if (dueTimeInput && dueTimeInput.value) {
                dueTimeInput.value = convertTimeTo24Hour(dueTimeInput.value);
            }
        });
    }

    // Event listeners
    console.log('Setting up event listeners (edit page):', {
        slaDatetimeRadio: !!slaDatetimeRadio,
        slaHoursRadio: !!slaHoursRadio
    });
    
    if (slaDatetimeRadio) {
        slaDatetimeRadio.addEventListener('change', function() {
            console.log('DateTime radio changed (edit page)');
            toggleSlaOptions();
        });
    }
    if (slaHoursRadio) {
        slaHoursRadio.addEventListener('change', function() {
            console.log('Hours radio changed (edit page)');
            toggleSlaOptions();
            // تحديث التواريخ تلقائياً عند التبديل إلى "Hours from Now"
            setTimeout(updateStartEndDateTimeFromHours, 100);
        });
    }
    
    // إضافة event listener لحقل الساعات
    if (slaHoursInput) {
        slaHoursInput.addEventListener('input', function() {
            calculateDueDateTime();
            updateStartEndDateTimeFromHours();
        });
        slaHoursInput.addEventListener('change', function() {
            calculateDueDateTime();
            updateStartEndDateTimeFromHours();
        });
    }
    
    // إضافة event listener لحقل due_time
    if (dueTimeInput) {
        dueTimeInput.addEventListener('change', updateStartEndDateTime);
        dueTimeInput.addEventListener('input', updateStartEndDateTime);
    }
    
    updateRepeatInfo();
    
    // Debug: تحقق من حالة العناصر عند التحميل
    console.log('=== EDIT PAGE LOADED ===');
    console.log('SLA DateTime radio:', document.getElementById('sla_datetime')?.checked);
    console.log('SLA Hours radio:', document.getElementById('sla_hours')?.checked);
    console.log('DateTime section display:', document.getElementById('datetime_section')?.style.display);
    console.log('Hours section display:', document.getElementById('hours_section')?.style.display);
    
    console.log('Calling toggleSlaOptions (edit page)...');
    toggleSlaOptions();
    
    // تحديث الوقت الحالي عند التحميل
    updateCurrentTime();
    
    // تحديث الوقت الحالي كل ثانية
    setInterval(updateCurrentTime, 1000);
    
    // تحديث start_datetime و end_datetime إذا كان هناك قيمة في due_time
    if (dueTimeInput && dueTimeInput.value) {
        updateStartEndDateTime();
    }
    
    // تحديث start_datetime و end_datetime إذا كان هناك قيمة في SLA hours
    if (slaHoursInput && slaHoursInput.value && slaHoursRadio && slaHoursRadio.checked) {
        updateStartEndDateTimeFromHours();
    }
    
    if (repeatTypeSelect) {
        repeatTypeSelect.addEventListener('change', updateRepeatInfo);
    }
});
</script>
@endpush
