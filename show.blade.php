@extends('layouts.app')

@section('title', __('messages.user_details') . ' - ' . __('messages.system_title'))

@section('content')
@php
    // تجميع المهام حسب النوع
    $marketingTasks = $user->assignedTasks->where('category', 'Marketing');
    $developmentTasks = $user->assignedTasks->where('category', 'Development');
    $supportTasks = $user->assignedTasks->where('category', 'Support');
    $salesTasks = $user->assignedTasks->where('category', 'Sales');
    $otherTasks = $user->assignedTasks->whereNotIn('category', ['Marketing', 'Development', 'Support', 'Sales']);
@endphp

@php
    // Check if today is user's birthday
    $isBirthday = false;
    $age = null;
    $firstName = '';
    
    if ($user->birthday || $user->birth_date) {
        $birthday = $user->birthday ?? $user->birth_date;
        $today = \Carbon\Carbon::today();
        $birthdayThisYear = \Carbon\Carbon::parse($birthday)->setYear($today->year);
        
        if ($today->format('Y-m-d') === $birthdayThisYear->format('Y-m-d')) {
            $isBirthday = true;
            $age = $today->year - \Carbon\Carbon::parse($birthday)->year;
            
            // Get first name (first word from name)
            $nameParts = explode(' ', trim($user->name));
            $firstName = $nameParts[0] ?? $user->name;
        }
    }
@endphp

@if($isBirthday)
<!-- Birthday Celebration Section -->
<div class="birthday-celebration-section mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -50px; left: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    
    <div style="position: relative; z-index: 1;">
        <h1 style="text-align: center; font-size: 2.5em; margin-top: 0; color: #fff; text-shadow: 2px 2px 4px rgba(0,0,0,0.3), 0 0 20px rgba(255,255,255,0.5); letter-spacing: 2px; font-weight: bold;">
            @if(app()->getLocale() === 'ar')
                عيد ميلاد سعيد، {{ $firstName }}! 🎉
            @else
                Happy {{ $age }}th Birthday, {{ $firstName }}! 🎉
            @endif
        </h1>
        
        <h2 style="text-align: center; font-size: 1.3em; margin-bottom: 2em; color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); letter-spacing: 1px;">
            @if(app()->getLocale() === 'ar')
                إليك كعكة للاحتفال 🎂
            @else
                Here's a Cake to Celebrate 🎂
            @endif
        </h2>
        
        <div class="cake-container" style="position: relative; width: 100%; display: flex; align-items: center; justify-content: center; padding-bottom: 5em; min-height: 150px;">
            <div class="mmd_bday_cake" style="position: relative; width: 200px; height: 40px; background: #704b33; border-radius: 100%; box-shadow: 0px 4px 0px #633e26, 0px 8px 0px #633e26, 0px 12px 0px #633e26, 0px 16px 0px #633e26, 0px 20px 0px #633e26, 0px 24px 0px #633e26, 0px 28px 0px #633e26, 0px 32px 0px #c9243d, 0px 36px 0px #c9243d, 0px 40px 0px #f8ecc9, 0px 44px 0px #f8ecc9, 0px 48px 0px #f8ecc9, 0px 52px 0px #f8ecc9, 0px 56px 0px #633e26, 0px 60px 0px #633e26, 0px 64px 0px #633e26, 0px 68px 0px #633e26, 0px 72px 0px #633e26, 0px 76px 0px #633e26, 0px 80px 0px #633e26;">
                <span class="candle" style="position: absolute; top: -30px; left: 50%; transform: translateX(-50%); width: 4px; height: 15px; background: #c2c2c2; border-radius: 2px; box-shadow: 0 -4px 10px rgba(255, 200, 0, 0.5); z-index: 1;"></span>
            </div>
        </div>
    </div>
</div>

<style>
.mmd_bday_cake::before {
    content: "";
    position: absolute;
    right: 0;
    top: 79px;
    left: -25px;
    margin: auto;
    width: 250px;
    height: 50px;
    border-radius: 100%;
    background: #fff;
    box-shadow: 0px 6px 0px rgba(0, 0, 0, 0.1);
    z-index: -1;
}

.mmd_bday_cake::after {
    content: "{{ $age }}";
    position: absolute;
    top: -30px;
    left: 0;
    right: 0;
    margin: 0;
    bottom: 0;
    font-family: "Impact", "Arial Black", sans-serif;
    font-size: 3em;
    font-weight: bold;
    color: #e9f2f9;
    -webkit-text-stroke: 2px #ff4081;
    text-shadow: 0 0 10px #ff4081;
    text-align: center;
}

.candle::before {
    content: "";
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 10px;
    height: 14px;
    background: orange;
    border-radius: 50% 50% 50% 50%;
    box-shadow: 0 0 10px orange;
    animation: flicker 1s infinite ease-in-out;
    z-index: 3;
}

@keyframes flicker {
    0% { opacity: 1; transform: scaleY(1) translateX(-50%); }
    50% { opacity: 0.7; transform: scaleY(0.9) translateX(-50%); }
    100% { opacity: 1; transform: scaleY(1) translateX(-50%); }
}

@media (max-width: 768px) {
    .birthday-celebration-section h1 {
        font-size: 1.8em !important;
    }
    .birthday-celebration-section h2 {
        font-size: 1.1em !important;
    }
    .mmd_bday_cake {
        transform: scale(0.8);
    }
}
</style>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user me-2"></i>{{ __('messages.user_details') }}</h2>
    <div>
        <a href="{{ route('users.contact-card', $user) }}" class="btn btn-info me-2">
            <i class="fas fa-id-card me-2"></i>{{ __('messages.comprehensive_contact_card') }}
        </a>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
        </a>
    </div>
