@extends('layouts.app')

@section('title', __('Password Categories'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tags me-2"></i>{{ __('Password Categories') }}
    </h2>
    <a href="{{ route('password-categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('Add New Category') }}
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $categories->total() }}</h4>
                        <p class="mb-0">{{ __('Total Categories') }}</p>
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
                        <p class="mb-0">{{ __('Active Categories') }}</p>
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
                        <p class="mb-0">{{ __('Inactive Categories') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x"></i>
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
                        <h4>{{ $categories->sum(function($cat) { return $cat->passwordAccounts->count(); }) }}</h4>
                        <p class="mb-0">{{ __('Total Accounts') }}</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('password-categories.index') }}" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">{{ __('Search Categories') }}</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="{{ __('Search categories...') }}">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">{{ __('Filter by Status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                        {{ __('Active') }}
                    </option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                        {{ __('Inactive') }}
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>{{ __('Search') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Categories Grid -->
<div class="row">
    @if($categories->count() > 0)
        @foreach($categories as $category)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 {{ $category->is_active ? '' : 'opacity-75' }}" data-entity-id="{{ $category->id }}" data-view-route="password-categories.show">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            @if($category->icon)
                                <img src="{{ $category->icon }}" alt="{{ $category->display_name }}" 
                                     class="me-3" style="width: 40px; height: 40px;">
                            @else
                                <div class="me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px; background-color: {{ $category->color }}; border-radius: 8px;">
                                    <i class="fas fa-tag text-white"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">{{ $category->display_name }}</h5>
                                <span class="badge" style="background-color: {{ $category->color }};">
                                    {{ $category->passwordAccounts->count() }} {{ __('Accounts') }}
                                </span>
                            </div>
                        </div>
                        
                        @if($category->display_description)
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($category->display_description, 100) }}
                            </p>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($category->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </div>
                            <div class="btn-group" role="group">
                                <a href="{{ route('password-categories.show', $category) }}" 
                                   class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('password-categories.edit', $category) }}" 
                                   class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('password-categories.destroy', $category) }}" 
                                      class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            title="{{ __('Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No categories found') }}</h5>
                <a href="{{ route('password-categories.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>{{ __('Create First Category') }}
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Pagination -->
@if($categories->hasPages())
    <div class="d-flex justify-content-center">
        {{ $categories->appends(request()->query())->links() }}
    </div>
@endif
@endsection









