@extends('layouts.app')

@section('title', $trans('contact_details') . ' - ' . $contact->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-address-book me-2"></i>{{ $trans('contact_details') }}</h2>
    <div>
        <a href="{{ route('users.contact-card', $contact) }}" class="btn btn-info me-2">
            <i class="fas fa-id-card me-2"></i>{{ $trans('contact_card') }}
        </a>
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>{{ $trans('back') }}
        </a>
    </div>
</div>

<div class="row">
    <!-- Contact Information -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <!-- Profile Image -->
                <div class="mb-4">
                    @if($contact->profile_picture)
                        <img src="{{ asset($contact->profile_picture) }}" 
                             class="rounded-circle" 
                             style="width: 120px; height: 120px; object-fit: cover;" 
                             alt="صورة {{ $contact->name }}"
                             onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                    @else
                        <img src="{{ asset('images/default-avatar.png') }}" 
                             class="rounded-circle" 
                             style="width: 120px; height: 120px; object-fit: cover;" 
                             alt="صورة {{ $contact->name }}">
                    @endif
                </div>

                <!-- Basic Info -->
                <h4 class="mb-2">{{ $contact->name }}</h4>
                <p class="text-muted mb-3">{{ $contact->job_title ?? $trans('not_specified') }}</p>
                
                @if($contact->department)
                    <span class="badge bg-primary fs-6 mb-3">{{ $contact->department->name }}</span>
                @endif

                <!-- Contact Actions -->
                <div class="d-grid gap-2 mt-4">
                    @if($contact->email)
                        <a href="mailto:{{ $contact->email }}" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>{{ $trans('send_email') }}
                        </a>
                    @endif
                    
                    @if($contact->phone_work)
                        <a href="tel:{{ $contact->phone_work }}" class="btn btn-outline-success">
                            <i class="fas fa-phone me-2"></i>{{ $trans('call') }}
                        </a>
                    @endif
                    
                    <a href="{{ route('users.contact-card', $contact) }}" class="btn btn-primary">
                        <i class="fas fa-id-card me-2"></i>{{ $trans('full_contact_card') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Details -->
    <div class="col-lg-8">
        <!-- Work Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-briefcase me-2"></i>{{ $trans('work_information') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>{{ $trans('name') }}:</strong>
                            <span class="text-muted">{{ $contact->name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>{{ $trans('email') }}:</strong>
                            <span class="text-muted">{{ $contact->email }}</span>
                        </div>
                        @if(isset($contact->job_title))
                            <div class="mb-3">
                                <strong>{{ $trans('job_title') }}:</strong>
                                <span class="text-muted">{{ $contact->job_title ?? $trans('not_specified') }}</span>
                            </div>
                        @endif
                        @if(isset($contact->employee_id))
                            <div class="mb-3">
                                <strong>{{ $trans('employee_id') }}:</strong>
                                <span class="text-muted">{{ $contact->employee_id ?? $trans('not_specified') }}</span>
                                @if(auth()->user()->role && auth()->user()->role->slug === 'admin')
                                    <small class="text-muted ms-2">(DB ID: {{ $contact->id }})</small>
                                @endif
                            </div>
                        @elseif(auth()->user()->role && auth()->user()->role->slug === 'admin')
                            <div class="mb-3">
                                <strong>{{ $trans('employee_id') }}:</strong>
                                <span class="text-muted">{{ $trans('not_specified') }}</span>
                                <small class="text-muted ms-2">(DB ID: {{ $contact->id }})</small>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>{{ $trans('department') }}:</strong>
                            <span class="text-muted">{{ $contact->department->name ?? $trans('not_specified') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>{{ $trans('role') }}:</strong>
                            @if($contact->role)
                                <span class="badge bg-{{ $contact->role->slug == 'ceo' ? 'danger' : ($contact->role->slug == 'head_manager' ? 'warning' : 'success') }}">
                                    {{ app()->getLocale() == 'ar' ? $contact->role->name_ar : $contact->role->name }}
                                </span>
                                @if(auth()->user()->role && auth()->user()->role->slug === 'admin')
                                    <small class="text-muted ms-2">(ID: {{ $contact->role->id }})</small>
                                @endif
                            @else
                                <span class="text-muted">{{ $trans('not_specified') }}</span>
                            @endif
                        </div>
                        @if(isset($contact->hire_date))
                            <div class="mb-3">
                                <strong>{{ $trans('hire_date') }}:</strong>
                                <span class="text-muted">{{ $contact->hire_date ? $contact->hire_date->format('d/m/Y') : $trans('not_specified') }}</span>
                            </div>
                        @endif
                        @if(isset($contact->work_location))
                            <div class="mb-3">
                                <strong>{{ $trans('work_location') }}:</strong>
                                <span class="text-muted">{{ $contact->work_location ?? $trans('not_specified') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-phone me-2"></i>{{ $trans('contact_information') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @if($contact->phone_work)
                            <div class="mb-3">
                                <strong>{{ $trans('work_phone') }}:</strong>
                                <a href="tel:{{ $contact->phone_work }}" class="text-decoration-none">
                                    {{ $contact->phone_work }}
                                </a>
                            </div>
                        @endif
                        
                        @if(isset($contact->phone_mobile) && $contact->phone_mobile)
                            <div class="mb-3">
                                <strong>{{ $trans('phone_mobile') }}:</strong>
                                <a href="tel:{{ $contact->phone_mobile }}" class="text-decoration-none">
                                    {{ $contact->phone_mobile }}
                                </a>
                            </div>
                        @endif
                        
                        @if(isset($contact->extension) && $contact->extension)
                            <div class="mb-3">
                                <strong>{{ $trans('extension') }}:</strong>
                                <span class="text-muted">{{ $contact->extension }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if(isset($contact->office_room) && $contact->office_room)
                            <div class="mb-3">
                                <strong>{{ $trans('office_room') }}:</strong>
                                <span class="text-muted">{{ $contact->office_room }}</span>
                            </div>
                        @endif
                        
                        @if($contact->manager)
                            <div class="mb-3">
                                <strong>{{ $trans('direct_manager') }}:</strong>
                                <a href="{{ route('contacts.show', $contact->manager) }}" class="text-decoration-none">
                                    {{ $contact->manager->name }}
                                </a>
                            </div>
                        @endif
                        
                        @if($contact->subordinates->count() > 0)
                            <div class="mb-3">
                                <strong>{{ $trans('subordinates') }}:</strong>
                                <span class="badge bg-info">{{ $contact->subordinates->count() }} {{ $trans('employees') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media -->
        @if($contact->linkedin_url || $contact->twitter_url || $contact->whatsapp || $contact->telegram)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-share-alt me-2"></i>{{ $trans('social_links') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="social-links">
                        @if($contact->linkedin_url)
                            <a href="{{ $contact->linkedin_url }}" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fab fa-linkedin"></i> LinkedIn
                            </a>
                        @endif
                        @if($contact->twitter_url)
                            <a href="{{ $contact->twitter_url }}" target="_blank" class="btn btn-outline-info btn-sm me-2">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                        @endif
                        @if($contact->whatsapp)
                            <a href="https://wa.me/{{ $contact->whatsapp }}" target="_blank" class="btn btn-outline-success btn-sm me-2">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                        @if($contact->telegram)
                            <a href="https://t.me/{{ $contact->telegram }}" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fab fa-telegram"></i> Telegram
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
