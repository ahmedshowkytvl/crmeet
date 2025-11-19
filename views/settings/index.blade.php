@extends('layouts.app')

@section('title', __('messages.settings'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cog me-2"></i>{{ __('messages.settings') }}</h2>
    <a href="{{ route('profile.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-globe me-2"></i>{{ __('messages.general_settings') }}</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="language" class="form-label">{{ __('messages.language') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="en" {{ old('language', $user->language ?? 'en') == 'en' ? 'selected' : '' }}>
                                        {{ __('messages.english') }}
                                    </option>
                                    <option value="ar" {{ old('language', $user->language ?? 'en') == 'ar' ? 'selected' : '' }}>
                                        {{ __('messages.arabic') }}
                                    </option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">{{ __('messages.timezone') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
                                    <option value="UTC" {{ old('timezone', $user->timezone ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="Asia/Riyadh" {{ old('timezone', $user->timezone ?? 'UTC') == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                                    <option value="Asia/Dubai" {{ old('timezone', $user->timezone ?? 'UTC') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                    <option value="Europe/London" {{ old('timezone', $user->timezone ?? 'UTC') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    <option value="America/New_York" {{ old('timezone', $user->timezone ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_format" class="form-label">{{ __('messages.date_format') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('date_format') is-invalid @enderror" id="date_format" name="date_format" required>
                                    <option value="Y-m-d" {{ old('date_format', $user->date_format ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>2024-01-15</option>
                                    <option value="d/m/Y" {{ old('date_format', $user->date_format ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' }}>15/01/2024</option>
                                    <option value="m/d/Y" {{ old('date_format', $user->date_format ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' }}>01/15/2024</option>
                                    <option value="d-m-Y" {{ old('date_format', $user->date_format ?? 'Y-m-d') == 'd-m-Y' ? 'selected' : '' }}>15-01-2024</option>
                                </select>
                                @error('date_format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="time_format" class="form-label">{{ __('messages.time_format') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('time_format') is-invalid @enderror" id="time_format" name="time_format" required>
                                    <option value="H:i" {{ old('time_format', $user->time_format ?? 'H:i') == 'H:i' ? 'selected' : '' }}>24 Hour (14:30)</option>
                                    <option value="h:i A" {{ old('time_format', $user->time_format ?? 'H:i') == 'h:i A' ? 'selected' : '' }}>12 Hour (2:30 PM)</option>
                                </select>
                                @error('time_format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('messages.save_settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>{{ __('messages.notification_settings') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.notifications.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">{{ __('messages.notification_channels') }}</h6>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="email" name="notifications[email]" 
                                       value="1" {{ old('notifications.email', $user->notification_preferences['email'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="email">
                                    <i class="fas fa-envelope me-2"></i>{{ __('messages.email_notifications') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="sms" name="notifications[sms]" 
                                       value="1" {{ old('notifications.sms', $user->notification_preferences['sms'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms">
                                    <i class="fas fa-sms me-2"></i>{{ __('messages.sms_notifications') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="push" name="notifications[push]" 
                                       value="1" {{ old('notifications.push', $user->notification_preferences['push'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="push">
                                    <i class="fas fa-mobile-alt me-2"></i>{{ __('messages.push_notifications') }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">{{ __('messages.notification_types') }}</h6>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="task_assignments" name="notifications[task_assignments]" 
                                       value="1" {{ old('notifications.task_assignments', $user->notification_preferences['task_assignments'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="task_assignments">
                                    <i class="fas fa-tasks me-2"></i>{{ __('messages.task_assignments') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="task_updates" name="notifications[task_updates]" 
                                       value="1" {{ old('notifications.task_updates', $user->notification_preferences['task_updates'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="task_updates">
                                    <i class="fas fa-edit me-2"></i>{{ __('messages.task_updates') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="request_updates" name="notifications[request_updates]" 
                                       value="1" {{ old('notifications.request_updates', $user->notification_preferences['request_updates'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="request_updates">
                                    <i class="fas fa-file-alt me-2"></i>{{ __('messages.request_updates') }}
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="system_updates" name="notifications[system_updates]" 
                                       value="1" {{ old('notifications.system_updates', $user->notification_preferences['system_updates'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="system_updates">
                                    <i class="fas fa-cog me-2"></i>{{ __('messages.system_updates') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('messages.save_notifications') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('messages.help') }}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ __('messages.settings_help') }}</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>{{ __('messages.language_affects_interface') }}</li>
                    <li><i class="fas fa-check text-success me-2"></i>{{ __('messages.timezone_affects_dates') }}</li>
                    <li><i class="fas fa-check text-success me-2"></i>{{ __('messages.notifications_customizable') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
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

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25);
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3ea8c4 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #3ea8c4 0%, var(--primary-color) 100%);
}
</style>
@endsection
