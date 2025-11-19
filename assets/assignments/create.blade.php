@extends('layouts.app')

@section('title', __('messages.assign_asset'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-plus me-2 text-primary"></i>
                    {{ __('messages.assign_asset') }}
                </h2>
                <a href="{{ route('assets.assignments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('messages.back_to_assignments') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('assets.assignments.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="asset_id" class="form-label">{{ __('messages.asset') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('asset_id') is-invalid @enderror" 
                                            id="asset_id" name="asset_id" required onchange="loadAssetDetails()">
                                        <option value="">{{ __('messages.select_asset') }}</option>
                                        @foreach($assets as $asset)
                                            <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                                {{ $asset->display_name }} ({{ $asset->asset_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('asset_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="user_id" class="form-label">{{ __('messages.assigned_to') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" name="user_id" required>
                                        <option value="">{{ __('messages.select_user') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_date" class="form-label">{{ __('messages.assigned_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('assigned_date') is-invalid @enderror" 
                                           id="assigned_date" name="assigned_date" value="{{ old('assigned_date', date('Y-m-d')) }}" required>
                                    @error('assigned_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" placeholder="{{ __('messages.assignment_notes_placeholder') }}">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Asset Details (will be loaded via JavaScript) -->
                        <div id="asset-details" class="mb-4" style="display: none;">
                            <hr>
                            <h5 class="text-primary">{{ __('messages.asset_details') }}</h5>
                            <div id="asset-info">
                                <!-- Asset information will be loaded here -->
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('assets.assignments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-handshake me-1"></i>
                                {{ __('messages.assign_asset') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.help') }}
                    </h5>
                </div>
                <div class="card-body">
                    <h6>{{ __('messages.assignment_help_title') }}</h6>
                    <p class="small text-muted">{{ __('messages.assignment_help_description') }}</p>
                    
                    <h6 class="mt-3">{{ __('messages.assignment_rules') }}:</h6>
                    <ul class="small text-muted">
                        <li>{{ __('messages.assignment_rule_1') }}</li>
                        <li>{{ __('messages.assignment_rule_2') }}</li>
                        <li>{{ __('messages.assignment_rule_3') }}</li>
                        <li>{{ __('messages.assignment_rule_4') }}</li>
                    </ul>
                    
                    <div class="mt-3">
                        <h6>{{ __('messages.notes_tips') }}:</h6>
                        <ul class="small text-muted">
                            <li>{{ __('messages.notes_tip_1') }}</li>
                            <li>{{ __('messages.notes_tip_2') }}</li>
                            <li>{{ __('messages.notes_tip_3') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('messages.quick_stats') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary" id="available-assets">{{ $assets->count() }}</h4>
                                <small class="text-muted">{{ __('messages.available_assets') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info" id="total-users">{{ $users->count() }}</h4>
                            <small class="text-muted">{{ __('messages.total_users') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function loadAssetDetails() {
    const assetId = document.getElementById('asset_id').value;
    const assetDetails = document.getElementById('asset-details');
    const assetInfo = document.getElementById('asset-info');
    
    if (!assetId) {
        assetDetails.style.display = 'none';
        return;
    }
    
    // Show loading
    assetInfo.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> {{ __("messages.loading") }}...</div>';
    assetDetails.style.display = 'block';
    
    // Fetch asset details
    fetch(`/assets/assignments/assets?asset_id=${assetId}`)
        .then(response => response.json())
        .then(assets => {
            const asset = assets.find(a => a.id == assetId);
            if (asset) {
                let html = '<div class="row">';
                html += '<div class="col-md-6">';
                html += '<table class="table table-sm table-borderless">';
                html += '<tr><td class="fw-bold">{{ __("messages.asset_code") }}:</td><td><span class="badge bg-secondary">' + asset.asset_code + '</span></td></tr>';
                html += '<tr><td class="fw-bold">{{ __("messages.category") }}:</td><td><span class="badge bg-info">' + asset.category.display_name + '</span></td></tr>';
                html += '<tr><td class="fw-bold">{{ __("messages.status") }}:</td><td><span class="badge bg-success">' + asset.status + '</span></td></tr>';
                html += '</table>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<table class="table table-sm table-borderless">';
                html += '<tr><td class="fw-bold">{{ __("messages.location") }}:</td><td>' + asset.location.display_name + '</td></tr>';
                if (asset.serial_number) {
                    html += '<tr><td class="fw-bold">{{ __("messages.serial_number") }}:</td><td>' + asset.serial_number + '</td></tr>';
                }
                if (asset.cost) {
                    html += '<tr><td class="fw-bold">{{ __("messages.cost") }}:</td><td>$' + parseFloat(asset.cost).toFixed(2) + '</td></tr>';
                }
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                if (asset.description) {
                    html += '<div class="mt-3">';
                    html += '<h6 class="fw-bold">{{ __("messages.description") }}:</h6>';
                    html += '<p class="small">' + asset.description + '</p>';
                    html += '</div>';
                }
                
                assetInfo.innerHTML = html;
            } else {
                assetInfo.innerHTML = '<div class="text-muted">{{ __("messages.asset_not_found") }}</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            assetInfo.innerHTML = '<div class="text-danger">{{ __("messages.error_loading_asset") }}</div>';
        });
}

// Load asset details on page load if asset is already selected
document.addEventListener('DOMContentLoaded', function() {
    const assetId = document.getElementById('asset_id').value;
    if (assetId) {
        loadAssetDetails();
    }
});
</script>
@endsection

