@extends('layouts.app')

@section('title', __('zoho.not_enabled.title'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-info-circle text-info" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h3 class="mb-3">{{ __('zoho.not_enabled.title') }}</h3>
                    
                    <p class="text-muted mb-4">
                        {{ __('zoho.not_enabled.message') }}
                    </p>
                    
                    <div class="alert alert-light border">
                        <p class="mb-0">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            {{ __('zoho.not_enabled.hint') }}
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('zoho.not_enabled.back_home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

