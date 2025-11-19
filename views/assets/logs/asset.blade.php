@extends('layouts.app')

@section('title', 'سجل الأصل - ' . $asset->display_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">سجل الأصل</h1>
                    <p class="text-muted mb-0">{{ $asset->asset_code }} - {{ $asset->display_name }}</p>
                </div>
                <div>
                    <a href="{{ route('assets.movement.show', $asset) }}" class="btn btn-primary">
                        <i class="fas fa-history"></i> عرض السجل المتقدم
                    </a>
                    <a href="{{ route('assets.logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للقائمة
                    </a>
                </div>
            </div>

            <!-- Asset Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">معلومات الأصل</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>كود الأصل:</strong><br>
                            <span class="text-primary">{{ $asset->asset_code }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>الاسم:</strong><br>
                            {{ $asset->display_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>الفئة:</strong><br>
                            {{ $asset->category->display_name ?? 'غير محدد' }}
                        </div>
                        <div class="col-md-3">
                            <strong>الرقم التسلسلي:</strong><br>
                            {{ $asset->serial_number ?? 'غير محدد' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">سجل العمليات</h5>
                </div>
                <div class="card-body">
                    @if($logs->count() > 0)
                        <div class="timeline">
                            @foreach($logs as $log)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        @switch($log->action)
                                            @case('stored')
                                                <i class="fas fa-warehouse text-success"></i>
                                                @break
                                            @case('checked_out')
                                                <i class="fas fa-user-check text-warning"></i>
                                                @break
                                            @case('returned')
                                                <i class="fas fa-undo text-info"></i>
                                                @break
                                            @case('moved')
                                                <i class="fas fa-arrows-alt text-primary"></i>
                                                @break
                                            @case('maintenance')
                                                <i class="fas fa-tools text-danger"></i>
                                                @break
                                            @case('assigned')
                                                <i class="fas fa-user-plus text-warning"></i>
                                                @break
                                            @case('repaired')
                                                <i class="fas fa-wrench text-info"></i>
                                                @break
                                            @case('created')
                                                <i class="fas fa-plus text-success"></i>
                                                @break
                                            @case('updated')
                                                <i class="fas fa-edit text-primary"></i>
                                                @break
                                            @default
                                                <i class="fas fa-circle text-muted"></i>
                                        @endswitch
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $log->action_label }}</h6>
                                                
                                                @if($log->cabinet && $log->shelf)
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $log->cabinet->warehouse->display_name ?? 'مخزن' }} - 
                                                        دولاب {{ $log->cabinet->cabinet_number }} - 
                                                        رف {{ $log->shelf->shelf_code }}
                                                    </p>
                                                @elseif($log->location_description)
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $log->location_description }}
                                                    </p>
                                                @endif
                                                
                                                @if($log->assignedToUser)
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-user"></i>
                                                        {{ $log->assignedToUser->name }}
                                                    </p>
                                                @endif
                                                
                                                @if($log->availability_status)
                                                    <p class="mb-1">
                                                        <span class="badge bg-{{ $log->availability_status === 'available' ? 'success' : ($log->availability_status === 'checked_out' ? 'warning' : ($log->availability_status === 'maintenance' ? 'danger' : 'secondary')) }}">
                                                            {{ $log->availability_status_label }}
                                                        </span>
                                                    </p>
                                                @endif
                                                
                                                @if($log->notes)
                                                    <p class="mb-1">
                                                        <i class="fas fa-sticky-note"></i>
                                                        {{ $log->notes }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    {{ $log->action_timestamp ? $log->action_timestamp->format('d/m/Y H:i') : $log->date }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    بواسطة {{ $log->user->name }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد عمليات مسجلة لهذا الأصل</p>
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
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 30px;
    height: 30px;
    background: #fff;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 35px;
    width: 2px;
    height: calc(100% + 15px);
    background: #dee2e6;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}
</style>
@endpush
