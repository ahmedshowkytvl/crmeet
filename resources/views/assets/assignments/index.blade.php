@extends('layouts.app')

@section('title', __('messages.asset_assignments'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-handshake me-2 text-primary"></i>
                    {{ __('messages.asset_assignments') }}
                </h2>
                <a href="{{ route('assets.assignments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('messages.assign_asset') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('assets.assignments.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('messages.status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                    {{ __('messages.active') }}
                                </option>
                                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>
                                    {{ __('messages.returned') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">{{ __('messages.user') }}</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">{{ __('messages.all_users') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="asset_id" class="form-label">{{ __('messages.asset') }}</label>
                            <select class="form-select" id="asset_id" name="asset_id">
                                <option value="">{{ __('messages.all_assets') }}</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->display_name }} ({{ $asset->asset_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                    {{ __('messages.filter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" data-view-route="assets.assignments.show">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.asset') }}</th>
                                        <th>{{ __('messages.assigned_to') }}</th>
                                        <th>{{ __('messages.assigned_date') }}</th>
                                        <th>{{ __('messages.returned_date') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.assigned_by') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $assignment->asset->display_name }}</strong>
                                                    <br><span class="badge bg-secondary">{{ $assignment->asset->asset_code }}</span>
                                                    <br><span class="badge bg-info">{{ $assignment->asset->category->display_name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $assignment->user ? $assignment->user->name : 'User Not Found' }}</strong>
                                                    @if($assignment->user && $assignment->user->email)
                                                        <br><small class="text-muted">{{ $assignment->user->email }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $assignment->assigned_date->format('Y-m-d') }}</td>
                                            <td>
                                                @if($assignment->returned_date)
                                                    {{ $assignment->returned_date->format('Y-m-d') }}
                                                @else
                                                    <span class="text-muted">{{ __('messages.not_returned') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($assignment->isActive())
                                                    <span class="badge bg-success">{{ __('messages.active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('messages.returned') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $assignment->assignedBy->name }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('assets.assignments.show', $assignment) }}" 
                                                       class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($assignment->isActive())
                                                        <a href="{{ route('assets.assignments.return', $assignment) }}" 
                                                           class="btn btn-sm btn-outline-warning" title="{{ __('messages.return') }}">
                                                            <i class="fas fa-undo"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $assignments->appends(request()->query())->links('pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-handshake fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">{{ __('messages.no_assignments_found') }}</h4>
                            <p class="text-muted">{{ __('messages.no_assignments_description') }}</p>
                            <a href="{{ route('assets.assignments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('messages.create_first_assignment') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

