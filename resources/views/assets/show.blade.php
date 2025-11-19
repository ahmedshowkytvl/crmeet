@extends('layouts.app')

@section('title', $asset->display_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-cube me-2 text-primary"></i>
                        {{ $asset->display_name }}
                    </h2>
                    <p class="text-muted mb-0">{{ $asset->asset_code }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('assets.assets.edit', $asset) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('messages.edit') }}
                    </a>
                    <a href="{{ route('assets.assets.print-barcode', $asset) }}" class="btn btn-info">
                        <i class="fas fa-barcode me-1"></i>
                        {{ __('messages.print_barcode') }}
                    </a>
                    <a href="{{ route('assets.assets.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Asset Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.asset_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">{{ __('messages.asset_code') }}:</td>
                                    <td><span class="badge bg-secondary">{{ $asset->asset_code }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.barcode') ?: 'Barcode' }}:</td>
                                    <td><span class="badge bg-info">{{ $asset->barcode }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.name') }}:</td>
                                    <td>{{ app()->getLocale() == 'ar' ? ($asset->name_ar ?: $asset->name) : $asset->name }}</td>
                                </tr>
                                @if($asset->name_ar)
                                <tr>
                                    <td class="fw-bold">{{ __('messages.name_ar') }}:</td>
                                    <td>{{ $asset->name_ar }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">{{ __('messages.category') }}:</td>
                                    <td><span class="badge bg-primary">{{ app()->getLocale() == 'ar' ? ($asset->category->name_ar ?: $asset->category->name) : $asset->category->name }}</span></td>
                                </tr>
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
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">{{ __('messages.serial_number') }}:</td>
                                    <td>{{ $asset->serial_number ?: __('messages.not_specified') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.location') }}:</td>
                                    <td>{{ app()->getLocale() == 'ar' ? ($asset->location->name_ar ?: $asset->location->name) : $asset->location->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.assigned_to') }}:</td>
                                    <td>
                                        @if($asset->assignedTo)
                                            <span class="badge bg-success">{{ app()->getLocale() == 'ar' ? ($asset->assignedTo->name_ar ?: $asset->assignedTo->name) : $asset->assignedTo->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('messages.unassigned') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.purchase_date') }}:</td>
                                    <td>
                                        @if($asset->purchase_date)
                                            <span class="badge bg-primary">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ $asset->purchase_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            {{ __('messages.not_specified') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.warranty_expiry') }}:</td>
                                    <td>
                                        @if($asset->warranty_expiry)
                                            <span class="badge {{ $asset->warranty_expiry->isFuture() ? 'bg-success' : 'bg-danger' }}">
                                                <i class="fas fa-calendar-check me-1"></i>
                                                {{ $asset->warranty_expiry->format('d/m/Y') }}
                                            </span>
                                            @if($asset->warranty_expiry->isFuture())
                                                <span class="badge bg-info ms-1">
                                                    {{ $asset->warranty_expiry->diffInDays(now()) }} {{ __('messages.days_remaining') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger ms-1">{{ __('messages.expired') }}</span>
                                            @endif
                                        @else
                                            {{ __('messages.not_specified') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.cost') }}:</td>
                                    <td>{{ $asset->cost ? '$' . number_format($asset->cost, 2) : __('messages.not_specified') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($asset->description || $asset->description_ar)
                    <hr>
                    <h6 class="fw-bold">{{ __('messages.description') }}:</h6>
                    <p>{{ $asset->display_description ?: __('messages.no_description') }}</p>
                    @endif
                </div>
            </div>

            <!-- Category Properties -->
            @if($asset->propertyValues->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('messages.category_properties') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($asset->propertyValues as $propertyValue)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3">
                                    <h6 class="fw-bold mb-2">{{ $propertyValue->property->display_name }}</h6>
                                    <p class="mb-0">
                                        @if($propertyValue->property->isImageType())
                                            @if($propertyValue->value)
                                                <img src="{{ Storage::url($propertyValue->value) }}" alt="{{ $propertyValue->property->display_name }}" 
                                                     class="img-thumbnail" style="max-width: 200px;">
                                            @else
                                                <span class="text-muted">{{ __('messages.no_image') }}</span>
                                            @endif
                                        @else
                                            {{ $propertyValue->formatted_value ?: __('messages.not_specified') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Barcode -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-barcode me-2"></i>
                        {{ __('messages.barcode') ?: 'Barcode' }}
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($asset->barcode_image)
                        <img src="{{ Storage::url($asset->barcode_image) }}" alt="Barcode" class="img-fluid mb-3">
                    @endif
                    <p class="mb-3"><strong>{{ $asset->barcode }}</strong></p>
                    <div class="btn-group">
                        <a href="{{ route('assets.assets.print-barcode', $asset) }}" class="btn btn-primary">
                            <i class="fas fa-print me-1"></i>
                            {{ __('messages.print_barcode') }}
                        </a>
                        <a href="{{ route('assets.assets.download-barcode', $asset) }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i>
                            {{ __('messages.download_barcode') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Assignment History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        {{ __('messages.assignment_history') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($asset->assignments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($asset->assignments->take(5) as $assignment)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $assignment->user ? (app()->getLocale() == 'ar' ? ($assignment->user->name_ar ?: $assignment->user->name) : $assignment->user->name) : __('messages.user_not_found') }}</h6>
                                            <small class="text-muted">
                                                {{ __('messages.assigned_on') }}: {{ $assignment->assigned_date->format('Y-m-d') }}
                                            </small>
                                            @if($assignment->returned_date)
                                                <br><small class="text-muted">
                                                    {{ __('messages.returned_on') }}: {{ $assignment->returned_date->format('Y-m-d') }}
                                                </small>
                                            @else
                                                <br><span class="badge bg-success">{{ __('messages.currently_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($assignment->notes)
                                        <p class="mt-2 mb-0 small text-muted">{{ $assignment->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($asset->assignments->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('assets.logs.asset', $asset) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('messages.view_all_history') }}
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <p>{{ __('messages.no_assignment_history') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        {{ __('messages.recent_activity') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($asset->logs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($asset->logs->take(5) as $log)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $log->action_label }}</h6>
                                            <small class="text-muted">
                                                {{ __('messages.by') ?: 'By' }}: {{ $log->user ? (app()->getLocale() == 'ar' ? ($log->user->name_ar ?: $log->user->name) : $log->user->name) : __('messages.user_not_found') }}
                                            </small>
                                            <br><small class="text-muted">
                                                {{ $log->created_at->format('Y-m-d H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                    @if($log->notes)
                                        <p class="mt-2 mb-0 small text-muted">{{ $log->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($asset->logs->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('assets.logs.asset', $asset) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('messages.view_all_activity') }}
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p>{{ __('messages.no_recent_activity') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ __('messages.quick_actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($asset->assignedTo)
                            <form method="POST" action="{{ route('assets.assignments.return', $asset->assignments->where('returned_date', null)->first()) }}" 
                                  onsubmit="return confirm('{{ __('messages.confirm_return_asset') }}')">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-undo me-1"></i>
                                    {{ __('messages.return_asset') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('assets.assignments.create') }}?asset_id={{ $asset->id }}" class="btn btn-success">
                                <i class="fas fa-handshake me-1"></i>
                                {{ __('messages.assign_asset') }}
                            </a>
                        @endif
                        
                        <a href="{{ route('assets.assets.edit', $asset) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('messages.edit_asset') }}
                        </a>
                        
                        <a href="{{ route('assets.assets.print-barcode', $asset) }}" class="btn btn-outline-info">
                            <i class="fas fa-barcode me-1"></i>
                            {{ __('messages.print_barcode') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

