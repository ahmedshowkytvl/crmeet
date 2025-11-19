// Excel Uploader Advanced JavaScript

// Global variables
let excelData = null;
let headers = [];
let currentPage = 1;
let itemsPerPage = 10;
let filteredData = [];
let columnMapping = {};
let isDarkMode = localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);

// Field definitions for mapping
const fieldDefinitions = {
    'name': { label: 'الاسم', required: true, type: 'text', icon: 'fas fa-user' },
    'name_ar': { label: 'الاسم بالعربي', required: false, type: 'text', icon: 'fas fa-user' },
    'email': { label: 'البريد الإلكتروني', required: true, type: 'email', icon: 'fas fa-envelope' },
    'phone_work': { label: 'هاتف العمل', required: false, type: 'text', icon: 'fas fa-phone' },
    'phone_personal': { label: 'الهاتف الشخصي', required: false, type: 'text', icon: 'fas fa-mobile-alt' },
    'work_email': { label: 'البريد الإلكتروني للعمل', required: false, type: 'email', icon: 'fas fa-envelope' },
    'job_title': { label: 'المسمى الوظيفي', required: false, type: 'text', icon: 'fas fa-briefcase' },
    'position': { label: 'المنصب', required: false, type: 'text', icon: 'fas fa-briefcase' },
    'position_ar': { label: 'المنصب بالعربي', required: false, type: 'text', icon: 'fas fa-briefcase' },
    'address': { label: 'العنوان', required: false, type: 'text', icon: 'fas fa-map-marker-alt' },
    'address_ar': { label: 'العنوان بالعربي', required: false, type: 'text', icon: 'fas fa-map-marker-alt' },
    'birthday': { label: 'تاريخ الميلاد', required: false, type: 'date', icon: 'fas fa-calendar' },
    'bio': { label: 'نبذة شخصية', required: false, type: 'text', icon: 'fas fa-info-circle' },
    'notes': { label: 'ملاحظات', required: false, type: 'text', icon: 'fas fa-sticky-note' },
    'linkedin_url': { label: 'رابط LinkedIn', required: false, type: 'url', icon: 'fab fa-linkedin' },
    'website_url': { label: 'رابط الموقع', required: false, type: 'url', icon: 'fas fa-globe' },
    'avaya_extension': { label: 'رقم AVAYA الداخلي', required: false, type: 'text', icon: 'fas fa-phone' },
    'microsoft_teams_id': { label: 'معرف Microsoft Teams', required: false, type: 'email', icon: 'fab fa-microsoft' },
    'office_address': { label: 'عنوان المكتب', required: false, type: 'text', icon: 'fas fa-building' }
};

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    initializeUploadZone();
    initializeEventListeners();
    loadStoredData();
});

// Theme management
function initializeTheme() {
    if (isDarkMode) {
        document.documentElement.classList.add('dark');
    }
    
    document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
}

function toggleTheme() {
    isDarkMode = !isDarkMode;
    localStorage.setItem('darkMode', isDarkMode);
    
    if (isDarkMode) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    showToast('تم تغيير المظهر', 'info');
}

// Upload zone management
function initializeUploadZone() {
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('file-input');
    
    if (!uploadZone || !fileInput) return;
    
    // Click to upload
    uploadZone.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    uploadZone.addEventListener('dragover', handleDragOver);
    uploadZone.addEventListener('dragleave', handleDragLeave);
    uploadZone.addEventListener('drop', handleDrop);
    
    // File input change
    fileInput.addEventListener('change', handleFileSelect);
}

function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        processFile(files[0]);
    }
}

function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        processFile(files[0]);
    }
}

