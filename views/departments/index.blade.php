@extends('layouts.app')

@section('title', __('messages.department_management') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i>{{ __('messages.department_management') }}</h2>
    <a href="{{ route('departments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('messages.add_new_department') }}
    </a>
</div>


<div class="row">
    @if($departments->count() > 0)
        @foreach($departments as $department)
            <div class="col-md-4 mb-4">
                <div class="card h-100" data-entity-id="{{ $department->id }}" data-view-route="departments.show">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title">{{ $department->name }}</h5>
                            <span class="badge bg-primary">{{ $department->users_count }} {{ __('messages.employee') }}</span>
                        </div>
                        
                        @if($department->description)
                            <p class="card-text text-muted">{{ $department->description }}</p>
                        @endif
                        
                        <!-- Tasks by Priority for this Department -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">{{ __('messages.tasks_by_priority') }}:</h6>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded bg-danger bg-opacity-10">
                                        <div class="text-danger fw-bold">{{ $department->critical_tasks_count ?? 0 }}</div>
                                        <small class="text-danger">{{ __('messages.critical') }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded bg-warning bg-opacity-10">
                                        <div class="text-warning fw-bold">{{ $department->medium_tasks_count ?? 0 }}</div>
                                        <small class="text-warning">{{ __('messages.medium') }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 border rounded bg-success bg-opacity-10">
                                        <div class="text-success fw-bold">{{ $department->low_tasks_count ?? 0 }}</div>
                                        <small class="text-success">{{ __('messages.low') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $department->created_at->format('d/m/Y') }}
                            </small>
                            
                            <div class="btn-group" role="group">
                                <a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline delete-form" data-message="{{ __('messages.confirm_delete_department') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        
        @if($departments->hasPages() || request('per_page') == 'all')
        <div class="d-flex justify-content-center mt-4">
            {{ $departments->appends(request()->query())->links('pagination.bootstrap-5') }}
        </div>
        @endif
    @else
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('messages.no_departments_found') }}</h5>
                    <p class="text-muted">{{ __('messages.start_adding_departments') }}</p>
                    <a href="{{ route('departments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('messages.add_department') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation
    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            if (confirm(message)) {
                this.submit();
            }
        });
    });
});
</script>
@endpush
