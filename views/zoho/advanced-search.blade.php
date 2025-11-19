@extends('layouts.app')

@section('title', 'البحث المتقدم في التذاكر')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-search me-2"></i>
                البحث المتقدم في التذاكر
            </h4>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <!-- Search by Text -->
                <div class="col-md-12">
                    <h5 class="mb-3">
                        <i class="fas fa-font me-2"></i>
                        البحث النصي
                    </h5>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchTextInput" placeholder="ابحث في العنوان، الوصف، أو رقم التذكرة...">
                        <button class="btn btn-primary" onclick="searchByText()">
                            <i class="fas fa-search me-2"></i>بحث
                        </button>
                    </div>
                </div>

                <!-- Search by Custom Field -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3">
                        <i class="fas fa-user-tie me-2"></i>
                        البحث حسب الموظف (CF_Closed_by)
                    </h5>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cfClosedByInput" placeholder="أدخل اسم الموظف...">
                        <button class="btn btn-success" onclick="searchByCustomField()">
                            <i class="fas fa-search me-2"></i>بحث
                        </button>
                    </div>
                </div>

                <!-- Search by Time Range -->
                <div class="col-md-12 mt-3">
                    <h5 class="mb-3">
                        <i class="fas fa-calendar-alt me-2"></i>
                        البحث حسب النطاق الزمني
                    </h5>
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-info" onclick="searchByTimeRange('day')">
                            <i class="fas fa-calendar-day me-2"></i>اليوم
                        </button>
                        <button class="btn btn-info" onclick="searchByTimeRange('month')">
                            <i class="fas fa-calendar-week me-2"></i>هذا الشهر
                        </button>
                        <button class="btn btn-info" onclick="searchByTimeRange('year')">
                            <i class="fas fa-calendar me-2"></i>هذا العام
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">جاري التحميل...</span>
        </div>
        <p class="mt-2">جاري البحث في التذاكر...</p>
    </div>

    <!-- Results Section -->
    <div id="resultsSection" style="display: none;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    النتائج (<span id="resultsCount">0</span>)
                </h5>
                <button class="btn btn-sm btn-outline-danger" onclick="clearResults()">
                    <i class="fas fa-times me-2"></i>مسح النتائج
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="resultsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>رقم التذكرة</th>
                                <th>العنوان</th>
                                <th>القسم</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الحالة</th>
                                <th>القناة</th>
                                <th>البريد الإلكتروني</th>
                                <th>تم الإغلاق بواسطة</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Section -->
    <div id="errorSection" class="alert alert-danger" style="display: none;">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

@push('scripts')
<script>
function searchByText() {
    const searchText = document.getElementById('searchTextInput').value.trim();
    
    if (!searchText) {
        showError('يرجى إدخال نص البحث');
        return;
    }

    performSearch('/api/zoho/advanced-search/text', { search_text: searchText });
}

function searchByCustomField() {
    const cfValue = document.getElementById('cfClosedByInput').value.trim();
    
    if (!cfValue) {
        showError('يرجى إدخال اسم الموظف');
        return;
    }

    performSearch('/api/zoho/advanced-search/custom-field', { cf_closed_by: cfValue });
}

function searchByTimeRange(period) {
    performSearch('/api/zoho/advanced-search/time-range', { period: period });
}

