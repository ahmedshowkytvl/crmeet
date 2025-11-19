@extends('layouts.app')

@section('title', __('messages.add_category'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-plus me-2 text-primary"></i>
                    {{ __('messages.add_category') }}
                </h2>
                <a href="{{ route('assets.asset-categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('messages.back_to_categories') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('assets.asset-categories.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name_ar" class="form-label">{{ __('messages.name_ar') }}</label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar') }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
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
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">{{ __('messages.price') }}</label>
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price') }}">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('messages.active') }}
                                </label>
                            </div>
                        </div>

                        <!-- Properties Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-list me-2"></i>
                                {{ __('messages.category_properties') }}
                            </h5>
                            <div id="properties-container">
                                <!-- Properties will be added here dynamically -->
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addProperty()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('messages.add_property') }}
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('assets.asset-categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('messages.create_category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.help') }}
                    </h5>
                </div>
                <div class="card-body">
                    <h6>{{ __('messages.category_help_title') }}</h6>
                    <p class="small text-muted">{{ __('messages.category_help_description') }}</p>
                    
                    <h6 class="mt-3">{{ __('messages.properties_help_title') }}</h6>
                    <p class="small text-muted">{{ __('messages.properties_help_description') }}</p>
                    
                    <div class="mt-3">
                        <h6>{{ __('messages.property_types') }}:</h6>
                        <ul class="small text-muted">
                            <li><strong>{{ __('messages.text') }}:</strong> {{ __('messages.text_description') }}</li>
                            <li><strong>{{ __('messages.number') }}:</strong> {{ __('messages.number_description') }}</li>
                            <li><strong>{{ __('messages.date') }}:</strong> {{ __('messages.date_description') }}</li>
                            <li><strong>{{ __('messages.select') }}:</strong> {{ __('messages.select_description') }}</li>
                            <li><strong>{{ __('messages.boolean') }}:</strong> {{ __('messages.boolean_description') }}</li>
                            <li><strong>{{ __('messages.image') }}:</strong> {{ __('messages.image_description') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let propertyIndex = 0;

function addProperty() {
    const container = document.getElementById('properties-container');
    const propertyDiv = document.createElement('div');
    propertyDiv.className = 'card mb-3 property-item';
    propertyDiv.innerHTML = `
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="properties[${propertyIndex}][name]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.name_ar') }}</label>
                    <input type="text" class="form-control" name="properties[${propertyIndex}][name_ar]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                    <select class="form-select" name="properties[${propertyIndex}][type]" required onchange="toggleOptionsField(${propertyIndex})">
                        <option value="">{{ __('messages.select_type') }}</option>
                        <option value="text">{{ __('messages.text') }}</option>
                        <option value="number">{{ __('messages.number') }}</option>
                        <option value="date">{{ __('messages.date') }}</option>
                        <option value="select">{{ __('messages.select') }}</option>
                        <option value="boolean">{{ __('messages.boolean') }}</option>
                        <option value="image">{{ __('messages.image') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('messages.sort_order') }}</label>
                    <input type="number" class="form-control" name="properties[${propertyIndex}][sort_order]" value="0" min="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-1">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="properties[${propertyIndex}][is_required]" value="1">
                            <label class="form-check-label small">{{ __('messages.required') }}</label>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeProperty(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mt-2" id="options-field-${propertyIndex}" style="display: none;">
                <div class="col-12">
                    <label class="form-label">{{ __('messages.options') }}</label>
                    <textarea class="form-control" name="properties[${propertyIndex}][options]" 
                              placeholder="{{ __('messages.options_placeholder') }}" rows="2"></textarea>
                    <small class="form-text text-muted">{{ __('messages.options_help') }}</small>
                </div>
            </div>
        </div>
    `;
    container.appendChild(propertyDiv);
    propertyIndex++;
}

function removeProperty(button) {
    button.closest('.property-item').remove();
}

function toggleOptionsField(index) {
    const type = document.querySelector(`select[name="properties[${index}][type]"]`).value;
    const optionsField = document.getElementById(`options-field-${index}`);
    if (type === 'select') {
        optionsField.style.display = 'block';
    } else {
        optionsField.style.display = 'none';
    }
}
</script>
@endpush