</div>

<div class="row">
    <!-- User Information -->
    <div class="col-md-4 mb-4">
        <div class="card" style="position: relative; overflow: hidden;">
            <!-- Insurance Status Ribbon -->
            @if($user->insurance_status == 'insured')
            <div class="ribbon {{ app()->getLocale() == 'ar' ? 'ribbon-top-left' : 'ribbon-top-right' }} insured-ribbon">
                <span>{{ app()->getLocale() == 'ar' ? 'مؤمن' : 'INSURED' }}</span>
            </div>
            @elseif($user->insurance_status == 'not_insured')
            <div class="ribbon {{ app()->getLocale() == 'ar' ? 'ribbon-top-left' : 'ribbon-top-right' }} not-insured-ribbon">
                <span>{{ app()->getLocale() == 'ar' ? 'غير مؤمن' : 'NOT INSURED' }}</span>
            </div>
            @endif
            
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>{{ __('messages.user_information') }}</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($user->profile_picture)
                    <img src="{{ Storage::url($user->profile_picture) }}" 
                         class="rounded-circle" 
                         style="width: 120px; height: 120px; object-fit: cover;" 
                         alt="{{ __('messages.profile_picture') }} {{ $user->name }}"
                         onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    @else
                    <img src="{{ asset('images/default-avatar.png') }}" 
                         class="rounded-circle" 
                         style="width: 120px; height: 120px; object-fit: cover;" 
                         alt="{{ __('messages.profile_picture') }} {{ $user->name }}">
                    @endif
                </div>
                <h4 class="text-center">{{ $user->name }}</h4>
                <p class="text-center text-muted">{{ $user->email }}</p>
                
                <hr>
                
                <div class="mb-2">
                    <strong>{{ __('messages.role') }}:</strong>
                    <span class="badge bg-{{ $user->role && $user->role->slug == 'admin' ? 'danger' : ($user->role && $user->role->slug == 'manager' ? 'warning' : 'info') }}">
                        {{ $user->role ? (app()->getLocale() === 'ar' ? $user->role->name_ar : $user->role->name) : __('messages.not_specified') }}
                    </span>
                </div>
                
                <div class="mb-2">
                    <strong>{{ __('messages.job_title') }}:</strong>
                    <span class="badge bg-secondary">
                        {{ app()->getLocale() === 'ar' ? ($user->position_ar ?? $user->job_title) : ($user->job_title ?? $user->position) }}
                        {{ !$user->position_ar && !$user->job_title && !$user->position ? __('messages.not_specified') : '' }}
                    </span>
                </div>
                
                <div class="mb-2">
                    <strong>{{ __('messages.department') }}:</strong>
                    @if($user->department)
                        <a href="{{ route('departments.show', $user->department) }}" class="badge bg-primary text-decoration-none department-link" style="cursor: pointer; transition: all 0.3s ease;" title="{{ __('messages.click_to_view_department_details') }}">
                            <i class="fas fa-building me-1"></i>{{ app()->getLocale() === 'ar' ? $user->department->name_ar : $user->department->name }}
                        </a>
                    @else
                        <span class="text-muted">{{ __('messages.not_specified') }}</span>
                    @endif
                </div>
                
                <div class="mb-2">
                    <strong>{{ __('messages.work_phone') }}:</strong>
                    @php
                        $primaryWorkPhone = $user->phones->filter(function($phone) {
                            return $phone->phoneType && $phone->phoneType->slug === 'work' && $phone->is_primary;
                        })->first();
                        $displayPhone = $primaryWorkPhone ? $primaryWorkPhone->phone_number : ($user->phone_work ?? null);
                    @endphp
                    <span>{{ $displayPhone ?? __('messages.not_specified') }}</span>
                </div>
                
                <div class="mb-2">
                    <strong>{{ __('messages.join_date') }}:</strong>
                    <span>{{ $user->created_at ? $user->created_at->format('d-m-Y') : __('messages.not_specified') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>{{ __('messages.assigned_tasks') }}</h5>
                <div class="btn-group">
                    <a href="{{ route('tasks.index') }}?assigned_to={{ $user->id }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-history me-1"></i>{{ __('messages.view_all_tasks_history') }}
                    </a>
                    <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>{{ __('messages.add_task') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($user->assignedTasks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.task_title') }}</th>
                                    <th>{{ __('messages.task_status') }}</th>
                                    <th>{{ __('messages.task_priority') }}</th>
                                    <th>{{ __('messages.due_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->assignedTasks->take(5) as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                {{ Str::limit($task->title, 30) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                {{ __('messages.' . $task->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                                {{ __('messages.' . $task->priority) }}
                                            </span>
                                        </td>
                                        <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : __('messages.not_specified') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($user->assignedTasks->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('tasks.index') }}?assigned_to={{ $user->id }}" class="btn btn-sm btn-outline-primary">
                                {{ __('messages.view_all_tasks') }} ({{ $user->assignedTasks->count() }})
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-muted text-center">{{ __('messages.no_assigned_tasks') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Employee Requests -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>{{ __('messages.user_requests') }}</h5>
                <a href="{{ route('requests.create') }}" class="btn btn-sm btn-primary">{{ __('messages.add_request') }}</a>
            </div>
            <div class="card-body">
                @if($user->employeeRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.request_title') }}</th>
                                    <th>{{ __('messages.request_status') }}</th>
                                    <th>{{ __('messages.created_date') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->employeeRequests->take(5) as $request)
                                    <tr>
                                        <td>
                                            <a href="{{ route('requests.show', $request) }}" class="text-decoration-none">
                                                {{ Str::limit($request->title, 40) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ __('messages.' . $request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->created_at ? $request->created_at->format('d-m-Y') : __('messages.not_specified') }}</td>
                                        <td>
                                            <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($user->employeeRequests->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('requests.index') }}?employee_id={{ $user->id }}" class="btn btn-sm btn-outline-primary">
                                {{ __('messages.view_all_requests') }} ({{ $user->employeeRequests->count() }})
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-muted text-center">{{ __('messages.no_requests') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Employee Overview Section -->
<div class="row mt-4 employee-overview-section">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary">
                <i class="fas fa-user-tie me-2"></i>{{ __('messages.employee_overview') }}
            </h3>
        </div>
        
        <!-- Employee Identification Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>{{ __('messages.employee_identification') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="info-card">
                                    <div class="info-icon">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>{{ __('messages.full_name') }}</h6>
                                        <p>{{ $user->name ?? __('messages.not_specified') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="info-card">
                                    <div class="info-icon">
                                        <i class="fas fa-hashtag text-primary"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>{{ __('messages.employee_number') }}</h6>
                                        <p>{{ $user->employee_id ?? 'EMP-001' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="info-card">
                                    <div class="info-icon">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>{{ __('messages.department_and_team') }}</h6>
                                        <p>{{ $user->department ? (app()->getLocale() === 'ar' ? $user->department->name_ar : $user->department->name) : __('messages.not_specified') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="info-card">
                                    <div class="info-icon">
                                        <i class="fas fa-briefcase text-primary"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>{{ __('messages.job_title') }}</h6>
                                        <p>{{ app()->getLocale() === 'ar' ? ($user->position_ar ?? $user->job_title) : ($user->job_title ?? $user->position) }}
                                        {{ !$user->position_ar && !$user->job_title && !$user->position ? __('messages.not_specified') : '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-card">
                                    <div class="info-icon">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </div>
                                    <div class="info-content">
                                        <h6>{{ __('messages.supervisor_or_manager') }}</h6>
                                        <p>{{ $user->department && $user->department->manager ? $user->department->manager->name : __('messages.not_specified') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="employeeStatusChart" width="300" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance and Punctuality Records Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>{{ __('messages.attendance_and_discipline_records') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>95%</h4>
                                        <p>{{ __('messages.attendance_rate') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>2</h4>
                                        <p>{{ __('messages.late_arrivals_this_month') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>15</h4>
                                        <p>{{ __('messages.remaining_vacation_days') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="attendanceChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="attendance-summary">
                                    <h6>{{ __('messages.attendance_summary') }}</h6>
                                    <div class="summary-item">
                                        <span class="label">{{ __('messages.working_days') }}:</span>
                                        <span class="value">22 {{ __('messages.days') }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">{{ __('messages.attendance_days') }}:</span>
                                        <span class="value">21 {{ __('messages.days') }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">{{ __('messages.absence_days') }}:</span>
                                        <span class="value">1 {{ __('messages.day') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task and Project Tracking Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>{{ __('messages.task_and_project_tracking') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- إحصائيات الوقت المقدر مقابل الفعلي -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-primary">
                                            <i class="fas fa-clock me-2"></i>الوقت المقدر الإجمالي
                                        </h5>
                                        <h3 class="text-primary">{{ number_format($completedTasks->sum('estimated_time'), 1) }}h</h3>
                                        <small class="text-muted">ساعات مقدرة للمهام المكتملة</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-success">
                                            <i class="fas fa-stopwatch me-2"></i>الوقت الفعلي الإجمالي
                                        </h5>
                                        <h3 class="text-success">{{ number_format($completedTasks->filter(function($task) { return $task->actual_start_datetime && $task->actual_end_datetime; })->sum(function($task) { return $task->actual_start_datetime->diffInHours($task->actual_end_datetime); }), 1) }}h</h3>
                                        <small class="text-muted">ساعات فعلية للمهام المكتملة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- إحصائيات المهام حسب النوع -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-primary">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'marketing')->count() }}</h4>
                                        <p>{{ __('messages.marketing_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'development')->count() }}</h4>
                                        <p>{{ __('messages.development_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'support')->count() }}</h4>
                                        <p>{{ __('messages.support_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'sales')->count() }}</h4>
                                        <p>{{ __('messages.sales_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- إحصائيات إضافية للمهام -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-purple">
                                        <i class="fas fa-paint-brush"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'design')->count() }}</h4>
                                        <p>{{ __('messages.design_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-teal">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'communication')->count() }}</h4>
                                        <p>{{ __('messages.communication_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-orange">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'research')->count() }}</h4>
                                        <p>{{ __('messages.research_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon bg-dark">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $completedTasks->where('category', 'general')->count() }}</h4>
                                        <p>{{ __('messages.general_tasks') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- عرض المهام حسب النوع -->
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="tasksChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="progress-summary">
                                    <h6>{{ __('messages.task_progress_by_type') }}</h6>
                                    
                                    @if($marketingTasks->count() > 0)
                                    <div class="progress-item">
                                        <div class="progress-label">{{ __('messages.marketing_tasks') }}</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" style="width: {{ $marketingTasks->where('status', 'completed')->count() / $marketingTasks->count() * 100 }}%">
                                                {{ round($marketingTasks->where('status', 'completed')->count() / $marketingTasks->count() * 100) }}%
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if($developmentTasks->count() > 0)
                                    <div class="progress-item">
                                        <div class="progress-label">التطوير</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ $developmentTasks->where('status', 'completed')->count() / $developmentTasks->count() * 100 }}%">
                                                {{ round($developmentTasks->where('status', 'completed')->count() / $developmentTasks->count() * 100) }}%
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if($supportTasks->count() > 0)
                                    <div class="progress-item">
                                        <div class="progress-label">الدعم</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ $supportTasks->where('status', 'completed')->count() / $supportTasks->count() * 100 }}%">
                                                {{ round($supportTasks->where('status', 'completed')->count() / $supportTasks->count() * 100) }}%
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if($salesTasks->count() > 0)
                                    <div class="progress-item">
                                        <div class="progress-label">المبيعات</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: {{ $salesTasks->where('status', 'completed')->count() / $salesTasks->count() * 100 }}%">
                                                {{ round($salesTasks->where('status', 'completed')->count() / $salesTasks->count() * 100) }}%
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- آخر المهام المكتملة -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">{{ __('messages.recent_completed_tasks') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('messages.task_title') }}</th>
                                                <th>{{ __('messages.category') }}</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>{{ __('messages.estimated_time') }}</th>
                                                <th>Actual Time</th>
                                                <th>{{ __('messages.status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($completedTasks->take(10) as $task)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        <span>{{ $task->title }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $task->category === 'marketing' ? 'primary' : ($task->category === 'development' ? 'success' : ($task->category === 'design' ? 'purple' : ($task->category === 'communication' ? 'teal' : ($task->category === 'research' ? 'orange' : 'dark')))) }}">
                                                        {{ __('messages.' . $task->category . '_tasks') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($task->start_datetime)
                                                    <small class="text-primary">
                                                        <i class="fas fa-play me-1"></i>
                                                        {{ $task->start_datetime->format('Y-m-d H:i') }}
                                                    </small>
                                                    @else
                                                    <small class="text-muted">غير محدد</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task->end_datetime)
                                                    <small class="text-warning">
                                                        <i class="fas fa-stop me-1"></i>
                                                        {{ $task->end_datetime->format('Y-m-d H:i') }}
                                                    </small>
                                                    @else
                                                    <small class="text-muted">غير محدد</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task->estimated_time)
                                                    <small class="text-info">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ number_format($task->estimated_time, 1) }}h
                                                    </small>
                                                    @else
                                                    <small class="text-muted">غير محدد</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task->actual_start_datetime && $task->actual_end_datetime)
                                                    <small class="text-success">
                                                        <i class="fas fa-stopwatch me-1"></i>
                                                        {{ number_format($task->actual_start_datetime->diffInHours($task->actual_end_datetime), 1) }}h
                                                    </small>
                                                    @else
                                                    <small class="text-muted">غير محدد</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">{{ __('messages.completed') }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    {{ __('messages.no_completed_tasks') }}
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($completedTasks->count() > 10)
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        {{ __('messages.showing') }} 10 {{ __('messages.of') }} {{ $completedTasks->count() }} {{ __('messages.completed_tasks') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                                
                                @if($marketingTasks->count() > 0)
                                <div class="task-category-section mb-3">
                                    <h6 class="text-primary">
                                        <i class="fas fa-bullhorn me-2"></i>{{ __('messages.marketing_tasks') }} ({{ $marketingTasks->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>عنوان المهمة</th>
                                                    <th>الحالة</th>
                                                    <th>الأولوية</th>
                                                    <th>تاريخ الاستحقاق</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($marketingTasks->take(3) as $task)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ Str::limit($task->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                            {{ __('messages.' . $task->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                                            {{ __('messages.' . $task->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : __('messages.not_specified') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($developmentTasks->count() > 0)
                                <div class="task-category-section mb-3">
                                    <h6 class="text-success">
                                        <i class="fas fa-code me-2"></i>{{ __('messages.development_tasks') }} ({{ $developmentTasks->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>عنوان المهمة</th>
                                                    <th>الحالة</th>
                                                    <th>الأولوية</th>
                                                    <th>تاريخ الاستحقاق</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($developmentTasks->take(3) as $task)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ Str::limit($task->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                            {{ __('messages.' . $task->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                                            {{ __('messages.' . $task->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : __('messages.not_specified') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($supportTasks->count() > 0)
                                <div class="task-category-section mb-3">
                                    <h6 class="text-warning">
                                        <i class="fas fa-headset me-2"></i>{{ __('messages.support_tasks') }} ({{ $supportTasks->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>عنوان المهمة</th>
                                                    <th>الحالة</th>
                                                    <th>الأولوية</th>
                                                    <th>تاريخ الاستحقاق</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($supportTasks->take(3) as $task)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ Str::limit($task->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                            {{ __('messages.' . $task->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                                            {{ __('messages.' . $task->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : __('messages.not_specified') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($salesTasks->count() > 0)
                                <div class="task-category-section mb-3">
                                    <h6 class="text-info">
                                        <i class="fas fa-chart-line me-2"></i>{{ __('messages.sales_tasks') }} ({{ $salesTasks->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>عنوان المهمة</th>
                                                    <th>الحالة</th>
                                                    <th>الأولوية</th>
                                                    <th>تاريخ الاستحقاق</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($salesTasks->take(3) as $task)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ Str::limit($task->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                            {{ __('messages.' . $task->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                                            {{ __('messages.' . $task->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : __('messages.not_specified') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($otherTasks->count() > 0)
                                <div class="task-category-section mb-3">
                                    <h6 class="text-secondary">
                                        <i class="fas fa-tasks me-2"></i>مهام أخرى ({{ $otherTasks->count() }})
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>عنوان المهمة</th>
                                                    <th>الحالة</th>
                                                    <th>الأولوية</th>
                                                    <th>تاريخ الاستحقاق</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($otherTasks->take(3) as $task)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ Str::limit($task->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                            {{ __('messages.' . $task->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning' : 'success') }}">
                                                            {{ __('messages.' . $task->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : __('messages.not_specified') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics and KPIs Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>{{ __('messages.performance_indicators') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="performanceChart" width="400" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="kpi-grid">
                                    <div class="kpi-item">
                                        <div class="kpi-icon bg-primary">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="kpi-content">
                                            <h3>150,000</h3>
                                            <p>{{ __('messages.monthly_sales') }}</p>
                                        </div>
                                    </div>
                                    <div class="kpi-item">
                                        <div class="kpi-icon bg-info">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <div class="kpi-content">
                                            <h3>45</h3>
                                            <p>{{ __('messages.resolved_tickets') }}</p>
                                        </div>
                                    </div>
                                    <div class="kpi-item">
                                        <div class="kpi-icon bg-success">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div class="kpi-content">
                                            <h3>12</h3>
                                            <p>{{ __('messages.new_customers') }}</p>
                                        </div>
                                    </div>
                                    <div class="kpi-item">
                                        <div class="kpi-icon bg-warning">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="kpi-content">
                                            <h3>4.8/5</h3>
                                            <p>{{ __('messages.customer_rating') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training and Development Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-purple text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>{{ __('messages.training_and_development') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="trainingChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="training-summary">
                                    <h6>{{ __('messages.training_summary') }}</h6>
                                    <div class="training-item">
                                        <i class="fas fa-certificate text-success"></i>
                                        <span>{{ __('messages.programming_basics') }}</span>
                                        <span class="badge bg-success">{{ __('messages.completed') }}</span>
                                    </div>
                                    <div class="training-item">
                                        <i class="fas fa-certificate text-info"></i>
                                        <span>{{ __('messages.project_management') }}</span>
                                        <span class="badge bg-info">{{ __('messages.completed') }}</span>
                                    </div>
                                    <div class="training-item">
                                        <i class="fas fa-certificate text-warning"></i>
                                        <span>{{ __('messages.customer_service') }}</span>
                                        <span class="badge bg-warning">{{ __('messages.in_progress') }}</span>
                                    </div>
                                    <div class="training-item">
                                        <i class="fas fa-certificate text-primary"></i>
                                        <span>{{ __('messages.leadership_program') }}</span>
                                        <span class="badge bg-primary">{{ __('messages.planned') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manager and Peer Evaluations Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>{{ __('messages.manager_and_peer_evaluations') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="evaluationChart" width="400" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="evaluation-summary">
                                    <h6>{{ __('messages.performance_evaluations') }}</h6>
                                    <div class="evaluation-item">
                                        <div class="evaluation-label">{{ __('messages.manager_evaluation') }}</div>
                                        <div class="rating">
                                            <div class="stars">
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                            </div>
                                            <span class="rating-value">4.2/5</span>
                                        </div>
                                    </div>
                                    <div class="evaluation-item">
                                        <div class="evaluation-label">{{ __('messages.peer_evaluation') }}</div>
                                        <div class="rating">
                                            <div class="stars">
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                            </div>
                                            <span class="rating-value">4.6/5</span>
                                        </div>
                                    </div>
                                    <div class="evaluation-item">
                                        <div class="evaluation-label">{{ __('messages.customer_evaluation') }}</div>
                                        <div class="rating">
                                            <div class="stars">
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                                <i class="fas fa-star text-warning"></i>
                                            </div>
                                            <span class="rating-value">4.8/5</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recognition and Disciplinary Records Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-trophy me-2"></i>{{ __('messages.recognition_and_discipline_records') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="recognitionChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="recognition-summary">
                                    <h6>{{ __('messages.awards_and_recognition') }}</h6>
                                    <div class="award-item">
                                        <i class="fas fa-trophy text-warning"></i>
                                        <div class="award-content">
                                            <strong>{{ __('messages.employee_of_month') }}</strong>
                                            <small>نوفمبر 2024</small>
                                        </div>
                                    </div>
                                    <div class="award-item">
                                        <i class="fas fa-medal text-success"></i>
                                        <div class="award-content">
                                            <strong>{{ __('messages.excellence_certificate') }}</strong>
                                            <small>{{ __('messages.in_performance') }}</small>
                                        </div>
                                    </div>
                                    <div class="award-item">
                                        <i class="fas fa-award text-info"></i>
                                        <div class="award-content">
                                            <strong>{{ __('messages.teamwork_award') }}</strong>
                                            <small>2024</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shared Documents Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>{{ __('messages.shared_documents') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="documents-grid">
                                    <div class="document-item">
                                        <div class="document-icon">
                                            <i class="fas fa-book text-primary"></i>
                                        </div>
                                        <div class="document-content">
                                            <h6>{{ __('messages.employee_handbook') }}</h6>
                                            <p>{{ __('messages.comprehensive_company_policies') }}</p>
                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>{{ __('messages.download') }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="document-item">
                                        <div class="document-icon">
                                            <i class="fas fa-cogs text-info"></i>
                                        </div>
                                        <div class="document-content">
                                            <h6>{{ __('messages.work_procedures') }}</h6>
                                            <p>{{ __('messages.operations_and_procedures_guide') }}</p>
                                            <a href="#" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-download me-1"></i>{{ __('messages.download') }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="document-item">
                                        <div class="document-icon">
                                            <i class="fas fa-shield-alt text-success"></i>
                                        </div>
                                        <div class="document-content">
                                            <h6>{{ __('messages.security_guide') }}</h6>
                                            <p>{{ __('messages.safety_and_security_guidelines') }}</p>
                                            <a href="#" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download me-1"></i>{{ __('messages.download') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <canvas id="documentsChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zoho Tickets Section -->
        @if(!empty($zohoTickets) && count($zohoTickets) > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card employee-section-card">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>{{ $user->department ? ($user->department->name_en ?? $user->department->name) : 'Department' }} Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Closed By</th>
                                        <th>Created</th>
                                        <th>Closed</th>
                                        <th>Threads</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($zohoTickets as $ticket)
                                    <tr style="cursor: pointer;" onclick="viewTicketDetails('{{ $ticket->zoho_ticket_id }}')">
                                        <td>{{ $ticket->ticket_number ?? '-' }}</td>
                                        <td>{{ Str::limit($ticket->subject ?? '-', 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ ($ticket->status ?? '') === 'Closed' ? 'success' : (($ticket->status ?? '') === 'Open' ? 'danger' : 'warning') }}">
                                                {{ $ticket->status ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $closedBy = null;
                                                
                                                // Check raw_data first (most accurate from Zoho API)
                                                if ($ticket->raw_data && isset($ticket->raw_data['cf']['cf_closed_by']) && !empty($ticket->raw_data['cf']['cf_closed_by'])) {
                                                    $closedBy = $ticket->raw_data['cf']['cf_closed_by'];
                                                }
                                                // Check customFields
                                                elseif ($ticket->raw_data && isset($ticket->raw_data['customFields']['Closed By']) && !empty($ticket->raw_data['customFields']['Closed By'])) {
                                                    $closedBy = $ticket->raw_data['customFields']['Closed By'];
                                                }
                                                // Check closed_by_name
                                                elseif ($ticket->closed_by_name) {
                                                    $closedBy = $ticket->closed_by_name;
                                                }
                                                
                                                // Format the display
                                                if ($closedBy) {
                                                    if ($closedBy === 'Auto Close') {
                                                        echo '<span class="badge bg-warning">Auto Close</span>';
                                                    } else {
                                                        echo '<span class="badge bg-info">' . htmlspecialchars($closedBy) . '</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">غير محدد</span>';
                                                }
                                            @endphp
                                        </td>
                                        <td>{{ $ticket->created_at_zoho ? \Carbon\Carbon::parse($ticket->created_at_zoho)->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $ticket->closed_at_zoho ? \Carbon\Carbon::parse($ticket->closed_at_zoho)->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $ticket->thread_count ?? 0 }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Showing {{ count($zohoTickets) }} ticket{{ count($zohoTickets) > 1 ? 's' : '' }} 
                                @if(count($zohoTickets) >= 3000)
                                (limited to 3000 most recent)
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css?family=Lato:700');
.department-link:hover {
    background-color: #0056b3 !important;
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Insurance Status Ribbon Styles - Based on Corner Ribbon */
.ribbon {
    width: 150px;
    height: 150px;
    overflow: hidden;
    position: absolute;
    z-index: 1000;
    pointer-events: none;
}

.ribbon::before,
.ribbon::after {
    position: absolute;
    z-index: -1;
    content: '';
    display: block;
    border: 5px solid;
}

.ribbon span {
    position: absolute;
    display: block;
    width: 225px;
    padding: 12px 0;
    box-shadow: 0 5px 10px rgba(0,0,0,.15);
    color: #fff;
    font: 700 12px/1 'Lato', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    text-shadow: 0 1px 1px rgba(0,0,0,.3);
    text-align: center;
    letter-spacing: 1px;
}

/* English - uppercase */
[dir="ltr"] .ribbon span {
    text-transform: uppercase;
}

/* Arabic - no uppercase, adjust width */
[dir="rtl"] .ribbon span {
    text-transform: none;
    width: 200px;
    font-size: 13px;
    letter-spacing: 0.5px;
}

/* Top Right - Insurance Status (LTR) */
.ribbon-top-right {
    top: -10px;
    right: -10px;
}

.ribbon-top-right::before,
.ribbon-top-right::after {
    border-top-color: transparent;
    border-right-color: transparent;
}

.ribbon-top-right::before {
    top: 0;
    left: 0;
}

.ribbon-top-right::after {
    bottom: 0;
    right: 0;
}

.ribbon-top-right span {
    left: -25px;
    top: 30px;
    transform: rotate(45deg);
}

/* Top Left - Insurance Status (RTL/Arabic) */
.ribbon-top-left {
    top: -10px;
    left: -10px;
}

.ribbon-top-left::before,
.ribbon-top-left::after {
    border-top-color: transparent;
    border-left-color: transparent;
}

.ribbon-top-left::before {
    top: 0;
    right: 0;
}

.ribbon-top-left::after {
    bottom: 0;
    left: 0;
}

.ribbon-top-left span {
    right: -25px;
    top: 30px;
    transform: rotate(-45deg);
}

/* Insured - Bright Green */
.insured-ribbon::before,
.insured-ribbon::after {
    border-color: #28a745;
}

.insured-ribbon span {
    background-color: #28a745;
}

/* Not Insured - Dark Green */
.not-insured-ribbon::before,
.not-insured-ribbon::after {
    border-color: #155724;
}

.not-insured-ribbon span {
    background-color: #155724;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Employee Status Chart
    const employeeStatusCtx = document.getElementById('employeeStatusChart');
    if (employeeStatusCtx) {
        new Chart(employeeStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['نشط', 'في إجازة', 'غير متاح'],
                datasets: [{
                    data: [85, 10, 5],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'معدل الحضور %',
                    data: [92, 88, 95, 90, 94, 96],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Tasks Chart - Updated to show tasks by category
    const tasksCtx = document.getElementById('tasksChart');
    if (tasksCtx) {
        new Chart(tasksCtx, {
            type: 'bar',
            data: {
                labels: ['التسويق', 'التطوير', 'الدعم', 'المبيعات'],
                datasets: [{
                    label: 'عدد المهام',
                    data: [
                        {{ $marketingTasks->count() }},
                        {{ $developmentTasks->count() }},
                        {{ $supportTasks->count() }},
                        {{ $salesTasks->count() }}
                    ],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8'],
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'radar',
            data: {
                labels: ['الإنتاجية', 'الجودة', 'التواصل', 'القيادة', 'الإبداع', 'العمل الجماعي'],
                datasets: [{
                    label: 'الأداء الحالي',
                    data: [85, 90, 88, 75, 82, 87],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    borderWidth: 2,
                    pointBackgroundColor: '#007bff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'المتوسط العام',
                    data: [75, 80, 78, 70, 75, 80],
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#6c757d',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                }
            }
        });
    }

    // Training Chart
    const trainingCtx = document.getElementById('trainingChart');
    if (trainingCtx) {
        new Chart(trainingCtx, {
            type: 'pie',
            data: {
                labels: ['{{ __('messages.completed') }}', '{{ __('messages.in_progress') }}', '{{ __('messages.planned') }}'],
                datasets: [{
                    data: [60, 25, 15],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Evaluation Chart
    const evaluationCtx = document.getElementById('evaluationChart');
    if (evaluationCtx) {
        new Chart(evaluationCtx, {
            type: 'bar',
            data: {
                labels: ['المدير', 'الزملاء', 'العملاء', 'المرؤوسين'],
                datasets: [{
                    label: 'التقييم',
                    data: [4.2, 4.6, 4.8, 4.4],
                    backgroundColor: ['#dc3545', '#007bff', '#28a745', '#ffc107'],
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Recognition Chart
    const recognitionCtx = document.getElementById('recognitionChart');
    if (recognitionCtx) {
        new Chart(recognitionCtx, {
            type: 'doughnut',
            data: {
                labels: ['جوائز', 'تقديرات', 'شهادات'],
                datasets: [{
                    data: [3, 5, 2],
                    backgroundColor: ['#ffc107', '#28a745', '#17a2b8'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Documents Chart
    const documentsCtx = document.getElementById('documentsChart');
    if (documentsCtx) {
        new Chart(documentsCtx, {
            type: 'bar',
            data: {
                labels: ['{{ __('messages.downloaded') }}', '{{ __('messages.available_for_download') }}', '{{ __('messages.under_review') }}'],
                datasets: [{
                    label: '{{ __('messages.documents') }}',
                    data: [8, 12, 3],
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 2
                        }
                    }
                }
            }
        });
    }
});

// Function to view ticket details
function viewTicketDetails(zohoTicketId) {
    // Show modal and load ticket details
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    modal.show();
    
    // Load ticket details via AJAX
    loadTicketDetailsFromZoho(zohoTicketId);
}

// Function to load ticket details from Zoho or cache
function loadTicketDetailsFromZoho(zohoTicketId) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    
    // Show loading state
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">جاري تحميل تفاصيل التذكرة...</p>
        </div>
    `;
    
    // Try to fetch full ticket details from Zoho API first
    fetch(`/api/zoho/ticket-full/${zohoTicketId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                displayTicketFullDetails(data.data);
            } else {
                // Fallback to cache
                fetch(`/api/zoho/ticket-cache/${zohoTicketId}`)
                    .then(response => response.json())
                    .then(cacheData => {
                        if (cacheData.success) {
                            displayTicketDetailsFromCache(cacheData.data);
                        } else {
                            contentDiv.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    خطأ في تحميل تفاصيل التذكرة: ${cacheData.error || 'خطأ غير معروف'}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading ticket from cache:', error);
                        contentDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                خطأ في الاتصال بالخادم
                            </div>
                        `;
                    });
            }
        })
        .catch(error => {
            console.error('Error loading full ticket details:', error);
            // Fallback to cache
            fetch(`/api/zoho/ticket-cache/${zohoTicketId}`)
                .then(response => response.json())
                .then(cacheData => {
                    if (cacheData.success) {
                        displayTicketDetailsFromCache(cacheData.data);
                    } else {
                        contentDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                خطأ في تحميل تفاصيل التذكرة
                            </div>
                        `;
                    }
                })
                .catch(innerError => {
                    console.error('Error loading ticket from cache:', innerError);
                    contentDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            خطأ في الاتصال بالخادم
                        </div>
                    `;
                });
        });
}

// Function to display full ticket details from Zoho API
function displayTicketFullDetails(fullData) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    const ticket = fullData.ticket || fullData.data || fullData;
    const threads = fullData.threads || fullData.threads_data || [];
    
    // Extract closed_by
    let closedBy = 'غير محدد';
    if (ticket.cf && ticket.cf.cf_closed_by) {
        closedBy = ticket.cf.cf_closed_by;
    } else if (ticket.customFields && ticket.customFields['Closed By']) {
        closedBy = ticket.customFields['Closed By'];
    }
    
    // Format dates
    const createdDate = ticket.createdTime ? new Date(ticket.createdTime).toLocaleString('ar-EG') : 'غير محدد';
    const closedDate = ticket.closedTime ? new Date(ticket.closedTime).toLocaleString('ar-EG') : 'N/A';
    
    // Status badge color
    const statusColors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary',
        'Resolved': 'primary'
    };
    const statusColor = statusColors[ticket.status || ticket.statusType] || 'secondary';
    
    // Format closed_by
    let closedByBadge = '';
    if (closedBy === 'Auto Close') {
        closedByBadge = '<span class="badge bg-warning">Auto Close</span>';
    } else if (closedBy !== 'غير محدد') {
        closedByBadge = `<span class="badge bg-info">${closedBy}</span>`;
    } else {
        closedByBadge = '<span class="text-muted">غير محدد</span>';
    }
    
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Ticket Number</h6>
                <p><code>${ticket.ticketNumber || ticket.id || '-'}</code></p>
            </div>
            <div class="col-md-6">
                <h6>Status</h6>
                <p><span class="badge bg-${statusColor}">${ticket.status || ticket.statusType || '-'}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Subject</h6>
                <p>${ticket.subject || 'بدون موضوع'}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>Closed By</h6>
                <p>${closedByBadge}</p>
            </div>
            <div class="col-md-6">
                <h6>Threads Count</h6>
                <p><span class="badge bg-secondary">${ticket.threadCount || threads.length || 0}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>Created Date</h6>
                <p>${createdDate}</p>
            </div>
            <div class="col-md-6">
                <h6>Closed Date</h6>
                <p>${closedDate}</p>
            </div>
        </div>
    `;
}

// Function to display ticket details in modal
function displayTicketDetailsFromCache(ticket) {
    const contentDiv = document.getElementById('ticketDetailsContent');
    
    // Extract closed_by
    let closedBy = 'غير محدد';
    if (ticket.raw_data && ticket.raw_data.cf && ticket.raw_data.cf.cf_closed_by) {
        closedBy = ticket.raw_data.cf.cf_closed_by;
    } else if (ticket.raw_data && ticket.raw_data.customFields && ticket.raw_data.customFields['Closed By']) {
        closedBy = ticket.raw_data.customFields['Closed By'];
    } else if (ticket.closed_by_name) {
        closedBy = ticket.closed_by_name;
    }
    
    // Format dates
    const createdDate = ticket.created_at_zoho ? new Date(ticket.created_at_zoho).toLocaleString('ar-EG') : 'غير محدد';
    const closedDate = ticket.closed_at_zoho ? new Date(ticket.closed_at_zoho).toLocaleString('ar-EG') : 'N/A';
    
    // Status badge color
    const statusColors = {
        'Open': 'warning',
        'Closed': 'success',
        'Pending': 'info',
        'In Progress': 'secondary',
        'Resolved': 'primary'
    };
    const statusColor = statusColors[ticket.status] || 'secondary';
    
    // Format closed_by
    let closedByBadge = '';
    if (closedBy === 'Auto Close') {
        closedByBadge = '<span class="badge bg-warning">Auto Close</span>';
    } else if (closedBy !== 'غير محدد') {
        closedByBadge = `<span class="badge bg-info">${closedBy}</span>`;
    } else {
        closedByBadge = '<span class="text-muted">غير محدد</span>';
    }
    
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Ticket Number</h6>
                <p><code>${ticket.ticket_number || '-'}</code></p>
            </div>
            <div class="col-md-6">
                <h6>Status</h6>
                <p><span class="badge bg-${statusColor}">${ticket.status || '-'}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Subject</h6>
                <p>${ticket.subject || 'بدون موضوع'}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>Closed By</h6>
                <p>${closedByBadge}</p>
            </div>
            <div class="col-md-6">
                <h6>Threads Count</h6>
                <p><span class="badge bg-secondary">${ticket.thread_count || 0}</span></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h6>Created Date</h6>
                <p>${createdDate}</p>
            </div>
            <div class="col-md-6">
                <h6>Closed Date</h6>
                <p>${closedDate}</p>
            </div>
        </div>
        ${ticket.response_time_minutes ? `
        <div class="row">
            <div class="col-md-6">
                <h6>Response Time</h6>
                <p><span class="badge bg-success">${(ticket.response_time_minutes / 60).toFixed(1)} ساعة</span></p>
            </div>
        </div>
        ` : ''}
        ${ticket.raw_data ? `
        <div class="row mt-3">
            <div class="col-12">
                <h6>Raw Data</h6>
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(ticket.raw_data, null, 2)}</code></pre>
            </div>
        </div>
        ` : ''}
    `;
}
</script>

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="ticketDetailsModalLabel">
                    <i class="fas fa-ticket-alt me-2"></i>تفاصيل التذكرة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">جاري تحميل تفاصيل التذكرة...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection
