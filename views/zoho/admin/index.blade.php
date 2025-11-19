@extends('layouts.app')

@section('title', 'إدارة Zoho Integration')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-cogs me-2"></i>
                إدارة Zoho Integration
            </h2>
            <p class="text-muted">إدارة ربط المستخدمين مع Zoho Desk</p>
        </div>
        <div class="col-md-4 text-end">
            <form action="{{ route('zoho.admin.testConnection') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-wifi me-2"></i>
                    اختبار الاتصال
                </button>
            </form>
            <form action="{{ route('zoho.admin.syncNow') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>
                    مزامنة الآن
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-0">إجمالي المستخدمين</h6>
                            <h3 class="mb-0 mt-2">{{ $totalUsers }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-0">مفعّل على Zoho</h6>
                            <h3 class="mb-0 mt-2">{{ $zohoEnabled }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-0">حالة الاتصال</h6>
                            <h3 class="mb-0 mt-2">
                                @if($connectionStatus)
                                    <span class="badge bg-success">متصل</span>
                                @else
                                    <span class="badge bg-danger">غير متصل</span>
                                @endif
                            </h3>
                        </div>
                        <div class="bg-{{ $connectionStatus ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded">
                            <i class="fas fa-wifi text-{{ $connectionStatus ? 'success' : 'danger' }} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-map Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i>
                        الربط التلقائي
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        يمكنك ربط المستخدمين تلقائياً مع Zoho Agents بناءً على عناوين البريد الإلكتروني المتطابقة
                    </p>
                    <form action="{{ route('zoho.admin.autoMap') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magic me-2"></i>
                            تشغيل الربط التلقائي
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        المستخدمون
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>Zoho Agent Name</th>
                                    <th>الحالة</th>
                                    <th>آخر تحديث</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($user->profile_picture)
                                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                     class="rounded-circle me-2" 
                                                     width="32" height="32" 
                                                     alt="{{ $user->name }}">
                                            @endif
                                            <span>{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->zoho_agent_name)
                                            <span class="badge bg-info">{{ $user->zoho_agent_name }}</span>
                                        @else
                                            <span class="text-muted">غير مربوط</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_zoho_enabled)
                                            <span class="badge bg-success">مفعّل</span>
                                        @else
                                            <span class="badge bg-secondary">غير مفعّل</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->zohoStats->first())
                                            {{ $user->zohoStats->first()->last_synced_at?->diffForHumans() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Toggle Button -->
                                            <form action="{{ route('zoho.admin.toggleUser', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-{{ $user->is_zoho_enabled ? 'warning' : 'success' }}"
                                                        title="{{ $user->is_zoho_enabled ? 'تعطيل' : 'تفعيل' }}">
                                                    <i class="fas fa-{{ $user->is_zoho_enabled ? 'times' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Edit Button -->
                                            <button type="button" 
                                                    class="btn btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal{{ $user->id }}"
                                                    title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل ربط Zoho - {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('zoho.admin.mapUser') }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Zoho Agent Name</label>
                                                                <input type="text" name="zoho_agent_name" 
                                                                       class="form-control" 
                                                                       value="{{ $user->zoho_agent_name }}"
                                                                       required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Zoho Agent ID (اختياري)</label>
                                                                <input type="text" name="zoho_agent_id" 
                                                                       class="form-control" 
                                                                       value="{{ $user->zoho_agent_id }}">
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Zoho Email (اختياري)</label>
                                                                <input type="email" name="zoho_email" 
                                                                       class="form-control" 
                                                                       value="{{ $user->zoho_email }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        },
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>
@endpush
@endsection

