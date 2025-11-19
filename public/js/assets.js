/**
 * Assets Control System JavaScript
 */

// Global Assets Object
window.Assets = {
    // Configuration
    config: {
        apiBase: '/assets',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        locale: document.documentElement.lang || 'en',
        dateFormat: 'YYYY-MM-DD',
        timeFormat: 'HH:mm:ss'
    },

    // Initialize
    init: function() {
        this.bindEvents();
        this.initializeComponents();
        this.setupTooltips();
        this.setupModals();
    },

    // Bind Events
    bindEvents: function() {
        // Category change event
        $(document).on('change', '#category_id', this.handleCategoryChange);
        
        // Property type change event
        $(document).on('change', '#property_type', this.handlePropertyTypeChange);
        
        // Asset assignment events
        $(document).on('click', '.assign-asset-btn', this.handleAssignAsset);
        $(document).on('click', '.return-asset-btn', this.handleReturnAsset);
        
        // Barcode print events
        $(document).on('click', '.print-barcode-btn', this.handlePrintBarcode);
        $(document).on('click', '.download-barcode-btn', this.handleDownloadBarcode);
        
        // Filter events
        $(document).on('submit', '.filter-form', this.handleFilterSubmit);
        $(document).on('click', '.clear-filters-btn', this.handleClearFilters);
        
        // Search events
        $(document).on('input', '.search-input', this.debounce(this.handleSearch, 300));
        
        // Bulk actions
        $(document).on('change', '.select-all-checkbox', this.handleSelectAll);
        $(document).on('change', '.asset-checkbox', this.handleAssetSelect);
        $(document).on('click', '.bulk-action-btn', this.handleBulkAction);
        
        // Auto-refresh
        if (window.location.pathname.includes('/dashboard')) {
            setInterval(this.refreshStatistics, 30000); // Refresh every 30 seconds
        }
    },

    // Initialize Components
    initializeComponents: function() {
        // Initialize DataTables if present
        if ($.fn.DataTable) {
            $('.assets-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    url: this.getDataTableLanguageUrl()
                }
            });
        }

        // Initialize Select2 if present
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        // Initialize Date Pickers - Disabled to avoid conflict with custom date picker
        // $('.datepicker').datepicker({
        //     format: 'yyyy-mm-dd',
        //     autoclose: true,
        //     todayHighlight: true
        // });

        // Initialize Tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    },

    // Setup Tooltips
    setupTooltips: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    },

    // Setup Modals
    setupModals: function() {
        // Auto-focus on first input when modal opens
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('input:first').focus();
        });
    },

    // Handle Category Change
    handleCategoryChange: function() {
        const categoryId = $(this).val();
        if (categoryId) {
            Assets.loadCategoryProperties(categoryId);
        } else {
            $('#category-properties').hide();
        }
    },

    // Load Category Properties
    loadCategoryProperties: function(categoryId) {
        const container = $('#properties-container');
        const propertiesDiv = $('#category-properties');
        
        // Show loading
        container.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> ' + Assets.t('loading') + '...</div>');
        propertiesDiv.show();
        
        // Fetch properties
        $.ajax({
            url: Assets.config.apiBase + '/categories/' + categoryId + '/properties',
            method: 'GET',
            success: function(properties) {
                if (properties.length === 0) {
                    container.html('<div class="text-muted">' + Assets.t('no_properties_defined') + '</div>');
                    return;
                }
                
                let html = '';
                properties.forEach(function(property) {
                    html += Assets.renderPropertyField(property);
                });
                
                container.html(html);
            },
            error: function() {
                container.html('<div class="text-danger">' + Assets.t('error_loading_properties') + '</div>');
            }
        });
    },

    // Render Property Field
    renderPropertyField: function(property) {
        let html = '<div class="mb-3">';
        html += '<label for="property_' + property.id + '" class="form-label">' + property.display_name;
        if (property.is_required) {
            html += ' <span class="text-danger">*</span>';
        }
        html += '</label>';
        
        switch(property.type) {
            case 'text':
                html += '<input type="text" class="form-control" id="property_' + property.id + '" name="properties[' + property.id + ']" value="' + (property.value || '') + '" ' + (property.is_required ? 'required' : '') + '>';
                break;
            case 'number':
                html += '<input type="number" class="form-control" id="property_' + property.id + '" name="properties[' + property.id + ']" value="' + (property.value || '') + '" ' + (property.is_required ? 'required' : '') + '>';
                break;
            case 'date':
                html += '<input type="date" class="form-control" id="property_' + property.id + '" name="properties[' + property.id + ']" value="' + (property.value || '') + '" ' + (property.is_required ? 'required' : '') + '>';
                break;
            case 'boolean':
                html += '<select class="form-select" id="property_' + property.id + '" name="properties[' + property.id + ']" ' + (property.is_required ? 'required' : '') + '>';
                html += '<option value="0" ' + (property.value == '0' ? 'selected' : '') + '>' + Assets.t('no') + '</option>';
                html += '<option value="1" ' + (property.value == '1' ? 'selected' : '') + '>' + Assets.t('yes') + '</option>';
                html += '</select>';
                break;
            case 'select':
                html += '<select class="form-select" id="property_' + property.id + '" name="properties[' + property.id + ']" ' + (property.is_required ? 'required' : '') + '>';
                html += '<option value="">' + Assets.t('select_option') + '</option>';
                if (property.options) {
                    property.options.forEach(function(option) {
                        html += '<option value="' + option + '" ' + (property.value == option ? 'selected' : '') + '>' + option + '</option>';
                    });
                }
                html += '</select>';
                break;
            case 'image':
                html += '<input type="file" class="form-control" id="property_' + property.id + '" name="properties[' + property.id + ']" accept="image/*" ' + (property.is_required ? 'required' : '') + '>';
                break;
        }
        html += '</div>';
        
        return html;
    },

    // Handle Property Type Change
    handlePropertyTypeChange: function() {
        const type = $(this).val();
        const optionsField = $('#options_field');
        
        if (type === 'select') {
            optionsField.show();
        } else {
            optionsField.hide();
        }
    },

    // Handle Assign Asset
    handleAssignAsset: function(e) {
        e.preventDefault();
        const assetId = $(this).data('asset-id');
        const userId = $(this).data('user-id');
        
        if (confirm(Assets.t('confirm_assign_asset'))) {
            Assets.assignAsset(assetId, userId);
        }
    },

    // Handle Return Asset
    handleReturnAsset: function(e) {
        e.preventDefault();
        const assignmentId = $(this).data('assignment-id');
        
        if (confirm(Assets.t('confirm_return_asset'))) {
            Assets.returnAsset(assignmentId);
        }
    },

    // Assign Asset
    assignAsset: function(assetId, userId, notes) {
        $.ajax({
            url: Assets.config.apiBase + '/assignments',
            method: 'POST',
            data: {
                asset_id: assetId,
                user_id: userId,
                notes: notes || '',
                _token: Assets.config.csrfToken
            },
            success: function(response) {
                Assets.showAlert('success', Assets.t('asset_assigned_successfully'));
                location.reload();
            },
            error: function(xhr) {
                Assets.showAlert('error', xhr.responseJSON?.message || Assets.t('error_occurred'));
            }
        });
    },

    // Return Asset
    returnAsset: function(assignmentId, notes) {
        $.ajax({
            url: Assets.config.apiBase + '/assignments/' + assignmentId + '/return',
            method: 'POST',
            data: {
                notes: notes || '',
                _token: Assets.config.csrfToken
            },
            success: function(response) {
                Assets.showAlert('success', Assets.t('asset_returned_successfully'));
                location.reload();
            },
            error: function(xhr) {
                Assets.showAlert('error', xhr.responseJSON?.message || Assets.t('error_occurred'));
            }
        });
    },

    // Handle Print Barcode
    handlePrintBarcode: function(e) {
        e.preventDefault();
        const assetId = $(this).data('asset-id');
        window.open(Assets.config.apiBase + '/assets/' + assetId + '/print-barcode', '_blank');
    },

    // Handle Download Barcode
    handleDownloadBarcode: function(e) {
        e.preventDefault();
        const assetId = $(this).data('asset-id');
        window.location.href = Assets.config.apiBase + '/assets/' + assetId + '/download-barcode';
    },

    // Handle Filter Submit
    handleFilterSubmit: function(e) {
        // Add loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + Assets.t('loading'));
    },

    // Handle Clear Filters
    handleClearFilters: function(e) {
        e.preventDefault();
        $('.filter-form')[0].reset();
        $('.filter-form').submit();
    },

    // Handle Search
    handleSearch: function() {
        const searchTerm = $(this).val();
        const table = $('.assets-table').DataTable();
        
        if (table) {
            table.search(searchTerm).draw();
        }
    },

    // Handle Select All
    handleSelectAll: function() {
        const isChecked = $(this).is(':checked');
        $('.asset-checkbox').prop('checked', isChecked);
        Assets.updateBulkActions();
    },

    // Handle Asset Select
    handleAssetSelect: function() {
        Assets.updateBulkActions();
    },

    // Update Bulk Actions
    updateBulkActions: function() {
        const selectedCount = $('.asset-checkbox:checked').length;
        const bulkActions = $('.bulk-actions');
        
        if (selectedCount > 0) {
            bulkActions.show();
            $('.selected-count').text(selectedCount);
        } else {
            bulkActions.hide();
        }
    },

    // Handle Bulk Action
    handleBulkAction: function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const selectedIds = $('.asset-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            Assets.showAlert('warning', Assets.t('no_assets_selected'));
            return;
        }
        
        if (confirm(Assets.t('confirm_bulk_action', {action: action, count: selectedIds.length}))) {
            Assets.performBulkAction(action, selectedIds);
        }
    },

    // Perform Bulk Action
    performBulkAction: function(action, assetIds) {
        $.ajax({
            url: Assets.config.apiBase + '/bulk-action',
            method: 'POST',
            data: {
                action: action,
                asset_ids: assetIds,
                _token: Assets.config.csrfToken
            },
            success: function(response) {
                Assets.showAlert('success', response.message);
                location.reload();
            },
            error: function(xhr) {
                Assets.showAlert('error', xhr.responseJSON?.message || Assets.t('error_occurred'));
            }
        });
    },

    // Refresh Statistics
    refreshStatistics: function() {
        $.ajax({
            url: Assets.config.apiBase + '/statistics',
            method: 'GET',
            success: function(data) {
                Object.keys(data).forEach(function(key) {
                    const element = $('#' + key.replace('_', '-'));
                    if (element.length) {
                        element.text(data[key]);
                    }
                });
            }
        });
    },

    // Show Alert
    showAlert: function(type, message) {
        const alertClass = 'alert-' + type;
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${Assets.getAlertIcon(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.alerts-container').html(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    },

    // Get Alert Icon
    getAlertIcon: function(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },

    // Debounce Function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Translation Function
    t: function(key, params) {
        // This would typically use a translation library
        // For now, we'll use a simple mapping
        const translations = {
            'loading': 'Loading...',
            'no_properties_defined': 'No properties defined',
            'error_loading_properties': 'Error loading properties',
            'select_option': 'Select option',
            'yes': 'Yes',
            'no': 'No',
            'confirm_assign_asset': 'Are you sure you want to assign this asset?',
            'confirm_return_asset': 'Are you sure you want to return this asset?',
            'asset_assigned_successfully': 'Asset assigned successfully',
            'asset_returned_successfully': 'Asset returned successfully',
            'error_occurred': 'An error occurred',
            'no_assets_selected': 'No assets selected',
            'confirm_bulk_action': 'Are you sure you want to perform this action on {count} assets?'
        };
        
        let translation = translations[key] || key;
        
        // Replace parameters
        if (params) {
            Object.keys(params).forEach(function(param) {
                translation = translation.replace('{' + param + '}', params[param]);
            });
        }
        
        return translation;
    },

    // Get DataTable Language URL
    getDataTableLanguageUrl: function() {
        const locale = Assets.config.locale;
        return locale === 'ar' ? 
            '//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json' : 
            '//cdn.datatables.net/plug-ins/1.10.24/i18n/English.json';
    },

    // Utility Functions
    utils: {
        // Format Date
        formatDate: function(date, format) {
            if (!date) return '';
            const d = new Date(date);
            return d.toLocaleDateString(Assets.config.locale);
        },

        // Format Currency
        formatCurrency: function(amount, currency) {
            currency = currency || 'USD';
            return new Intl.NumberFormat(Assets.config.locale, {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        // Generate Barcode
        generateBarcode: function(text, options) {
            options = options || {};
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Simple barcode generation (you might want to use a library like JsBarcode)
            canvas.width = 200;
            canvas.height = 100;
            
            ctx.fillStyle = '#000';
            ctx.fillRect(10, 10, 180, 80);
            
            ctx.fillStyle = '#fff';
            ctx.font = '12px monospace';
            ctx.textAlign = 'center';
            ctx.fillText(text, 100, 60);
            
            return canvas.toDataURL();
        },

        // Download File
        downloadFile: function(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename || 'download';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        // Copy to Clipboard
        copyToClipboard: function(text) {
            navigator.clipboard.writeText(text).then(function() {
                Assets.showAlert('success', 'Copied to clipboard');
            }).catch(function() {
                Assets.showAlert('error', 'Failed to copy to clipboard');
            });
        }
    }
};

// Initialize when document is ready
$(document).ready(function() {
    Assets.init();
});

// Export for global use
window.Assets = Assets;

