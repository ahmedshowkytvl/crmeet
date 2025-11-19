@extends('layouts.app')

@section('title', __('messages.edit_profile') . ' - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit me-2"></i>{{ __('messages.edit_profile') }}</h2>
    <a href="{{ route('profile.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>{{ __('messages.personal_information') }}</h5>
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

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone_work" class="form-label">{{ __('messages.work_phone') }}</label>
                                <input type="text" class="form-control @error('phone_work') is-invalid @enderror" 
                                       id="phone_work" name="phone_work" value="{{ old('phone_work', $user->phone_work) }}">
                                @error('phone_work')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone_personal" class="form-label">{{ __('messages.mobile_phone') }}</label>
                                <input type="text" class="form-control @error('phone_personal') is-invalid @enderror" 
                                       id="phone_personal" name="phone_personal" value="{{ old('phone_personal', $user->phone_personal) }}">
                                @error('phone_personal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="personal_email" class="form-label">{{ __('messages.personal_email') }}</label>
                                <input type="email" class="form-control @error('personal_email') is-invalid @enderror" 
                                       id="personal_email" name="personal_email" value="{{ old('personal_email', $user->personal_email) }}">
                                @error('personal_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="job_title" class="form-label">{{ __('messages.job_title') }}</label>
                                <input type="text" class="form-control @error('job_title') is-invalid @enderror" 
                                       id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}">
                                @error('job_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company" class="form-label">{{ __('messages.company') }}</label>
                                <input type="text" class="form-control @error('company') is-invalid @enderror" 
                                       id="company" name="company" value="{{ old('company', $user->company) }}">
                                @error('company')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="manager_id" class="form-label">{{ __('messages.manager') }}</label>
                                <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                                    <option value="">{{ __('messages.select_manager') }}</option>
                                    @foreach($users as $manager)
                                        <option value="{{ $manager->id }}" {{ old('manager_id', $user->manager_id) == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('manager_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="office_address" class="form-label">{{ __('messages.office_address') }}</label>
                        <textarea class="form-control @error('office_address') is-invalid @enderror" 
                                  id="office_address" name="office_address" rows="3">{{ old('office_address', $user->office_address) }}</textarea>
                        @error('office_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="linkedin_url" class="form-label">{{ __('messages.linkedin_url') }}</label>
                                <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" 
                                       id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $user->linkedin_url) }}">
                                @error('linkedin_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="twitter_url" class="form-label">{{ __('messages.twitter_url') }}</label>
                                <input type="url" class="form-control @error('twitter_url') is-invalid @enderror" 
                                       id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $user->twitter_url) }}">
                                @error('twitter_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="website_url" class="form-label">{{ __('messages.website_url') }}</label>
                                <input type="url" class="form-control @error('website_url') is-invalid @enderror" 
                                       id="website_url" name="website_url" value="{{ old('website_url', $user->website_url) }}">
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="birthday" class="form-label">{{ __('messages.birthday') }}</label>
                                <input type="date" class="form-control @error('birthday') is-invalid @enderror" 
                                       id="birthday" name="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}">
                                @error('birthday')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">{{ __('messages.bio') }}</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                  id="bio" name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $user->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>{{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('messages.save') }}
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
                <p class="text-muted">{{ __('messages.profile_edit_help') }}</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>{{ __('messages.required_fields_marked') }}</li>
                    <li><i class="fas fa-check text-success me-2"></i>{{ __('messages.email_must_be_unique') }}</li>
                    <li><i class="fas fa-check text-success me-2"></i>{{ __('messages.social_links_optional') }}</li>
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

.form-control:focus {
    border-color: var(--primary-color);
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
