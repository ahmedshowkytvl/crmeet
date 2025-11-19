@extends('layouts.app')

@section('title', 'Tasks and Projects Dashboard - ' . __('messages.system_title'))

@push('styles')
<style>
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: none;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 24px;
    color: white;
}

.stat-content h4 {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #7f8c8d;
    font-weight: 500;
}

.chart-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.progress-summary {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.progress-item {
    margin-bottom: 15px;
}

.progress-label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #2c3e50;
}

.progress {
    height: 8px;
    border-radius: 4px;
    background-color: #ecf0f1;
}

.progress-bar {
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.bg-purple { background-color: #9b59b6 !important; }
.bg-teal { background-color: #1abc9c !important; }
.bg-orange { background-color: #e67e22 !important; }

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 10px 0 0 0;
}

.stats-overview {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.overview-item {
    text-align: center;
    padding: 15px;
}

.overview-number {
    font-size: 2rem;
    font-weight: bold;
    color: #2c3e50;
}

.overview-label {
    color: #7f8c8d;
    font-weight: 500;
    margin-top: 5px;
}

.recent-tasks-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    border: none;
    font-weight: 600;
    color: #2c3e50;
}

.table td {
    border: none;
    border-bottom: 1px solid #ecf0f1;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">
            <i class="fas fa-tasks me-3"></i>Tasks and Projects Dashboard
        </h1>
        <p class="dashboard-subtitle">
            <i class="fas fa-chart-line me-2"></i>Monitor and track task and project performance in real-time
        </p>
    </div>

    <!-- إحصائيات عامة -->
    <div class="stats-overview">
        <div class="row">
            <div class="col-md-3">
                <div class="overview-item">
                    <div class="overview-number">{{ $totalTasks }}</div>
                    <div class="overview-label">Total Tasks</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="overview-item">
                    <div class="overview-number text-success">{{ $completedTasksCount }}</div>
                    <div class="overview-label">Completed Tasks</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="overview-item">
                    <div class="overview-number text-warning">{{ $inProgressTasksCount }}</div>
                    <div class="overview-label">In Progress</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="overview-item">
                    <div class="overview-number text-danger">{{ $overdueTasks->count() }}</div>
                    <div class="overview-label">Overdue Tasks</div>
                </div>
            </div>
        </div>
    </div>

    <!-- إحصائيات المهام حسب النوع -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $marketingTasks->count() }}</h4>
                    <p>Marketing Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-code"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $developmentTasks->count() }}</h4>
                    <p>Development Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $supportTasks->count() }}</h4>
                    <p>Support Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $salesTasks->count() }}</h4>
                    <p>Sales Tasks</p>
                </div>
            </div>
        </div>
    </div>

    <!-- إحصائيات إضافية -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-purple">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $designTasks->count() }}</h4>
                    <p>Design Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-teal">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $communicationTasks->count() }}</h4>
                    <p>Communication Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $researchTasks->count() }}</h4>
                    <p>Research Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-dark">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <h4>{{ $generalTasks->count() }}</h4>
                    <p>General Tasks</p>
                </div>
            </div>
        </div>
    </div>

    <!-- المخططات والإحصائيات -->
    <div class="row">
        <!-- مخطط المهام المكتملة مؤخراً -->
        <div class="col-md-8 mb-4">
            <div class="chart-container">
                <h5 class="mb-3">
                    <i class="fas fa-chart-bar me-2"></i>Recently Completed Tasks
                </h5>
                <canvas id="recentCompletedTasksChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- تقدم المهام حسب النوع -->
        <div class="col-md-4 mb-4">
            <div class="progress-summary">
                <h6 class="mb-3">
                    <i class="fas fa-tasks me-2"></i>Task Progress by Type
                </h6>
                
                @foreach($taskProgressByType as $category => $data)
                <div class="progress-item">
                    <div class="progress-label">
                        @switch($category)
                            @case('marketing') Marketing Tasks @break
                            @case('development') Development Tasks @break
                            @case('support') Support Tasks @break
                            @case('sales') Sales Tasks @break
                            @case('design') Design Tasks @break
                            @case('communication') Communication Tasks @break
                            @case('research') Research Tasks @break
                            @case('general') General Tasks @break
                        @endswitch
                        <small class="text-muted">({{ $data['completed'] }}/{{ $data['total'] }})</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-{{ $category === 'marketing' ? 'primary' : ($category === 'development' ? 'success' : ($category === 'support' ? 'warning' : ($category === 'sales' ? 'info' : ($category === 'design' ? 'purple' : ($category === 'communication' ? 'teal' : ($category === 'research' ? 'orange' : 'dark')))))) }}" 
                             style="width: {{ $data['progress_percentage'] }}%">
                            {{ $data['progress_percentage'] }}%
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- جدول المهام المكتملة مؤخراً -->
    <div class="row">
        <div class="col-12">
            <div class="recent-tasks-table">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Recently Completed Tasks
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($recentCompletedTasks->count() > 0)
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Task Title</th>
                                    <th>Type</th>
                                    <th>Assigned To</th>
                                    <th>Completion Date</th>
                                    <th>Estimated Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCompletedTasks as $task)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span>{{ $task->title }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->category === 'marketing' ? 'primary' : ($task->category === 'development' ? 'success' : ($task->category === 'design' ? 'purple' : ($task->category === 'communication' ? 'teal' : ($task->category === 'research' ? 'orange' : 'dark')))) }}">
                                            @switch($task->category)
                                                @case('marketing') Marketing @break
                                                @case('development') Development @break
                                                @case('support') Support @break
                                                @case('sales') Sales @break
                                                @case('design') Design @break
                                                @case('communication') Communication @break
                                                @case('research') Research @break
                                                @case('general') General @break
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>{{ $task->assignedTo->name ?? 'Not Assigned' }}</td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $task->actual_end_datetime ? $task->actual_end_datetime->format('Y-m-d H:i') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $task->estimated_time ? number_format($task->estimated_time, 1) . 'h' : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ __('messages.completed') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recently completed tasks</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // مخطط المهام المكتملة مؤخراً
    const ctx = document.getElementById('recentCompletedTasksChart');
    if (ctx) {
        // جلب البيانات من الخادم
        fetch('{{ route("task-dashboard.recent-completed") }}')
            .then(response => response.json())
            .then(data => {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.week),
                        datasets: [
                            {
                                label: 'Marketing',
                                data: data.map(item => item.marketing),
                                backgroundColor: '#007bff',
                                borderColor: '#007bff',
                                borderWidth: 0,
                                borderRadius: 8
                            },
                            {
                                label: 'Development',
                                data: data.map(item => item.development),
                                backgroundColor: '#28a745',
                                borderColor: '#28a745',
                                borderWidth: 0,
                                borderRadius: 8
                            },
                            {
                                label: 'Support',
                                data: data.map(item => item.support),
                                backgroundColor: '#ffc107',
                                borderColor: '#ffc107',
                                borderWidth: 0,
                                borderRadius: 8
                            },
                            {
                                label: 'Sales',
                                data: data.map(item => item.sales),
                                backgroundColor: '#17a2b8',
                                borderColor: '#17a2b8',
                                borderWidth: 0,
                                borderRadius: 8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
                // عرض مخطط افتراضي في حالة الخطأ
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Marketing', 'Development', 'Support', 'Sales'],
                        datasets: [{
                            label: 'Completed Tasks',
                            data: [
                                {{ $completedTasksByCategory['marketing'] }},
                                {{ $completedTasksByCategory['development'] }},
                                {{ $completedTasksByCategory['support'] }},
                                {{ $completedTasksByCategory['sales'] }}
                            ],
                            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8'],
                            borderWidth: 0,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            });
    }
});
</script>
@endpush



