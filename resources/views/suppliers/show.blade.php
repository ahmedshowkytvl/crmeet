@extends('layouts.app')

@section('title', 'تفاصيل المورد - ' . $supplier->display_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-truck me-2 text-primary"></i>
                    تفاصيل المورد
                </h2>
                <div>
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>
                        تعديل
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Supplier Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات المورد
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>الاسم:</strong>
                                <p class="mb-0">{{ $supplier->display_name }}</p>
                                @if($supplier->name_ar)
                                    <small class="text-muted">{{ $supplier->name_ar }}</small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>البريد الإلكتروني:</strong>
                                <p class="mb-0">
                                    @if($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}" class="text-decoration-none">
                                            {{ $supplier->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <strong>الهاتف:</strong>
                                <p class="mb-0">
                                    @if($supplier->phone)
                                        <a href="tel:{{ $supplier->phone }}" class="text-decoration-none">
                                            {{ $supplier->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <strong>الجوال:</strong>
                                <p class="mb-0">
                                    @if($supplier->mobile)
                                        <a href="tel:{{ $supplier->mobile }}" class="text-decoration-none">
                                            {{ $supplier->mobile }}
                                        </a>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>الموقع الإلكتروني:</strong>
                                <p class="mb-0">
                                    @if($supplier->website)
                                        <a href="{{ $supplier->website }}" target="_blank" class="text-decoration-none">
                                            {{ $supplier->website }}
                                            <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <strong>المدينة:</strong>
                                <p class="mb-0">{{ $supplier->city ?: 'غير محدد' }}</p>
                            </div>

                            <div class="mb-3">
                                <strong>البلد:</strong>
                                <p class="mb-0">{{ $supplier->country ?: 'غير محدد' }}</p>
                            </div>

                            <div class="mb-3">
                                <strong>الحالة:</strong>
                                <p class="mb-0">
                                    @if($supplier->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($supplier->address)
                        <div class="mb-3">
                            <strong>العنوان:</strong>
                            <p class="mb-0">{{ $supplier->address }}</p>
                        </div>
                    @endif

                    @if($supplier->notes)
                        <div class="mb-3">
                            <strong>ملاحظات:</strong>
                            <p class="mb-0">{{ $supplier->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Person Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        الشخص المسؤول
                    </h5>
                </div>
                <div class="card-body">
                    @if($supplier->contact_person)
                        <div class="mb-3">
                            <strong>الاسم:</strong>
                            <p class="mb-0">{{ $supplier->contact_person }}</p>
                        </div>
                    @endif

                    @if($supplier->contact_phone)
                        <div class="mb-3">
                            <strong>الهاتف:</strong>
                            <p class="mb-0">
                                <a href="tel:{{ $supplier->contact_phone }}" class="text-decoration-none">
                                    {{ $supplier->contact_phone }}
                                </a>
                            </p>
                        </div>
                    @endif

                    @if($supplier->contact_email)
                        <div class="mb-3">
                            <strong>البريد الإلكتروني:</strong>
                            <p class="mb-0">
                                <a href="mailto:{{ $supplier->contact_email }}" class="text-decoration-none">
                                    {{ $supplier->contact_email }}
                                </a>
                            </p>
                        </div>
                    @endif

                    @if(!$supplier->contact_person && !$supplier->contact_phone && !$supplier->contact_email)
                        <p class="text-muted text-center">لا توجد معلومات عن الشخص المسؤول</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        الإجراءات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>
                            تعديل المورد
                        </a>
                        
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" 
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المورد؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                حذف المورد
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-sticky-note me-2"></i>
                        ملاحظات المورد
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                        <i class="fas fa-plus me-1"></i>
                        إضافة ملاحظة
                    </button>
                </div>
                <div class="card-body">
                    @php
                        $notes = $supplier->supplierNotes;
                    @endphp
                    @if($notes->isNotEmpty())
                        <div class="timeline">
                            @foreach($notes as $note)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <i class="fas fa-circle text-primary"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-{{ $note->type == 'general' ? 'secondary' : ($note->type == 'follow_up' ? 'warning' : ($note->type == 'issue' ? 'danger' : 'info')) }} me-2">
                                                        {{ $note->type == 'general' ? 'عامة' : ($note->type == 'follow_up' ? 'متابعة' : ($note->type == 'issue' ? 'مشكلة' : ($note->type == 'meeting' ? 'اجتماع' : 'دفع'))) }}
                                                    </span>
                                                    <small class="text-muted">{{ $note->formatted_date }}</small>
                                                </div>
                                                <p class="mb-2">{{ $note->note }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $note->user->name }}
                                                </small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editNote({{ $note->id }}, '{{ $note->note }}', '{{ $note->type }}')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteNote({{ $note->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">لا توجد ملاحظات</h6>
                            <p class="text-muted">ابدأ بإضافة ملاحظة جديدة للمورد</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">إضافة ملاحظة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('suppliers.notes.store', $supplier) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note_type" class="form-label">نوع الملاحظة</label>
                        <select class="form-select" id="note_type" name="type">
                            <option value="general">عامة</option>
                            <option value="follow_up">متابعة</option>
                            <option value="issue">مشكلة</option>
                            <option value="meeting">اجتماع</option>
                            <option value="payment">دفع</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note_content" class="form-label">الملاحظة</label>
                        <textarea class="form-control" id="note_content" name="note" rows="4" required placeholder="اكتب ملاحظتك هنا..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة الملاحظة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNoteModalLabel">تعديل الملاحظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNoteForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_note_type" class="form-label">نوع الملاحظة</label>
                        <select class="form-select" id="edit_note_type" name="type">
                            <option value="general">عامة</option>
                            <option value="follow_up">متابعة</option>
                            <option value="issue">مشكلة</option>
                            <option value="meeting">اجتماع</option>
                            <option value="payment">دفع</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_note_content" class="form-label">الملاحظة</label>
                        <textarea class="form-control" id="edit_note_content" name="note" rows="4" required></textarea>
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

<!-- Delete Note Modal -->
<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNoteModalLabel">حذف الملاحظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف هذه الملاحظة؟ هذا الإجراء لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteNoteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 20px;
    height: 20px;
    background: #fff;
    border: 3px solid #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #007bff;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: -21px;
    top: 25px;
    width: 2px;
    height: calc(100% + 15px);
    background: #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
function editNote(noteId, noteContent, noteType) {
    document.getElementById('editNoteForm').action = `/supplier-notes/${noteId}`;
    document.getElementById('edit_note_content').value = noteContent;
    document.getElementById('edit_note_type').value = noteType;
    
    const editModal = new bootstrap.Modal(document.getElementById('editNoteModal'));
    editModal.show();
}

function deleteNote(noteId) {
    document.getElementById('deleteNoteForm').action = `/supplier-notes/${noteId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteNoteModal'));
    deleteModal.show();
}
</script>
@endpush
