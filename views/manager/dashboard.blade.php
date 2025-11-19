@extends('layouts.app')

@section('title', __('messages.manager_dashboard') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>{{ __('messages.manager_dashboard') }}</h2>
    <div class="btn-group">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>{{ __('messages.add_team_member') }}
        </a>
        <a href="{{ route('tasks.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>{{ __('messages.create_task') }}
        </a>
    </div>
</div>

<div class="row">
    <!-- Team Overview -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ __('messages.team_overview') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-primary">{{ $teamMembers->count() }}</h3>
                            <p class="text-muted">{{ __('messages.team_members') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-success">{{ $completedTasks }}</h3>
                            <p class="text-muted">{{ __('messages.completed_tasks') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-warning">{{ $pendingTasks }}</h3>
                            <p class="text-muted">{{ __('messages.pending_tasks') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-info">{{ $pendingRequests }}</h3>
                            <p class="text-muted">{{ __('messages.pending_requests') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>{{ __('messages.team_members') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('messages.name') }}</th>
                                <th>{{ __('messages.job_title') }}</th>
                                <th>{{ __('messages.tasks') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teamMembers as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($member->profile_picture)
                                            <img src="{{ Storage::url($member->profile_picture) }}" alt="{{ $member->name }}" class="user-avatar me-2" style="width: 35px; height: 35px;" onerror="this.style.display='none';">
                                        @endif
                                        <div>
                                            <strong>{{ $member->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $member->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->job_title ?? $trans('no_data') }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $member->assignedTasks->where('status', 'completed')->count() }}</span>
                                    <span class="badge bg-warning">{{ $member->assignedTasks->where('status', 'in_progress')->count() }}</span>
                                    <span class="badge bg-secondary">{{ $member->assignedTasks->where('status', 'pending')->count() }}</span>
                                </td>
                                <td>
                                    @if($member->assignedTasks->where('status', 'in_progress')->count() > 0)
                                        <span class="badge bg-warning">{{ __('messages.busy') }}</span>
                                    @elseif($member->assignedTasks->where('status', 'pending')->count() > 0)
                                        <span class="badge bg-info">{{ __('messages.available') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('messages.free') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.contact-card', $member) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.contact_card') }}">
                                            <i class="fas fa-id-card"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $member) }}" class="btn btn-sm btn-outline-warning" title="{{ __('messages.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Stats -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>{{ __('messages.quick_actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('tasks.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('messages.assign_task') }}
                    </a>
                    <a href="{{ route('users.create') }}" class="btn btn-outline-success">
                        <i class="fas fa-user-plus me-2"></i>{{ __('messages.add_team_member') }}
                    </a>
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-warning">
                        <i class="fas fa-clipboard-list me-2"></i>{{ __('messages.review_requests') }}
                    </a>
                    <a href="{{ route('reports.team') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>{{ __('messages.team_reports') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>{{ __('messages.recent_activity') }}</h5>
            </div>
            <div class="card-body">
                @if($recentActivities->count() > 0)
                    @foreach($recentActivities as $activity)
                    <div class="d-flex align-items-start mb-3">
                        <div class="user-avatar bg-{{ $activity->type === 'task' ? 'success' : 'info' }} text-white me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            <i class="fas fa-{{ $activity->type === 'task' ? 'tasks' : 'clipboard-list' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-1">{{ $activity->description }}</p>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">{{ __('messages.no_recent_activity') }}</p>
                @endif
            </div>
        </div>

        <!-- Team Performance -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>{{ __('messages.team_performance') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>{{ __('messages.completion_rate') }}</span>
                        <span>{{ $completionRate }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>{{ __('messages.on_time_delivery') }}</span>
                        <span>{{ $onTimeRate }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: {{ $onTimeRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar {
    font-size: 0.8em;
    font-weight: bold;
}

.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #3ea8c4 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    border: none;
}

.btn-group .btn {
    border-radius: 6px;
}
</style>
@endsection
