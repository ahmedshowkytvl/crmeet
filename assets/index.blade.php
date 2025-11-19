@extends('layouts.app')

@section('title', __('messages.assets'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-cubes me-2 text-primary"></i>
                    {{ __('assets.assets') }}
                </h2>
                <a href="{{ route('assets.assets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('assets.add_asset') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('assets.assets.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">{{ __('assets.search') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('assets.search_assets') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="category_id" class="form-label">{{ __('assets.category') }}</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">{{ __('assets.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? ($category->name_ar ?: $category->name) : $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ __('assets.status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('assets.all_statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                    {{ __('assets.active') }}
                                </option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>
                                    {{ __('assets.maintenance') }}
                                </option>
                                <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>
                                    {{ __('assets.retired') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="location_id" class="form-label">{{ __('assets.location') }}</label>
                            <select class="form-select" id="location_id" name="location_id">
                                <option value="">{{ __('assets.all_locations') }}</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? ($location->name_ar ?: $location->name) : $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="assigned_to" class="form-label">{{ __('assets.assigned_to') }}</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">{{ __('assets.all_users') }}</option>
                                <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>
                                    {{ __('assets.unassigned') }}
                                </option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? ($user->name_ar ?: $user->name) : $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assets Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($assets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" data-view-route="assets.assets.show">
                                <thead>
                                    <tr>
                                        <th>{{ __('assets.asset_code') }}</th>
                                        <th>{{ __('assets.name') }}</th>
                                        <th>{{ __('assets.category') }}</th>
                                        <th>{{ __('assets.status') }}</th>
                                        <th>{{ __('messages.warehouse') }}</th>
                                        <th>{{ __('messages.inventory_status') }}</th>
                                        <th>{{ __('messages.quantity') }}</th>
                                        <th>{{ __('messages.price') }}</th>
                                        <th>{{ __('assets.location') }}</th>
                                        <th>{{ __('assets.assigned_to') }}</th>
                                        <th>{{ __('assets.actions') ?: 'الإجراءات' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assets as $asset)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $asset->asset_code }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ app()->getLocale() == 'ar' ? ($asset->name_ar ?: $asset->name) : $asset->name }}</strong>
                                                    @if($asset->serial_number)
                                                        <br><small class="text-muted">{{ __('assets.serial_number') }}: {{ $asset->serial_number }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? ($asset->category->name_ar ?: $asset->category->name) : $asset->category->name }}</span>
                                            </td>
                                            <td>
                                                @if($asset->status == 'active')
                                                    <span class="badge bg-success">{{ $asset->status_label }}</span>
                                                @elseif($asset->status == 'maintenance')
                                                    <span class="badge bg-warning">{{ $asset->status_label }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ $asset->status_label }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($asset->warehouse)
                                                    <span class="badge bg-info">{{ app()->getLocale() == 'ar' ? ($asset->warehouse->name_ar ?: $asset->warehouse->name) : $asset->warehouse->name }}</span>
                                                @else
                                                    <span class="text-muted">{{ __('messages.not_specified') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'in_stock' => 'success',
                                                        'out_of_stock' => 'danger',
                                                        'low_stock' => 'warning',
                                                        'reserved' => 'info',
                                                        'damaged' => 'secondary',
                                                        'expired' => 'dark'
                                                    ];
                                                    $color = $statusColors[$asset->inventory_status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">{{ $asset->inventory_status_label }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $asset->quantity }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $asset->formatted_price }}</strong>
                                            </td>
                                            <td>
                                                @if($asset->location)
                                                    {{ app()->getLocale() == 'ar' ? ($asset->location->name_ar ?: $asset->location->name) : $asset->location->name }}
                                                @else
                                                    <span class="text-muted">{{ __('messages.not_specified') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($asset->assignedTo)
                                                    <span class="badge bg-primary">{{ app()->getLocale() == 'ar' ? ($asset->assignedTo->name_ar ?: $asset->assignedTo->name) : $asset->assignedTo->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('assets.unassigned') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('assets.assets.show', $asset) }}" 
                                                       class="btn btn-sm btn-outline-info" title="{{ __('assets.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('assets.assets.edit', $asset) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="{{ __('assets.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('assets.assets.print-barcode', $asset) }}" 
                                                       class="btn btn-sm btn-outline-secondary" title="{{ __('assets.print_barcode') }}">
                                                        <i class="fas fa-barcode"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('assets.assets.destroy', $asset) }}" 
                                                          class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('assets.delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $assets->appends(request()->query())->links('pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cubes fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">{{ __('assets.no_assets_found') }}</h4>
                            <p class="text-muted">{{ __('assets.no_assets_description') }}</p>
                            <a href="{{ route('assets.assets.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('assets.add_first_asset') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

