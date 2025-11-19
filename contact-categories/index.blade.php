@extends('layouts.app')

@section('title', __('messages.contact_categories') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="title-in-black"><i class="fas fa-tags me-2"></i>{{ __('messages.contact_categories') }}</h2>
    <a href="{{ route('contact-categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('messages.add_category') }}
    </a>
</div>

<!-- إحصائيات سريعة -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $categories->count() }}</h4>
                        <p class="mb-0">{{ __('messages.total_categories') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tags fa-2x"></i>
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
                        <h4>{{ $categories->where('is_active', true)->count() }}</h4>
                        <p class="mb-0">{{ __('messages.active_categories') }}</p>
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
                        <h4>{{ $categories->where('is_active', false)->count() }}</h4>
                        <p class="mb-0">{{ __('messages.inactive_categories') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-pause-circle fa-2x"></i>
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
                        <h4>{{ $categories->sum(function($cat) { return $cat->getContactsCount(); }) }}</h4>
                        <p class="mb-0">{{ __('messages.total_contacts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-address-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- قائمة التصنيفات -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('messages.categories_list') }}</h5>
    </div>
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="row">
                @foreach($categories as $category)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 category-card" data-entity-id="{{ $category->id }}" data-view-route="contact-categories.show" style="border-left: 4px solid {{ $category->color }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $category->icon }} me-2" style="color: {{ $category->color }}"></i>
                                        <h5 class="mb-0">{{ $category->display_name }}</h5>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('contact-categories.show', $category) }}">
                                                <i class="fas fa-eye me-2"></i>{{ __('messages.view') }}
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('contact-categories.edit', $category) }}">
                                                <i class="fas fa-edit me-2"></i>{{ __('messages.edit') }}
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('contact-categories.toggle-status', $category) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }} me-2"></i>
                                                        {{ $category->is_active ? __('messages.deactivate') : __('messages.activate') }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('contact-categories.destroy', $category) }}" method="POST" 
                                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i>{{ __('messages.delete') }}
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                @if($category->display_description)
                                    <p class="text-muted mb-3">{{ Str::limit($category->display_description, 100) }}</p>
                                @endif
                                
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h6 class="mb-1">{{ $category->getContactsCount() }}</h6>
                                            <small class="text-muted">{{ __('messages.total_contacts') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h6 class="mb-1">{{ $category->getActiveContactsCount() }}</h6>
                                        <small class="text-muted">{{ __('messages.active_contacts') }}</small>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                        {{ $category->is_active ? __('messages.active') : __('messages.inactive') }}
                                    </span>
                                    <span class="badge bg-light text-dark ms-1">
                                        {{ __('messages.order') }}: {{ $category->sort_order }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('messages.no_categories_found') }}</h5>
                <p class="text-muted">{{ __('messages.create_first_category') }}</p>
                <a href="{{ route('contact-categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ __('messages.add_category') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.category-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.category-card .card-body {
    padding: 1.25rem;
}
</style>
@endpush
