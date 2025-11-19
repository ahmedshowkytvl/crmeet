@extends('layouts.app')

@section('title', __('messages.asset_categories'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-tags me-2 text-primary"></i>
                    {{ __('assets.asset_categories') }}
                </h2>
                <a href="{{ route('assets.asset-categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('assets.add_category') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($categories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" data-view-route="assets.asset-categories.show">
                                <thead>
                                    <tr>
                                        <th>{{ __('assets.name') }}</th>
                                        <th>{{ __('messages.description') }}</th>
                                        <th>{{ __('messages.price') }}</th>
                                        <th>{{ __('messages.properties') }}</th>
                                        <th>{{ __('messages.assets_count') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $category->display_name }}</strong>
                                                    @if($category->name_ar)
                                                        <br><small class="text-muted">{{ $category->name_ar }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $category->display_description }}">
                                                    {{ $category->display_description ?: __('messages.no_description') }}
                                                </div>
                                            </td>
                                            <td>
                                                {{ $category->price ? number_format($category->price, 2) . ' USD' : '-' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $category->properties->count() }} {{ __('messages.properties') }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $category->assets->count() }} {{ __('messages.assets') }}</span>
                                            </td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('assets.asset-categories.show', $category) }}" 
                                                       class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('assets.asset-categories.edit', $category) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="{{ __('messages.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('assets.asset-categories.toggle-status', $category) }}" 
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-{{ $category->is_active ? 'warning' : 'success' }}" 
                                                                title="{{ $category->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                                            <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('assets.asset-categories.destroy', $category) }}" 
                                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
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
                            {{ $categories->appends(request()->query())->links('pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">{{ __('messages.no_categories_found') }}</h4>
                            <p class="text-muted">{{ __('messages.no_categories_description') }}</p>
                            <a href="{{ route('assets.asset-categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('messages.add_first_category') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