// File processing
function processFile(file) {
    if (!file.name.match(/\.(xlsx|xls)$/i)) {
        showToast('يرجى اختيار ملف Excel صحيح', 'error');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        showToast('حجم الملف يجب أن يكون أقل من 10 ميجابايت', 'error');
        return;
    }
    
    showUploadProgress();
    
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
                excelData = excelData.slice(1);
                filteredData = [...excelData];
                
                hideUploadProgress();
                showDataSection();
                renderDataTable();
                generateColumnMapping();
                
                showToast('تم تحميل الملف بنجاح', 'success');
            } else {
                throw new Error('الملف فارغ');
            }
        } catch (error) {
            hideUploadProgress();
            showToast('خطأ في قراءة الملف: ' + error.message, 'error');
            console.error('File processing error:', error);
        }
    };
    
    reader.readAsArrayBuffer(file);
}

function showUploadProgress() {
    const uploadContent = document.getElementById('upload-content');
    const uploadProgress = document.getElementById('upload-progress');
    
    if (uploadContent) uploadContent.classList.add('hidden');
    if (uploadProgress) uploadProgress.classList.remove('hidden');
    
    // Simulate progress
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        const progressBar = document.getElementById('progress-bar');
        if (progressBar) progressBar.style.width = progress + '%';
    }, 100);
    
    setTimeout(() => {
        clearInterval(interval);
        const progressBar = document.getElementById('progress-bar');
        if (progressBar) progressBar.style.width = '100%';
    }, 1000);
}

function hideUploadProgress() {
    const uploadContent = document.getElementById('upload-content');
    const uploadProgress = document.getElementById('upload-progress');
    
    if (uploadContent) uploadContent.classList.remove('hidden');
    if (uploadProgress) uploadProgress.classList.add('hidden');
    
    const progressBar = document.getElementById('progress-bar');
    if (progressBar) progressBar.style.width = '0%';
}

// Data section management
function showDataSection() {
    const dataSection = document.getElementById('data-section');
    const saveSection = document.getElementById('save-section');
    
    if (dataSection) dataSection.classList.remove('hidden');
    if (saveSection) saveSection.classList.remove('hidden');
}

function renderDataTable() {
    const tableHeaders = document.getElementById('table-headers');
    const tableBody = document.getElementById('table-body');
    
    if (!tableHeaders || !tableBody) return;
    
    // Clear existing content
    tableHeaders.innerHTML = '';
    tableBody.innerHTML = '';
    
    // Create headers
    headers.forEach((header, index) => {
        const th = document.createElement('th');
        th.className = 'px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600';
        th.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${header}</span>
                <i class="fas fa-sort text-gray-400"></i>
            </div>
        `;
        th.addEventListener('click', () => sortTable(index));
        tableHeaders.appendChild(th);
    });
    
    // Create data rows
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = filteredData.slice(startIndex, endIndex);
    
    pageData.forEach((row, rowIndex) => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';
        
        headers.forEach((header, colIndex) => {
            const td = document.createElement('td');
            td.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white';
            td.textContent = row[colIndex] || '';
            tr.appendChild(td);
        });
        
        tableBody.appendChild(tr);
    });
    
    renderPagination();
}

function sortTable(columnIndex) {
    filteredData.sort((a, b) => {
        const aVal = a[columnIndex] || '';
        const bVal = b[columnIndex] || '';
        return aVal.toString().localeCompare(bVal.toString());
    });
    
    renderDataTable();
}

function renderPagination() {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;
    
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = `
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                عرض ${(currentPage - 1) * itemsPerPage + 1} إلى ${Math.min(currentPage * itemsPerPage, filteredData.length)} من ${filteredData.length} نتيجة
            </div>
            <div class="flex space-x-2">
    `;
    
    // Previous button
    if (currentPage > 1) {
        paginationHTML += `<button onclick="changePage(${currentPage - 1})" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">السابق</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            paginationHTML += `<button class="px-3 py-1 bg-blue-600 text-white rounded text-sm">${i}</button>`;
        } else {
            paginationHTML += `<button onclick="changePage(${i})" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">${i}</button>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHTML += `<button onclick="changePage(${currentPage + 1})" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">التالي</button>`;
    }
    
    paginationHTML += `
            </div>
        </div>
    `;
    
    pagination.innerHTML = paginationHTML;
}

function changePage(page) {
    currentPage = page;
    renderDataTable();
}

// Search functionality
function initializeEventListeners() {
    const searchInput = document.getElementById('search-input');
    const toggleMapping = document.getElementById('toggle-mapping');
    const cancelMapping = document.getElementById('cancel-mapping');
    const saveMapping = document.getElementById('save-mapping');
    const cancelSave = document.getElementById('cancel-save');
    const clearData = document.getElementById('clear-data');
    
    if (searchInput) searchInput.addEventListener('input', handleSearch);
    if (toggleMapping) toggleMapping.addEventListener('click', toggleMapping);
    if (cancelMapping) cancelMapping.addEventListener('click', cancelMapping);
    if (saveMapping) saveMapping.addEventListener('click', saveMapping);
    if (cancelSave) cancelSave.addEventListener('click', cancelSave);
    if (clearData) clearData.addEventListener('click', clearData);
}

function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    if (searchTerm === '') {
        filteredData = [...excelData];
    } else {
        filteredData = excelData.filter(row => 
            row.some(cell => 
                cell && cell.toString().toLowerCase().includes(searchTerm)
            )
        );
    }
    
    currentPage = 1;
    renderDataTable();
}

