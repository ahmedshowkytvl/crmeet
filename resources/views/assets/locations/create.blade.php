@extends('layouts.app')

@section('title', __('messages.add_location'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-plus me-2 text-primary"></i>
                    {{ __('messages.add_location') }}
                </h2>
                <a href="{{ route('assets.locations.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('messages.back_to_locations') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('assets.locations.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('messages.name_ar') }}</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar') }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="address" class="form-label">{{ __('messages.address') }}</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address_ar" class="form-label">{{ __('messages.address_ar') }}</label>
                                    <textarea class="form-control @error('address_ar') is-invalid @enderror" 
                                              id="address_ar" name="address_ar" rows="3">{{ old('address_ar') }}</textarea>
                                    @error('address_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('messages.active') }}
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('assets.locations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('messages.create_location') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.help') }}
                    </h5>
                </div>
                <div class="card-body">
                    <h6>{{ __('messages.location_help_title') }}</h6>
                    <p class="small text-muted">{{ __('messages.location_help_description') }}</p>
                    
                    <h6 class="mt-3">{{ __('messages.examples') }}:</h6>
                    <ul class="small text-muted">
                        <li>{{ __('messages.office_building') }}</li>
                        <li>{{ __('messages.warehouse') }}</li>
                        <li>{{ __('messages.lab') }}</li>
                        <li>{{ __('messages.server_room') }}</li>
                        <li>{{ __('messages.workshop') }}</li>
                    </ul>
                    
                    <div class="mt-3">
                        <h6>{{ __('messages.tips') }}:</h6>
                        <ul class="small text-muted">
                            <li>{{ __('messages.location_tip_1') }}</li>
                            <li>{{ __('messages.location_tip_2') }}</li>
                            <li>{{ __('messages.location_tip_3') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

