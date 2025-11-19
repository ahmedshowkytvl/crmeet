@extends('layouts.app')

@section('title', 'الأرشيف - الموردين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <h2 class="section-title me-4"><i class="fas fa-archive me-2"></i>الأرشيف - الموردين</h2>
        <div class="modern-search-container">
            <form method="GET" action="{{ route('suppliers.archived') }}" class="modern-search-form">
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
                    <a href="{{ route('suppliers.archived') }}" class="modern-search-clear" title="مسح البحث">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('suppliers.index') }}" class="btn btn-primary">
            <i class="fas fa-truck me-2"></i>العودة للموردين النشطين
        </a>
    </div>
</div>

<!-- Archived Suppliers Table -->
<div class="card">
    <div class="card-body">
        @if($suppliers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>المدينة</th>
                            <th>الشخص المسؤول</th>
                            <th>تاريخ الأرشفة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $supplier->display_name }}</span>
                                        @if($supplier->name_ar)
                                            <small class="text-muted">{{ $supplier->name_ar }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $supplier->email ?: 'غير محدد' }}</span>
                                        @if($supplier->contact_email)
                                            <small class="text-muted">{{ $supplier->contact_email }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($supplier->phone)
                                            <span>{{ $supplier->phone }}</span>
                                        @endif
                                        @if($supplier->mobile)
                                            <small class="text-muted">{{ $supplier->mobile }}</small>
                                        @endif
                                        @if($supplier->contact_phone)
                                            <small class="text-muted">{{ $supplier->contact_phone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $supplier->city ?: 'غير محدد' }}</span>
                                </td>
                                <td>
                                    @if($supplier->contact_person)
                                        <span>{{ $supplier->contact_person }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $supplier->archived_at ? $supplier->archived_at->format('Y-m-d H:i') : 'غير محدد' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('suppliers.show', $supplier) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('suppliers.restore', $supplier) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من استعادة هذا المورد؟')">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    title="استعادة">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المورد نهائياً؟')">
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
                {{ $suppliers->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد عناصر مؤرشفة</h4>
                <p class="text-muted">لم يتم أرشفة أي موردين بعد</p>
                <a href="{{ route('suppliers.index') }}" class="btn btn-primary">
                    <i class="fas fa-truck me-2"></i>العودة للموردين النشطين
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
