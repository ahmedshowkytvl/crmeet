@extends('layouts.app')

@section('title', __('messages.add_new_task') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title"><i class="fas fa-plus me-2"></i>{{ __('messages.add_new_task') }}</h2>
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
        <form action="{{ route('tasks.store') }}" method="POST" id="taskForm">
            @csrf
            
            <!-- اختيار قالب المهمة -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-clipboard-list me-2"></i>اختيار قالب المهمة (اختياري)</h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="template_department" class="form-label">القسم</label>
                    <select class="form-select" id="template_department" name="template_department">
                        <option value="">اختر القسم أولاً</option>
                        <option value="Contracting">Contracting (Egypt-KSA-Global)</option>
                        <option value="IT">IT</option>
                        <option value="Internet dep">Internet dep</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Callcenter">Callcenter</option>
                        <option value="Marketing">Marketing</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="task_template_id" class="form-label">قالب المهمة</label>
                    <select class="form-select" id="task_template_id" name="task_template_id">
                        <option value="">اختر قالب المهمة</option>
                    </select>
                    <small class="form-text text-muted">اختيار قالب سيتم ملء البيانات تلقائياً</small>
                </div>
            </div>

            <!-- معلومات المهمة الأساسية -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>{{ __('messages.basic_information') }}</h5>
                </div>
                
                <!-- العنوان بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">{{ __('messages.task_title') }} (English) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- العنوان بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="title_ar" class="form-label">{{ __('messages.task_title') }} (عربي)</label>
                    <input type="text" class="form-control @error('title_ar') is-invalid @enderror" id="title_ar" name="title_ar" value="{{ old('title_ar') }}" dir="rtl">
                    @error('title_ar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الوصف بالإنجليزية -->
                <div class="col-md-6 mb-3">
                    <label for="description" class="form-label">{{ __('messages.task_description') }} (English) <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الوصف بالعربية -->
                <div class="col-md-6 mb-3">
                    <label for="description_ar" class="form-label">{{ __('messages.task_description') }} (عربي)</label>
                    <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="4" dir="rtl">{{ old('description_ar') }}</textarea>
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
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }} data-department="{{ $user->department_id }}">
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
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> {{ __('messages.task_assignment_info') }}
                    </small>
                </div>
                
                <!-- الأولوية -->
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">{{ __('messages.priority') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        <option value="">{{ __('messages.select_priority') }}</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                            <i class="fas fa-circle text-success"></i> {{ __('messages.low') }}
                        </option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                            <i class="fas fa-circle text-warning"></i> {{ __('messages.medium') }}
                        </option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                            <i class="fas fa-circle text-danger"></i> {{ __('messages.high') }}
                        </option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الحالة (فقط إذا كانت المهمة للمستخدم نفسه) -->
                <div class="col-md-6 mb-3" id="statusField" style="display: none;">
                    <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                        <option value="">{{ __('messages.select_status') }}</option>
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                        <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>{{ __('messages.on_hold') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- الفئة -->
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">{{ __('messages.category') }}</label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category') }}" placeholder="{{ __('messages.category_placeholder') }}">
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- الوقت المقدر -->
                <div class="col-md-6 mb-3">
                    <label for="estimated_time" class="form-label">الوقت المقدر (ساعات)</label>
                    <input type="number" step="0.001" min="0" class="form-control @error('estimated_time') is-invalid @enderror" 
                           id="estimated_time" name="estimated_time" value="{{ old('estimated_time') }}">
                    @error('estimated_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">مثال: 0.5 = نصف ساعة، 1.25 = ساعة وربع</small>
                </div>
                
                <!-- خيارات SLA -->
                <div class="col-12 mb-4">
                    <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-clock me-2"></i>{{ __('messages.sla_settings') }}</h6>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sla_type" id="sla_datetime" value="datetime" checked>
                                <label class="form-check-label" for="sla_datetime">
                                    {{ __('messages.specific_datetime') }}
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="sla_type" id="sla_hours" value="hours">
                                <label class="form-check-label" for="sla_hours">
                                    {{ __('messages.hours_from_now') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تحديد تاريخ ووقت محدد -->
                    <div id="datetime_section" class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">{{ __('messages.due_date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="due_time" class="form-label">{{ __('messages.due_time') }}</label>
                            <input type="time" class="form-control @error('due_time') is-invalid @enderror" id="due_time" name="due_time" value="{{ old('due_time', '23:59') }}">
                            @error('due_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.default_time_23_59') }}</small>
                            <small class="form-text text-info">
                                <i class="fas fa-info-circle"></i> 
                                عند تغيير هذا الوقت، سيتم تحديث تلقائي لـ Start Date & Time (الآن) و End Date & Time (الآن + الوقت المحدد)
                            </small>
                        </div>
                    </div>
                    
                    <!-- تحديد عدد الساعات -->
                    <div id="hours_section" class="row" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="sla_hours_input" class="form-label">{{ __('messages.number_of_hours') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('sla_hours') is-invalid @enderror" id="sla_hours_input" name="sla_hours" value="{{ old('sla_hours') }}" min="1" placeholder="24">
                                <span class="input-group-text">{{ __('messages.hours') }}</span>
                                @error('sla_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">{{ __('messages.task_due_after_hours') }}</small>
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
                                <i class="fas fa-calendar-check text-success"></i> {{ __('messages.enter_hours_to_calculate') }}
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
                            <input type="datetime-local" class="form-control @error('start_datetime') is-invalid @enderror" id="start_datetime" name="start_datetime" value="{{ old('start_datetime') }}">
                            @error('start_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.optional_start_datetime') }}</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_datetime" class="form-label">{{ __('messages.end_datetime') }}</label>
                            <input type="datetime-local" class="form-control @error('end_datetime') is-invalid @enderror" id="end_datetime" name="end_datetime" value="{{ old('end_datetime') }}">
                            @error('end_datetime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.optional_end_datetime') }}</small>
                        </div>
                    </div>
                </div>
                
                <!-- نوع التكرار -->
                <div class="col-md-6 mb-3">
                    <label for="repeat_type" class="form-label">{{ __('messages.repeat_type') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('repeat_type') is-invalid @enderror" id="repeat_type" name="repeat_type" required>
                        <option value="one_time" {{ old('repeat_type', 'one_time') == 'one_time' ? 'selected' : '' }}>
                            <i class="fas fa-calendar-day"></i> {{ __('messages.one_time') }}
                        </option>
                        <option value="daily" {{ old('repeat_type') == 'daily' ? 'selected' : '' }}>
                            <i class="fas fa-redo"></i> {{ __('messages.daily') }}
                        </option>
                        <option value="quarterly" {{ old('repeat_type') == 'quarterly' ? 'selected' : '' }}>
                            <i class="fas fa-calendar-alt"></i> {{ __('messages.quarterly') }}
                        </option>
                        <option value="yearly" {{ old('repeat_type') == 'yearly' ? 'selected' : '' }}>
                            <i class="fas fa-calendar"></i> {{ __('messages.yearly') }}
                        </option>
                    </select>
                    @error('repeat_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted" id="repeatInfo">
                        <i class="fas fa-info-circle"></i> <span id="repeatInfoText">{{ __('messages.one_time_task_info') }}</span>
                    </small>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('messages.note') }}:</strong>
                        <ul class="mb-0 mt-2">
                            <li>{{ __('messages.task_assignment_rule_1') }}</li>
                            <li>{{ __('messages.task_assignment_rule_2') }}</li>
                            <li>{{ __('messages.task_assignment_rule_3') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
                </button>
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
    console.log('Page loaded - starting initialization');
    
    // Get all elements
    const assignedToSelect = document.getElementById('assigned_to');
    const statusField = document.getElementById('statusField');
    const statusSelect = document.getElementById('status');
    const currentUserId = {!! auth()->id() !!};
    const repeatTypeSelect = document.getElementById('repeat_type');
    const repeatInfoText = document.getElementById('repeatInfoText');
    
    // Template elements
    const templateDepartmentSelect = document.getElementById('template_department');
    const taskTemplateSelect = document.getElementById('task_template_id');
    const titleInput = document.getElementById('title');
    const titleArInput = document.getElementById('title_ar');
    const descriptionInput = document.getElementById('description');
    const descriptionArInput = document.getElementById('description_ar');
    const estimatedTimeInput = document.getElementById('estimated_time');
    
    // SLA elements
    const slaDatetimeRadio = document.getElementById('sla_datetime');
    const slaHoursRadio = document.getElementById('sla_hours');
    const datetimeSection = document.getElementById('datetime_section');
    const hoursSection = document.getElementById('hours_section');
    const dueDateInput = document.getElementById('due_date');
    const dueTimeInput = document.getElementById('due_time');
    const slaHoursInput = document.getElementById('sla_hours_input');
    const calculatedDatetime = document.getElementById('calculated_datetime');
    
    console.log('Elements found:', {
        slaDatetimeRadio: !!slaDatetimeRadio,
        slaHoursRadio: !!slaHoursRadio,
        datetimeSection: !!datetimeSection,
        hoursSection: !!hoursSection,
        slaHoursInput: !!slaHoursInput,
        calculatedDatetime: !!calculatedDatetime
    });
    
    // إظهار/إخفاء حقل الحالة حسب المكلف بالمهمة
    function toggleStatusField() {
        const selectedUserId = parseInt(assignedToSelect.value);
        
        if (selectedUserId === currentUserId) {
            statusField.style.display = 'block';
            statusSelect.required = true;
        } else {
            statusField.style.display = 'none';
            statusSelect.required = false;
            statusSelect.value = '';
        }
    }
    
    // تحديث معلومات التكرار
    function updateRepeatInfo() {
        const repeatType = repeatTypeSelect.value;
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
        
        repeatInfoText.textContent = infoText;
    }
    
    // التبديل بين خيارات SLA - نسخة مبسطة
    function toggleSlaOptions() {
        console.log('toggleSlaOptions called');
        
        if (!slaDatetimeRadio || !slaHoursRadio || !datetimeSection || !hoursSection) {
            console.log('Required elements not found');
            return;
        }
        
        if (slaDatetimeRadio.checked) {
            console.log('DateTime radio is checked - showing datetime section');
            datetimeSection.style.display = 'block';
            hoursSection.style.display = 'none';
            if (dueDateInput) dueDateInput.required = true;
            if (slaHoursInput) {
                slaHoursInput.required = false;
                slaHoursInput.value = '';
            }
        } else if (slaHoursRadio.checked) {
            console.log('Hours radio is checked - showing hours section');
            datetimeSection.style.display = 'none';
            hoursSection.style.display = 'block';
            if (dueDateInput) dueDateInput.required = false;
            if (slaHoursInput) slaHoursInput.required = true;
        }
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
    
    // حساب التاريخ والوقت المتوقع - نسخة مبسطة
    function calculateDueDateTime() {
        console.log('calculateDueDateTime called');
        
        if (!slaHoursInput || !calculatedDatetime) {
            console.log('Required elements not found for calculation');
            return;
        }
        
        const hours = parseInt(slaHoursInput.value) || 0;
        console.log('Hours value:', hours);
        
        if (hours <= 0) {
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
        
        console.log('Calculated datetime updated:', `${dateStr} - ${timeStr}`);
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

    // Event listeners - مبسطة
    console.log('Setting up event listeners');
    
    if (slaDatetimeRadio) {
        slaDatetimeRadio.addEventListener('change', function() {
            console.log('DateTime radio changed');
            toggleSlaOptions();
        });
    }
    
    if (slaHoursRadio) {
        slaHoursRadio.addEventListener('change', function() {
            console.log('Hours radio changed');
            toggleSlaOptions();
            // تحديث التواريخ تلقائياً عند التبديل إلى "Hours from Now"
            setTimeout(updateStartEndDateTimeFromHours, 100);
        });
    }
    
    if (slaHoursInput) {
        slaHoursInput.addEventListener('input', function() {
            console.log('Hours input changed');
            calculateDueDateTime();
            updateStartEndDateTimeFromHours();
        });
        slaHoursInput.addEventListener('change', function() {
            calculateDueDateTime();
            updateStartEndDateTimeFromHours();
        });
    }
    
    if (dueTimeInput) {
        dueTimeInput.addEventListener('change', updateStartEndDateTime);
        dueTimeInput.addEventListener('input', updateStartEndDateTime);
    }
    
    // Initialize page
    toggleStatusField();
    updateRepeatInfo();
    updateCurrentTime();
    toggleSlaOptions();
    
    // Update time every second
    setInterval(updateCurrentTime, 1000);
    
    // تحديث التواريخ تلقائياً إذا كان هناك قيمة في SLA hours
    if (slaHoursInput && slaHoursInput.value && slaHoursRadio && slaHoursRadio.checked) {
        updateStartEndDateTimeFromHours();
    }
    
    // Template functions
    function loadTemplatesForDepartment() {
        const department = templateDepartmentSelect.value;
        
        if (!department) {
            taskTemplateSelect.innerHTML = '<option value="">اختر قالب المهمة</option>';
            return;
        }
        
        // إظهار loading
        taskTemplateSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        
        fetch(`/zoho/task-templates/api/templates-for-department?department=${department}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin' // إرسال cookies للمصادقة
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(templates => {
                taskTemplateSelect.innerHTML = '<option value="">اختر قالب المهمة</option>';
                
                if (Array.isArray(templates) && templates.length > 0) {
                    templates.forEach(template => {
                        const option = document.createElement('option');
                        option.value = template.id;
                        option.textContent = template.name_ar || template.name;
                        option.dataset.name = template.name;
                        option.dataset.nameAr = template.name_ar || '';
                        option.dataset.description = template.description || '';
                        option.dataset.descriptionAr = template.description_ar || '';
                        option.dataset.estimatedTime = template.estimated_time;
                        taskTemplateSelect.appendChild(option);
                    });
                } else {
                    taskTemplateSelect.innerHTML = '<option value="">لا توجد قوالب لهذا القسم</option>';
                }
            })
            .catch(error => {
                console.error('Error loading templates:', error);
                taskTemplateSelect.innerHTML = '<option value="">خطأ في تحميل القوالب</option>';
            });
    }
    
    function applyTemplate() {
        const selectedOption = taskTemplateSelect.options[taskTemplateSelect.selectedIndex];
        
        if (!selectedOption.value) {
            return;
        }
        
        // ملء البيانات من القالب
        if (selectedOption.dataset.name) {
            titleInput.value = selectedOption.dataset.name;
        }
        
        if (selectedOption.dataset.nameAr) {
            titleArInput.value = selectedOption.dataset.nameAr;
        }
        
        if (selectedOption.dataset.description) {
            descriptionInput.value = selectedOption.dataset.description;
        }
        
        if (selectedOption.dataset.descriptionAr) {
            descriptionArInput.value = selectedOption.dataset.descriptionAr;
        }
        
        if (selectedOption.dataset.estimatedTime) {
            estimatedTimeInput.value = selectedOption.dataset.estimatedTime;
        }
        
        // إظهار رسالة تأكيد
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show mt-2';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            تم تطبيق القالب بنجاح
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // إزالة أي تنبيهات سابقة
        const existingAlert = taskTemplateSelect.parentNode.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        taskTemplateSelect.parentNode.appendChild(alert);
        
        // إخفاء التنبيه بعد 3 ثوان
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }

    // Other event listeners
    if (assignedToSelect) assignedToSelect.addEventListener('change', toggleStatusField);
    if (repeatTypeSelect) repeatTypeSelect.addEventListener('change', updateRepeatInfo);
    
    // Template event listeners
    if (templateDepartmentSelect) {
        templateDepartmentSelect.addEventListener('change', loadTemplatesForDepartment);
    }
    
    if (taskTemplateSelect) {
        taskTemplateSelect.addEventListener('change', applyTemplate);
    }
    
    console.log('Initialization completed');
});
</script>
@endpush
