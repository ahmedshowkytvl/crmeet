@extends('layouts.app')

@section('title', 'إحصائيات التقدم')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-chart-line me-2"></i>
                إحصائيات التقدم
            </h2>
            <p class="text-muted">تتبع تقدم الموظفين بناءً على الوقت المقدر للمهام المكتملة</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt me-2"></i>
                تحديث البيانات
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filter_date" class="form-label">التاريخ</label>
                    <input type="date" class="form-control" id="filter_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="filter_user" class="form-label">المستخدم</label>
                    <select class="form-select" id="filter_user">
                        <option value="">جميع المستخدمين</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_view" class="form-label">نوع العرض</label>
                    <select class="form-select" id="filter_view">
                        <option value="daily">يومي</option>
                        <option value="period">فترة زمنية</option>
                        <option value="all-users">جميع المستخدمين</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" onclick="loadProgressData()">
                        <i class="fas fa-search me-2"></i>
                        بحث
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Cards -->
    <div class="row mb-4" id="progress-cards" style="display: none;">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="total-progress">0%</h4>
                            <p class="mb-0">إجمالي التقدم</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-pie fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="completed-time">0</h4>
                            <p class="mb-0">ساعات مكتملة</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="work-day-completion">0</h4>
                            <p class="mb-0">أيام عمل</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="efficiency">0%</h4>
                            <p class="mb-0">الكفاءة</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tachometer-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Table -->
    <div class="card">
        <div class="card-body">
            <div id="loading" class="text-center py-4" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">جاري التحميل...</p>
            </div>
            
            <div id="progress-content">
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">اختر المعايير أعلاه لعرض إحصائيات التقدم</h5>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentData = null;

function refreshData() {
    if (currentData) {
        loadProgressData();
    }
}

function loadProgressData() {
    const date = document.getElementById('filter_date').value;
    const userId = document.getElementById('filter_user').value;
    const view = document.getElementById('filter_view').value;
    
    showLoading(true);
    
    let url = '';
    let params = new URLSearchParams();
    
    switch(view) {
        case 'daily':
            url = '/api/task-progress/user';
            if (userId) params.append('user_id', userId);
            params.append('date', date);
            break;
        case 'period':
            url = '/api/task-progress/period';
            if (userId) params.append('user_id', userId);
            params.append('start_date', date);
            // Default to one week period
            const endDate = new Date(date);
            endDate.setDate(endDate.getDate() + 7);
            params.append('end_date', endDate.toISOString().split('T')[0]);
            break;
        case 'all-users':
            url = '/api/task-progress/all-users';
            params.append('date', date);
            break;
    }
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    fetch(url, {
        headers: {
            'Authorization': 'Bearer ' + getAuthToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            currentData = data;
            displayProgressData(data, view);
        } else {
            showError(data.error || 'حدث خطأ في تحميل البيانات');
        }
    })
    .catch(error => {
        showLoading(false);
        showError('خطأ في الاتصال: ' + error.message);
    });
}

function displayProgressData(data, view) {
    const contentDiv = document.getElementById('progress-content');
    const cardsDiv = document.getElementById('progress-cards');
    
    // Show cards
    cardsDiv.style.display = 'block';
    
    let html = '';
    
    if (view === 'all-users') {
        // Display all users progress
        updateCards({
            total_progress: data.average_progress || 0,
            completed_time: data.users_progress.reduce((sum, user) => sum + user.completed_time, 0),
            work_day_completion: data.users_progress.reduce((sum, user) => sum + user.work_day_completion, 0),
            efficiency: data.users_progress.reduce((sum, user) => sum + user.efficiency, 0) / data.users_progress.length
        });
        
        html = `
            <h5 class="mb-3">
                <i class="fas fa-users me-2"></i>
                تقدم جميع المستخدمين - ${data.date}
            </h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>القسم</th>
                            <th>التقدم %</th>
                            <th>الساعات المكتملة</th>
                            <th>أيام العمل</th>
                            <th>الكفاءة %</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.users_progress.forEach(user => {
            const efficiencyClass = user.efficiency >= 100 ? 'text-success' : 
                                   user.efficiency >= 80 ? 'text-warning' : 'text-danger';
            
            html += `
                <tr>
                    <td>${user.user_name_ar || user.user_name}</td>
                    <td>${user.department}</td>
                    <td><span class="badge bg-primary">${user.progress_percentage}%</span></td>
                    <td>${user.completed_time} ساعة</td>
                    <td>${user.work_day_completion}</td>
                    <td><span class="${efficiencyClass}">${user.efficiency}%</span></td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        
    } else if (view === 'period') {
        // Display period progress
        updateCards(data.summary);
        
        html = `
            <h5 class="mb-3">
                <i class="fas fa-calendar-week me-2"></i>
                تقدم ${data.user.user_name_ar || data.user.user_name} - من ${data.period.start_date} إلى ${data.period.end_date}
            </h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>التقدم %</th>
                            <th>الساعات المكتملة</th>
                            <th>أيام العمل</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.daily_progress.forEach(day => {
            html += `
                <tr>
                    <td>${day.date}</td>
                    <td><span class="badge bg-primary">${day.progress_percentage}%</span></td>
                    <td>${day.completed_time} ساعة</td>
                    <td>${day.work_day_completion}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        
    } else {
        // Display single user daily progress
        updateCards(data.stats);
        
        html = `
            <h5 class="mb-3">
                <i class="fas fa-user me-2"></i>
                تقدم ${data.user.user_name_ar || data.user.user_name} - ${data.date}
            </h5>
        `;
        
        if (data.completed_tasks && data.completed_tasks.length > 0) {
            html += `
                <h6>المهام المكتملة:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>المهمة</th>
                                <th>القالب</th>
                                <th>الوقت المقدر</th>
                                <th>وقت الإكمال</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.completed_tasks.forEach(task => {
                html += `
                    <tr>
                        <td>${task.title}</td>
                        <td>${task.template_name || 'بدون قالب'}</td>
                        <td>${task.estimated_time} ساعة</td>
                        <td>${task.completed_at}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        } else {
            html += '<div class="alert alert-info">لا توجد مهام مكتملة لهذا التاريخ</div>';
        }
    }
    
    contentDiv.innerHTML = html;
}

function updateCards(stats) {
    document.getElementById('total-progress').textContent = (stats.total_progress || stats.progress_percentage || 0) + '%';
    document.getElementById('completed-time').textContent = (stats.total_completed_time || stats.completed_time || 0) + ' ساعة';
    document.getElementById('work-day-completion').textContent = (stats.total_work_days_completion || stats.work_day_completion || 0);
    document.getElementById('efficiency').textContent = (stats.average_efficiency || stats.efficiency || 0) + '%';
}

function showLoading(show) {
    document.getElementById('loading').style.display = show ? 'block' : 'none';
    document.getElementById('progress-content').style.display = show ? 'none' : 'block';
}

function showError(message) {
    const contentDiv = document.getElementById('progress-content');
    contentDiv.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${message}
        </div>
    `;
}

function getAuthToken() {
    // Get token from meta tag or localStorage
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return token || localStorage.getItem('auth_token') || '';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Load today's data by default
    loadProgressData();
});
</script>
@endpush
