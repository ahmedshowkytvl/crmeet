@extends('layouts.app')

@section('title', 'تقارير Zoho')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-chart-bar me-2"></i>
                تقارير أداء Zoho Desk
            </h2>
            <p class="text-muted">تقارير شاملة لأداء الفريق</p>
        </div>
        <div class="col-md-4">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="daily" {{ $periodType == 'daily' ? 'selected' : '' }}>يومي</option>
                        <option value="weekly" {{ $periodType == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                        <option value="monthly" {{ $periodType == 'monthly' ? 'selected' : '' }}>شهري</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="date" name="date" class="form-control" 
                           value="{{ $periodDate->format('Y-m-d') }}" 
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        أفضل 10 موظفين
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($topPerformers->take(3) as $index => $stat)
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 bg-{{ ['warning', 'secondary', 'success'][$index] }} bg-opacity-10">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-medal fa-3x text-{{ ['warning', 'secondary', 'success'][$index] }}"></i>
                                    </div>
                                    <h4 class="mb-1">{{ $stat->user->name }}</h4>
                                    <p class="text-muted mb-2">{{ $stat->user->position ?? 'موظف' }}</p>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h5 class="mb-0">{{ $stat->tickets_closed_count }}</h5>
                                            <small class="text-muted">تذكرة</small>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="mb-0">{{ number_format($stat->performance_score, 0) }}</h5>
                                            <small class="text-muted">نقطة</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        جميع الموظفين
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>الترتيب</th>
                                    <th>الموظف</th>
                                    <th>القسم</th>
                                    <th>عدد التذاكر</th>
                                    <th>متوسط الرد (دقيقة)</th>
                                    <th>TPH</th>
                                    <th>نقاط الأداء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                    @php
                                        $stat = $user->zohoStats->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($user->profile_picture)
                                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                         class="rounded-circle me-2" 
                                                         width="32" height="32" 
                                                         alt="{{ $user->name }}">
                                                @endif
                                                <div>
                                                    <div>{{ $user->name }}</div>
                                                    <small class="text-muted">{{ $user->zoho_agent_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->department->name ?? '-' }}</td>
                                        <td>
                                            @if($stat)
                                                <span class="badge bg-primary">{{ $stat->tickets_closed_count }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($stat && $stat->avg_response_time_minutes)
                                                {{ number_format($stat->avg_response_time_minutes, 0) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($stat)
                                                {{ number_format($stat->tickets_per_hour, 2) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($stat)
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar 
                                                        @if($stat->performance_score >= 80) bg-success
                                                        @elseif($stat->performance_score >= 60) bg-warning
                                                        @else bg-danger
                                                        @endif" 
                                                        role="progressbar" 
                                                        style="width: {{ $stat->performance_score }}%">
                                                        {{ number_format($stat->performance_score, 0) }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
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
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#reportsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        },
        order: [[6, 'desc']], // Sort by performance score
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script>
@endpush
@endsection

