@extends('layouts.app')

@section('title', __('Create Category'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-plus me-2"></i>{{ __('Create New Category') }}
    </h2>
    <a href="{{ route('password-categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Category Information') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password-categories.store') }}">
                    @csrf
                    
                    <div class="row">
                        <!-- Category Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                {{ __('Category Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Arabic Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name_ar" class="form-label">{{ __('Category Name (Arabic)') }}</label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" name="name_ar" value="{{ old('name_ar') }}">
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Icon URL -->
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">{{ __('Icon URL') }}</label>
                            <input type="url" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon') }}" 
                                   placeholder="https://example.com/icon.png">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Color -->
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">{{ __('Color') }}</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                       id="color" name="color" value="{{ old('color', '#6c757d') }}">
                                <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                       id="color_text" value="{{ old('color', '#6c757d') }}" 
                                       placeholder="#6c757d" pattern="^#[0-9A-Fa-f]{6}$">
                            </div>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Sort Order -->
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                   min="0" step="1">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Lower numbers appear first') }}</small>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Arabic Description -->
                    <div class="mb-3">
                        <label for="description_ar" class="form-label">{{ __('Description (Arabic)') }}</label>
                        <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                  id="description_ar" name="description_ar" rows="3">{{ old('description_ar') }}</textarea>
                        @error('description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('password-categories.index') }}" class="btn btn-secondary me-2">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ __('Create Category') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Preview Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Preview') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div id="preview-icon" class="me-3 d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px; background-color: #6c757d; border-radius: 8px;">
                        <i class="fas fa-tag text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 id="preview-name" class="mb-1">{{ __('Category Name') }}</h6>
                        <span id="preview-badge" class="badge" style="background-color: #6c757d;">
                            0 {{ __('Accounts') }}
                        </span>
                    </div>
                </div>
                <p id="preview-description" class="text-muted small mb-0">
                    {{ __('Category description will appear here...') }}
                </p>
            </div>
        </div>
        
        <!-- Tips -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Tips') }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Use descriptive names for easy identification') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Choose colors that match your brand') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Use sort order to organize categories') }}
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success me-2"></i>
                        {{ __('Icons should be 40x40 pixels for best results') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Color picker synchronization
document.getElementById('color').addEventListener('input', function() {
    document.getElementById('color_text').value = this.value;
    updatePreview();
});

document.getElementById('color_text').addEventListener('input', function() {
    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        document.getElementById('color').value = this.value;
        updatePreview();
    }
});

// Form field updates
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('name_ar').addEventListener('input', updatePreview);
document.getElementById('description').addEventListener('input', updatePreview);
document.getElementById('description_ar').addEventListener('input', updatePreview);
document.getElementById('icon').addEventListener('input', updatePreview);

function updatePreview() {
    const name = document.getElementById('name').value || '{{ __("Category Name") }}';
    const nameAr = document.getElementById('name_ar').value;
    const description = document.getElementById('description').value || '{{ __("Category description will appear here...") }}';
    const descriptionAr = document.getElementById('description_ar').value;
    const color = document.getElementById('color').value || '#6c757d';
    const icon = document.getElementById('icon').value;
    
    // Update name (prefer Arabic if available and locale is Arabic)
    const displayName = nameAr && '{{ app()->getLocale() }}' === 'ar' ? nameAr : name;
    document.getElementById('preview-name').textContent = displayName;
    
    // Update description (prefer Arabic if available and locale is Arabic)
    const displayDescription = descriptionAr && '{{ app()->getLocale() }}' === 'ar' ? descriptionAr : description;
    document.getElementById('preview-description').textContent = displayDescription;
    
    // Update color
    document.getElementById('preview-badge').style.backgroundColor = color;
    document.getElementById('preview-icon').style.backgroundColor = color;
    
    // Update icon
    if (icon) {
        document.getElementById('preview-icon').innerHTML = `<img src="${icon}" alt="${displayName}" style="width: 24px; height: 24px;">`;
    } else {
        document.getElementById('preview-icon').innerHTML = '<i class="fas fa-tag text-white"></i>';
    }
}
</script>
@endpush












