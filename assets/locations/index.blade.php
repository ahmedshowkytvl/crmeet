@extends('layouts.app')

@section('title', __('messages.asset_locations'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                    {{ __('messages.asset_locations') }}
                </h2>
                <a href="{{ route('assets.locations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('messages.add_location') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Locations Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($locations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" data-view-route="assets.locations.show">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.address') }}</th>
                                        <th>{{ __('messages.assets_count') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($locations as $location)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $location->display_name }}</strong>
                                                    @if($location->name_ar)
                                                        <br><small class="text-muted">{{ $location->name_ar }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;" title="{{ $location->display_address }}">
                                                    {{ $location->display_address ?: __('messages.no_address') }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $location->assets->count() }} {{ __('messages.assets') }}</span>
                                            </td>
                                            <td>
                                                @if($location->is_active)
                                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('assets.locations.show', $location) }}" 
                                                       class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('assets.locations.edit', $location) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="{{ __('messages.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('assets.locations.toggle-status', $location) }}" 
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-{{ $location->is_active ? 'warning' : 'success' }}" 
                                                                title="{{ $location->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                                            <i class="fas fa-{{ $location->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('assets.locations.destroy', $location) }}" 
                                                          class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
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
                            {{ $locations->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">{{ __('messages.no_locations_found') }}</h4>
                            <p class="text-muted">{{ __('messages.no_locations_description') }}</p>
                            <a href="{{ route('assets.locations.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('messages.add_first_location') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

