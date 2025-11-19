@extends('layouts.app')

@section('title', __('messages.task_templates'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-clipboard-list me-2"></i>
                {{ __('messages.task_templates') }}
            </h2>
            <p class="text-muted">{{ __('messages.template_management') }}</p>
        </div>
        <div class="col-md-4 text-end">
            @can('manage-tasks')
                <a href="{{ route('task-templates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('messages.add_new_template') }}
                </a>
            @endcan
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            <p class="mb-0">{{ __('messages.total_templates') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['active'] }}</h4>
                            <p class="mb-0">{{ __('messages.active_templates') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['inactive'] }}</h4>
                            <p class="mb-0">{{ __('messages.inactive_templates') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pause-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ count($stats['departments']) }}</h4>
                            <p class="mb-0">{{ __('messages.departments') }}</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('task-templates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('messages.search_templates') }}" 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="department" class="form-select">
                        <option value="">{{ __('messages.all_departments') }}</option>
                        @foreach($departments as $key => $name)
                            <option value="{{ $key }}" {{ request('department') == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort_by" class="form-select">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>{{ __('messages.sort_by_name') }}</option>
                        <option value="estimated_time" {{ request('sort_by') == 'estimated_time' ? 'selected' : '' }}>{{ __('messages.sort_by_estimated_time') }}</option>
                        <option value="department" {{ request('sort_by') == 'department' ? 'selected' : '' }}>{{ __('messages.sort_by_department') }}</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('messages.sort_by_created_at') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- Import CSV -->
            @can('manage-tasks')
                <hr>
                <form method="POST" action="{{ route('task-templates.import') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload me-2"></i>
                            {{ __('messages.import_csv') }}
                        </button>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            {{ __('messages.template_help_text') }}
                        </small>
                    </div>
                </form>
            @endcan
        </div>
    </div>

    <!-- Templates List -->
    <div class="card">
        <div class="card-body">
            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('messages.template_name') }}</th>
                                <th>{{ __('messages.template_department') }}</th>
                                <th>{{ __('messages.estimated_time') }}</th>
                                <th>{{ __('messages.template_status') }}</th>
                                <th>{{ __('messages.usage_count') }}</th>
                                <th>{{ __('messages.template_created_at') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $template->display_name }}</strong>
                                            @if($template->description)
                                                <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $template->department_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $template->estimated_time }} {{ __('messages.hours') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $template->estimated_time_in_minutes }} {{ __('messages.minutes') }}</small>
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">{{ __('messages.active') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('messages.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $template->usage_count }}</span>
                                    </td>
                                    <td>{{ $template->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('task-templates.show', $template) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('manage-tasks')
                                                <a href="{{ route('task-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($template->canBeDeleted())
                                                    <form method="POST" action="{{ route('task-templates.destroy', $template) }}" 
                                                          style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('task-templates.toggle-status', $template) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $template->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                        <i class="fas fa-{{ $template->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $templates->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('messages.no_templates_found') }}</h5>
                    <p class="text-muted">{{ __('messages.template_help_text') }}</p>
                    @can('manage-tasks')
                        <a href="{{ route('task-templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('messages.add_new_template') }}
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection
