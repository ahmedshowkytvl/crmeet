@extends('layouts.app')

@section('title', __('messages.add_category') . ' - ' . __('messages.system_title'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus me-2"></i>{{ __('messages.add_category') }}</h2>
    <a href="{{ route('contact-categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right me-2"></i>{{ __('messages.back') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('contact-categories.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-language me-2"></i>{{ $trans('arabic_info') }}
                    </h5>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ $trans('name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ $trans('description_ar') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-language me-2"></i>{{ $trans('english_info') }}
                    </h5>
                    
                    <div class="mb-3">
                        <label for="name_en" class="form-label">{{ $trans('name_en') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                               id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                        @error('name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description_en" class="form-label">{{ $trans('description_en') }}</label>
                        <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                  id="description_en" name="description_en" rows="3">{{ old('description_en') }}</textarea>
                        @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-palette me-2"></i>{{ $trans('appearance') }}
                    </h5>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">{{ $trans('color') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', '#007bff') }}" required>
                            <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                   id="color_text" value="{{ old('color', '#007bff') }}" readonly>
                        </div>
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="icon" class="form-label">{{ $trans('icon') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="icon-preview">
                                <i class="fas fa-tag" id="icon-display"></i>
                            </span>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon', 'fas fa-tag') }}" 
                                   placeholder="fas fa-tag" required readonly>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#iconSelectorModal">
                                <i class="fas fa-search"></i> {{ $trans('choose_icon') }}
                            </button>
                        </div>
                        <small class="form-text text-muted">{{ $trans('icon_help') }}</small>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-cog me-2"></i>{{ $trans('settings') }}
                    </h5>
                    
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">{{ $trans('sort_order') }}</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                               id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            {{ $trans('is_active') }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- معاينة التصنيف -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-eye me-2"></i>{{ $trans('preview') }}
                    </h5>
                    
                    <!-- معاينة باللغة الإنجليزية -->
                    <div class="card mb-3" id="category-preview-en">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-flag me-2"></i>{{ $trans('english_preview') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tag me-2" id="preview-icon-en" style="color: #007bff"></i>
                                <h5 class="mb-0" id="preview-name-en">{{ $trans('category_name') }}</h5>
                            </div>
                            <p class="text-muted mb-0 mt-2" id="preview-description-en">{{ $trans('category_description') }}</p>
                        </div>
                    </div>
                    
                    <!-- معاينة باللغة العربية -->
                    <div class="card" id="category-preview-ar" dir="rtl">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-flag me-2"></i>{{ $trans('arabic_preview') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tag me-2" id="preview-icon-ar" style="color: #007bff"></i>
                                <h5 class="mb-0" id="preview-name-ar">{{ $trans('category_name') }}</h5>
                            </div>
                            <p class="text-muted mb-0 mt-2" id="preview-description-ar">{{ $trans('category_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>{{ $trans('create_category') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Icon Selector Modal -->
<div class="modal fade" id="iconSelectorModal" tabindex="-1" aria-labelledby="iconSelectorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="iconSelectorModalLabel">
                    <i class="fas fa-icons me-2"></i>{{ $trans('choose_icon') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="iconSearch" placeholder="{{ $trans('search_icons') }}">
                    </div>
                </div>
                
                <!-- Icon Categories -->
                <div class="mb-3">
                    <label class="form-label">{{ $trans('icon_category') }}:</label>
                    <select class="form-select" id="iconCategory">
                        <option value="">{{ $trans('all_categories') }}</option>
                        <option value="Web Application Icons">Web Application Icons</option>
                        <option value="Form Control Icons">Form Control Icons</option>
                        <option value="Chart Icons">Chart Icons</option>
                        <option value="Payment Icons">Payment Icons</option>
                        <option value="Transportation Icons">Transportation Icons</option>
                        <option value="Hand Icons">Hand Icons</option>
                        <option value="File Type Icons">File Type Icons</option>
                        <option value="Spinner Icons">Spinner Icons</option>
                        <option value="Accessibility Icons">Accessibility Icons</option>
                        <option value="Brand Icons">Brand Icons</option>
                        <option value="Currency Icons">Currency Icons</option>
                        <option value="Gender Icons">Gender Icons</option>
                        <option value="Medical Icons">Medical Icons</option>
                    </select>
                </div>
                
                <!-- Icons Grid -->
                <div class="row" id="iconsGrid" style="max-height: 400px; overflow-y: auto;">
                    <!-- Icons will be loaded here -->
                </div>
                
                <style>
                .icon-item {
                    min-height: 80px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }
                .icon-item i {
                    font-size: 2rem !important;
                    margin-bottom: 0.5rem;
                }
                .icon-item:hover {
                    background-color: #f8f9fa !important;
                    border-color: #007bff !important;
                }
                .icon-item.selected {
                    background-color: #e3f2fd !important;
                    border-color: #2196f3 !important;
                }
                </style>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $trans('cancel') }}</button>
                <button type="button" class="btn btn-primary" id="selectIconBtn" disabled>{{ $trans('select') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// FontAwesome Icons Data will be loaded via AJAX
let fontAwesomeIcons = [
        { name: "home", class: "fa-home", category: "Web Application Icons" },
        { name: "user", class: "fa-user", category: "Web Application Icons" },
        { name: "users", class: "fa-users", category: "Web Application Icons" },
        { name: "envelope", class: "fa-envelope", category: "Web Application Icons" },
        { name: "phone", class: "fa-phone", category: "Web Application Icons" },
        { name: "star", class: "fa-star", category: "Web Application Icons" },
        { name: "heart", class: "fa-heart", category: "Web Application Icons" },
        { name: "cog", class: "fa-cog", category: "Web Application Icons" },
        { name: "search", class: "fa-search", category: "Web Application Icons" },
        { name: "plus", class: "fa-plus", category: "Web Application Icons" },
        { name: "edit", class: "fa-edit", category: "Web Application Icons" },
        { name: "trash", class: "fa-trash", category: "Web Application Icons" },
        { name: "save", class: "fa-save", category: "Web Application Icons" },
        { name: "download", class: "fa-download", category: "Web Application Icons" },
        { name: "upload", class: "fa-upload", category: "Web Application Icons" },
        { name: "calendar", class: "fa-calendar", category: "Web Application Icons" },
        { name: "clock", class: "fa-clock", category: "Web Application Icons" },
        { name: "location", class: "fa-location-arrow", category: "Web Application Icons" },
        { name: "camera", class: "fa-camera", category: "Web Application Icons" },
        { name: "file", class: "fa-file", category: "Web Application Icons" }
    ];

document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color_text');
    const iconInput = document.getElementById('icon');
    const iconDisplay = document.getElementById('icon-display');
    const previewIcon = document.getElementById('preview-icon');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewCard = document.getElementById('category-preview');
    
    // Icon selector elements
    const iconSearch = document.getElementById('iconSearch');
    const iconCategory = document.getElementById('iconCategory');
    const iconsGrid = document.getElementById('iconsGrid');
    const selectIconBtn = document.getElementById('selectIconBtn');
    let selectedIcon = null;
    
    console.log('DOM loaded, elements found:');
    console.log('iconSearch:', iconSearch);
    console.log('iconCategory:', iconCategory);
    console.log('iconsGrid:', iconsGrid);
    console.log('selectIconBtn:', selectIconBtn);

    // تحديث اللون
    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
        previewIcon.style.color = this.value;
        previewCard.style.borderLeftColor = this.value;
        // تحديث المعاينة الإنجليزية
        document.getElementById('preview-icon-en').style.color = this.value;
        // تحديث المعاينة العربية
        document.getElementById('preview-icon-ar').style.color = this.value;
    });

    // تحديث الأيقونة
    iconInput.addEventListener('input', function() {
        const iconClass = this.value || 'fas fa-tag';
        iconDisplay.className = iconClass;
        previewIcon.className = iconClass;
        // تحديث المعاينة الإنجليزية
        document.getElementById('preview-icon-en').className = iconClass;
        // تحديث المعاينة العربية
        document.getElementById('preview-icon-ar').className = iconClass;
    });

    // تحديث الاسم
    nameInput.addEventListener('input', function() {
        previewName.textContent = this.value || '{{ $trans("category_name") }}';
        // تحديث المعاينة الإنجليزية
        document.getElementById('preview-name-en').textContent = this.value || '{{ $trans("category_name") }}';
        // تحديث المعاينة العربية
        document.getElementById('preview-name-ar').textContent = this.value || '{{ $trans("category_name") }}';
    });

    // تحديث الوصف
    descriptionInput.addEventListener('input', function() {
        previewDescription.textContent = this.value || '{{ $trans("category_description") }}';
        // تحديث المعاينة الإنجليزية
        document.getElementById('preview-description-en').textContent = this.value || '{{ $trans("category_description") }}';
        // تحديث المعاينة العربية
        document.getElementById('preview-description-ar').textContent = this.value || '{{ $trans("category_description") }}';
    });

    // تحديث لون الحدود
    previewCard.style.borderLeft = '4px solid #007bff';

    // Load icons when modal opens
    document.getElementById('iconSelectorModal').addEventListener('show.bs.modal', function() {
        console.log('Modal opened, loading icons...');
        loadIcons();
    });


    // Search functionality
    iconSearch.addEventListener('input', function() {
        filterIcons();
    });

    // Category filter
    iconCategory.addEventListener('change', function() {
        filterIcons();
    });

    // Load icons function
    function loadIcons() {
        console.log('Loading icons to grid, count:', fontAwesomeIcons.length);
        if (!iconsGrid) {
            console.error('iconsGrid not found!');
            return;
        }
        iconsGrid.innerHTML = '';
        
        fontAwesomeIcons.forEach(icon => {
            const iconElement = createIconElement(icon);
            iconsGrid.appendChild(iconElement);
        });
        console.log('Icons loaded to grid, children count:', iconsGrid.children.length);
    }

    // Create icon element
    function createIconElement(icon) {
        const col = document.createElement('div');
        col.className = 'col-2 col-md-1 mb-3';
        
        const iconDiv = document.createElement('div');
        iconDiv.className = 'icon-item text-center p-2 border rounded cursor-pointer';
        iconDiv.style.cursor = 'pointer';
        iconDiv.style.transition = 'all 0.2s';
        iconDiv.setAttribute('data-icon-class', `fas ${icon.class}`);
        iconDiv.setAttribute('data-icon-name', icon.name);
        iconDiv.setAttribute('data-icon-category', icon.category);
        
        iconDiv.innerHTML = `
            <i class="fas ${icon.class} fa-2x mb-2" style="display: block;"></i>
            <div class="small text-muted">${icon.name}</div>
        `;
        
        console.log('Creating icon element for:', icon.name, 'class:', icon.class);
        
        // Hover effects
        iconDiv.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
            this.style.borderColor = '#007bff';
        });
        
        iconDiv.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.backgroundColor = '';
                this.style.borderColor = '';
            }
        });
        
        // Click to select
        iconDiv.addEventListener('click', function() {
            // Remove previous selection
            document.querySelectorAll('.icon-item').forEach(item => {
                item.classList.remove('selected');
                item.style.backgroundColor = '';
                item.style.borderColor = '';
            });
            
            // Select current icon
            this.classList.add('selected');
            this.style.backgroundColor = '#e3f2fd';
            this.style.borderColor = '#2196f3';
            
            selectedIcon = this.getAttribute('data-icon-class');
            selectIconBtn.disabled = false;
        });
        
        col.appendChild(iconDiv);
        return col;
    }

    // Filter icons function
    function filterIcons() {
        const searchTerm = iconSearch.value.toLowerCase();
        const selectedCategory = iconCategory.value;
        
        const iconItems = document.querySelectorAll('.icon-item');
        iconItems.forEach(item => {
            const iconName = item.getAttribute('data-icon-name').toLowerCase();
            const iconCategory = item.getAttribute('data-icon-category');
            
            const matchesSearch = iconName.includes(searchTerm);
            const matchesCategory = !selectedCategory || iconCategory === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                item.parentElement.style.display = 'block';
            } else {
                item.parentElement.style.display = 'none';
            }
        });
    }

    // Select icon button
    selectIconBtn.addEventListener('click', function() {
        if (selectedIcon) {
            iconInput.value = selectedIcon;
            iconDisplay.className = selectedIcon;
            previewIcon.className = selectedIcon;
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('iconSelectorModal'));
            modal.hide();
        }
    });
});
</script>
@endpush
