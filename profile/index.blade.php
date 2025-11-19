@extends('layouts.app')

@section('title', __('messages.profile') . ' - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user me-2"></i>{{ __('messages.profile') }}</h2>
    <div class="btn-group">
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>{{ __('messages.edit_profile') }}
        </a>
        <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary">
            <i class="fas fa-key me-2"></i>{{ __('messages.change_password') }}
        </a>
    </div>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>{{ __('messages.personal_information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="profile-avatar">
                            @if($user->profile_photo)
                                <img src="{{ asset($user->profile_photo) }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                            @else
                                <img src="{{ asset('images/default-avatar.png') }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4 class="text-primary">{{ $user->name }}</h4>
                        <p class="text-muted mb-2">{{ $user->job_title ?? __('messages.no_data') }}</p>
                        <p class="text-muted mb-2">{{ $user->company ?? __('messages.no_data') }}</p>
                        <p class="text-muted mb-3">{{ $user->department->name ?? __('messages.no_data') }}</p>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>{{ __('messages.email') }}:</strong><br>
                                <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                            </div>
                            <div class="col-sm-6">
                                <strong>{{ __('messages.work_phone') }}:</strong><br>
                                @if($user->phone_work)
                                    <a href="tel:{{ $user->phone_work }}" class="text-decoration-none">{{ $user->phone_work }}</a>
                                @else
                                    <span class="text-muted">{{ __('messages.no_data') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>{{ __('messages.contact_information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>{{ __('messages.personal_email') }}:</strong><br>
                            @if($user->personal_email)
                                <a href="mailto:{{ $user->personal_email }}" class="text-decoration-none">{{ $user->personal_email }}</a>
                            @else
                                <span class="text-muted">{{ __('messages.no_data') }}</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('messages.mobile_phone') }}:</strong><br>
                            @if($user->phone_personal)
                                <a href="tel:{{ $user->phone_personal }}" class="text-decoration-none">{{ $user->phone_personal }}</a>
                            @else
                                <span class="text-muted">{{ __('messages.no_data') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>{{ __('messages.office_address') }}:</strong><br>
                            @if($user->office_address)
                                <span>{{ $user->office_address }}</span>
                            @else
                                <span class="text-muted">{{ __('messages.no_data') }}</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('messages.birthday') }}:</strong><br>
                            @if($user->birthday)
                                <span>{{ $user->birthday->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted">{{ __('messages.no_data') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bio -->
        @if($user->bio)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('messages.bio') }}</h5>
            </div>
            <div class="card-body">
                <p>{{ $user->bio }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>{{ __('messages.quick_actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>{{ __('messages.edit_profile') }}
                    </a>
                    <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-key me-2"></i>{{ __('messages.change_password') }}
                    </a>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-cog me-2"></i>{{ __('messages.settings') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Social Links -->
        @if($user->linkedin_url || $user->twitter_url || $user->website_url)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>{{ __('messages.social_links') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    @if($user->linkedin_url)
                        <a href="{{ $user->linkedin_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fab fa-linkedin me-2"></i>LinkedIn
                        </a>
                    @endif
                    @if($user->twitter_url)
                        <a href="{{ $user->twitter_url }}" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fab fa-twitter me-2"></i>Twitter
                        </a>
                    @endif
                    @if($user->website_url)
                        <a href="{{ $user->website_url }}" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-globe me-2"></i>{{ __('messages.website') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>{{ __('messages.statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $user->assignedTasks->count() }}</h4>
                        <small class="text-muted">{{ __('messages.tasks') }}</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $user->employeeRequests->count() }}</h4>
                        <small class="text-muted">{{ __('messages.requests') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-avatar img {
    border: 4px solid #e9ecef;
    transition: all 0.3s ease;
}

.profile-avatar img:hover {
    border-color: var(--primary-color);
    transform: scale(1.05);
}

.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3ea8c4 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    border: none;
}
</style>
@endsection
