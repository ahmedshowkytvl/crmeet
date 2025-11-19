@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'الأرشيف - إدارة المستخدمين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <h2 class="section-title me-4"><i class="fas fa-archive me-2"></i>الأرشيف - المستخدمين</h2>
        <div class="modern-search-container">
            <form method="GET" action="{{ route('users.archived') }}" class="modern-search-form">
                <input type="text" 
                       class="modern-search-input" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="البحث في الأرشيف..."
                       autocomplete="off">
                <button class="modern-search-btn" type="submit" title="بحث">
                    <i class="fas fa-search search-icon"></i>
                </button>
                @if(request('search'))
                    <a href="{{ route('users.archived') }}" class="modern-search-clear" title="مسح البحث">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('users.index') }}" class="btn btn-primary">
            <i class="fas fa-users me-2"></i>العودة للمستخدمين النشطين
        </a>
    </div>
</div>

<!-- Archived Users Table -->
<div class="card">
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>القسم</th>
                            <th>المنصب</th>
                            <th>تاريخ الأرشفة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    @if($user->profile_picture && Storage::disk('public')->exists($user->profile_picture))
                                        <img src="{{ Storage::disk('public')->url($user->profile_picture) }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $user->name }}</span>
                                        @if($user->name_ar)
                                            <small class="text-muted">{{ $user->name_ar }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $user->email }}</span>
                                        @if($user->work_email)
                                            <small class="text-muted">{{ $user->work_email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($user->department)
                                        <span class="badge bg-info">{{ $user->department->display_name }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->role)
                                        <span class="badge bg-primary">{{ $user->role->display_name }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $user->archived_at ? $user->archived_at->format('Y-m-d H:i') : 'غير محدد' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('users.restore', $user) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من استعادة هذا المستخدم؟')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    title="استعادة">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('users.destroy', $user) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="حذف نهائي">
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
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد عناصر مؤرشفة</h4>
                <p class="text-muted">لم يتم أرشفة أي مستخدمين بعد</p>
                <a href="{{ route('users.index') }}" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i>العودة للمستخدمين النشطين
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add any additional JavaScript here if needed
</script>
@endsection
