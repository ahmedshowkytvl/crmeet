@extends('layouts.app')

@section('title', __('zoho.dashboard.title') . ' - ' . $user->name)

@push('styles')
<style>
.rtl {
    direction: rtl;
    text-align: right;
}
.rtl .me-2 {
    margin-left: 0.5rem !important;
    margin-right: 0 !important;
}
.rtl .ms-2 {
    margin-right: 0.5rem !important;
    margin-left: 0 !important;
}
.rtl .text-start {
    text-align: right !important;
}
.rtl .text-end {
    text-align: left !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4 {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('zoho.dashboard.title') }}
                    </h2>
                    <p class="text-muted">
                        {{ __('zoho.dashboard.welcome', ['name' => $user->name]) }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('zoho.tickets') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-ticket-alt me-2"></i>
                        {{ __('zoho.tickets.all_tickets') }}
                    </a>
                    <a href="{{ route('zoho.tickets.in-progress') }}" class="btn btn-outline-warning me-2">
                        <i class="fas fa-cog me-2"></i>
                        تذاكر قيد التنفيذ
                    </a>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-2"></i>
                        {{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'ar') }}">العربية</a></li>
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">English</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="row mb-4">
        <!-- Daily Stats -->
        @if($statsSummary['daily'])
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-0">{{ __('zoho.dashboard.daily') }}</h6>
                            <h3 class="mb-0 mt-2">{{ $statsSummary['daily']->tickets_closed_count }}</h3>
                            <small class="text-muted">{{ $statsSummary['daily']->tickets_closed_count == 1 ? __('zoho.dashboard.tickets_closed') : __('zoho.dashboard.tickets_closed_plural') }}</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-day text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="border-top pt-2">
                        <small class="text-muted">{{ __('zoho.dashboard.tph') }}: {{ number_format($statsSummary['daily']->tickets_per_hour, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Weekly Stats -->
        @if($statsSummary['weekly'])
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-0">{{ __('zoho.dashboard.weekly') }}</h6>
                            <h3 class="mb-0 mt-2">{{ $statsSummary['weekly']->tickets_closed_count }}</h3>
                            <small class="text-muted">{{ $statsSummary['weekly']->tickets_closed_count == 1 ? __('zoho.dashboard.tickets_closed') : __('zoho.dashboard.tickets_closed_plural') }}</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-week text-success fa-2x"></i>
                        </div>
                    </div>
                    <div class="border-top pt-2">
                        <small class="text-muted">{{ __('zoho.dashboard.avg_response_time') }}: {{ $statsSummary['weekly']->avg_response_time_minutes ? number_format($statsSummary['weekly']->avg_response_time_minutes, 0) . ' ' . __('zoho.dashboard.minutes') : 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Monthly Stats -->
        @if($statsSummary['monthly'])
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-0">{{ __('zoho.dashboard.monthly') }}</h6>
                            <h3 class="mb-0 mt-2">{{ $statsSummary['monthly']->tickets_closed_count }}</h3>
                            <small class="text-muted">{{ $statsSummary['monthly']->tickets_closed_count == 1 ? __('zoho.dashboard.tickets_closed') : __('zoho.dashboard.tickets_closed_plural') }}</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-alt text-warning fa-2x"></i>
                        </div>
                    </div>
                    <div class="border-top pt-2">
                        <small class="text-muted">{{ __('zoho.dashboard.performance_score') }}: {{ number_format($statsSummary['monthly']->performance_score, 0) }}/100</small>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row">
        <!-- Performance Chart -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        {{ __('zoho.dashboard.performance_chart') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($performanceTrend->isNotEmpty())
                    <canvas id="performanceChart" height="100"></canvas>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p>{{ __('zoho.dashboard.no_data') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        {{ __('zoho.dashboard.achievements') }}
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($achievements as $achievement)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            @php
                                $levelColors = [
                                    'bronze' => 'warning',
                                    'silver' => 'secondary',
                                    'gold' => 'success',
                                    'platinum' => 'primary'
                                ];
                                $color = $levelColors[$achievement->achievement_level] ?? 'info';
                            @endphp
                            <i class="fas fa-medal fa-2x text-{{ $color }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $achievement->title }}</h6>
                            <small class="text-muted">{{ $achievement->level_name }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-award fa-3x mb-3"></i>
                        <p class="mb-0">{{ __('zoho.dashboard.start_working') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        {{ __('zoho.dashboard.recent_tickets') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('zoho.common.ticket_number') }}</th>
                                    <th>{{ __('zoho.common.subject') }}</th>
                                    <th>{{ __('zoho.common.status') }}</th>
                                    <th>{{ __('zoho.common.closed_date') }}</th>
                                    <th>{{ __('zoho.common.response_time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets as $ticket)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $ticket->ticket_number }}</span>
                                    </td>
                                    <td>{{ \Str::limit($ticket->subject, 50) }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($ticket->status) {
                                                'Closed' => 'success',
                                                'Open' => 'primary',
                                                'Pending' => 'warning',
                                                'In Progress' => 'info',
                                                'Resolved' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ __('zoho.ticket_status.' . strtolower(str_replace(' ', '_', $ticket->status))) }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->closed_at_zoho?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($ticket->response_time_minutes)
                                            {{ number_format($ticket->response_time_minutes / 60, 1) }} {{ __('zoho.dashboard.hours') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        {{ __('zoho.dashboard.no_tickets') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('manage-zoho')
    <!-- Admin Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        إدارة النظام
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('zoho.admin.index') }}" class="btn btn-outline-dark w-100 h-100 d-flex flex-column justify-content-center align-items-center py-4">
                                <i class="fas fa-users-cog fa-2x mb-2"></i>
                                <span>إدارة المستخدمين</span>
                                <small class="text-muted">ربط وإدارة مستخدمي Zoho</small>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('zoho.department-mappings.index') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center align-items-center py-4">
                                <i class="fas fa-exchange-alt fa-2x mb-2"></i>
                                <span>إدارة الأقسام</span>
                                <small class="text-muted">ربط أقسام Zoho بالأقسام المحلية</small>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('zoho.reports') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column justify-content-center align-items-center py-4">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <span>التقارير</span>
                                <small class="text-muted">تقارير مفصلة وإحصائيات</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($performanceTrend->isNotEmpty())
const ctx = document.getElementById('performanceChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($performanceTrend->pluck('period_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M Y'))),
        datasets: [{
            label: '{{ __('zoho.dashboard.tickets_closed_plural') }}',
            data: @json($performanceTrend->pluck('tickets_closed_count')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: '{{ __('zoho.dashboard.performance_score') }}',
            data: @json($performanceTrend->pluck('performance_score')),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: '{{ __('zoho.dashboard.tickets_closed_plural') }}'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: '{{ __('zoho.dashboard.performance_score') }}'
                },
                grid: {
                    drawOnChartArea: false,
                },
                max: 100
            }
        }
    }
});
@endif
</script>
@endpush
@endsection

