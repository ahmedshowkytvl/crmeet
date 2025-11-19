@extends('layouts.app')

@section('title', __('assets.asset_logs'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>
                    {{ __('assets.asset_logs') }}
                </h2>
                <div class="btn-group">
                    <a href="{{ route('assets.logs.export', request()->query()) }}" class="btn btn-outline-success">
                        <i class="fas fa-download me-1"></i>
                        {{ __('assets.export') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('assets.logs.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="asset_id" class="form-label">{{ __('assets.asset') }}</label>
                            <select class="form-select" id="asset_id" name="asset_id">
                                <option value="">{{ __('assets.all_assets') }}</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->display_name }} ({{ $asset->asset_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="action" class="form-label">{{ __('assets.action') }}</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">{{ __('assets.all_actions') }}</option>
                                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>
                                    {{ __('assets.created') }}
                                </option>
                                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>
                                    {{ __('assets.updated') }}
                                </option>
                                <option value="assigned" {{ request('action') === 'assigned' ? 'selected' : '' }}>
                                    {{ __('assets.assigned') }}
                                </option>
                                <option value="returned" {{ request('action') === 'returned' ? 'selected' : '' }}>
                                    {{ __('assets.returned') }}
                                </option>
                                <option value="moved" {{ request('action') === 'moved' ? 'selected' : '' }}>
                                    {{ __('assets.moved') }}
                                </option>
                                <option value="repaired" {{ request('action') === 'repaired' ? 'selected' : '' }}>
                                    {{ __('assets.repaired') }}
                                </option>
                                <option value="disposed" {{ request('action') === 'disposed' ? 'selected' : '' }}>
                                    {{ __('assets.disposed') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="user_id" class="form-label">{{ __('assets.user') }}</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">{{ __('assets.all_users') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">{{ __('assets.date_from') }}</label>
                            <input type="date" class="form-control custom-date-input" id="date_from" name="date_from" value="{{ request('date_from') }}" data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" style="text-align: center; font-weight: 500;">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">{{ __('assets.date_to') }}</label>
                            <input type="date" class="form-control custom-date-input" id="date_to" name="date_to" value="{{ request('date_to') }}" data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" style="text-align: center; font-weight: 500;">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                    {{ __('assets.filter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" data-view-route="assets.logs.asset">
                                <thead>
                                    <tr>
                                        <th>{{ __('assets.date') }}</th>
                                        <th>{{ __('assets.asset') }}</th>
                                        <th>{{ __('assets.action') }}</th>
                                        <th>{{ __('assets.user') }}</th>
                                        <th>{{ __('assets.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr data-asset-id="{{ $log->asset->id }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $log->date ? $log->date->format('Y-m-d') : '-' }}</strong>
                                                    <br><small class="text-muted">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $log->asset->display_name }}</strong>
                                                    <br><span class="badge bg-secondary">{{ $log->asset->asset_code }}</span>
                                                    <br><span class="badge bg-info">{{ $log->asset->category->display_name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($log->action)
                                                    @case('created')
                                                        <span class="badge bg-success">{{ $log->action_label }}</span>
                                                        @break
                                                    @case('updated')
                                                        <span class="badge bg-primary">{{ $log->action_label }}</span>
                                                        @break
                                                    @case('assigned')
                                                        <span class="badge bg-info">{{ $log->action_label }}</span>
                                                        @break
                                                    @case('returned')
                                                        <span class="badge bg-warning">{{ $log->action_label }}</span>
                                                        @break
                                                    @case('moved')
                                                        <span class="badge bg-secondary">{{ $log->action_label }}</span>
                                                        @break
                                                    @case('repaired')
                                                        <span class="badge bg-dark">{{ $log->action_label }}</span>
                                                        @break
                                                    @case('disposed')
                                                        <span class="badge bg-danger">{{ $log->action_label }}</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-light text-dark">{{ $log->action_label }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $log->user ? $log->user->name : 'User Not Found' }}</strong>
                                                    @if($log->user && $log->user->email)
                                                        <br><small class="text-muted">{{ $log->user->email }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($log->notes)
                                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $log->notes }}">
                                                        {{ $log->notes }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">{{ __('assets.no_notes') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $logs->appends(request()->query())->links('pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">{{ __('assets.no_logs_found') }}</h4>
                            <p class="text-muted">{{ __('assets.no_logs_description') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.custom-date-input::placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input::-webkit-input-placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input::-moz-placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input:-ms-input-placeholder {
    color: #fff !important;
    opacity: 1;
}

.custom-date-input:-moz-placeholder {
    color: #fff !important;
    opacity: 1;
}
</style>
@endpush