function performSearch(url, data) {
    // Safe element access
    const loadingIndicator = document.getElementById('loadingIndicator');
    const resultsSection = document.getElementById('resultsSection');
    const errorSection = document.getElementById('errorSection');
    
    // Show loading
    if (loadingIndicator) loadingIndicator.style.display = 'block';
    if (resultsSection) resultsSection.style.display = 'none';
    if (errorSection) errorSection.style.display = 'none';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        // Check if response is OK
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        
        // Try to parse as JSON
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('استجابة غير صحيحة من الخادم. يرجى المحاولة مرة أخرى.');
            }
        });
    })
    .then(result => {
        if (loadingIndicator) loadingIndicator.style.display = 'none';

        if (result && result.success) {
            displayResults(result.tickets, result.count);
            // Save to history
            saveToHistory(result);
        } else {
            showError(result?.error || result?.message || 'حدث خطأ أثناء البحث');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        
        let errorMessage = 'حدث خطأ أثناء الاتصال بالخادم';
        
        if (error.message.includes('HTTP 500')) {
            errorMessage = 'خطأ في الخادم. يرجى التحقق من سجلات الأخطاء (Logs)';
        } else if (error.message.includes('HTTP 401')) {
            errorMessage = 'غير مصرح لك بالوصول. يرجى تسجيل الدخول مرة أخرى';
        } else if (error.message.includes('HTTP 404')) {
            errorMessage = 'غير موجود. يرجى التحقق من الرابط';
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        showError(errorMessage);
    });
}

function displayResults(tickets, count) {
    const tbody = document.getElementById('resultsTableBody');
    const resultsSection = document.getElementById('resultsSection');
    const resultsCount = document.getElementById('resultsCount');
    
    if (!tbody || !resultsSection || !resultsCount) {
        console.error('Required elements not found');
        return;
    }
    
    tbody.innerHTML = '';

    if (!tickets || tickets.length === 0) {
        showError('لم يتم العثور على أي تذاكر');
        return;
    }

    tickets.forEach(ticket => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${ticket.ticketNumber || ticket.id || '-'}</td>
            <td>${ticket.subject || '-'}</td>
            <td>${ticket.cf?.cf_department || '-'}</td>
            <td>${formatDate(ticket.createdTime)}</td>
            <td><span class="badge bg-${getStatusBadgeColor(ticket.status)}">${ticket.status || '-'}</span></td>
            <td>${ticket.channel || '-'}</td>
            <td>${ticket.email || '-'}</td>
            <td>${ticket.cf?.cf_closed_by || '-'}</td>
        `;
        tbody.appendChild(row);
    });

    resultsCount.textContent = count;
    resultsSection.style.display = 'block';
}

function showError(message) {
    const errorMessageEl = document.getElementById('errorMessage');
    const errorSectionEl = document.getElementById('errorSection');
    
    if (errorMessageEl) errorMessageEl.textContent = message;
    if (errorSectionEl) errorSectionEl.style.display = 'block';
}

function clearResults() {
    const resultsSection = document.getElementById('resultsSection');
    const errorSection = document.getElementById('errorSection');
    const tbody = document.getElementById('resultsTableBody');
    
    if (resultsSection) resultsSection.style.display = 'none';
    if (errorSection) errorSection.style.display = 'none';
    if (tbody) tbody.innerHTML = '';
}

function saveToHistory(searchResult) {
    // Create history entry
    const historyEntry = {
        timestamp: new Date().toISOString(),
        searchType: getLastSearchType(),
        results: {
            count: searchResult.count,
            tickets: searchResult.tickets.slice(0, 5) // Save first 5 tickets
        }
    };
    
    // Get existing history
    let history = JSON.parse(localStorage.getItem('zoho_search_history') || '[]');
    
    // Add new entry to the beginning
    history.unshift(historyEntry);
    
    // Keep only last 10 searches
    if (history.length > 10) {
        history = history.slice(0, 10);
    }
    
    // Save back to localStorage
    localStorage.setItem('zoho_search_history', JSON.stringify(history));
    
    // Update history display
    updateHistoryDisplay();
}

function getLastSearchType() {
    const searchTextEl = document.getElementById('searchTextInput');
    const cfValueEl = document.getElementById('cfClosedByInput');
    
    const searchText = searchTextEl ? searchTextEl.value.trim() : '';
    const cfValue = cfValueEl ? cfValueEl.value.trim() : '';
    
    if (searchText) return 'بحث نصي';
    if (cfValue) return 'بحث بالحقل المخصص';
    return 'بحث زمني';
}

function updateHistoryDisplay() {
    // Optional: If you want to display history in the page
    const history = JSON.parse(localStorage.getItem('zoho_search_history') || '[]');
    console.log('Search history:', history);
}

// Display history on page load
document.addEventListener('DOMContentLoaded', function() {
    updateHistoryDisplay();
});

function formatDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusBadgeColor(status) {
    const colors = {
        'Open': 'danger',
        'Closed': 'success',
        'Pending': 'warning',
        'In Progress': 'info'
    };
    
    return colors[status] || 'secondary';
}
</script>
@endpush

@endsection

