@extends('layouts.app')

@section('title', __('messages.dashboard') . ' - ' . __('messages.system_title'))

@push('styles')
<style>
.draggable-section {
    transition: all 0.3s ease;
    cursor: move;
}

.draggable-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.drag-handle {
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.drag-handle:hover {
    opacity: 1;
}

.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.02);
}

.sortable-drag {
    opacity: 0.8;
}

.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.toast-container {
    z-index: 9999;
}
</style>
@endpush

@section('content')
<div class="col-md-10 main-content">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i> Dashboard</h2>
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetDashboardLayout()" title="Reset Dashboard Layout" style="transform: translateY(0px);">
            <i class="fas fa-undo me-1"></i>Reset Layout
        </button>
        <div class="text-end">
            <div class="text-muted">
                <i class="fas fa-clock me-1"></i>
                <span id="current-time">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="small text-muted">
                <i class="fas fa-globe me-1"></i>
                <span id="device-timezone">Africa/Cairo (UTC+02:00)</span>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 " id="statistics-row">
    <div class="col-md-3 mb-3" data-section="total-users">
        <div class="card bg-primary draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-body text-center">
                <div class="drag-handle text-muted mb-2" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <i class="fas fa-users fa-2x mb-2"></i>
                <h3 class="">{{ $stats['total_users'] }}</h3>
                <p class="mb-0" style="color: #fff;">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3" data-section="pending-requests">
        <div class="card stats-card danger draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-body text-center">
                <div class="drag-handle text-muted mb-2" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h3 class="">{{ $stats['pending_requests'] }}</h3>
                <p class="mb-0">Pending Requests</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3" data-section="total-tasks">
        <div class="card bg-success draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-body text-center">
                <div class="drag-handle text-muted mb-2" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <i class="fas fa-tasks fa-2x mb-2"></i>
                <h3>{{ $stats['total_tasks'] }}</h3>
                <p class="mb-0" style="color: #fff;">Total Tasks</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3" data-section="total-departments">
        <div class="card stats-card warning draggable-section" style="background:#DF5425;opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-body text-center">
                <div class="drag-handle text-muted mb-2" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <i class="fas fa-building fa-2x mb-2"></i>
                <h3 style="color: #fff;">{{ $stats['total_departments'] }}</h3>
                <p class="mb-0">Total Departments</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Row -->
