@extends('layouts.app')

@section('title', $category->display_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-tag me-2 text-primary"></i>
                        {{ $category->display_name }}
                    </h2>
                    @if($category->description)
                        <p class="text-muted mb-0">{{ $category->display_description }}</p>
                    @endif
                </div>
                <div class="btn-group">
                    <a href="{{ route('assets.asset-categories.edit', $category) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('messages.edit') }}
                    </a>
                    <a href="{{ route('assets.asset-categories.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Category Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.category_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">{{ __('messages.name') }}:</td>
                                    <td>{{ $category->display_name }}</td>
                                </tr>
                                @if($category->name_ar)
                                <tr>
                                    <td class="fw-bold">{{ __('messages.name_ar') }}:</td>
                                    <td>{{ $category->name_ar }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">{{ __('messages.status') }}:</td>
                                    <td>
                                        @if($category->is_active)
                                            <span class="badge bg-success">{{ __('messages.active') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('messages.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">{{ __('messages.assets_count') }}:</td>
                                    <td><span class="badge bg-primary">{{ $category->assets->count() }} {{ __('messages.assets') }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.properties_count') }}:</td>
                                    <td><span class="badge bg-info">{{ $category->propertiesOrdered->count() }} {{ __('messages.properties') }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.price') }}:</td>
                                    <td>{{ $category->price ? number_format($category->price, 2) . ' USD' : __('messages.not_specified') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ __('messages.created_at') }}:</td>
                                    <td>{{ $category->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($category->description || $category->description_ar)
                    <hr>
                    <h6 class="fw-bold">{{ __('messages.description') }}:</h6>
                    <p>{{ $category->display_description ?: __('messages.no_description') }}</p>
                    @endif
                </div>
            </div>

            <!-- Properties Management -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('messages.category_properties') }}
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('messages.add_property') }}
                    </button>
                </div>
                <div class="card-body">
                    @if($category->propertiesOrdered->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.type') }}</th>
                                        <th>{{ __('messages.required') }}</th>
                                        <th>{{ __('messages.sort_order') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->propertiesOrdered->sortBy('sort_order') as $property)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $property->display_name }}</strong>
                                                    @if($property->name_ar)
                                                        <br><small class="text-muted">{{ $property->name_ar }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($property->type) }}</span>
                                                @if($property->isSelectType() && $property->options)
                                                    <br><small class="text-muted">{{ count(is_array($property->options) ? $property->options : explode("\n", $property->options)) }} {{ __('messages.options') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($property->is_required)
                                                    <span class="badge bg-danger">{{ __('messages.required') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ __('messages.optional') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $property->sort_order }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            onclick="editProperty({{ $property->id }})" title="{{ __('messages.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('assets.properties.destroy', $property) }}" 
                                                          class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') }}">
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
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('messages.no_properties_defined') }}</h5>
                            <p class="text-muted">{{ __('messages.add_properties_description') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('messages.add_first_property') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assets in this Category -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cubes me-2"></i>
                        {{ __('messages.assets_in_category') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($category->assets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.asset_code') }}</th>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.assigned_to') }}</th>
                                        <th>{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->assets->take(10) as $asset)
                                        <tr>
                                            <td><span class="badge bg-secondary">{{ $asset->asset_code }}</span></td>
                                            <td>{{ $asset->display_name }}</td>
                                            <td>
                                                @if($asset->status == 'active')
                                                    <span class="badge bg-success">{{ $asset->status_label }}</span>
                                                @elseif($asset->status == 'maintenance')
                                                    <span class="badge bg-warning">{{ $asset->status_label }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ $asset->status_label }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($asset->assignedTo)
                                                    <span class="badge bg-primary">{{ $asset->assignedTo->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('messages.unassigned') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('assets.assets.show', $asset) }}" 
                                                   class="btn btn-sm btn-outline-info" title="{{ __('messages.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($category->assets->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('assets.assets.index', ['category_id' => $category->id]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    {{ __('messages.view_all_assets') }} ({{ $category->assets->count() }})
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-cubes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('messages.no_assets_in_category') }}</h5>
                            <p class="text-muted">{{ __('messages.no_assets_in_category_description') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('messages.quick_stats') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $category->assets->count() }}</h4>
                                <small class="text-muted">{{ __('messages.total_assets') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info">{{ $category->propertiesOrdered->count() }}</h4>
                            <small class="text-muted">{{ __('messages.properties') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ __('messages.quick_actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('assets.assets.create') }}?category_id={{ $category->id }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('messages.add_asset_to_category') }}
                        </a>
                        
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                            <i class="fas fa-list me-1"></i>
                            {{ __('messages.add_property') }}
                        </button>
                        
                        <a href="{{ route('assets.asset-categories.edit', $category) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('messages.edit_category') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Property Modal -->
<div class="modal fade" id="addPropertyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('assets.asset-categories.store-property', $category) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.add_property') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="property_name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="property_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="property_name_ar" class="form-label">{{ __('messages.name_ar') }}</label>
                        <input type="text" class="form-control" id="property_name_ar" name="name_ar">
                    </div>
                    
                    <div class="mb-3">
                        <label for="property_type" class="form-label">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="property_type" name="type" required onchange="toggleOptionsField()">
                            <option value="">{{ __('messages.select_type') }}</option>
                            <option value="text">{{ __('messages.text') }}</option>
                            <option value="number">{{ __('messages.number') }}</option>
                            <option value="date">{{ __('messages.date') }}</option>
                            <option value="select">{{ __('messages.select') }}</option>
                            <option value="boolean">{{ __('messages.boolean') }}</option>
                            <option value="image">{{ __('messages.image') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="options_field" style="display: none;">
                        <label for="property_options" class="form-label">{{ __('messages.options') }}</label>
                        <textarea class="form-control" id="property_options" name="options" 
                                  placeholder="{{ __('messages.options_placeholder') }}" rows="3"></textarea>
                        <small class="form-text text-muted">{{ __('messages.options_help') }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="property_required" name="is_required" value="1">
                            <label class="form-check-label" for="property_required">
                                {{ __('messages.required') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="property_sort_order" class="form-label">{{ __('messages.sort_order') }}</label>
                        <input type="number" class="form-control" id="property_sort_order" name="sort_order" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.add_property') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Property Modal -->
<div class="modal fade" id="editPropertyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editPropertyForm" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_property_id" name="property_id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.edit_property') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_property_name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_property_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_property_name_ar" class="form-label">{{ __('messages.name_ar') }}</label>
                        <input type="text" class="form-control" id="edit_property_name_ar" name="name_ar">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_property_type" class="form-label">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_property_type" name="type" required onchange="toggleEditOptionsField()">
                            <option value="">{{ __('messages.select_type') }}</option>
                            <option value="text">{{ __('messages.text') }}</option>
                            <option value="number">{{ __('messages.number') }}</option>
                            <option value="date">{{ __('messages.date') }}</option>
                            <option value="select">{{ __('messages.select') }}</option>
                            <option value="boolean">{{ __('messages.boolean') }}</option>
                            <option value="image">{{ __('messages.image') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="edit_options_field" style="display: none;">
                        <label for="edit_property_options" class="form-label">{{ __('messages.options') }}</label>
                        <textarea class="form-control" id="edit_property_options" name="options" 
                                  placeholder="{{ __('messages.options_placeholder') }}" rows="3"></textarea>
                        <small class="form-text text-muted">{{ __('messages.options_help') }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_property_required" name="is_required" value="1">
                            <label class="form-check-label" for="edit_property_required">
                                {{ __('messages.required') }}
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_property_sort_order" class="form-label">{{ __('messages.sort_order') }}</label>
                        <input type="number" class="form-control" id="edit_property_sort_order" name="sort_order" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('messages.update_property') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Store translations in JavaScript variables
const editTitle = @json(__('messages.edit'));
const confirmDeleteMessage = @json(__('messages.confirm_delete'));
function toggleOptionsField() {
    const type = document.getElementById('property_type').value;
    const optionsField = document.getElementById('options_field');
    
    if (type === 'select') {
        optionsField.style.display = 'block';
    } else {
        optionsField.style.display = 'none';
    }
}

function toggleEditOptionsField() {
    const type = document.getElementById('edit_property_type').value;
    const optionsField = document.getElementById('edit_options_field');
    
    if (type === 'select') {
        optionsField.style.display = 'block';
    } else {
        optionsField.style.display = 'none';
    }
}

function editProperty(propertyId) {
    // Set form action
    document.getElementById('editPropertyForm').action = `/assets/properties/${propertyId}`;
    
    // Fetch property data and open edit modal
    fetch(`/assets/categories/properties/${propertyId}`)
        .then(response => response.json())
        .then(property => {
            // Populate edit modal with property data
            document.getElementById('edit_property_id').value = property.id;
            document.getElementById('edit_property_name').value = property.name;
            document.getElementById('edit_property_name_ar').value = property.name_ar || '';
            document.getElementById('edit_property_type').value = property.type;
            document.getElementById('edit_property_required').checked = property.is_required;
            document.getElementById('edit_property_sort_order').value = property.sort_order;
            
            // Handle options for select type
            if (property.type === 'select' && property.options) {
                // Handle both string and array formats
                const optionsValue = Array.isArray(property.options) 
                    ? property.options.join('\n') 
                    : property.options;
                document.getElementById('edit_property_options').value = optionsValue;
                document.getElementById('edit_options_field').style.display = 'block';
            } else {
                document.getElementById('edit_options_field').style.display = 'none';
            }
            
            // Show edit modal
            const editModal = new bootstrap.Modal(document.getElementById('editPropertyModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في تحميل بيانات الخاصية');
        });
}

// Handle edit form submission
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editPropertyForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const propertyId = document.getElementById('edit_property_id').value;
            
            // Add _method field for Laravel to recognize PUT request
            formData.append('_method', 'PUT');
            
            fetch(`/assets/properties/${propertyId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Server error: ' + response.status);
                }
            })
            .then(data => {
                if (data.success) {
                    alert('تم تحديث الخاصية بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في تحديث الخاصية: ' + error.message);
            });
        });
    }
});
</script>
@endpush

