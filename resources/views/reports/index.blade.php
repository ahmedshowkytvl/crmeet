@extends('layouts.app')

@section('title', __('messages.reports_and_data'))

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                {{ __('messages.reports_and_data') }}
            </h1>
            <p class="text-muted mb-0">{{ __('messages.reports_description') }}</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fullBackupModal">
                <i class="fas fa-download me-1"></i>
                {{ __('messages.full_system_backup') }}
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#fullRestoreModal">
                <i class="fas fa-upload me-1"></i>
                {{ __('messages.restore_backup') }}
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('messages.total_modules') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($modules) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('messages.completed_operations') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="completed-operations">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('messages.last_export') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="last-export">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-download fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('messages.last_import') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="last-import">-</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-upload fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.search_and_filter') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="searchModules">{{ __('messages.search_modules') }}</label>
                        <input type="text" class="form-control" id="searchModules" placeholder="{{ __('messages.search_modules_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterCategory">{{ __('messages.filter_by_category') }}</label>
                        <select class="form-control" id="filterCategory">
                            <option value="">{{ __('messages.all_categories') }}</option>
                            <option value="users">{{ __('messages.user_management') }}</option>
                            <option value="contacts">{{ __('messages.contact_management') }}</option>
                            <option value="tasks">{{ __('messages.task_management') }}</option>
                            <option value="assets">{{ __('messages.asset_management') }}</option>
                            <option value="warehouse">{{ __('messages.warehouse_management') }}</option>
                            <option value="passwords">{{ __('messages.password_management') }}</option>
                            <option value="communication">{{ __('messages.communication_management') }}</option>
                            <option value="employees">{{ __('messages.employee_management') }}</option>
                            <option value="integration">{{ __('messages.external_integrations') }}</option>
                            <option value="misc">{{ __('messages.miscellaneous') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sortBy">{{ __('messages.sort_by') }}</label>
                        <select class="form-control" id="sortBy">
                            <option value="name">{{ __('messages.sort_by_name') }}</option>
                            <option value="records">{{ __('messages.sort_by_records') }}</option>
                            <option value="size">{{ __('messages.sort_by_size') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="row" id="modulesGrid">
        @foreach($modules as $index => $module)
        @php
            $category = 'misc';
            $categoryMap = [
                'users' => ['users', 'roles', 'permissions', 'departments', 'branches', 'user_phones', 'phone_types', 'role_permissions', 'user_achievements'],
                'contacts' => ['contacts', 'contact_categories', 'contact_interactions'],
                'tasks' => ['tasks', 'task_templates', 'schedule_events', 'events'],
                'assets' => ['assets', 'asset_categories', 'asset_locations', 'asset_assignments', 'asset_logs', 'asset_category_properties', 'asset_property_values'],
                'warehouse' => ['warehouses', 'warehouse_cabinets', 'warehouse_shelves', 'inventory', 'stock_movements', 'suppliers', 'supplier_notes'],
                'passwords' => ['password_accounts', 'password_categories', 'password_assignments', 'password_audit_logs', 'password_history'],
                'communication' => ['chat_rooms', 'chat_messages', 'chat_participants', 'notifications', 'notification_preferences', 'announcements'],
                'employees' => ['employee_emails', 'employee_requests', 'hiring_documents'],
                'integration' => ['user_zoho_stats', 'zoho_ticket_cache', 'zoho_department_mappings', 'snipe_it_sync_logs'],
                'misc' => ['comments', 'audit_logs', 'shoutouts']
            ];
            
            foreach ($categoryMap as $cat => $tables) {
                if (in_array($module['table'], $tables)) {
                    $category = $cat;
                    break;
                }
            }
        @endphp
        <div class="col-lg-4 col-md-6 mb-4 module-card" 
             data-name="{{ strtolower($module['name'] . ' ' . $module['name_en']) }}"
             data-category="{{ $category }}">
            <div class="card shadow h-100 module-item" data-table="{{ $module['table'] }}">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="d-flex align-items-center mb-2">
                                <i class="{{ $module['icon'] }} fa-lg me-2" style="color: {{ $module['color'] }}"></i>
                                <h5 class="card-title mb-0">{{ $module['name'] }}</h5>
                            </div>
                            <p class="text-muted small">{{ $module['name_en'] }}</p>
                            <p class="card-text small">{{ $module['description'] }}</p>
                            
                            <div class="module-stats mb-3" data-table="{{ $module['table'] }}">
                                <small class="text-muted">
                                    <i class="fas fa-database me-1"></i>
                                    <span class="records-count">{{ __('messages.loading') }}</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group d-flex" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm export-btn" 
                                data-module="{{ $module['table'] }}" data-name="{{ $module['name'] }}">
                            <i class="fas fa-download me-1"></i>
                            {{ __('messages.export') }}
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm import-btn" 
                                data-module="{{ $module['table'] }}" data-name="{{ $module['name'] }}">
                            <i class="fas fa-upload me-1"></i>
                            {{ __('messages.import') }}
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm stats-btn" 
                                data-module="{{ $module['table'] }}" data-name="{{ $module['name'] }}">
                            <i class="fas fa-chart-bar me-1"></i>
                            {{ __('messages.statistics') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-download me-2"></i>
                    {{ __('messages.export_data') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <input type="hidden" id="exportModule" name="module">
                    
                    <div class="mb-3">
                        <label for="exportModuleName" class="form-label">{{ __('messages.selected_module') }}</label>
                        <input type="text" class="form-control" id="exportModuleName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">{{ __('messages.export_format') }}</label>
                        <select class="form-control" id="exportFormat" name="format" required>
                            <option value="excel">{{ __('messages.excel_xlsx') }}</option>
                            <option value="csv">{{ __('messages.csv_format') }}</option>
                            <option value="json">{{ __('messages.json_format') }}</option>
                            <option value="sql">{{ __('messages.sql_format') }}</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.export_info') }}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirmExport">
                    <i class="fas fa-download me-1"></i>
                    {{ __('messages.export_now') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-upload me-2"></i>
                    {{ __('messages.import_data') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                    <input type="hidden" id="importModule" name="module">
                    
                    <div class="mb-3">
                        <label for="importModuleName" class="form-label">{{ __('messages.selected_module') }}</label>
                        <input type="text" class="form-control" id="importModuleName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFile" class="form-label">{{ __('messages.choose_file') }}</label>
                        <input type="file" class="form-control" id="importFile" name="file" 
                               accept=".xlsx,.xls,.csv,.json,.sql" required>
                        <div class="form-text">{{ __('messages.supported_formats') }}</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('messages.import_warning') }}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-success" id="confirmImport">
                    <i class="fas fa-upload me-1"></i>
                    {{ __('messages.import_now') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div class="modal fade" id="statsModal" tabindex="-1" aria-labelledby="statsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statsModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>
                    {{ __('messages.module_statistics') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="statsModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">{{ __('messages.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Backup Modal -->
<div class="modal fade" id="fullBackupModal" tabindex="-1" aria-labelledby="fullBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullBackupModalLabel">
                    <i class="fas fa-download me-2"></i>
                    {{ __('messages.full_system_backup') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('messages.backup_info') }}</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('messages.backup_time_warning') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirmFullBackup">
                    <i class="fas fa-download me-1"></i>
                    {{ __('messages.create_backup') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Full Restore Modal -->
<div class="modal fade" id="fullRestoreModal" tabindex="-1" aria-labelledby="fullRestoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullRestoreModalLabel">
                    <i class="fas fa-upload me-2"></i>
                    {{ __('messages.restore_backup') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fullRestoreForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="backupFile" class="form-label">{{ __('messages.choose_backup_file') }}</label>
                        <input type="file" class="form-control" id="backupFile" name="backup_file" 
                               accept=".json" required>
                        <div class="form-text">{{ __('messages.json_only') }}</div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="overwriteExisting" name="overwrite_existing">
                        <label class="form-check-label" for="overwriteExisting">
                            {{ __('messages.overwrite_existing') }}
                        </label>
                    </div>
                    
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('messages.restore_warning') }}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirmFullRestore">
                    <i class="fas fa-upload me-1"></i>
                    {{ __('messages.restore_backup_btn') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Arabic and English Bold Text Styles */
body, .card-title, .card-text, .btn, .nav-link, .form-label, .modal-title, h1, h2, h3, h4, h5, h6 {
    font-weight: 600 !important;
}

/* Arabic Bold Font Enhancement */
[dir="rtl"], .text-arabic, .arabic {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, 'Arabic UI Text', sans-serif !important;
    font-weight: 700 !important;
}

/* English Bold Font Enhancement */
.text-english, .english, [lang="en"] {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif !important;
    font-weight: 600 !important;
}

/* Module Cards Bold Styling */
.module-card {
    transition: all 0.3s ease;
}

.module-card:hover {
    transform: translateY(-2px);
}

.module-item {
    border: none;
    border-left: 4px solid #e3e6f0;
    transition: all 0.3s ease;
}

.module-item:hover {
    border-left-color: #4e73df;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.module-item .card-title {
    font-weight: 700 !important;
    font-size: 1.1rem !important;
}

.module-item .card-text {
    font-weight: 600 !important;
}

.module-stats {
    background: #f8f9fc;
    padding: 8px;
    border-radius: 4px;
    border-left: 3px solid #4e73df;
    font-weight: 600 !important;
}

/* Statistics Cards Bold */
.h5, .h3 {
    font-weight: 700 !important;
}

.text-xs {
    font-weight: 600 !important;
}

/* Button Bold Styling */
.btn {
    font-weight: 600 !important;
    letter-spacing: 0.5px;
}

.btn-primary {
    font-weight: 700 !important;
}

/* Modal Content Bold */
.modal-body, .modal-header, .modal-footer {
    font-weight: 600 !important;
}

.modal-title {
    font-weight: 700 !important;
}

/* Form Labels Bold */
.form-label, label {
    font-weight: 600 !important;
}

/* Navigation Bold */
.nav-section h6 {
    font-weight: 700 !important;
}

/* Progress and Alert Bold */
.alert {
    font-weight: 600 !important;
}

.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}

.progress-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    text-align: center;
    min-width: 300px;
    font-weight: 600 !important;
}

.progress-content h5 {
    font-weight: 700 !important;
}

/* Statistics Card Borders */
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

/* Table Bold Headers */
.table th {
    font-weight: 700 !important;
}

.table td {
    font-weight: 500 !important;
}

/* Search and Filter Bold */
.form-control {
    font-weight: 500 !important;
}

.form-control:focus {
    font-weight: 600 !important;
}

/* Text Color Enhancement for Better Readability */
.text-gray-800 {
    color: #2d3436 !important;
    font-weight: 700 !important;
}

.text-muted {
    font-weight: 500 !important;
}

/* Icon and Text Combinations */
.fa + .text, i + span, .fas + .text {
    font-weight: 600 !important;
}

@media (max-width: 768px) {
    .btn-group.d-flex {
        flex-direction: column;
    }
    
    .btn-group.d-flex .btn {
        margin-bottom: 5px;
        font-weight: 600 !important;
    }
    
    /* Mobile Text Enhancement */
    .card-title, .card-text {
        font-weight: 600 !important;
    }
}

/* RTL Specific Bold Styling */
[dir="rtl"] .card-title,
[dir="rtl"] .btn,
[dir="rtl"] .modal-title,
[dir="rtl"] h1, h2, h3, h4, h5, h6 {
    font-weight: 700 !important;
    text-shadow: 0 0 1px rgba(0,0,0,0.1);
}

/* Enhanced Text Shadows for Arabic */
[dir="rtl"] .text-primary,
[dir="rtl"] .text-success,
[dir="rtl"] .text-info,
[dir="rtl"] .text-warning,
[dir="rtl"] .text-danger {
    text-shadow: 0 0 2px rgba(0,0,0,0.1);
    font-weight: 700 !important;
}
</style>

@endsection

@push('scripts')
<script>
// Translation variables for JavaScript
const translations = {
    records: @json(__('messages.records')),
    megabytes: @json(__('messages.megabytes')),
    not_available: @json(__('messages.not_available')),
    import_success: @json(__('messages.import_success')),
    restore_success: @json(__('messages.restore_success')),
    export_success: @json(__('messages.export_success')),
    error_occurred: @json(__('messages.error_occurred')),
    table_structure: @json(__('messages.table_structure')),
    column_name: @json(__('messages.column_name')),
    total_records: @json(__('messages.total_records')),
    total_columns: @json(__('messages.total_columns')),
    data_size_mb: @json(__('messages.data_size_mb')),
    last_updated: @json(__('messages.last_updated')),
    statistics: @json(__('messages.statistics'))
};

$(document).ready(function() {
    let completedOperations = 0;
    
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Load module statistics
    loadModuleStats();
    
    // Search and filter setup
    setupSearchAndFilter();
    
    // Setup event handlers
    setupEventHandlers();
    
    function loadModuleStats() {
        $('.module-stats').each(function() {
            const table = $(this).data('table');
            const $statsElement = $(this);
            
            $.get('{{ route("reports.module-stats") }}', { table: table })
                .done(function(response) {
                    if (response.success) {
                        const data = response.data;
                        $statsElement.find('.records-count').html(
                            `<strong>${data.total_records.toLocaleString()}</strong> ${translations.records} | 
                             ${data.table_size} ${translations.megabytes}`
                        );
                    }
                })
                .fail(function() {
                    $statsElement.find('.records-count').text(translations.not_available);
                });
        });
    }
    
    function setupSearchAndFilter() {
        $('#searchModules').on('input', function() {
            filterModules();
        });
        
        $('#filterCategory').on('change', function() {
            filterModules();
        });
        
        $('#sortBy').on('change', function() {
            sortModules();
        });
    }
    
    function filterModules() {
        const searchTerm = $('#searchModules').val().toLowerCase();
        const selectedCategory = $('#filterCategory').val();
        
        $('.module-card').each(function() {
            const $card = $(this);
            const name = $card.data('name').toLowerCase();
            const category = $card.data('category');
            
            const matchesSearch = !searchTerm || name.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                $card.show();
            } else {
                $card.hide();
            }
        });
    }
    
    function sortModules() {
        const sortBy = $('#sortBy').val();
        const $grid = $('#modulesGrid');
        const $cards = $('.module-card').detach();
        
        $cards.sort(function(a, b) {
            switch (sortBy) {
                case 'name':
                    return $(a).data('name').localeCompare($(b).data('name'));
                default:
                    return 0;
            }
        });
        
        $grid.append($cards);
    }
    
    function setupEventHandlers() {
        // Export buttons
        $('.export-btn').click(function() {
            const module = $(this).data('module');
            const name = $(this).data('name');
            
            $('#exportModule').val(module);
            $('#exportModuleName').val(name);
            $('#exportModal').modal('show');
        });
        
        // Import buttons
        $('.import-btn').click(function() {
            const module = $(this).data('module');
            const name = $(this).data('name');
            
            $('#importModule').val(module);
            $('#importModuleName').val(name);
            $('#importModal').modal('show');
        });
        
        // Statistics buttons
        $('.stats-btn').click(function() {
            const module = $(this).data('module');
            const name = $(this).data('name');
            
            showModuleStats(module, name);
        });
        
        // Confirm export
        $('#confirmExport').click(function() {
            performExport();
        });
        
        // Confirm import
        $('#confirmImport').click(function() {
            performImport();
        });
        
        // Full backup
        $('#confirmFullBackup').click(function() {
            performFullBackup();
        });
        
        // Full restore
        $('#confirmFullRestore').click(function() {
            performFullRestore();
        });
    }
    
    function performExport() {
        const formData = new FormData();
        formData.append('module', $('#exportModule').val());
        const format = $('#exportFormat').val();
        formData.append('format', format);
        formData.append('_token', '{{ csrf_token() }}');
        
        showProgress('{{ __('messages.exporting_data') }}');
        
        fetch('{{ route("reports.export") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            } else {
                return response.json().then(err => Promise.reject(err));
            }
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            
            // تحديد امتداد الملف بناءً على التنسيق المختار
            const format = $('#exportFormat').val();
            let fileExtension = format;
            if (format === 'excel') {
                fileExtension = 'xlsx'; // تحويل excel إلى xlsx
            }
            
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-').replace('T', '_');
            a.download = `${$('#exportModule').val()}_${timestamp}.${fileExtension}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            hideProgress();
            $('#exportModal').modal('hide');
            updateStats('export');
            showSuccess('{{ __('messages.export_success') }}');
        })
        .catch(error => {
            hideProgress();
            showError('{{ __('messages.error_occurred') }}: ' + (error.message || '{{ __('messages.table_not_found') }}'));
        });
    }
    
    function performImport() {
        const formData = new FormData();
        formData.append('module', $('#importModule').val());
        formData.append('file', $('#importFile')[0].files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        
        showProgress('{{ __('messages.importing_data') }}');
        
        fetch('{{ route("reports.import") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();
            $('#importModal').modal('hide');
            
            if (data.success) {
                updateStats('import');
                showSuccess(`${translations.import_success} ${data.imported_rows} ${translations.records}.`);
                loadModuleStats(); // Reload statistics
            } else {
                showError('{{ __('messages.error_occurred') }}: ' + data.message);
            }
        })
        .catch(error => {
            hideProgress();
            showError('{{ __('messages.error_occurred') }}: ' + error.message);
        });
    }
    
    function performFullBackup() {
        showProgress('{{ __('messages.creating_backup') }}');
        
        fetch('{{ route("reports.full-backup") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            } else {
                return response.json().then(err => Promise.reject(err));
            }
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `crm_full_backup_${new Date().toISOString().slice(0, 10)}.json`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            hideProgress();
            $('#fullBackupModal').modal('hide');
            showSuccess('{{ __('messages.backup_success') }}');
        })
        .catch(error => {
            hideProgress();
            showError('{{ __('messages.error_occurred') }}: ' + (error.message || '{{ __('messages.table_not_found') }}'));
        });
    }
    
    function performFullRestore() {
        const formData = new FormData();
        formData.append('backup_file', $('#backupFile')[0].files[0]);
        formData.append('overwrite_existing', $('#overwriteExisting').is(':checked') ? 1 : 0);
        formData.append('_token', '{{ csrf_token() }}');
        
        showProgress('{{ __('messages.restoring_backup') }}');
        
        fetch('{{ route("reports.full-restore") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();
            $('#fullRestoreModal').modal('hide');
            
            if (data.success) {
                showSuccess(`${translations.restore_success} ${data.restored_tables} ${translations.records}.`);
                loadModuleStats(); // Reload statistics
            } else {
                showError('{{ __('messages.error_occurred') }}: ' + data.message);
            }
        })
        .catch(error => {
            hideProgress();
            showError('{{ __('messages.error_occurred') }}: ' + error.message);
        });
    }
    
    function showModuleStats(module, name) {
        $('#statsModalLabel').html(`<i class="fas fa-chart-bar me-2"></i> ${translations.statistics} ${name}`);
        $('#statsModalBody').html(`
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ __('messages.loading') }}</span>
                </div>
            </div>
        `);
        $('#statsModal').modal('show');
        
        $.get('{{ route("reports.module-stats") }}', { table: module })
            .done(function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#statsModalBody').html(`
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4>${data.total_records.toLocaleString()}</h4>
                                        <small>${translations.total_records}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4>${data.column_count}</h4>
                                        <small>${translations.total_columns}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4>${data.table_size} ${translations.megabytes}</h4>
                                        <small>${translations.data_size_mb}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4>${data.last_updated ? new Date(data.last_updated).toLocaleDateString('{{ app()->getLocale() }}') : translations.not_available}</h4>
                                        <small>${translations.last_updated}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mt-4 mb-3">${translations.table_structure}:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>${translations.column_name}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.columns.map((col, index) => `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td><code>${col}</code></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `);
                } else {
                    $('#statsModalBody').html(`<div class="alert alert-danger">${translations.error_occurred}</div>`);
                }
            })
            .fail(function() {
                $('#statsModalBody').html(`<div class="alert alert-danger">${translations.error_occurred}</div>`);
            });
    }
    
    function updateStats(type) {
        completedOperations++;
        $('#completed-operations').text(completedOperations);
        
        const now = new Date().toLocaleString('{{ app()->getLocale() }}');
        if (type === 'export') {
            $('#last-export').text(now);
        } else if (type === 'import') {
            $('#last-import').text(now);
        }
    }
    
    function showProgress(message) {
        $('body').append(`
            <div class="progress-overlay" style="display: flex !important;">
                <div class="progress-content">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">{{ __('messages.loading') }}</span>
                    </div>
                    <h5>${message}</h5>
                    <p class="text-muted mb-0">{{ __('messages.do_not_close') }}</p>
                </div>
            </div>
        `);
    }
    
    function hideProgress() {
        $('.progress-overlay').remove();
    }
    
    function showSuccess(message) {
        toastr.success(message, '{{ __('messages.export_success') }}', {
            timeOut: 5000,
            progressBar: true,
            positionClass: 'toast-top-right'
        });
    }
    
    function showError(message) {
        toastr.error(message, translations.error_occurred, {
            timeOut: 10000,
            progressBar: true,
            positionClass: 'toast-top-right'
        });
    }
});
</script>
@endpush