@extends('layouts.app')

@section('title', 'Zoho Bulk Sync')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>
                <i class="fas fa-sync-alt me-2"></i>
                Zoho Bulk Sync - جلب التذاكر من Zoho وتخزينها في الكاش
            </h2>
            <p class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                هذه الصفحة تستخدمها لجلب التذاكر من Zoho وتخزينها في قاعدة البيانات (zoho_tickets_cached)
                <br>
                <strong>ملاحظة:</strong> صفحات العرض الأخرى تستخدم البيانات المخزنة في الكاش فقط
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">إجمالي التذاكر</h5>
                    <h3>{{ number_format($stats['total_tickets']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">تذاكر اليوم</h5>
                    <h3>{{ number_format($stats['today_tickets']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">تذاكر أمس</h5>
                    <h3>{{ number_format($stats['yesterday_tickets']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">آخر تحديث</h5>
                    <h6>{{ $stats['last_sync'] ? \Carbon\Carbon::parse($stats['last_sync'])->diffForHumans() : 'لا يوجد' }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>إعدادات الجلب من Zoho API
                    </h5>
                </div>
                <div class="card-body">
                    <form id="bulkSyncForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="selected_date" class="form-label">
                                    <i class="fas fa-calendar me-2"></i>تاريخ الجلب
                                </label>
                                <input type="date" class="form-control" id="selected_date" name="selected_date" 
                                       value="{{ old('selected_date', today()->format('Y-m-d')) }}">
                                <small class="text-muted">اختر تاريخ محدد أو اتركه فارغاً لجلب أحدث التذاكر من Zoho</small>
                            </div>
                            <div class="col-md-6">
                                <label for="target_count" class="form-label">
                                    <i class="fas fa-ticket-alt me-2"></i>عدد التذاكر المراد جلبها
                                </label>
                                <input type="number" class="form-control" id="target_count" name="target_count" 
                                       value="2000" min="1" max="5000" required>
                                <small class="text-muted">الحد الأقصى: 5000 تذكرة</small>
                            </div>
                        </div>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-database me-2"></i>
                            <strong>سيتم جلب التذاكر من Zoho وتخزينها في: <code>zoho_tickets_cached</code> table</strong>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="startSyncBtn">
                                <i class="fas fa-cloud-download-alt me-2"></i>جلب من Zoho وحفظ في الكاش
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="card mt-4" id="progressCard" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-spinner fa-spin me-2"></i>جاري التنفيذ...</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             id="progressBar" role="progressbar" style="width: 0%">
                            <span id="progressText">0%</span>
                        </div>
                    </div>
                    <div id="progressDetails"></div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="card mt-4" id="resultsCard" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>نتائج الجلب</h5>
                </div>
                <div class="card-body" id="resultsBody">
                    <!-- Results will be shown here -->
                </div>
            </div>
        </div>

        <!-- Logs Section -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>سجل العمليات</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div id="logsContainer">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            لا توجد عمليات سابقة
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulkSyncForm');
    const startBtn = document.getElementById('startSyncBtn');
    const progressCard = document.getElementById('progressCard');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressDetails = document.getElementById('progressDetails');
    const resultsCard = document.getElementById('resultsCard');
    const resultsBody = document.getElementById('resultsBody');
    const logsContainer = document.getElementById('logsContainer');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const targetCount = parseInt(formData.get('target_count'));
        const selectedDate = formData.get('selected_date');

        // Disable button and show progress
        startBtn.disabled = true;
        startBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التنفيذ...';
        
        progressCard.style.display = 'block';
        resultsCard.style.display = 'none';
        
        // Clear previous logs
        logsContainer.innerHTML = '';
        addLog('info', 'بدء عملية الجلب من Zoho...');
        
        // Show initial progress
        updateProgress(0, 'جاري البدء...');

        try {
            const response = await fetch('{{ route("zoho.bulk-sync.execute") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    target_count: targetCount,
                    selected_date: selectedDate
                })
            });

            const data = await response.json();

            if (data.success) {
                updateProgress(100, 'اكتمل بنجاح!');
                showResults(data.data);
                addLog('success', `تم إكمال الجلب بنجاح: ${data.data.total_processed} تذكرة`);
            } else {
                throw new Error('فشلت عملية الجلب');
            }
        } catch (error) {
            console.error('Error:', error);
            addLog('danger', 'حدث خطأ أثناء الجلب: ' + error.message);
            showError('حدث خطأ أثناء تنفيذ عملية الجلب');
        } finally {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-play me-2"></i>بدء الجلب الآن';
        }
    });

    function updateProgress(percentage, text) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressText.textContent = text;
    }

    function showResults(data) {
        resultsCard.style.display = 'block';
        resultsBody.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>تم الجلب بنجاح</h5>
                            <h3>${data.total_processed}</h3>
                            <small>تذكرة</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>الوقت المستغرق</h5>
                            <h3>${data.duration_seconds}</h3>
                            <small>ثانية</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <tr>
                        <th>إجمالي التذاكر المُستلمة</th>
                        <td>${data.total_fetched}</td>
                    </tr>
                    <tr>
                        <th>التذاكر المعالجة بنجاح</th>
                        <td><span class="badge bg-success">${data.total_processed}</span></td>
                    </tr>
                    <tr>
                        <th>الدفعات الفاشلة</th>
                        <td><span class="badge bg-danger">${data.failed_batches}</span></td>
                    </tr>
                    <tr>
                        <th>الحالة</th>
                        <td><span class="badge bg-success">${data.status === 'success' ? 'نجح' : 'فشل'}</span></td>
                    </tr>
                    <tr>
                        <th>وقت الإكمال</th>
                        <td>${new Date(data.completed_at).toLocaleString('ar-SA')}</td>
                    </tr>
                </table>
            </div>
        `;
    }

    function showError(message) {
        resultsCard.style.display = 'block';
        resultsCard.querySelector('.card-header').classList.remove('bg-success');
        resultsCard.querySelector('.card-header').classList.add('bg-danger');
        resultsCard.querySelector('.card-header h5').innerHTML = '<i class="fas fa-times-circle me-2"></i>خطأ في التنفيذ';
        
        resultsBody.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    function addLog(type, message) {
        const timestamp = new Date().toLocaleTimeString('ar-SA');
        const alertClass = {
            'success': 'alert-success',
            'danger': 'alert-danger',
            'info': 'alert-info',
            'warning': 'alert-warning'
        }[type] || 'alert-info';
        
        const icon = {
            'success': 'check-circle',
            'danger': 'exclamation-triangle',
            'info': 'info-circle',
            'warning': 'exclamation-circle'
        }[type] || 'info-circle';

        const logEntry = document.createElement('div');
        logEntry.className = `alert ${alertClass} mb-2`;
        logEntry.innerHTML = `
            <i class="fas fa-${icon} me-2"></i>
            <strong>${timestamp}</strong><br>
            ${message}
        `;
        
        logsContainer.insertBefore(logEntry, logsContainer.firstChild);
        
        // Keep only last 20 logs
        while (logsContainer.children.length > 20) {
            logsContainer.removeChild(logsContainer.lastChild);
        }
    }
});
</script>
@endpush
@endsection
