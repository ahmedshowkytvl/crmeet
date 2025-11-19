@extends('layouts.app')

@section('title', $contactCategory->display_name . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <i class="{{ $contactCategory->icon }} me-3" style="color: {{ $contactCategory->color }}; font-size: 2rem;"></i>
        <div>
            <h2 class="mb-0">{{ $contactCategory->display_name }}</h2>
            <p class="text-muted mb-0">{{ $contactCategory->display_description }}</p>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('contact-categories.edit', $contactCategory) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}
        </a>
        <a href="{{ route('contact-categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
        </a>
    </div>
</div>

<!-- إحصائيات التصنيف -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $contactCategory->getContactsCount() }}</h4>
                        <p class="mb-0">{{ __('messages.total_contacts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-address-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $contactCategory->getActiveContactsCount() }}</h4>
                        <p class="mb-0">{{ __('messages.active_contacts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $contacts->where('is_favorite', true)->count() }}</h4>
                        <p class="mb-0">{{ __('messages.favorite_contacts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $contacts->where('last_contact_date', '>=', now()->subDays(30))->count() }}</h4>
                        <p class="mb-0">{{ __('messages.recent_contacts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- معلومات التصنيف -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.category_info') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('messages.arabic_name') }}</h6>
                        <p class="text-muted">{{ $contactCategory->name }}</p>

                        <h6>{{ __('messages.english_name') }}</h6>
                        <p class="text-muted">{{ $contactCategory->name_en }}</p>

                        <h6>{{ __('messages.color') }}</h6>
                        <div class="d-flex align-items-center">
                            <div class="color-preview me-2" style="width: 30px; height: 30px; background-color: {{ $contactCategory->color }}; border-radius: 4px;"></div>
                            <span class="text-muted">{{ $contactCategory->color }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('messages.icon') }}</h6>
                        <p class="text-muted">
                            <i class="{{ $contactCategory->icon }} me-2" style="color: {{ $contactCategory->color }};"></i>
                            {{ $contactCategory->icon }}
                        </p>

                        <h6>{{ __('messages.sort_order') }}</h6>
                        <p class="text-muted">{{ $contactCategory->sort_order }}</p>

                        <h6>{{ __('messages.status') }}</h6>
                        <span class="badge bg-{{ $contactCategory->is_active ? 'success' : 'secondary' }}">
                            {{ $contactCategory->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </div>
                </div>

                @if($contactCategory->description)
                    <hr>
                    <h6>{{ __('messages.arabic_description') }}</h6>
                    <p class="text-muted">{{ $contactCategory->description }}</p>
                @endif

                @if($contactCategory->description_en)
                    <h6>{{ __('messages.english_description') }}</h6>
                    <p class="text-muted">{{ $contactCategory->description_en }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('messages.quick_actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('contacts.create', ['category' => $contactCategory->name]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('messages.add_contact_to_category') }}
                    </a>

                    <form action="{{ route('contact-categories.toggle-status', $contactCategory) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-{{ $contactCategory->is_active ? 'warning' : 'success' }} w-100">
                            <i class="fas fa-{{ $contactCategory->is_active ? 'pause' : 'play' }} me-2"></i>
                            {{ $contactCategory->is_active ? __('messages.deactivate') : __('messages.activate') }}
                        </button>
                    </form>

                    <a href="{{ route('contact-categories.edit', $contactCategory) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>{{ __('messages.edit_category') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- قائمة جهات الاتصال في هذا التصنيف -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('messages.contacts_in_category') }}</h5>
        <span class="badge bg-primary">{{ $contacts->total() }} {{ __('messages.contacts') }}</span>
    </div>
    <div class="card-body">
        @if($contacts->count() > 0)
            <div class="row">
                @foreach($contacts as $contact)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">{{ $contact->name }}</h6>
                                    @if($contact->is_favorite)
                                        <i class="fas fa-star text-warning"></i>
                                    @endif
                                </div>

                                @if($contact->company)
                                    <p class="text-muted small mb-2">{{ $contact->company }}</p>
                                @endif

                                <div class="mb-2">
                                    @if($contact->phone_primary)
                                        <small class="text-muted d-block">
                                            <i class="fas fa-phone me-1"></i>{{ $contact->phone_primary }}
                                        </small>
                                    @endif
                                    @if($contact->email_primary)
                                        <small class="text-muted d-block">
                                            <i class="fas fa-envelope me-1"></i>{{ $contact->email_primary }}
                                        </small>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $contact->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ __('messages.' . $contact->status) }}
                                    </span>
                                    <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-primary">
                                        {{ __('messages.view') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $contacts->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('messages.no_contacts_in_category') }}</h5>
                <p class="text-muted">{{ __('messages.add_first_contact_to_category') }}</p>
                <a href="{{ route('contacts.create', ['category' => $contactCategory->name]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ __('messages.add_contact') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.color-preview {
    border: 1px solid #dee2e6;
}
</style>
@endpush