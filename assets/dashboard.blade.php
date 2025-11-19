@extends('layouts.app')

@section('title', __('assets.assets_dashboard'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2 text-white"></i>
                    {{ __('assets.assets_dashboard') }}
                </h2>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshStatistics()">
                        <i class="fas fa-sync-alt me-1"></i>
                        {{ __('assets.refresh') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="AssetsStatisticsCards">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                {{ __('assets.total_assets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white" id="total-assets">
                                {{ $totalAssets }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                {{ __('assets.active_assets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white" id="active-assets">
                                {{ $activeAssets }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                {{ __('assets.maintenance_assets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white" id="maintenance-assets">
                                {{ $maintenanceAssets }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                {{ __('assets.retired_assets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-white" id="retired-assets">
                                {{ $retiredAssets }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Statistics -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('assets.assigned_assets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="assigned-assets">
                                {{ $assignedAssets }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-handshake fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                {{ __('assets.unassigned_assets') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="unassigned-assets">
                                {{ $unassignedAssets }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Assets by Category -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">
                        {{ __('assets.assets_by_category') }}
                <div class="card-body">
                    @if($assetsByCategory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('assets.category') }}</th>
                                        <th class="text-center">{{ __('assets.count') }}</th>
                                        <th class="text-center">{{ __('assets.percentage') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assetsByCategory as $category)
                                        <tr>
                                            <td>{{ $category->display_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $category->assets_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $totalAssets > 0 ? ($category->assets_count / $totalAssets) * 100 : 0 }}%">
                                                        {{ $totalAssets > 0 ? round(($category->assets_count / $totalAssets) * 100, 1) : 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>{{ __('assets.no_data_available') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Assets by Location -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">
                        {{ __('assets.assets_by_location') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($assetsByLocation->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('assets.location') }}</th>
                                        <th class="text-center">{{ __('assets.count') }}</th>
                                        <th class="text-center">{{ __('assets.percentage') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assetsByLocation as $location)
                                        <tr>
                                            <td>{{ $location->display_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $location->assets_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: {{ $totalAssets > 0 ? ($location->assets_count / $totalAssets) * 100 : 0 }}%">
                                                        {{ $totalAssets > 0 ? round(($location->assets_count / $totalAssets) * 100, 1) : 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                            <p>{{ __('assets.no_data_available') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Assignments -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">
                        {{ __('assets.recent_assignments') }}
                    </h6>
                    <a href="{{ route('assets.assignments.index') }}" class="btn btn-sm btn-outline-primary">
                        {{ __('assets.view_all') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($recentAssignments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentAssignments as $assignment)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $assignment->asset->display_name }}</h6>
                                        <small class="text-muted">
                                            {{ __('messages.assigned_to') }}: {{ $assignment->user ? $assignment->user->name : __('messages.user_not_found') }}
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        {{ $assignment->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-handshake fa-3x mb-3"></i>
                            <p>{{ __('assets.no_recent_assignments') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">
                        {{ __('assets.recent_activity') }}
                    </h6>
                    <a href="{{ route('assets.logs.index') }}" class="btn btn-sm btn-outline-primary">
                        {{ __('assets.view_all') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($recentLogs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentLogs as $log)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $log->asset->display_name }}</h6>
                                        <small class="text-muted">
                                            {{ $log->action_label }} - {{ $log->user ? $log->user->name : __('messages.user_not_found') }}
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        {{ $log->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <p>{{ __('assets.no_recent_activity') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Warranty Expiring -->
    @if($expiringWarranty->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('assets.warranty_expiring_soon') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('assets.asset') }}</th>
                                    <th>{{ __('assets.category') }}</th>
                                    <th>{{ __('assets.warranty_expiry') }}</th>
                                    <th>{{ __('assets.days_remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiringWarranty as $asset)
                                    <tr>
                                        <td>{{ $asset->display_name }}</td>
                                        <td>{{ $asset->category->display_name }}</td>
                                        <td>{{ $asset->warranty_expiry->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $asset->warranty_expiry->diffInDays(now()) }} {{ __('assets.days') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function refreshStatistics() {
    fetch('{{ route("assets.statistics") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-assets').textContent = data.total_assets;
            document.getElementById('active-assets').textContent = data.active_assets;
            document.getElementById('maintenance-assets').textContent = data.maintenance_assets;
            document.getElementById('retired-assets').textContent = data.retired_assets;
            document.getElementById('assigned-assets').textContent = data.assigned_assets;
            document.getElementById('unassigned-assets').textContent = data.unassigned_assets;
        })
        .catch(error => console.error('Error:', error));
}
</script>
@endsection

