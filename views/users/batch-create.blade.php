@extends('layouts.app')

@section('title', 'إضافة الموظفين بالدفعة - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users-plus me-2"></i>إضافة الموظفين بالدفعة</h2>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="row">
    <!-- Instructions Card -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>تعليمات الاستخدام
                </h5>
            </div>
            <div class="card-body">
                <ol class="mb-3">
                    <li>قم بتحميل ملف Excel يحتوي على بيانات الموظفين</li>
                    <li>حدد أي عمود في الملف يتطابق مع أي حقل في النظام</li>
                    <li>اختر القسم والمنصب الافتراضي للموظفين الجدد</li>
                    <li>اضغط على "إضافة الموظفين" لإنشاء الحسابات</li>
                </ol>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('batch.template') }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i>تحميل قالب Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-upload me-2"></i>رفع ملف Excel
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('batch.store') }}" method="POST" enctype="multipart/form-data" id="batchForm">
                    @csrf
                    
                    <!-- File Upload -->
                    <div class="mb-4">
                        <label for="excel_file" class="form-label">ملف Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror" 
                               id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                        <div class="form-text">يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls) وحجمه أقل من 10 ميجابايت</div>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Default Values -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="default_department_id" class="form-label">القسم الافتراضي</label>
                            <select class="form-select @error('default_department_id') is-invalid @enderror" 
                                    id="default_department_id" name="default_department_id">
                                <option value="">اختر القسم</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('default_department_id') == $department->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? $department->name_ar : $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('default_department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="default_role_id" class="form-label">المنصب الافتراضي</label>
                            <select class="form-select @error('default_role_id') is-invalid @enderror" 
                                    id="default_role_id" name="default_role_id">
                                <option value="">اختر المنصب</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('default_role_id') == $role->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'ar' ? $role->name_ar : $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('default_role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Column Mapping -->
                    <div class="mb-4">
                        <h6 class="mb-3">ربط الأعمدة</h6>
                        <div class="row" id="columnMapping">
                            <!-- This will be populated by JavaScript -->
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-users-plus me-2"></i>إضافة الموظفين
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معاينة البيانات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="previewTable">
                        <thead class="table-dark">
                            <tr id="previewHeaders">
                                <!-- Headers will be populated by JavaScript -->
                            </tr>
                        </thead>
                        <tbody id="previewBody">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="confirmUpload()">تأكيد الرفع</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.column-mapping {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.required-field {
    color: #dc3545;
}

.optional-field {
    color: #6c757d;
}

.preview-table {
    max-height: 400px;
    overflow-y: auto;
}

#previewTable th, #previewTable td {
    white-space: nowrap;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let excelData = null;
let headers = [];

// Field definitions
const fieldDefinitions = {
    'name': { label: 'الاسم', required: true, type: 'text' },
    'name_ar': { label: 'الاسم بالعربي', required: false, type: 'text' },
    'email': { label: 'البريد الإلكتروني', required: true, type: 'email' },
    'phone_work': { label: 'هاتف العمل', required: false, type: 'text' },
    'phone_personal': { label: 'الهاتف الشخصي', required: false, type: 'text' },
    'work_email': { label: 'البريد الإلكتروني للعمل', required: false, type: 'email' },
    'job_title': { label: 'المسمى الوظيفي', required: false, type: 'text' },
    'position': { label: 'المنصب', required: false, type: 'text' },
    'position_ar': { label: 'المنصب بالعربي', required: false, type: 'text' },
    'address': { label: 'العنوان', required: false, type: 'text' },
    'address_ar': { label: 'العنوان بالعربي', required: false, type: 'text' },
    'birthday': { label: 'تاريخ الميلاد', required: false, type: 'date' },
    'bio': { label: 'نبذة شخصية', required: false, type: 'text' },
    'notes': { label: 'ملاحظات', required: false, type: 'text' },
    'linkedin_url': { label: 'رابط LinkedIn', required: false, type: 'url' },
    'website_url': { label: 'رابط الموقع', required: false, type: 'url' },
    'avaya_extension': { label: 'رقم AVAYA الداخلي', required: false, type: 'text' },
    'microsoft_teams_id': { label: 'معرف Microsoft Teams', required: false, type: 'email' },
    'office_address': { label: 'عنوان المكتب', required: false, type: 'text' }
};

document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const sheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[sheetName];
                excelData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                
                if (excelData.length > 0) {
                    headers = excelData[0];
                    excelData = excelData.slice(1); // Remove header row
                    generateColumnMapping();
                    showPreview();
                }
            } catch (error) {
                alert('خطأ في قراءة الملف: ' + error.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }
});

function generateColumnMapping() {
    const container = document.getElementById('columnMapping');
    container.innerHTML = '';
    
    Object.keys(fieldDefinitions).forEach(field => {
        const fieldDef = fieldDefinitions[field];
        const div = document.createElement('div');
        div.className = 'col-md-6 mb-3';
        
        div.innerHTML = `
            <label for="column_mapping_${field}" class="form-label">
                ${fieldDef.label}
                ${fieldDef.required ? '<span class="required-field">*</span>' : '<span class="optional-field">(اختياري)</span>'}
            </label>
            <select class="form-select" id="column_mapping_${field}" name="column_mapping[${field}]" ${fieldDef.required ? 'required' : ''}>
                <option value="">اختر العمود</option>
                ${headers.map((header, index) => `<option value="${header}">${header}</option>`).join('')}
            </select>
        `;
        
        container.appendChild(div);
    });
    
    // Add event listeners for validation
    container.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', validateForm);
    });
}

function showPreview() {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    const headersRow = document.getElementById('previewHeaders');
    const body = document.getElementById('previewBody');
    
    // Clear previous content
    headersRow.innerHTML = '';
    body.innerHTML = '';
    
    // Add headers
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        headersRow.appendChild(th);
    });
    
    // Add sample data (first 10 rows)
    const sampleData = excelData.slice(0, 10);
    sampleData.forEach(row => {
        const tr = document.createElement('tr');
        headers.forEach((header, index) => {
            const td = document.createElement('td');
            td.textContent = row[index] || '';
            tr.appendChild(td);
        });
        body.appendChild(tr);
    });
    
    modal.show();
}

function validateForm() {
    const requiredFields = Object.keys(fieldDefinitions).filter(field => fieldDefinitions[field].required);
    const allRequiredFilled = requiredFields.every(field => {
        const select = document.getElementById(`column_mapping_${field}`);
        return select && select.value !== '';
    });
    
    document.getElementById('submitBtn').disabled = !allRequiredFilled;
}

function confirmUpload() {
    // Close modal and submit form
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
    document.getElementById('batchForm').submit();
}

// Initialize form validation
document.addEventListener('DOMContentLoaded', function() {
    validateForm();
});
</script>
@endpush
