@extends('layouts.app')

@section('title', __('messages.edit_asset'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-edit me-2 text-primary"></i>
                    {{ __('messages.edit_asset') }}
                </h2>
                <a href="{{ route('assets.assets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('messages.back_to_assets') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('messages.edit_asset') }}: {{ $asset->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('assets.assets.update', $asset) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('messages.basic_information') }}
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Asset Code -->
                            <div class="col-md-6 mb-3">
                                <label for="asset_code" class="form-label">{{ __('messages.asset_code') }}</label>
                                <input type="text" class="form-control" id="asset_code" name="asset_code" 
                                       value="{{ old('asset_code', $asset->asset_code) }}" readonly>
                                @error('asset_code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Asset Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">{{ __('messages.asset_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ old('name', $asset->name) }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Asset Name (Arabic) -->
                            <div class="col-md-6 mb-3">
                                <label for="name_ar" class="form-label">{{ __('messages.asset_name_ar') }}</label>
                                <input type="text" class="form-control" id="name_ar" name="name_ar" 
                                       value="{{ old('name_ar', $asset->name_ar) }}" dir="rtl">
                                @error('name_ar')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Serial Number -->
                            <div class="col-md-6 mb-3">
                                <label for="serial_number" class="form-label">{{ __('messages.serial_number') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                       value="{{ old('serial_number', $asset->serial_number) }}" required>
                                @error('serial_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">{{ __('messages.select_status') }}</option>
                                    <option value="active" {{ old('status', $asset->status) == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                    <option value="inactive" {{ old('status', $asset->status) == 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                    <option value="maintenance" {{ old('status', $asset->status) == 'maintenance' ? 'selected' : '' }}>{{ __('messages.maintenance') }}</option>
                                    <option value="disposed" {{ old('status', $asset->status) == 'disposed' ? 'selected' : '' }}>{{ __('messages.disposed') }}</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    {{ __('messages.additional_information') }}
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Model -->
                            <div class="col-md-6 mb-3">
                                <label for="model" class="form-label">{{ __('messages.model') }}</label>
                                <input type="text" class="form-control" id="model" name="model" 
                                       value="{{ old('model', $asset->model) }}">
                                @error('model')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Brand -->
                            <div class="col-md-6 mb-3">
                                <label for="brand" class="form-label">{{ __('messages.brand') }}</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="{{ old('brand', $asset->brand) }}">
                                @error('brand')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Purchase Price -->
                            <div class="col-md-6 mb-3">
                                <label for="purchase_price" class="form-label">{{ __('messages.purchase_price') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="purchase_price" name="purchase_price" 
                                           value="{{ old('purchase_price', $asset->purchase_price) }}" step="0.01" min="0">
                                    <span class="input-group-text">{{ __('messages.egp') }}</span>
                                </div>
                                @error('purchase_price')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Purchase Date -->
                            <div class="col-md-6 mb-3">
                                <label for="purchase_date" class="form-label">{{ __('messages.purchase_date') }}</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                       value="{{ old('purchase_date', $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '') }}">
                                @error('purchase_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Warranty Expiry -->
                            <div class="col-md-6 mb-3">
                                <label for="warranty_expiry" class="form-label">{{ __('messages.warranty_expiry') }}</label>
                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry" 
                                       value="{{ old('warranty_expiry', $asset->warranty_expiry ? $asset->warranty_expiry->format('Y-m-d') : '') }}">
                                @error('warranty_expiry')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="col-md-6 mb-3">
                                <label for="location_id" class="form-label">{{ __('messages.location') }}</label>
                                <select class="form-select" id="location_id" name="location_id">
                                    <option value="">{{ __('messages.select_location') }}</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id', $asset->location_id) == $location->id ? 'selected' : '' }}>
                                            {{ $location->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Warehouse -->
                            <div class="col-md-6 mb-3">
                                <label for="warehouse_id" class="form-label">{{ __('messages.warehouse') }}</label>
                                <select class="form-select" id="warehouse_id" name="warehouse_id">
                                    <option value="">{{ __('messages.select_warehouse') }}</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $asset->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Assigned To -->
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">{{ __('messages.assigned_to') }}</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">{{ __('messages.unassigned') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $asset->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Inventory Status -->
                            <div class="col-md-6 mb-3">
                                <label for="inventory_status" class="form-label">{{ __('messages.inventory_status') }}</label>
                                <select class="form-select" id="inventory_status" name="inventory_status">
                                    <option value="">{{ __('messages.select_inventory_status') }}</option>
                                    <option value="in_stock" {{ old('inventory_status', $asset->inventory_status) == 'in_stock' ? 'selected' : '' }}>{{ __('messages.inventory_statuses.in_stock') }}</option>
                                    <option value="out_of_stock" {{ old('inventory_status', $asset->inventory_status) == 'out_of_stock' ? 'selected' : '' }}>{{ __('messages.inventory_statuses.out_of_stock') }}</option>
                                    <option value="low_stock" {{ old('inventory_status', $asset->inventory_status) == 'low_stock' ? 'selected' : '' }}>{{ __('messages.inventory_statuses.low_stock') }}</option>
                                    <option value="reserved" {{ old('inventory_status', $asset->inventory_status) == 'reserved' ? 'selected' : '' }}>{{ __('messages.inventory_statuses.reserved') }}</option>
                                    <option value="damaged" {{ old('inventory_status', $asset->inventory_status) == 'damaged' ? 'selected' : '' }}>{{ __('messages.inventory_statuses.damaged') }}</option>
                                    <option value="expired" {{ old('inventory_status', $asset->inventory_status) == 'expired' ? 'selected' : '' }}>{{ __('messages.inventory_statuses.expired') }}</option>
                                </select>
                                @error('inventory_status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-align-left me-2"></i>
                                    {{ __('messages.description') }}
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Description (English) -->
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">{{ __('messages.description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $asset->description) }}</textarea>
                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description (Arabic) -->
                            <div class="col-md-6 mb-3">
                                <label for="description_ar" class="form-label">{{ __('messages.description_ar') }}</label>
                                <textarea class="form-control" id="description_ar" name="description_ar" rows="4" dir="rtl">{{ old('description_ar', $asset->description_ar) }}</textarea>
                                @error('description_ar')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Property Values -->
                        @if($asset->category && $asset->category->properties->count() > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-cogs me-2"></i>
                                        {{ __('messages.properties') }}
                                    </h6>
                                </div>
                            </div>

                            <div class="row">
                                @foreach($asset->category->properties as $property)
                                    <div class="col-md-6 mb-3">
                                        <label for="property_{{ $property->id }}" class="form-label">
                                            {{ $property->display_name }}
                                            @if($property->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        
                                        @php
                                            $currentValue = $asset->propertyValues->where('property_id', $property->id)->first()?->value ?? '';
                                        @endphp
                                        
                                        @if($property->type === 'text')
                                            <input type="text" class="form-control" id="property_{{ $property->id }}" 
                                                   name="properties[{{ $property->id }}]" value="{{ old('properties.' . $property->id, $currentValue) }}"
                                                   {{ $property->is_required ? 'required' : '' }}>
                                        @elseif($property->type === 'number')
                                            <input type="number" class="form-control" id="property_{{ $property->id }}" 
                                                   name="properties[{{ $property->id }}]" value="{{ old('properties.' . $property->id, $currentValue) }}"
                                                   {{ $property->is_required ? 'required' : '' }}>
                                        @elseif($property->type === 'date')
                                            <input type="date" class="form-control" id="property_{{ $property->id }}" 
                                                   name="properties[{{ $property->id }}]" value="{{ old('properties.' . $property->id, $currentValue) }}"
                                                   {{ $property->is_required ? 'required' : '' }}>
                                        @elseif($property->type === 'select')
                                            <select class="form-select" id="property_{{ $property->id }}" 
                                                    name="properties[{{ $property->id }}]" {{ $property->is_required ? 'required' : '' }}>
                                                <option value="">{{ __('messages.select_option') }}</option>
                                                @foreach(explode(',', $property->options) as $option)
                                                    @php $option = trim($option); @endphp
                                                    <option value="{{ $option }}" {{ old('properties.' . $property->id, $currentValue) == $option ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($property->type === 'boolean')
                                            <select class="form-select" id="property_{{ $property->id }}" 
                                                    name="properties[{{ $property->id }}]" {{ $property->is_required ? 'required' : '' }}>
                                                <option value="">{{ __('messages.select_option') }}</option>
                                                <option value="1" {{ old('properties.' . $property->id, $currentValue) == '1' ? 'selected' : '' }}>{{ __('messages.yes') }}</option>
                                                <option value="0" {{ old('properties.' . $property->id, $currentValue) == '0' ? 'selected' : '' }}>{{ __('messages.no') }}</option>
                                            </select>
                                        @endif
                                        
                                        @error('properties.' . $property->id)
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('assets.assets.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('messages.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('messages.save') }}
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('{{ __("messages.required_field") }}');
        }
    });
    
    // Auto-generate asset code if empty
    const assetCodeField = document.getElementById('asset_code');
    const nameField = document.getElementById('name');
    
    if (!assetCodeField.value && nameField.value) {
        assetCodeField.value = nameField.value.replace(/\s+/g, '_').toUpperCase();
    }
    
    nameField.addEventListener('input', function() {
        if (!assetCodeField.value) {
            assetCodeField.value = this.value.replace(/\s+/g, '_').toUpperCase();
        }
    });
});
</script>
@endpush










