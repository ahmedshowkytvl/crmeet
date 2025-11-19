@extends('layouts.app')

@section('title', 'تفاصيل الطلب - نظام إدارة الموظفين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-alt me-2"></i>تفاصيل الطلب</h2>
    <div>
        <a href="{{ route('requests.edit', $request) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>تعديل
        </a>
        <a href="{{ route('requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>العودة
        </a>
    </div>
</div>

<div class="row">
    <!-- Request Information -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات الطلب</h5>
            </div>
            <div class="card-body">
                <h4>{{ $request->title }}</h4>
                <p class="text-muted">{{ $request->description }}</p>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>الموظف:</strong>
                        <p>{{ $request->employee->name ?? 'غير محدد' }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>مقدم الطلب:</strong>
                        <p>{{ $request->requestedBy->name ?? 'غير محدد' }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>المدير المسؤول:</strong>
                        <p>{{ $request->manager->name ?? 'غير محدد' }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>الحالة:</strong>
                        <span class="badge bg-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}">
                            {{ $request->status == 'approved' ? 'موافق عليه' : ($request->status == 'rejected' ? 'مرفوض' : 'معلق') }}
                        </span>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>تاريخ الإنشاء:</strong>
                        <p>{{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'غير محدد' }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>آخر تحديث:</strong>
                        <p>{{ $request->updated_at ? $request->updated_at->format('d/m/Y H:i') : 'غير محدد' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>التعليقات</h5>
            </div>
            <div class="card-body">
                @if($request->comments->count() > 0)
                    @foreach($request->comments as $comment)
                        <div class="border-bottom py-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong>{{ $comment->user->name ?? 'غير محدد' }}</strong>
                                <small class="text-muted">{{ $comment->created_at ? $comment->created_at->format('d/m/Y H:i') : 'غير محدد' }}</small>
                            </div>
                            <p class="mb-0 mt-1">{{ $comment->content }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">لا توجد تعليقات</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>الإجراءات</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($request->status == 'pending')
                        <div class="col-md-3 mb-2">
                            <form action="{{ route('requests.update-status', $request) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-2"></i>الموافقة
                                </button>
                            </form>
                        </div>
                        <div class="col-md-3 mb-2">
                            <form action="{{ route('requests.update-status', $request) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times me-2"></i>الرفض
                                </button>
                            </form>
                        </div>
                    @endif
                    <div class="col-md-3 mb-2">
                        <form action="{{ route('requests.destroy', $request) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>حذف الطلب
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('requests.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
