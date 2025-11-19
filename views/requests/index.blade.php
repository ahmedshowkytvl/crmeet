@extends('layouts.app')

@section('title', __('messages.request_management') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-alt me-2"></i>{{ __('messages.request_management') }}</h2>
    <a href="{{ route('requests.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('messages.add_new_request') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" data-view-route="requests.show">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('messages.title') }}</th>
                            <th>{{ __('messages.employee') }}</th>
                            <th>{{ __('messages.type') }}</th>
                            <th>{{ __('messages.created_at') }}</th>
                            <th>{{ __('messages.updated_at') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-1">{{ $request->title }}</h6>
                                        <small class="text-muted">{{ Str::limit($request->description, 50) }}</small>
                                    </div>
                                </td>
                                <td>{{ $request->employee->name ?? $trans('no_data') }}</td>
                                <td>
                                    <span class="badge bg-info">{{ __('messages.request') }}</span>
                                </td>
                                <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                <td>{{ $request->updated_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ __('messages.' . $request->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($request->status == 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $request->id }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <form action="{{ route('requests.destroy', $request) }}" method="POST" class="d-inline delete-form" data-message="{{ __('messages.confirm_delete_request') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('messages.no_requests_found') }}</h5>
                <p class="text-muted">{{ __('messages.start_adding_requests') }}</p>
                <a href="{{ route('requests.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ __('messages.add_request') }}
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Approve Modal -->
@foreach($requests as $request)
    @if($request->status == 'pending')
        <div class="modal fade" id="approveModal{{ $request->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('requests.update-status', $request) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <div class="modal-header">
                            <h5 class="modal-title">الموافقة على الطلب</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>هل أنت متأكد من الموافقة على طلب <strong>{{ $request->title }}</strong>؟</p>
                            <div class="mb-3">
                                <label for="admin_notes" class="form-label">ملاحظات إدارية (اختياري)</label>
                                <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-success">موافقة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('requests.update-status', $request) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <div class="modal-header">
                            <h5 class="modal-title">رفض الطلب</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>هل أنت متأكد من رفض طلب <strong>{{ $request->title }}</strong>؟</p>
                            <div class="mb-3">
                                <label for="admin_notes" class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger">رفض</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
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
