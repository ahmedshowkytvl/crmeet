@extends('layouts.app')

@section('title', 'Employee Management - Temporary')

@push('styles')
<style>
    .toolbar {
        background: white;
        padding: 1rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box input {
        padding-right: 40px;
    }
    
    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    table {
        margin: 0;
        width: 100%;
    }
    
    th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 10;
        padding: 1rem 0.75rem;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    
    .editable {
        position: relative;
        cursor: pointer;
    }
    
    .editable:hover {
        background: #f8f9fa;
    }
    
    .editable.editing {
        background: #fff3cd;
    }
    
    .editable input {
        border: 2px solid #0d6efd;
        border-radius: 4px;
        padding: 0.5rem;
        width: 100%;
        font-size: 0.9rem;
    }
    
    .checkbox-cell {
        text-align: center;
        width: 50px;
    }
    
    .actions-cell {
        width: 100px;
    }
    
    .action-btns {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-save, .btn-cancel {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .bulk-actions {
        display: none;
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        background: #0d6efd;
        color: white;
        padding: 1rem 2rem;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 1000;
    }
    
    .bulk-actions.show {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .stat-card h3 {
        font-size: 2rem;
        font-weight: bold;
        color: #0d6efd;
        margin: 0;
    }
    
    .stat-card p {
        color: #6c757d;
        margin: 0.5rem 0 0 0;
    }
    
    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #e9ecef;
        border-radius: 50px;
        font-size: 0.875rem;
    }
    
    .filter-badge.active {
        background: #0d6efd;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="fas fa-users-cog me-2"></i>
                Employee Management - Temporary
            </h1>
            <p class="text-muted">Manage employees data with inline editing and bulk operations</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3 id="totalEmployees">0</h3>
            <p>Total Employees</p>
        </div>
        <div class="stat-card">
            <h3 id="selectedCount">0</h3>
            <p>Selected</p>
        </div>
        <div class="stat-card">
            <h3 id="filteredCount">0</h3>
            <p>Filtered</p>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="search-box">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, work number, ID number, or email...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" id="selectAllBtn">
                        <i class="fas fa-check-square me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="deselectAllBtn">
                        <i class="fas fa-times me-1"></i> Deselect All
                    </button>
                    <button type="button" class="btn btn-success" id="exportBtn">
                        <i class="fas fa-file-export me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Quick Filters -->
        <div class="row mt-3">
            <div class="col-12">
                <strong>Quick Filters: </strong>
                <button class="filter-badge" data-filter="all">
                    All
                </button>
                <button class="filter-badge" data-filter="active">
                    Active
                </button>
                <button class="filter-badge" data-filter="inactive">
                    Inactive
                </button>
                <button class="filter-badge" data-filter="hasWorkNumber">
                    Has Work Number
                </button>
                <button class="filter-badge" data-filter="noWorkNumber">
                    Missing Work Number
                </button>
                <button class="filter-badge" data-filter="hasIdNumber">
                    Has ID Number
                </button>
                <button class="filter-badge" data-filter="noIdNumber">
                    Missing ID Number
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover" id="employeesTable">
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" id="selectAllCheckbox">
                        </th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Work Number</th>
                        <th>ID Number</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="actions-cell">Actions</th>
                    </tr>
                </thead>
                <tbody id="employeesTableBody">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="bulk-actions" id="bulkActionsBar">
        <span id="selectedBadge" class="badge">0 selected</span>
        <button class="btn btn-light btn-sm" id="bulkEditBtn">
            <i class="fas fa-edit me-1"></i> Bulk Edit
        </button>
        <button class="btn btn-light btn-sm" id="bulkDeleteBtn">
            <i class="fas fa-trash me-1"></i> Delete
        </button>
        <button class="btn btn-light btn-sm" id="bulkStatusBtn">
            <i class="fas fa-toggle-on me-1"></i> Toggle Status
        </button>
        <button class="btn btn-info btn-sm" id="columnTransferBtn">
            <i class="fas fa-exchange-alt me-1"></i> Column Transfer
        </button>
        <button class="btn btn-danger btn-sm" id="closeBulkActions">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Edit - <span id="bulkEditCount">0</span> Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkEditForm">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="bulkStatus">
                            <option value="">-- No Change --</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id" id="bulkDepartment">
                            <option value="">-- No Change --</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="bulkRole">
                            <option value="">-- No Change --</option>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBulkEditBtn">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Column Transfer Modal -->
<div class="modal fade" id="columnTransferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Column Transfer - <span id="transferCount">0</span> Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    This will copy values from Source Column to Target Column for selected employees.
                </div>
                <form id="columnTransferForm">
                    <div class="mb-3">
                        <label class="form-label">Source Column (من)</label>
                        <select class="form-select" id="sourceColumn" required>
                            <option value="">-- Select Source --</option>
                            <option value="work_number">Work Number</option>
                            <option value="id_number">ID Number</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Column (إلى)</label>
                        <select class="form-select" id="targetColumn" required>
                            <option value="">-- Select Target --</option>
                            <option value="work_number">Work Number</option>
                            <option value="id_number">ID Number</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="overwriteExisting" checked>
                        <label class="form-check-label" for="overwriteExisting">
                            Overwrite existing values in target column
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="executeTransferBtn">
                    <i class="fas fa-exchange-alt me-1"></i> Execute Transfer
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let employees = [];
    let filteredEmployees = [];
    let editingCell = null;
    let selectedIds = new Set();
    
    // Initialize
    loadEmployees();
    
    // Event Listeners
    document.getElementById('searchInput').addEventListener('input', handleSearch);
    document.getElementById('selectAllBtn').addEventListener('click', selectAll);
    document.getElementById('deselectAllBtn').addEventListener('click', deselectAll);
    document.getElementById('selectAllCheckbox').addEventListener('change', toggleSelectAll);
    document.getElementById('exportBtn').addEventListener('click', exportData);
    document.getElementById('closeBulkActions').addEventListener('click', closeBulkActions);
    document.getElementById('bulkEditBtn').addEventListener('click', openBulkEditModal);
    document.getElementById('saveBulkEditBtn').addEventListener('click', saveBulkEdit);
    document.getElementById('bulkStatusBtn').addEventListener('click', toggleBulkStatus);
    document.getElementById('bulkDeleteBtn').addEventListener('click', bulkDelete);
    document.getElementById('columnTransferBtn').addEventListener('click', openColumnTransferModal);
    document.getElementById('executeTransferBtn').addEventListener('click', executeColumnTransfer);
    
    // Filter badges
    document.querySelectorAll('.filter-badge').forEach(badge => {
        badge.addEventListener('click', function() {
            document.querySelectorAll('.filter-badge').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            applyFilter(filter);
        });
    });
    
    function loadEmployees() {
        fetch('/employees/temp-data')
            .then(response => response.json())
            .then(data => {
                employees = data;
                filteredEmployees = employees;
                renderTable();
                updateStats();
            })
            .catch(error => {
                console.error('Error loading employees:', error);
                // Fallback to sample data
                employees = sampleData();
                filteredEmployees = employees;
                renderTable();
                updateStats();
            });
    }
    
    function sampleData() {
        return [
            { id: 1, name: 'Ahmed Ali', work_number: 'E001', id_number: '29012345678901', email: 'ahmed@example.com', phone: '01234567890', department: 'IT', status: 'active' },
            { id: 2, name: 'Fatima Hassan', work_number: 'E002', id_number: '29123456789012', email: 'fatima@example.com', phone: '01234567891', department: 'HR', status: 'active' },
            { id: 3, name: 'Mohammed Said', work_number: 'E003', id_number: '', email: 'mohammed@example.com', phone: '01234567892', department: 'Marketing', status: 'inactive' },
            { id: 4, name: 'Mariam Mostafa', work_number: '', id_number: '29334567890123', email: 'mariam@example.com', phone: '01234567893', department: 'Sales', status: 'active' },
            { id: 5, name: 'Ali Hassan', work_number: 'E005', id_number: '29445678901234', email: 'ali@example.com', phone: '01234567894', department: 'IT', status: 'active' }
        ];
    }
    
    function renderTable() {
        const tbody = document.getElementById('employeesTableBody');
        tbody.innerHTML = '';
        
        filteredEmployees.forEach(emp => {
            const tr = document.createElement('tr');
            tr.dataset.id = emp.id;
            tr.innerHTML = `
                <td class="checkbox-cell">
                    <input type="checkbox" class="row-checkbox" value="${emp.id}" ${selectedIds.has(emp.id) ? 'checked' : ''}>
                </td>
                <td>${emp.id}</td>
                <td class="editable" data-field="name" data-id="${emp.id}">${emp.name || '-'}</td>
                <td class="editable" data-field="work_number" data-id="${emp.id}">${emp.work_number || '-'}</td>
                <td class="editable" data-field="id_number" data-id="${emp.id}">${emp.id_number || '-'}</td>
                <td class="editable" data-field="email" data-id="${emp.id}">${emp.email || '-'}</td>
                <td class="editable" data-field="phone" data-id="${emp.id}">${emp.phone || '-'}</td>
                <td class="editable" data-field="department" data-id="${emp.id}">${emp.department || '-'}</td>
                <td><span class="badge ${emp.status === 'active' ? 'bg-success' : 'bg-danger'}">${emp.status || 'inactive'}</span></td>
                <td class="actions-cell">
                    <button class="btn btn-sm btn-primary" onclick="editRow(${emp.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRow(${emp.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        // Add editable cell listeners
        document.querySelectorAll('.editable').forEach(cell => {
            cell.addEventListener('click', handleCellEdit);
        });
        
        // Add checkbox listeners
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });
    }
    
    function handleCellEdit(e) {
        if (editingCell) return;
        
        const cell = e.currentTarget;
        const field = cell.dataset.field;
        const id = cell.dataset.id;
        const currentValue = cell.textContent.trim();
        
        if (currentValue === '-') return;
        
        cell.classList.add('editing');
        editingCell = { cell, field, id, originalValue: currentValue };
        
        const input = document.createElement('input');
        input.type = 'text';
        input.value = currentValue;
        input.className = 'form-control';
        cell.innerHTML = '';
        cell.appendChild(input);
        input.focus();
        input.select();
        
        const saveBtn = document.createElement('button');
        saveBtn.className = 'btn btn-sm btn-success btn-save';
        saveBtn.innerHTML = '<i class="fas fa-check"></i>';
        saveBtn.onclick = () => saveCellEdit();
        
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-sm btn-secondary btn-cancel';
        cancelBtn.innerHTML = '<i class="fas fa-times"></i>';
        cancelBtn.onclick = () => cancelCellEdit();
        
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'action-btns mt-2';
        actionsDiv.appendChild(saveBtn);
        actionsDiv.appendChild(cancelBtn);
        cell.appendChild(actionsDiv);
        
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') saveCellEdit();
            if (e.key === 'Escape') cancelCellEdit();
        });
    }
    
    function saveCellEdit() {
        if (!editingCell) return;
        
        const { cell, field, id, originalValue } = editingCell;
        const input = cell.querySelector('input');
        const newValue = input.value.trim();
        
        if (newValue === originalValue) {
            cancelCellEdit();
            return;
        }
        
        // Update in memory
        const employee = employees.find(e => e.id == id);
        if (employee) {
            employee[field] = newValue;
        }
        
        const filteredEmp = filteredEmployees.find(e => e.id == id);
        if (filteredEmp) {
            filteredEmp[field] = newValue;
        }
        
        // Simulate API call
        saveToAPI(id, field, newValue);
        
        cell.classList.remove('editing');
        cell.textContent = newValue || '-';
        editingCell = null;
        
        updateStats();
    }
    
    function cancelCellEdit() {
        if (!editingCell) return;
        
        const { cell, originalValue } = editingCell;
        cell.classList.remove('editing');
        cell.textContent = originalValue;
        editingCell = null;
    }
    
    function saveToAPI(id, field, value) {
        console.log(`Saving ${field} for employee ${id}: ${value}`);
        
        fetch(`/employees/${id}/temp-update`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ field: field, value: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Changes saved successfully', 'success');
            } else {
                showToast('Error saving changes', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error saving changes', 'danger');
        });
    }
    
    function handleCheckboxChange(e) {
        const checkbox = e.target;
        const id = parseInt(checkbox.value);
        
        if (checkbox.checked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        
        updateBulkActions();
    }
    
    function selectAll() {
        filteredEmployees.forEach(emp => selectedIds.add(emp.id));
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = true);
        document.getElementById('selectAllCheckbox').checked = true;
        updateBulkActions();
    }
    
    function deselectAll() {
        selectedIds.clear();
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAllCheckbox').checked = false;
        updateBulkActions();
    }
    
    function toggleSelectAll(e) {
        const checked = e.target.checked;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
        
        if (checked) {
            filteredEmployees.forEach(emp => selectedIds.add(emp.id));
        } else {
            filteredEmployees.forEach(emp => selectedIds.delete(emp.id));
        }
        
        updateBulkActions();
    }
    
    function handleSearch(e) {
        const query = e.target.value.toLowerCase();
        filteredEmployees = employees.filter(emp => {
            return emp.name?.toLowerCase().includes(query) ||
                   emp.work_number?.toLowerCase().includes(query) ||
                   emp.id_number?.toLowerCase().includes(query) ||
                   emp.email?.toLowerCase().includes(query) ||
                   emp.phone?.includes(query) ||
                   emp.department?.toLowerCase().includes(query);
        });
        
        renderTable();
        updateStats();
    }
    
    function applyFilter(filter) {
        switch(filter) {
            case 'all':
                filteredEmployees = employees;
                break;
            case 'active':
                filteredEmployees = employees.filter(e => e.status === 'active');
                break;
            case 'inactive':
                filteredEmployees = employees.filter(e => e.status === 'inactive');
                break;
            case 'hasWorkNumber':
                filteredEmployees = employees.filter(e => e.work_number && e.work_number.trim() !== '');
                break;
            case 'noWorkNumber':
                filteredEmployees = employees.filter(e => !e.work_number || e.work_number.trim() === '');
                break;
            case 'hasIdNumber':
                filteredEmployees = employees.filter(e => e.id_number && e.id_number.trim() !== '');
                break;
            case 'noIdNumber':
                filteredEmployees = employees.filter(e => !e.id_number || e.id_number.trim() === '');
                break;
        }
        
        renderTable();
        updateStats();
    }
    
    function updateStats() {
        document.getElementById('totalEmployees').textContent = employees.length;
        document.getElementById('selectedCount').textContent = selectedIds.size;
        document.getElementById('filteredCount').textContent = filteredEmployees.length;
    }
    
    function updateBulkActions() {
        const count = selectedIds.size;
        document.getElementById('selectedBadge').textContent = `${count} selected`;
        
        if (count > 0) {
            document.getElementById('bulkActionsBar').classList.add('show');
        } else {
            document.getElementById('bulkActionsBar').classList.remove('show');
        }
    }
    
    function closeBulkActions() {
        selectedIds.clear();
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        updateBulkActions();
    }
    
    function exportData() {
        const dataToExport = filteredEmployees.length > 0 ? filteredEmployees : employees;
        const csv = convertToCSV(dataToExport);
        downloadCSV(csv, 'employees.csv');
    }
    
    function convertToCSV(data) {
        if (!data || data.length === 0) return '';
        
        const headers = Object.keys(data[0]);
        const rows = data.map(row => 
            headers.map(header => `"${row[header] || ''}"`).join(',')
        );
        
        return [headers.join(','), ...rows].join('\n');
    }
    
    function downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }
    
    function openBulkEditModal() {
        const count = selectedIds.size;
        document.getElementById('bulkEditCount').textContent = count;
        const modal = new bootstrap.Modal(document.getElementById('bulkEditModal'));
        modal.show();
    }
    
    function saveBulkEdit() {
        const form = document.getElementById('bulkEditForm');
        const formData = new FormData(form);
        const changes = {};
        
        Array.from(formData.entries()).forEach(([key, value]) => {
            if (value) changes[key] = value;
        });
        
        if (Object.keys(changes).length === 0) {
            showToast('No changes to save', 'warning');
            return;
        }
        
        selectedIds.forEach(id => {
            const emp = employees.find(e => e.id == id);
            if (emp) {
                Object.assign(emp, changes);
            }
        });
        
        showToast(`Updated ${selectedIds.size} employees`, 'success');
        selectedIds.clear();
        renderTable();
        updateStats();
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('bulkEditModal'));
        modal.hide();
    }
    
    function toggleBulkStatus() {
        selectedIds.forEach(id => {
            const emp = employees.find(e => e.id == id);
            if (emp) {
                emp.status = emp.status === 'active' ? 'inactive' : 'active';
            }
        });
        
        showToast(`Toggled status for ${selectedIds.size} employees`, 'success');
        renderTable();
    }
    
    function bulkDelete() {
        if (!confirm(`Are you sure you want to delete ${selectedIds.size} employees?`)) return;
        
        employees = employees.filter(e => !selectedIds.has(e.id));
        filteredEmployees = filteredEmployees.filter(e => !selectedIds.has(e.id));
        selectedIds.clear();
        
        showToast('Employees deleted', 'success');
        renderTable();
        updateStats();
    }
    
    function openColumnTransferModal() {
        const count = selectedIds.size;
        document.getElementById('transferCount').textContent = count;
        const modal = new bootstrap.Modal(document.getElementById('columnTransferModal'));
        modal.show();
    }
    
    function executeColumnTransfer() {
        const sourceColumn = document.getElementById('sourceColumn').value;
        const targetColumn = document.getElementById('targetColumn').value;
        const overwrite = document.getElementById('overwriteExisting').checked;
        
        if (!sourceColumn || !targetColumn) {
            showToast('Please select both source and target columns', 'warning');
            return;
        }
        
        if (sourceColumn === targetColumn) {
            showToast('Source and target columns cannot be the same', 'warning');
            return;
        }
        
        if (!confirm(`Transfer ${sourceColumn} to ${targetColumn} for ${selectedIds.size} employees?`)) return;
        
        // Save to API
        const idsArray = Array.from(selectedIds);
        
        fetch('/employees/bulk-column-transfer', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({
                ids: idsArray,
                source_column: sourceColumn,
                target_column: targetColumn,
                overwrite: overwrite
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update local data based on what was transferred
                selectedIds.forEach(id => {
                    const emp = employees.find(e => e.id == id);
                    if (emp) {
                        const sourceValue = emp[sourceColumn];
                        const targetValue = emp[targetColumn];
                        
                        // Only update if transfer actually happened
                        if (sourceValue && sourceValue.trim() !== '' && sourceValue !== '-') {
                            if (overwrite || !targetValue || targetValue.trim() === '' || targetValue === '-') {
                                emp[targetColumn] = sourceValue;
                            }
                        }
                    }
                });
                
                // Also update filteredEmployees
                filteredEmployees.forEach(emp => {
                    if (selectedIds.has(emp.id)) {
                        const sourceValue = emp[sourceColumn];
                        const targetValue = emp[targetColumn];
                        
                        if (sourceValue && sourceValue.trim() !== '' && sourceValue !== '-') {
                            if (overwrite || !targetValue || targetValue.trim() === '' || targetValue === '-') {
                                emp[targetColumn] = sourceValue;
                            }
                        }
                    }
                });
                
                showToast(`Successfully transferred ${data.transferred} values. Skipped ${data.skipped} due to existing values.`, 'success');
                selectedIds.clear();
                renderTable();
                updateStats();
            } else {
                showToast('Error transferring columns: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error transferring columns: ' + error.message, 'danger');
        });
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('columnTransferModal'));
        modal.hide();
    }
    
    function editRow(id) {
        console.log('Edit row:', id);
        // Implement row editing modal or inline editing
    }
    
    function deleteRow(id) {
        if (!confirm('Are you sure you want to delete this employee?')) return;
        
        employees = employees.filter(e => e.id !== id);
        filteredEmployees = filteredEmployees.filter(e => e.id !== id);
        selectedIds.delete(id);
        
        showToast('Employee deleted', 'success');
        renderTable();
        updateStats();
    }
    
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'}`;
        toast.textContent = message;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }
    
    // Expose functions globally for inline handlers
    window.editRow = editRow;
    window.deleteRow = deleteRow;
});
</script>
@endpush

