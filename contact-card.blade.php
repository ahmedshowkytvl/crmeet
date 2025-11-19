@extends('layouts.app')

@section('title', __('messages.contact_card') . ' - ' . $user->name)

@section('content')
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

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-3">
                        <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i>
                    </a>
                    <h2 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>{{ __('messages.contact_card') }}
                    </h2>
                </div>
                <div class="btn-group">
                    <a href="{{ route('users.edit-contact-card', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>{{ __('messages.edit_card') }}
                    </a>
                    <button onclick="startDirectChat({{ $user->id }})" class="btn btn-primary">
                        <i class="fas fa-paper-plane {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>{{ __('messages.quick_message') }}
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#scheduleMeetingModal">
                        <i class="fas fa-calendar-plus {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>{{ __('messages.schedule_meeting') }}
                    </button>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#privacySettingsModal">
                        <i class="fas fa-cog {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }}"></i>{{ __('messages.privacy_settings') }}
                    </button>
                </div>
            </div>
        </div>
</div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center" id="profile-id-card">
                    <!-- Profile Image -->
                    <div class="mb-4 position-relative d-inline-block">
                            @if($user->profile_picture)
                            <img src="{{ Storage::url($user->profile_picture) }}" 
                                 class="rounded-circle profile-image-clickable" 
                                 style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;" 
                                 alt="{{ __('messages.profile_picture') }} {{ $user->name }}"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal"
                                 data-image-src="{{ Storage::url($user->profile_picture) }}"
                                 data-image-alt="{{ $user->name }}"
                                 onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                            @else
                            <img src="{{ asset('images/default-avatar.png') }}" 
                                 class="rounded-circle profile-image-clickable" 
                                 style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;" 
                                 alt="{{ __('messages.profile_picture') }} {{ $user->name }}"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#imageModal"
                                 data-image-src="{{ asset('images/default-avatar.png') }}"
                                 data-image-alt="{{ $user->name }}">
                            @endif
                            
                            <!-- Online Status Indicator -->
                            @php
                                // الحصول على حالة المستخدم من قاعدة البيانات
                                $isOnline = \DB::selectOne("SELECT is_online FROM user_online_status WHERE user_id = ?", [$user->id]);
                                $isOnline = $isOnline ? $isOnline->is_online : false;
                                $statusColor = $isOnline ? '#28a745' : '#dc3545'; // أخضر للعبر الإنترنت، أحمر للخارج
                                $statusTitle = $isOnline ? __('messages.online') : __('messages.offline');
                            @endphp
                            <div class="position-absolute online-indicator {{ $isOnline ? 'pulse-animation' : 'offline-indicator' }}" 
                                 style="bottom: 8px; right: 8px; width: 24px; height: 24px; background-color: {{ $statusColor }}; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"
                                 title="{{ $statusTitle }}">
                            </div>
                    </div>

                    <!-- Basic Info -->
                    <h4 class="mb-2 " id="card-display-name">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->job_title ?? 'غير محدد' }}</p>
                    
                    @if($user->department)
                        <span class="badge bg-primary fs-6 mb-2">{{ $user->department->name }}</span>
                    @endif
                    
                    @if($user->role)
                        <div class="mb-3">
                            <span class="badge bg-{{ $user->role->slug == 'ceo' ? 'danger' : ($user->role->slug == 'head_manager' ? 'warning' : 'success') }} fs-6">
                                {{ app()->getLocale() == 'ar' ? $user->role->name_ar : $user->role->name }}
                            </span>
                        </div>
                    @endif

                    <!-- Contact Info -->
                    <div class="contact-info mt-4">
                        @if($user->show_email && $user->email)
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                    {{ $user->email }}
                                </a>
                                </div>
                                @endif

                        @if($user->show_phone_work && $user->phone_work)
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-phone text-success me-2"></i>
                                <a href="tel:{{ $user->phone_work }}" class="text-decoration-none">
                                    {{ $user->phone_work }}
                                </a>
                                </div>
                                @endif

                        @if(isset($user->show_phone_mobile) && $user->show_phone_mobile && isset($user->phone_mobile) && $user->phone_mobile)
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-mobile-alt text-info me-2"></i>
                                <a href="tel:{{ $user->phone_mobile }}" class="text-decoration-none">
                                    {{ $user->phone_mobile }}
                                </a>
                            </div>
                        @endif

                        @if($user->avaya_extension)
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-phone-square text-warning me-2"></i>
                                <span>{{ __('messages.avaya_extension') }}: {{ $user->avaya_extension }}</span>
                            </div>
                        @endif
                        
                        @if($user->microsoft_teams_id)
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fab fa-microsoft text-primary me-2"></i>
                                <span>{{ __('messages.microsoft_teams_id') }}: {{ $user->microsoft_teams_id }}</span>
                            </div>
                        @endif
                        
                        @if($user->extension)
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="fas fa-phone text-info me-2"></i>
                                <span>{{ __('messages.extension') }}: {{ $user->extension }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Social Media -->
                    @if($user->show_social_media)
                        <div class="social-links mt-4">
                                @if($user->linkedin_url)
                                <a href="{{ $user->linkedin_url }}" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                @endif
                                @if($user->twitter_url)
                                <a href="{{ $user->twitter_url }}" target="_blank" class="btn btn-outline-info btn-sm me-2">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                @endif
                            @if($user->whatsapp)
                                <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank" class="btn btn-outline-success btn-sm me-2">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                            @if($user->telegram)
                                <a href="https://t.me/{{ $user->telegram }}" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fab fa-telegram"></i>
                                </a>
                                @endif
                        </div>
                        @endif
        </div>
    </div>
</div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Work Information -->
            <div class="card mb-4">
            <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>{{ __('messages.work_information') }}
                    </h5>
            </div>
            <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>{{ __('messages.employee_id') }}:</strong>
                                <span class="text-muted">{{ $user->employee_id ?? $user->id }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.hire_date') }}:</strong>
                                <span class="text-muted">{{ $user->hire_date ? $user->hire_date->format('d-m-Y') : __('messages.not_specified') }}</span>
                            </div>
                <div class="mb-3">
                                <strong>{{ __('messages.work_location') }}:</strong>
                                <span class="text-muted">{{ $user->work_location ?? __('messages.not_specified') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>{{ __('messages.department') }}:</strong>
                                <span class="text-muted">{{ $user->department ? $user->department->name : __('messages.not_specified') }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.direct_manager') }}:</strong>
                                @if($user->manager)
                                    <a href="{{ route('users.contact-card', $user->manager) }}" class="text-decoration-none">
                                {{ $user->manager->name }}
                            </a>
                                @else
                                    <span class="text-muted">{{ __('messages.not_specified') }}</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.subordinates') }}:</strong>
                                <span class="badge bg-info">{{ $user->subordinates->count() }} {{ __('messages.employees') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-phone me-2"></i>{{ __('messages.contact_information') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    @if($user->avaya_extension)
                                        <div class="mb-2">
                                            <i class="fas fa-phone-square text-warning me-2"></i>
                                            <strong>{{ __('messages.avaya_extension') }}:</strong> {{ $user->avaya_extension }}
                                        </div>
                                    @endif
                                    
                                    @if($user->microsoft_teams_id)
                                        <div class="mb-2">
                                            <i class="fab fa-microsoft text-primary me-2"></i>
                                            <strong>{{ __('messages.microsoft_teams_id') }}:</strong> {{ $user->microsoft_teams_id }}
                                        </div>
                                    @endif
                                    
                                    @if($user->extension)
                                        <div class="mb-2">
                                            <i class="fas fa-phone text-info me-2"></i>
                                            <strong>{{ __('messages.extension') }}:</strong> {{ $user->extension }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-6">
                                    @if($user->skype)
                                        <div class="mb-2">
                                            <i class="fab fa-skype text-primary me-2"></i>
                                            <strong>{{ __('messages.skype') }}:</strong> {{ $user->skype }}
                                        </div>
                                    @endif
                                    
                                    @if($user->work_email)
                                        <div class="mb-2">
                                            <i class="fas fa-envelope text-success me-2"></i>
                                            <strong>{{ __('messages.work_email') }}:</strong> {{ $user->work_email }}
                                        </div>
                                    @endif
                                    
                                    @if($user->office_address)
                                        <div class="mb-2">
                                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                            <strong>{{ __('messages.office_address') }}:</strong> {{ $user->office_address }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>{{ __('messages.personal_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>{{ __('messages.birth_date') }}:</strong>
                                <span class="text-muted">{{ ($user->birth_date ?? $user->birthday) ? ($user->birth_date ?? $user->birthday)->format('d-m-Y') : __('messages.not_specified') }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('messages.nationality') }}:</strong>
                                <span class="text-muted">{{ $user->nationality ?? __('messages.not_specified') }}</span>
                            </div>
                            @if($user->show_address && $user->address)
                                <div class="mb-3">
                                    <strong>{{ __('messages.address') }}:</strong>
                                    <span class="text-muted">{{ $user->address }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($user->city)
                                <div class="mb-3">
                                    <strong>{{ __('messages.city') }}:</strong>
                                    <span class="text-muted">{{ $user->city }}</span>
                                </div>
                            @endif
                            @if($user->country)
                                <div class="mb-3">
                                    <strong>{{ __('messages.country') }}:</strong>
                                    <span class="text-muted">{{ $user->country }}</span>
                                </div>
                            @endif
                            @if($user->languages)
                                <div class="mb-3">
                                    <strong>{{ __('messages.languages') }}:</strong>
                                    <div class="mt-1">
                                        @foreach($user->languages as $language)
                                            <span class="badge bg-secondary me-1">{{ $language }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($user->bio)
                        <div class="mt-3">
                            <strong>{{ __('messages.bio') }}:</strong>
                            <p class="text-muted mt-1">{{ $user->bio }}</p>
                        </div>
                    @endif

                    @if($user->skills)
                        <div class="mt-3">
                            <strong>{{ __('messages.skills') }}:</strong>
                            <div class="mt-1">
                                @foreach($user->skills as $skill)
                                    <span class="badge bg-primary me-1">{{ $skill }}</span>
                    @endforeach
                            </div>
                        </div>
                    @endif
        </div>
    </div>

    <!-- Recent Activity -->
            <div class="card mb-4">
            <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>{{ __('messages.recent_activity') }}
                    </h5>
            </div>
            <div class="card-body">
                    <div class="row">
                        <!-- Recent Tasks -->
                        <div class="col-md-6">
                            <h6 class="text-primary">{{ __('messages.recent_tasks') }}</h6>
                @if($user->assignedTasks->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($user->assignedTasks as $task)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                    {{ Str::limit($task->title, 30) }}
                                                </a>
                                                <small class="text-muted d-block">{{ $task->created_at->diffForHumans() }}</small>
                                            </div>
                    <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                {{ $task->status == 'completed' ? __('messages.completed') : ($task->status == 'in_progress' ? __('messages.in_progress') : __('messages.pending')) }}
                    </span>
                </div>
                @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ __('messages.no_recent_tasks') }}</p>
                @endif
                        </div>

                        <!-- Recent Requests -->
                        <div class="col-md-6">
                            <h6 class="text-primary">{{ __('messages.recent_requests') }}</h6>
                @if($user->employeeRequests->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($user->employeeRequests as $request)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <a href="{{ route('requests.show', $request) }}" class="text-decoration-none">
                                                    {{ Str::limit($request->title, 30) }}
                                                </a>
                                                <small class="text-muted d-block">{{ $request->created_at->diffForHumans() }}</small>
                                            </div>
                    <span class="badge bg-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ $request->status == 'approved' ? __('messages.approved') : ($request->status == 'rejected' ? __('messages.rejected') : __('messages.pending')) }}
                    </span>
                </div>
                @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ __('messages.no_recent_requests') }}</p>
                @endif
            </div>
        </div>
    </div>
            </div>

            <!-- Employee Emails Section -->
            @php
                // Get all active emails from employee_emails table
                try {
                    $activeEmails = $user->activeEmails ? $user->activeEmails : collect();
                } catch (\Exception $e) {
                    $activeEmails = collect();
                }
                
                // Initialize emails collection
                $emailsToShow = collect();
                
                // First try to get emails from employee_emails table
                if ($activeEmails && $activeEmails->count() > 0) {
                    foreach ($activeEmails as $email) {
                        $emailsToShow->push((object)[
                            'email_address' => $email->email_address ?? $email->email ?? null,
                            'email_type' => $email->email_type ?? 'work',
                            'is_primary' => $email->is_primary ?? false,
                            'is_active' => $email->is_active ?? true,
                            'notes' => $email->notes ?? null,
                            'email_type_arabic' => $email->email_type_arabic ?? ($email->email_type == 'work' ? 'عمل' : ($email->email_type == 'personal' ? 'شخصي' : 'أخرى'))
                        ]);
                    }
                }
                
                // Fallback: Always show emails from users table if no emails in employee_emails or as additional emails
                if ($emailsToShow->where('email_address', $user->email)->isEmpty() && $user->email) {
                    $emailsToShow->prepend((object)[
                        'email_address' => $user->email,
                        'email_type' => 'work',
                        'is_primary' => true,
                        'is_active' => true,
                        'notes' => null,
                        'email_type_arabic' => 'عمل'
                    ]);
                }
                
                // Also add work_email if exists and different from main email
                if ($user->work_email && $user->work_email != $user->email && $emailsToShow->where('email_address', $user->work_email)->isEmpty()) {
                    $emailsToShow->push((object)[
                        'email_address' => $user->work_email,
                        'email_type' => 'work',
                        'is_primary' => false,
                        'is_active' => true,
                        'notes' => null,
                        'email_type_arabic' => 'عمل'
                    ]);
                }
            @endphp
            
            @if($emailsToShow->count() > 0)
                <div class="card mb-4">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px 8px 0 0;">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-envelope me-2"></i>{{ __('messages.employee_emails') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.email_address') }}</th>
                                        <th>{{ __('messages.email_type') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.notes') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emailsToShow as $email)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-envelope text-primary me-2"></i>
                                                    <a href="mailto:{{ $email->email_address }}" class="text-decoration-none fw-bold">
                                                        {{ $email->email_address ?? '-' }}
                                                    </a>
                                                    @if(isset($email->is_primary) && $email->is_primary)
                                                        <span class="badge bg-success ms-2">{{ __('messages.primary') }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ (isset($email->email_type) && $email->email_type == 'work') ? 'primary' : ((isset($email->email_type) && $email->email_type == 'personal') ? 'info' : 'secondary') }}">
                                                    @if(isset($email->email_type))
                                                        @if($email->email_type == 'work')
                                                            {{ __('messages.work') }}
                                                        @elseif($email->email_type == 'personal')
                                                            {{ __('messages.personal') }}
                                                        @else
                                                            {{ __('messages.other') }}
                                                        @endif
                                                    @else
                                                        {{ __('messages.not_specified') }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                                            </td>
                                            <td>
                                                @if(isset($email->notes) && $email->notes)
                                                    <small class="text-muted">{{ Str::limit($email->notes, 50) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="mailto:{{ $email->email_address }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.send_email') }}">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </a>
                                                    <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('{{ $email->email_address }}')" title="{{ __('messages.copy_email') }}">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Colleagues -->
            @if($colleagues->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>{{ __('messages.colleagues') }}
                        </h5>
            </div>
            <div class="card-body">
                        <div class="row">
                            @foreach($colleagues as $colleague)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        @if($colleague->profile_picture)
                                        <img src="{{ Storage::url($colleague->profile_picture) }}" alt="{{ $colleague->name }}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                        @else
                                        <img src="{{ asset('images/default-avatar.png') }}" alt="{{ $colleague->name }}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <a href="{{ route('users.contact-card', $colleague) }}" class="text-decoration-none">
                                                <strong>{{ $colleague->name }}</strong>
                                            </a>
                                            <div class="text-muted small">{{ $colleague->job_title ?? __('messages.not_specified') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
        </div>
    </div>
</div>

<!-- Quick Message Modal -->
<div class="modal fade" id="quickMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.send_quick_message') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickMessageForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.message_type') }}</label>
                        <select class="form-select" name="type" required>
                            <option value="email">{{ __('messages.email') }}</option>
                            <option value="whatsapp">{{ __('messages.whatsapp') }}</option>
                            <option value="telegram">{{ __('messages.telegram') }}</option>
                        </select>
                    </div>
                <div class="mb-3">
                        <label class="form-label">{{ __('messages.message') }}</label>
                        <textarea class="form-control" name="message" rows="4" required 
                                  placeholder="{{ __('messages.write_message_here') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Meeting Modal -->
<div class="modal fade" id="scheduleMeetingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.schedule_meeting') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleMeetingForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.meeting_title') }}</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.date') }}</label>
                                <input type="date" class="form-control" name="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.time') }}</label>
                                <input type="time" class="form-control" name="time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.duration_minutes') }}</label>
                        <input type="number" class="form-control" name="duration" min="15" max="480" value="60" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('messages.notes') }}</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('messages.schedule') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Privacy Settings Modal -->
<div class="modal fade" id="privacySettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.privacy_settings') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="privacySettingsForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_phone_work" 
                                   {{ $user->show_phone_work ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('messages.show_work_phone') }}</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_phone_personal" 
                                   {{ $user->show_phone_personal ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('messages.show_personal_phone') }}</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_phone_mobile" 
                                   {{ $user->show_phone_mobile ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('messages.show_mobile_phone') }}</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_email" 
                                   {{ $user->show_email ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('messages.show_email') }}</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_address" 
                                   {{ $user->show_address ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('messages.show_address') }}</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_social_media" 
                                   {{ $user->show_social_media ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('messages.show_social_media') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// دالة بدء محادثة مباشرة
function startDirectChat(userId) {
    try {
        // إظهار رسالة تحميل
        showLoadingMessage('{{ __('messages.creating_chat') }}...');
        
        // إعادة التوجيه مباشرة إلى صفحة الدردشة السريعة
        window.location.href = '{{ route("users.quick-chat", $user) }}';
        
    } catch (error) {
        console.error('Error starting direct chat:', error);
        showErrorMessage('{{ __('messages.connection_error_try_again') }}');
    }
}

// دالة إظهار رسالة التحميل
function showLoadingMessage(message) {
    // إنشاء modal للتحميل
    const loadingModal = document.createElement('div');
    loadingModal.className = 'modal fade show';
    loadingModal.style.display = 'block';
    loadingModal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>${message}</p>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(loadingModal);
    
    // إزالة modal بعد 5 ثوان
    setTimeout(() => {
        if (loadingModal.parentNode) {
            loadingModal.parentNode.removeChild(loadingModal);
        }
    }, 5000);
}

// دالة إظهار رسالة خطأ
function showErrorMessage(message) {
    // إنشاء alert للخطأ
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <strong>{{ __('messages.error') }}!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // إزالة alert بعد 5 ثوان
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Quick Message Form
    document.getElementById('quickMessageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('{{ route("users.quick-message", $user) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('{{ __('messages.message_sent_successfully') }}');
                bootstrap.Modal.getInstance(document.getElementById('quickMessageModal')).hide();
                this.reset();
            } else {
                alert('{{ __('messages.message_send_error') }}');
            }
        });
    });

    // Schedule Meeting Form
    document.getElementById('scheduleMeetingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('{{ route("users.schedule-meeting", $user) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('{{ __('messages.meeting_scheduled_successfully') }}');
                bootstrap.Modal.getInstance(document.getElementById('scheduleMeetingModal')).hide();
                this.reset();
            } else {
                alert('{{ __('messages.meeting_schedule_error') }}');
            }
        });
    });

    // Privacy Settings Form
    document.getElementById('privacySettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('{{ route("users.privacy-settings", $user) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('{{ __('messages.privacy_settings_saved_successfully') }}');
                bootstrap.Modal.getInstance(document.getElementById('privacySettingsModal')).hide();
                location.reload();
            } else {
                alert('{{ __('messages.settings_save_error') }}');
            }
        });
    });
});

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-body">
                <i class="fas fa-check-circle text-success me-2"></i>
                {{ __('messages.email_copied') }}: ${text}
            </div>
        `;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('{{ __('messages.email_copy_error') }}');
    });
}
</script>

<style>
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

.online-indicator {
    transition: all 0.3s ease;
}

.online-indicator:hover {
    transform: scale(1.1);
}

.online-indicator[style*="background-color: #6c757d"] {
    animation: none;
}

.online-indicator[style*="background-color: #6c757d"]:hover {
    background-color: #5a6268 !important;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast-notification {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
}

.employee-emails-table {
    border-radius: 8px;
    overflow: hidden;
}

.employee-emails-table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.employee-emails-table td {
    vertical-align: middle;
}

.email-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Online/Offline Status Styles */
.pulse-animation {
    animation: pulse 2s infinite;
}

.offline-indicator {
    animation: blink 1.5s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

@keyframes blink {
    0%, 50% {
        opacity: 1;
    }
    51%, 100% {
        opacity: 0.3;
    }
}

/* Image Modal Styles */
.profile-image-clickable {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-image-clickable:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
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
                this.style.transform = 'scale(1.05)';
            }, 100);
        });
    });
});
</script>
@endpush