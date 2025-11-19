@extends('layouts.app')

@section('title', __('messages.add_asset'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-plus me-2 text-primary"></i>
                    {{ __('messages.add_asset') }}
                </h2>
                <a href="{{ route('assets.assets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('messages.back_to_assets') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('assets.assets.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('messages.basic_information') }}
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.asset_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('messages.asset_name_ar') }}</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar') }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id" required onchange="loadCategoryProperties()">
                                        <option value="">{{ __('messages.select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="serial_number" class="form-label">{{ __('messages.serial_number') }}</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror" 
                                           id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
                                    @error('serial_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">{{ __('messages.description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description_ar" class="form-label">{{ __('messages.description_ar') }}</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" name="description_ar" rows="3">{{ old('description_ar') }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-cog me-2"></i>
                                    {{ __('messages.additional_information') }}
                                </h5>

                                <div class="mb-3">
                                    <label for="purchase_date" class="form-label">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                        {{ __('messages.purchase_date') }}
                                    </label>
                                    <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                           id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}"
                                           data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy">
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ __('messages.purchase_date_help') }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="warranty_expiry" class="form-label">
                                        <i class="fas fa-calendar-check me-2 text-warning"></i>
                                        {{ __('messages.warranty_expiry') }}
                                    </label>
                                    <input type="date" class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                           id="warranty_expiry" name="warranty_expiry" value="{{ old('warranty_expiry') }}"
                                           data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy">
                                    @error('warranty_expiry')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ __('messages.warranty_expiry_help') }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cost" class="form-label">{{ __('messages.cost') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('cost') is-invalid @enderror" 
                                               id="cost" name="cost" value="{{ old('cost') }}" step="0.01" min="0">
                                    </div>
                                    @error('cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="store_code" class="form-label">{{ __('messages.store_code') }}</label>
                                    <input type="text" class="form-control @error('store_code') is-invalid @enderror" 
                                           id="store_code" name="store_code" value="{{ old('store_code') }}">
                                    @error('store_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="price" class="form-label">{{ __('messages.price') }}</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price') }}" step="0.01" min="0">
                                        <select class="form-select @error('currency') is-invalid @enderror" 
                                                id="currency" name="currency" style="max-width: 120px;">
                                            @foreach(__('messages.currencies') as $key => $value)
                                                <option value="{{ $key }}" {{ old('currency', 'EGP') == $key ? 'selected' : '' }}>
                                                    {{ $key }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                            {{ __('messages.active') }}
                                        </option>
                                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>
                                            {{ __('messages.maintenance') }}
                                        </option>
                                        <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>
                                            {{ __('messages.retired') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="location_id" class="form-label">{{ __('messages.location') }}</label>
                                    <select class="form-select @error('location_id') is-invalid @enderror" 
                                            id="location_id" name="location_id">
                                        <option value="">{{ __('messages.select_location') }}</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        اختياري - يمكن تحديد الموقع لاحقاً
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="warehouse_id" class="form-label">{{ __('messages.warehouse') }}</label>
                                    <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" name="warehouse_id">
                                        <option value="">{{ __('messages.select_warehouse') }}</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="inventory_status" class="form-label">{{ __('messages.inventory_status') }}</label>
                                    <select class="form-select @error('inventory_status') is-invalid @enderror" 
                                            id="inventory_status" name="inventory_status">
                                        @foreach(__('messages.inventory_statuses') as $key => $value)
                                            <option value="{{ $key }}" {{ old('inventory_status', 'in_stock') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('inventory_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="quantity" class="form-label">{{ __('messages.quantity') }}</label>
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1">
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">{{ __('messages.assigned_to') }}</label>
                                    <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                            id="assigned_to" name="assigned_to">
                                        <option value="">{{ __('messages.unassigned') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Category Properties -->
                        <div class="row mt-4" id="category-properties" style="display: none;">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-list me-2"></i>
                                    {{ __('messages.category_properties') }}
                                </h5>
                                <div id="properties-container">
                                    <!-- Properties will be loaded here via JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('assets.assets.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('messages.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('messages.create_asset') }}
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
@endsection

@section('scripts')
<script>
function loadCategoryProperties() {
    const categoryId = document.getElementById('category_id').value;
    const propertiesContainer = document.getElementById('properties-container');
    const categoryPropertiesDiv = document.getElementById('category-properties');
    
    if (!categoryId) {
        categoryPropertiesDiv.style.display = 'none';
        return;
    }
    
    // Show loading
    propertiesContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> {{ __("messages.loading") }}...</div>';
    categoryPropertiesDiv.style.display = 'block';
    
    // Fetch category properties
    fetch(`/assets/categories/${categoryId}/properties`)
        .then(response => response.json())
        .then(properties => {
            if (properties.length === 0) {
                propertiesContainer.innerHTML = '<div class="text-muted">{{ __("messages.no_properties_defined") }}</div>';
                return;
            }
            
            let html = '';
            properties.forEach(property => {
                html += `<div class="mb-3">`;
                html += `<label for="property_${property.id}" class="form-label">${property.display_name}`;
                if (property.is_required) {
                    html += ` <span class="text-danger">*</span>`;
                }
                html += `</label>`;
                
                switch(property.type) {
                    case 'text':
                        html += `<input type="text" class="form-control" id="property_${property.id}" name="properties[${property.id}]" value="${property.value || ''}" ${property.is_required ? 'required' : ''}>`;
                        break;
                    case 'number':
                        html += `<input type="number" class="form-control" id="property_${property.id}" name="properties[${property.id}]" value="${property.value || ''}" ${property.is_required ? 'required' : ''}>`;
                        break;
                    case 'date':
                        html += `<input type="date" class="form-control custom-date-input" id="property_${property.id}" name="properties[${property.id}]" value="${property.value || ''}" ${property.is_required ? 'required' : ''} data-format="dd/mm/yyyy" placeholder="dd/mm/yyyy">`;
                        break;
                    case 'boolean':
                        html += `<select class="form-select" id="property_${property.id}" name="properties[${property.id}]" ${property.is_required ? 'required' : ''}>`;
                        html += `<option value="0" ${property.value == '0' ? 'selected' : ''}>{{ __("messages.no") }}</option>`;
                        html += `<option value="1" ${property.value == '1' ? 'selected' : ''}>{{ __("messages.yes") }}</option>`;
                        html += `</select>`;
                        break;
                    case 'select':
                        html += `<select class="form-select" id="property_${property.id}" name="properties[${property.id}]" ${property.is_required ? 'required' : ''}>`;
                        html += `<option value="">{{ __("messages.select_option") }}</option>`;
                        if (property.options) {
                            property.options.forEach(option => {
                                html += `<option value="${option}" ${property.value == option ? 'selected' : ''}>${option}</option>`;
                            });
                        }
                        html += `</select>`;
                        break;
                    case 'image':
                        html += `<input type="file" class="form-control" id="property_${property.id}" name="properties[${property.id}]" accept="image/*" ${property.is_required ? 'required' : ''}>`;
                        break;
                }
                html += `</div>`;
            });
            
            propertiesContainer.innerHTML = html;
            
            // Setup date pickers for dynamically loaded properties
            setTimeout(() => {
                if (window.setupSingleDatePicker) {
                    const dateInputs = propertiesContainer.querySelectorAll('input[type="date"]');
                    dateInputs.forEach(input => {
                        window.setupSingleDatePicker(input);
                    });
                }
            }, 100);
        })
        .catch(error => {
            console.error('Error:', error);
            propertiesContainer.innerHTML = '<div class="text-danger">{{ __("messages.error_loading_properties") }}</div>';
        });
}

// Load properties on page load if category is already selected
document.addEventListener('DOMContentLoaded', function() {
    const categoryId = document.getElementById('category_id').value;
    if (categoryId) {
        loadCategoryProperties();
    }
    
    // Setup date pickers for dynamically loaded properties
    setTimeout(() => {
        if (window.reinitializeDatePickers) {
            window.reinitializeDatePickers();
        }
    }, 100);
});

// Override the loadCategoryProperties function to setup date pickers after loading
const originalLoadCategoryProperties = loadCategoryProperties;
loadCategoryProperties = function() {
    originalLoadCategoryProperties();
    // Setup date pickers for dynamically loaded properties
    setTimeout(() => {
        if (window.reinitializeDatePickers) {
            window.reinitializeDatePickers();
        }
    }, 100);
};
</script>
@endsection

