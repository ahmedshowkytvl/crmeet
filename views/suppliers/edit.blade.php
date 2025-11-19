@extends('layouts.app')

@section('title', 'تعديل المورد - ' . $supplier->display_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-edit me-2 text-primary"></i>
                    تعديل المورد
                </h2>
                <div>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i>
                        عرض
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>
                        العودة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">المعلومات الأساسية</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">الاسم بالعربية</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $supplier->name_ar) }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">البريد الإلكتروني</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $supplier->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="website" class="form-label">الموقع الإلكتروني</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website', $supplier->website) }}" placeholder="https://example.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">معلومات الاتصال</h5>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">الهاتف</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="mobile" class="form-label">الجوال</label>
                                    <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                                           id="mobile" name="mobile" value="{{ old('mobile', $supplier->mobile) }}">
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">الشخص المسؤول</label>
                                    <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                           id="contact_person" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
                                    @error('contact_person')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">هاتف الشخص المسؤول</label>
                                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                           id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $supplier->contact_phone) }}">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">بريد الشخص المسؤول</label>
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                           id="contact_email" name="contact_email" value="{{ old('contact_email', $supplier->contact_email) }}">
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Address Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">معلومات العنوان</h5>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">العنوان</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $supplier->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="city" class="form-label">المدينة</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $supplier->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="country" class="form-label">البلد</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                           id="country" name="country" value="{{ old('country', $supplier->country) }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">معلومات إضافية</h5>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="4">{{ old('notes', $supplier->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            نشط
                                        </label>
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
                                        <button type="button" class="btn btn-primary btn-sm" onclick="addNote()">
                                            <i class="fas fa-plus me-1"></i>
                                            إضافة ملاحظة
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="notesContainer">
                                            <!-- Notes will be added here dynamically -->
                                        </div>
                                        <div id="noNotesMessage" class="text-center py-4">
                                            <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">لا توجد ملاحظات</h6>
                                            <p class="text-muted">اضغط "إضافة ملاحظة" لبدء إضافة الملاحظات</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-1"></i>
                                        إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        حفظ التغييرات
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Note Template (Hidden) -->
<div id="noteTemplate" class="note-item mb-3" style="display: none;">
    <div class="card border">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">نوع الملاحظة</label>
                    <select class="form-select note-type" name="notes[INDEX][type]">
                        <option value="general">عامة</option>
                        <option value="follow_up">متابعة</option>
                        <option value="issue">مشكلة</option>
                        <option value="meeting">اجتماع</option>
                        <option value="payment">دفع</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">الملاحظة</label>
                    <textarea class="form-control note-content" name="notes[INDEX][note]" rows="2" placeholder="اكتب ملاحظتك هنا..."></textarea>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeNote(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let noteIndex = 0;

function addNote() {
    const template = document.getElementById('noteTemplate');
    const container = document.getElementById('notesContainer');
    const noNotesMessage = document.getElementById('noNotesMessage');
    
    // Hide no notes message
    noNotesMessage.style.display = 'none';
    
    // Clone template
    const noteElement = template.cloneNode(true);
    noteElement.style.display = 'block';
    noteElement.id = 'note-' + noteIndex;
    
    // Replace INDEX with actual index
    const html = noteElement.innerHTML.replace(/INDEX/g, noteIndex);
    noteElement.innerHTML = html;
    
    // Add to container
    container.appendChild(noteElement);
    
    noteIndex++;
}

function removeNote(button) {
    const noteItem = button.closest('.note-item');
    const container = document.getElementById('notesContainer');
    const noNotesMessage = document.getElementById('noNotesMessage');
    
    noteItem.remove();
    
    // Show no notes message if no notes left
    if (container.children.length === 0) {
        noNotesMessage.style.display = 'block';
    }
}

// Add initial note on page load if needed
document.addEventListener('DOMContentLoaded', function() {
    // You can add an initial note here if needed
    // addNote();
});
</script>
@endpush

@push('styles')
<style>
.note-item {
    transition: all 0.3s ease;
}

.note-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