<div class="row mb-4" id="activity-row">
    <!-- Recent Tasks -->
    <div class="col-md-6 mb-3" data-section="recent-tasks">
        <div class="card draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Recent Tasks</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="drag-handle text-muted" style="cursor: move;">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary viewmore-anchor" style="transform: translateY(0px);">View Tasks</a>
                </div>
            </div>
            <div class="card-body">
                @if($stats['recent_tasks']->count() > 0)
                    @foreach($stats['recent_tasks'] as $task)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <h6 class="mb-1">{{ $task->title }}</h6>
                                <small class="text-muted">{{ $task->assignedTo->name ?? __('messages.no_data') }}</small>
                            </div>
                            <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                {{ $task->status == 'completed' ? 'Completed' : ($task->status == 'in_progress' ? 'In Progress' : ucfirst($task->status)) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">{{ __('messages.no_data') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="col-md-6 mb-3" data-section="recent-requests">
        <div class="card draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent Requests</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="drag-handle text-muted" style="cursor: move;">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    <a href="{{ route('requests.index') }}" class="btn viewmore-anchor btn-sm btn-outline-primary" style="transform: translateY(0px);">View Requests</a>
                </div>
            </div>
            <div class="card-body">
                @if($stats['recent_requests']->count() > 0)
                    @foreach($stats['recent_requests'] as $request)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <h6 class="mb-1">{{ $request->title }}</h6>
                                <small class="text-muted">{{ $request->employee->name ?? __('messages.no_data') }}</small>
                            </div>
                            <span class="badge bg-{{ $request->status == 'approved' ? 'success' : ($request->status == 'rejected' ? 'danger' : 'warning') }}">
                                {{ __('messages.' . $request->status) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">No data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Quick Actions and Time Zones -->
<div class="row" id="bottom-row">
    <!-- Quick Actions -->
    <div class="col-md-6 mb-3" data-section="quick-actions">
        <div class="card draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                <div class="drag-handle text-muted" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('users.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>Add User
                        </a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('tasks.create') }}" class="btn btn-success w-100" style="transform: translateY(0px);">
                            <i class="fas fa-plus me-2"></i>Assign Task
                        </a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('departments.create') }}" class="btn btn-info w-100">
                            <i class="fas fa-building me-2"></i>Add Department
                        </a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('requests.create') }}" class="btn btn-warning w-100" style="transform: translateY(0px);">
                            <i class="fas fa-file-plus me-2"></i>Add Request
                        </a>
                    </div>
                </div>
                
                <!-- Organizational Chart Links - Only for Software Developers -->
                @if(auth()->user()->role && auth()->user()->role->slug === 'software_developer')
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('org-chart') }}" class="btn btn-lg btn-outline-primary" target="_blank">
                                <i class="fas fa-sitemap me-2"></i>الهيكل التنظيمي (ثابت)
                                <small class="d-block mt-1">صفحة منفصلة - Full Screen</small>
                            </a>
                            <a href="{{ route('dynamic-org-chart') }}" class="btn btn-lg btn-outline-success" target="_blank">
                                <i class="fas fa-sitemap me-2"></i>الهيكل التنظيمي (ديناميكي)
                                <small class="d-block mt-1">بيانات حقيقية من النظام</small>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Device Time Zone -->
    <div class="col-md-3 mb-3" data-section="device-timezone">
        <div class="card border-info draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-globe me-2"></i>Device Time Zone</h5>
                <div class="drag-handle text-white" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                    <h6>Current Time</h6>
                    <div id="live-time" class="h5 text-primary"></div>
                </div>
                <div class="text-center">
                    <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                    <h6>Time Zone</h6>
                    <div id="timezone-info" class="h6 text-success">
                        <div class="fw-bold">Africa/Cairo</div>
                        <div class="small">UTC+02:00</div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        This shows your device's local time and timezone
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Time Zone -->
    <div class="col-md-3 mb-3" data-section="server-timezone">
        <div class="card border-warning draggable-section" style="opacity: 1; transform: translateY(0px); transition: 0.5s;">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-server me-2"></i>Server Time Zone</h5>
                <div class="drag-handle text-dark" style="cursor: move;">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-server fa-2x text-warning mb-2"></i>
                    <h6>Server Time</h6>
                    <div class="h5 text-warning">{{ now()->format('H:i:s') }}</div>
                </div>
                <div class="text-center">
                    <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                    <h6>Server Date</h6>
                    <div class="h6 text-info">{{ now()->format('d/m/Y') }}</div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Server timezone: UTC
                    </small>
                </div>
            </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update live time
    function updateLiveTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        
        const liveTimeElement = document.getElementById('live-time');
        if (liveTimeElement) {
            liveTimeElement.textContent = timeString;
        }
        
        // Update header time
        const headerTimeElement = document.getElementById('current-time');
        if (headerTimeElement) {
            const dateString = now.toLocaleDateString('en-GB');
            headerTimeElement.textContent = `${dateString} ${timeString}`;
        }
    }
    
    // Function to get and display timezone information
    function displayTimezoneInfo() {
        const now = new Date();
        
        // Get timezone offset in minutes
        const timezoneOffset = now.getTimezoneOffset();
        const offsetHours = Math.abs(Math.floor(timezoneOffset / 60));
        const offsetMinutes = Math.abs(timezoneOffset % 60);
        const offsetSign = timezoneOffset <= 0 ? '+' : '-';
        
        // Format offset string
        const offsetString = `UTC${offsetSign}${offsetHours.toString().padStart(2, '0')}:${offsetMinutes.toString().padStart(2, '0')}`;
        
        // Get timezone name
        const timezoneName = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        // Display timezone info
        const timezoneInfoElement = document.getElementById('timezone-info');
        if (timezoneInfoElement) {
            timezoneInfoElement.innerHTML = `
                <div class="fw-bold">${timezoneName}</div>
                <div class="small">${offsetString}</div>
            `;
        }
        
        // Display in header
        const deviceTimezoneElement = document.getElementById('device-timezone');
        if (deviceTimezoneElement) {
            deviceTimezoneElement.textContent = `${timezoneName} (${offsetString})`;
        }
    }
    
    // Initialize
    updateLiveTime();
    displayTimezoneInfo();
    
    // Update time every second
    setInterval(updateLiveTime, 1000);
    
    // Update timezone info every minute (in case of DST changes)
    setInterval(displayTimezoneInfo, 60000);
    
    // Dashboard Drag & Drop Functionality
    function initializeDashboardDragDrop() {
        // Statistics Cards Drag & Drop
        const statisticsRow = document.getElementById('statistics-row');
        if (statisticsRow) {
            new Sortable(statisticsRow, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function(evt) {
                    saveDashboardOrder();
                }
            });
        }
        
        // Activity Row Drag & Drop
        const activityRow = document.getElementById('activity-row');
        if (activityRow) {
            new Sortable(activityRow, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function(evt) {
                    saveDashboardOrder();
                }
            });
        }
        
        // Bottom Row Drag & Drop
        const bottomRow = document.getElementById('bottom-row');
        if (bottomRow) {
            new Sortable(bottomRow, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function(evt) {
                    saveDashboardOrder();
                }
            });
        }
    }
    
    // Save dashboard order to localStorage
    function saveDashboardOrder() {
        const order = {
            statistics: [],
            activity: [],
            bottom: []
        };
        
        // Get statistics order
        const statisticsRow = document.getElementById('statistics-row');
        if (statisticsRow) {
            Array.from(statisticsRow.children).forEach(child => {
                const section = child.getAttribute('data-section');
                if (section) order.statistics.push(section);
            });
        }
        
        // Get activity order
        const activityRow = document.getElementById('activity-row');
        if (activityRow) {
            Array.from(activityRow.children).forEach(child => {
                const section = child.getAttribute('data-section');
                if (section) order.activity.push(section);
            });
        }
        
        // Get bottom order
        const bottomRow = document.getElementById('bottom-row');
        if (bottomRow) {
            Array.from(bottomRow.children).forEach(child => {
                const section = child.getAttribute('data-section');
                if (section) order.bottom.push(section);
            });
        }
        
        // Save to localStorage
        localStorage.setItem('dashboard-order', JSON.stringify(order));
        
        // Show success message
        showToast('Dashboard layout saved!', 'success');
    }
    
    // Load dashboard order from localStorage
    function loadDashboardOrder() {
        const savedOrder = localStorage.getItem('dashboard-order');
        if (savedOrder) {
            try {
                const order = JSON.parse(savedOrder);
                
                // Apply statistics order
                if (order.statistics && order.statistics.length > 0) {
                    const statisticsRow = document.getElementById('statistics-row');
                    if (statisticsRow) {
                        reorderElements(statisticsRow, order.statistics);
                    }
                }
                
                // Apply activity order
                if (order.activity && order.activity.length > 0) {
                    const activityRow = document.getElementById('activity-row');
                    if (activityRow) {
                        reorderElements(activityRow, order.activity);
                    }
                }
                
                // Apply bottom order
                if (order.bottom && order.bottom.length > 0) {
                    const bottomRow = document.getElementById('bottom-row');
                    if (bottomRow) {
                        reorderElements(bottomRow, order.bottom);
                    }
                }
            } catch (e) {
                console.error('Error loading dashboard order:', e);
            }
        }
    }
    
    // Reorder elements based on saved order
    function reorderElements(container, order) {
        const children = Array.from(container.children);
        const sortedChildren = [];
        
        order.forEach(sectionName => {
            const child = children.find(c => c.getAttribute('data-section') === sectionName);
            if (child) {
                sortedChildren.push(child);
            }
        });
        
        // Add any remaining children that weren't in the order
        children.forEach(child => {
            if (!sortedChildren.includes(child)) {
                sortedChildren.push(child);
            }
        });
        
        // Replace container children
        sortedChildren.forEach(child => container.appendChild(child));
    }
    
    // Show toast notification
    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Add to page
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove after hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    // Initialize drag and drop
    initializeDashboardDragDrop();
    
    // Load saved order
    loadDashboardOrder();
    
    // Reset dashboard layout function (global scope)
    window.resetDashboardLayout = function() {
        if (confirm('Are you sure you want to reset the dashboard layout to default?')) {
            localStorage.removeItem('dashboard-order');
            location.reload();
        }
    };
});
</script>
@endpush