// Column mapping
function generateColumnMapping() {
    const mappingFields = document.getElementById('mapping-fields');
    if (!mappingFields) return;
    
    mappingFields.innerHTML = '';
    
    Object.keys(fieldDefinitions).forEach(field => {
        const fieldDef = fieldDefinitions[field];
        const div = document.createElement('div');
        div.className = 'mapping-field';
        div.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="${fieldDef.icon} ml-2"></i>
                    ${fieldDef.label}
                    ${fieldDef.required ? '<span class="text-red-500">*</span>' : ''}
                </label>
                <span class="text-xs text-gray-500 dark:text-gray-400">${fieldDef.required ? 'مطلوب' : 'اختياري'}</span>
            </div>
            <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" data-field="${field}">
                <option value="">اختر العمود</option>
                ${headers.map((header, index) => `<option value="${header}">${header}</option>`).join('')}
            </select>
        `;
        
        // Load saved mapping
        if (columnMapping[field]) {
            div.querySelector('select').value = columnMapping[field];
            div.classList.add('mapped');
        }
        
        // Add change listener
        div.querySelector('select').addEventListener('change', (e) => {
            if (e.target.value) {
                div.classList.add('mapped');
                columnMapping[field] = e.target.value;
            } else {
                div.classList.remove('mapped');
                delete columnMapping[field];
            }
            saveMappingToStorage();
        });
        
        mappingFields.appendChild(div);
    });
}

function toggleMapping() {
    const mappingSection = document.getElementById('mapping-section');
    if (mappingSection) {
        mappingSection.classList.toggle('hidden');
    }
}

function cancelMapping() {
    const mappingSection = document.getElementById('mapping-section');
    if (mappingSection) {
        mappingSection.classList.add('hidden');
    }
}

function saveMapping() {
    // Validate required fields
    const requiredFields = Object.keys(fieldDefinitions).filter(field => fieldDefinitions[field].required);
    const missingFields = requiredFields.filter(field => !columnMapping[field]);
    
    if (missingFields.length > 0) {
        showToast('يرجى ربط الحقول المطلوبة', 'error');
        return;
    }
    
    const mappingSection = document.getElementById('mapping-section');
    if (mappingSection) {
        mappingSection.classList.add('hidden');
    }
    
    showToast('تم حفظ ربط الأعمدة بنجاح', 'success');
}

function cancelSave() {
    const saveSection = document.getElementById('save-section');
    if (saveSection) {
        saveSection.classList.add('hidden');
    }
}

function clearData() {
    if (confirm('هل أنت متأكد من مسح جميع البيانات؟')) {
        excelData = null;
        headers = [];
        filteredData = [];
        columnMapping = {};
        currentPage = 1;
        
        const dataSection = document.getElementById('data-section');
        const mappingSection = document.getElementById('mapping-section');
        const saveSection = document.getElementById('save-section');
        const fileInput = document.getElementById('file-input');
        
        if (dataSection) dataSection.classList.add('hidden');
        if (mappingSection) mappingSection.classList.add('hidden');
        if (saveSection) saveSection.classList.add('hidden');
        if (fileInput) fileInput.value = '';
        
        localStorage.removeItem('excelData');
        localStorage.removeItem('columnMapping');
        
        showToast('تم مسح البيانات', 'success');
    }
}

// Storage management
function saveMappingToStorage() {
    localStorage.setItem('columnMapping', JSON.stringify(columnMapping));
}

function loadStoredData() {
    const storedMapping = localStorage.getItem('columnMapping');
    if (storedMapping) {
        try {
            columnMapping = JSON.parse(storedMapping);
        } catch (error) {
            console.error('Error loading stored mapping:', error);
            columnMapping = {};
        }
    }
}

// Toast notifications
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    toast.className = `toast ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 max-w-sm`;
    toast.innerHTML = `
        <i class="${icons[type]}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto remove
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    const saveForm = document.getElementById('save-form');
    if (saveForm) {
        saveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!excelData || excelData.length === 0) {
                showToast('لا توجد بيانات لحفظها', 'error');
                return;
            }
            
            // Prepare mapped data
            const mappedData = excelData.map(row => {
                const mappedRow = {};
                Object.keys(columnMapping).forEach(field => {
                    const columnName = columnMapping[field];
                    const columnIndex = headers.indexOf(columnName);
                    mappedRow[field] = row[columnIndex] || '';
                });
                return mappedRow;
            });
            
            // Set form data
            const mappedDataInput = document.getElementById('mapped-data');
            const columnMappingInput = document.getElementById('column-mapping');
            
            if (mappedDataInput) mappedDataInput.value = JSON.stringify(mappedData);
            if (columnMappingInput) columnMappingInput.value = JSON.stringify(columnMapping);
            
            // Submit form
            this.submit();
        });
    }
});

