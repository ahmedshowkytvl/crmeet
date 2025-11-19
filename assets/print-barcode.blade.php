@extends('layouts.app')

@section('title', __('messages.print_barcode') . ' - ' . $asset->display_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-barcode me-2 text-primary"></i>
                    {{ __('messages.print_barcode') }}
                </h2>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        {{ __('messages.print') }}
                    </button>
                    <a href="{{ route('assets.assets.download-barcode', $asset) }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-1"></i>
                        {{ __('messages.download') }}
                    </a>
                    <a href="{{ route('assets.assets.show', $asset) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Area -->
    <div class="row">
        <div class="col-12">
            <div class="card" id="print-area">
                <div class="card-body text-center">
                    <!-- Asset Information -->
                    <div class="mb-4">
                        <h3 class="mb-2">{{ $asset->display_name }}</h3>
                        <p class="text-muted mb-1">{{ $asset->asset_code }}</p>
                        <p class="text-muted mb-0">{{ $asset->category->display_name }}</p>
                    </div>

                    <!-- Barcode Image -->
                    <div class="mb-4">
                        @if($asset->barcode_image)
                            <img src="{{ Storage::url($asset->barcode_image) }}" alt="Barcode" class="img-fluid" style="max-height: 200px;">
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('messages.barcode_image_not_found') }}
                            </div>
                        @endif
                    </div>

                    <!-- Barcode Text -->
                    <div class="mb-4">
                        <h4 class="font-monospace">{{ $asset->barcode }}</h4>
                    </div>

                    <!-- Additional Information -->
                    <div class="row text-start">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold">{{ __('messages.serial_number') }}:</td>
                                    <td>{{ $asset->serial_number ?: __('messages.not_specified') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.location') }}:</td>
                                    <td>{{ $asset->location->display_name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold">{{ __('messages.status') }}:</td>
                                    <td>
                                        @if($asset->status == 'active')
                                            <span class="badge bg-success">{{ $asset->status_label }}</span>
                                        @elseif($asset->status == 'maintenance')
                                            <span class="badge bg-warning">{{ $asset->status_label }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $asset->status_label }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.assigned_to') }}:</td>
                                    <td>
                                        @if($asset->assignedTo)
                                            {{ $asset->assignedTo->name }}
                                        @else
                                            {{ __('messages.unassigned') }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Print Date -->
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            {{ __('messages.printed_on') }}: {{ now()->format('Y-m-d H:i:s') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-print when page loads (optional)
document.addEventListener('DOMContentLoaded', function() {
    // Uncomment the line below to auto-print when the page loads
    // window.print();
});

// Print function
function printBarcode() {
    window.print();
}

// Keyboard shortcut for printing
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    
    #print-area, #print-area * {
        visibility: visible;
    }
    
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    
    .btn, .btn-group {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-body {
        padding: 20px !important;
    }
    
    h3 {
        font-size: 24px !important;
        margin-bottom: 10px !important;
    }
    
    h4 {
        font-size: 20px !important;
        margin-bottom: 15px !important;
    }
    
    .table {
        font-size: 12px !important;
    }
    
    .badge {
        font-size: 10px !important;
        padding: 4px 8px !important;
    }
}
</style>
@endsection

