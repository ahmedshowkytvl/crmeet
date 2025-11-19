@extends('layouts.app')

@section('title', 'إدارة الموردين')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-truck me-2 text-primary"></i>
                    إدارة الموردين
                </h2>
                <div class="btn-group" role="group">
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        إضافة مورد جديد
                    </a>
                    <a href="{{ route('suppliers.archived') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-archive me-1"></i>
                        الأرشيف
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($suppliers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" data-view-route="suppliers.show">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>الهاتف</th>
                                        <th>المدينة</th>
                                        <th>الشخص المسؤول</th>
                                        <th>الملاحظات</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suppliers as $supplier)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $supplier->display_name }}</strong>
                                                    @if($supplier->name_ar)
                                                        <br><small class="text-muted">{{ $supplier->name_ar }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($supplier->email)
                                                    <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">
                                                        {{ $supplier->email }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($supplier->phone)
                                                    <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">
                                                        {{ $supplier->phone }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">غير محدد</span>
                                                @endif
                                            </td>
                                            <td>{{ $supplier->city ?: 'غير محدد' }}</td>
                                            <td>{{ $supplier->contact_person ?: 'غير محدد' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    {{ $supplier->notes_count }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($supplier->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('suppliers.show', $supplier) }}" 
                                                       class="btn btn-sm btn-outline-info" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('suppliers.edit', $supplier) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('suppliers.archive', $supplier) }}" 
                                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد من أرشفة هذا المورد؟')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="أرشفة">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" 
                                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
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
                        <div class="d-flex justify-content-center">
                            {{ $suppliers->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-truck fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد موردين</h4>
                            <p class="text-muted">لم يتم إضافة أي موردين بعد</p>
                            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                إضافة أول مورد
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