// Export functions for global access
window.changePage = changePage;
window.toggleMapping = toggleMapping;
window.cancelMapping = cancelMapping;
window.saveMapping = saveMapping;
window.cancelSave = cancelSave;
window.clearData = clearData;

// Enhanced visual effects
function addLoadingAnimations() {
    // Add loading animation to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التحميل...';
                
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.innerHTML = originalText;
                }, 2000);
            }
        });
    });
}

function initializeTooltips() {
    // Add tooltips to mapping fields
    const mappingFields = document.querySelectorAll('.mapping-field');
    mappingFields.forEach(field => {
        const label = field.querySelector('label');
        if (label) {
            field.setAttribute('title', `ربط ${label.textContent.trim()} مع عمود Excel`);
        }
    });
}

// Enhanced file upload with better visual feedback
function enhanceFileUpload() {
    const uploadZone = document.getElementById('upload-zone');
    const fileInput = document.getElementById('file-input');
    
    if (uploadZone && fileInput) {
        // Add ripple effect on click
        uploadZone.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }
}

// Add ripple effect CSS
const rippleCSS = `
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}
`;

// Inject ripple CSS
const style = document.createElement('style');
style.textContent = rippleCSS;
document.head.appendChild(style);

// Initialize enhanced features
document.addEventListener('DOMContentLoaded', function() {
    enhanceFileUpload();
    addLoadingAnimations();
    initializeTooltips();
    
    // Add fade-in animations to sections
    const sections = document.querySelectorAll('[class*="fade-in-up"]');
    sections.forEach((section, index) => {
        section.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Add hover effects to cards
    const cards = document.querySelectorAll('[class*="hover-lift"]');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
