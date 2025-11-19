<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مراقب النظام - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .monitor-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 20px;
            padding: 30px;
        }
        
        .status-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
        }
        
        .status-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .status-card.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .status-card.danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        
        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .metric-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .metric-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .progress-custom {
            height: 10px;
            border-radius: 10px;
            background: #f0f0f0;
            overflow: hidden;
        }
        
        .progress-bar-custom {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .activity-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 10px;
        }
        
        .status-online {
            background: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
        
        .status-offline {
            background: #dc3545;
        }
        
        .status-warning {
            background: #ffc107;
        }
        
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            width: 60px;
            height: 60px;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 50px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .header-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .header-title h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
        }
        
        .header-title p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .last-updated {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="monitor-container">
            <!-- Header -->
            <div class="header-title">
                <h1><i class="fas fa-desktop"></i> مراقب النظام</h1>
                <p>مراقبة شاملة لحالة النظام والأداء في الوقت الفعلي</p>
            </div>
            
            <!-- Last Updated -->
            <div class="last-updated" id="lastUpdated">
                آخر تحديث: <span id="lastUpdateTime">جاري التحميل...</span>
                | حالة الاتصال: <span id="connectionStatus">جاري الاتصال...</span>
            </div>
            
            <!-- Loading -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>جاري تحميل بيانات النظام...</p>
            </div>
            
            <!-- System Status -->
            <div class="row" id="systemStatus" style="display: none;">
                <div class="col-12">
                    <div class="status-card" id="overallStatus">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3><i class="fas fa-heartbeat"></i> حالة النظام العامة</h3>
                                <p class="mb-0" id="overallStatusText">جاري التحقق...</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="status-indicator" id="overallStatusIndicator"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Metrics -->
            <div class="row" id="systemMetrics" style="display: none;">
                <!-- Server Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-server"></i> معلومات الخادم</h5>
                        <div class="metric-value" id="phpVersion">-</div>
                        <div class="metric-label">إصدار PHP</div>
                        <hr>
                        <small class="text-muted" id="serverInfo">جاري التحميل...</small>
                    </div>
                </div>
                
                <!-- Memory Usage -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-memory"></i> استخدام الذاكرة</h5>
                        <div class="metric-value" id="memoryUsage">-</div>
                        <div class="metric-label">النسبة المئوية</div>
                        <div class="progress-custom mt-3">
                            <div class="progress-bar-custom bg-primary" id="memoryProgress" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-2" id="memoryDetails">جاري التحميل...</small>
                    </div>
                </div>
                
                <!-- Disk Usage -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-hdd"></i> استخدام القرص</h5>
                        <div class="metric-value" id="diskUsage">-</div>
                        <div class="metric-label">النسبة المئوية</div>
                        <div class="progress-custom mt-3">
                            <div class="progress-bar-custom bg-success" id="diskProgress" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-2" id="diskDetails">جاري التحميل...</small>
                    </div>
                </div>
                
                <!-- Database Status -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-database"></i> قاعدة البيانات</h5>
                        <div class="metric-value" id="dbStatus">-</div>
                        <div class="metric-label">الحالة</div>
                        <hr>
                        <small class="text-muted" id="dbInfo">جاري التحميل...</small>
                    </div>
                </div>
                
                <!-- Response Time -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-tachometer-alt"></i> وقت الاستجابة</h5>
                        <div class="metric-value" id="responseTime">-</div>
                        <div class="metric-label">ميلي ثانية</div>
                        <hr>
                        <small class="text-muted">متوسط وقت الاستجابة</small>
                    </div>
                </div>
                
                <!-- Active Users -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-users"></i> المستخدمون النشطون</h5>
                        <div class="metric-value" id="activeUsers">-</div>
                        <div class="metric-label">مستخدم نشط</div>
                        <hr>
                        <small class="text-muted" id="usersInfo">جاري التحميل...</small>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row" id="chartsRow" style="display: none;">
                <div class="col-lg-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-chart-line"></i> استخدام الذاكرة</h5>
                        <div class="chart-container">
                            <canvas id="memoryChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="metric-card">
                        <h5><i class="fas fa-chart-area"></i> وقت الاستجابة</h5>
                        <div class="chart-container">
                            <canvas id="responseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Users List -->
            <div class="row" id="activeUsersRow" style="display: none;">
                <div class="col-12">
                    <div class="metric-card">
                        <h5><i class="fas fa-user-friends"></i> المستخدمون النشطون حالياً</h5>
                        <div id="activeUsersList">
                            <!-- سيتم ملؤها بواسطة JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="row" id="recentActivitiesRow" style="display: none;">
                <div class="col-12">
                    <div class="metric-card">
                        <h5><i class="fas fa-history"></i> الأنشطة الأخيرة</h5>
                        <div id="recentActivitiesList">
                            <!-- سيتم ملؤها بواسطة JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Refresh Button -->
    <button class="refresh-btn" id="refreshBtn" title="تحديث البيانات">
        <i class="fas fa-sync-alt"></i>
    </button>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/system-monitor-websocket.js') }}"></script>
    <script>
        class SystemMonitor {
            constructor() {
                this.charts = {};
                this.dataHistory = {
                    memory: [],
                    responseTime: []
                };
                this.maxHistoryPoints = 20;
                this.refreshInterval = 5000; // 5 seconds
                this.isRefreshing = false;
                
                this.init();
            }
            
            init() {
                this.setupEventListeners();
                this.loadSystemData();
                this.startAutoRefresh();
            }
            
            setupEventListeners() {
                document.getElementById('refreshBtn').addEventListener('click', () => {
                    this.loadSystemData();
                });
            }
            
            async loadSystemData() {
                if (this.isRefreshing) return;
                
                this.isRefreshing = true;
                this.showLoading(true);
                
                try {
                    const response = await fetch('/api/system-monitor/data', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.updateUI(result.data);
                        this.updateLastUpdateTime();
                    } else {
                        this.showError('خطأ في جلب بيانات النظام: ' + result.message);
                    }
                } catch (error) {
                    this.showError('خطأ في الاتصال: ' + error.message);
                } finally {
                    this.isRefreshing = false;
                    this.showLoading(false);
                }
            }
            
            updateUI(data) {
                this.updateSystemStatus(data.system_health);
                this.updateServerInfo(data.server_info);
                this.updateDatabaseInfo(data.database_info);
                this.updateApplicationInfo(data.application_info);
                this.updatePerformanceMetrics(data.performance_metrics);
                this.updateActiveUsers(data.active_users);
                this.updateRecentActivities(data.recent_activities);
                this.updateCharts(data.performance_metrics);
                
                // إظهار المحتوى
                document.getElementById('systemStatus').style.display = 'block';
                document.getElementById('systemMetrics').style.display = 'block';
                document.getElementById('chartsRow').style.display = 'block';
                document.getElementById('activeUsersRow').style.display = 'block';
                document.getElementById('recentActivitiesRow').style.display = 'block';
            }
            
            updateSystemStatus(health) {
                const statusCard = document.getElementById('overallStatus');
                const statusText = document.getElementById('overallStatusText');
                const statusIndicator = document.getElementById('overallStatusIndicator');
                
                statusText.textContent = `حالة النظام: ${health.overall_status}`;
                
                // تحديث لون البطاقة
                statusCard.className = 'status-card';
                if (health.overall_status === 'جيد') {
                    statusCard.classList.add('success');
                    statusIndicator.className = 'status-indicator status-online';
                } else if (health.overall_status === 'تحذير') {
                    statusCard.classList.add('warning');
                    statusIndicator.className = 'status-indicator status-warning';
                } else {
                    statusCard.classList.add('danger');
                    statusIndicator.className = 'status-indicator status-offline';
                }
            }
            
            updateServerInfo(serverInfo) {
                document.getElementById('phpVersion').textContent = serverInfo.php_version;
                document.getElementById('serverInfo').textContent = 
                    `OS: ${serverInfo.operating_system} | Memory Limit: ${serverInfo.memory_limit}`;
            }
            
            updateDatabaseInfo(dbInfo) {
                if (dbInfo.error) {
                    document.getElementById('dbStatus').textContent = 'خطأ';
                    document.getElementById('dbInfo').textContent = dbInfo.error;
                } else {
                    document.getElementById('dbStatus').textContent = 'متصل';
                    document.getElementById('dbInfo').textContent = 
                        `${dbInfo.driver} ${dbInfo.version} | Connections: ${dbInfo.current_connections}/${dbInfo.max_connections}`;
                }
            }
            
            updateApplicationInfo(appInfo) {
                // يمكن إضافة المزيد من المعلومات هنا
            }
            
            updatePerformanceMetrics(metrics) {
                // تحديث استخدام الذاكرة
                const memoryPercentage = Math.round((metrics.memory_current_usage / metrics.memory_peak_usage) * 100);
                document.getElementById('memoryUsage').textContent = memoryPercentage + '%';
                document.getElementById('memoryProgress').style.width = memoryPercentage + '%';
                document.getElementById('memoryDetails').textContent = 
                    `المستخدم: ${this.formatBytes(metrics.memory_current_usage)} | الذروة: ${this.formatBytes(metrics.memory_peak_usage)}`;
                
                // تحديث وقت الاستجابة
                document.getElementById('responseTime').textContent = metrics.response_time + 'ms';
                
                // إضافة البيانات للتاريخ
                this.dataHistory.memory.push(memoryPercentage);
                this.dataHistory.responseTime.push(metrics.response_time);
                
                // الحفاظ على عدد محدود من النقاط
                if (this.dataHistory.memory.length > this.maxHistoryPoints) {
                    this.dataHistory.memory.shift();
                    this.dataHistory.responseTime.shift();
                }
            }
            
            updateActiveUsers(users) {
                document.getElementById('activeUsers').textContent = users.length;
                document.getElementById('usersInfo').textContent = `${users.length} مستخدم نشط حالياً`;
                
                const usersList = document.getElementById('activeUsersList');
                usersList.innerHTML = '';
                
                users.forEach(user => {
                    const userDiv = document.createElement('div');
                    userDiv.className = 'activity-item';
                    userDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${user.name}</strong>
                                <br>
                                <small class="text-muted">${user.email}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-${user.status === 'نشط' ? 'success' : 'secondary'}">${user.status}</span>
                                <br>
                                <small class="text-muted">${user.last_activity}</small>
                            </div>
                        </div>
                    `;
                    usersList.appendChild(userDiv);
                });
            }
            
            updateRecentActivities(activities) {
                const activitiesList = document.getElementById('recentActivitiesList');
                activitiesList.innerHTML = '<p class="text-muted">لا توجد أنشطة حديثة</p>';
                // يمكن تخصيص هذا حسب احتياجاتك
            }
            
            updateCharts(metrics) {
                this.updateMemoryChart();
                this.updateResponseChart();
            }
            
            updateMemoryChart() {
                const ctx = document.getElementById('memoryChart').getContext('2d');
                
                if (this.charts.memory) {
                    this.charts.memory.destroy();
                }
                
                this.charts.memory = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.dataHistory.memory.map((_, index) => index + 1),
                        datasets: [{
                            label: 'استخدام الذاكرة (%)',
                            data: this.dataHistory.memory,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
            
            updateResponseChart() {
                const ctx = document.getElementById('responseChart').getContext('2d');
                
                if (this.charts.response) {
                    this.charts.response.destroy();
                }
                
                this.charts.response = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.dataHistory.responseTime.map((_, index) => index + 1),
                        datasets: [{
                            label: 'وقت الاستجابة (ms)',
                            data: this.dataHistory.responseTime,
                            borderColor: '#f093fb',
                            backgroundColor: 'rgba(240, 147, 251, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            updateLastUpdateTime() {
                const now = new Date();
                document.getElementById('lastUpdateTime').textContent = now.toLocaleString('ar-SA');
            }
            
            showLoading(show) {
                document.getElementById('loading').style.display = show ? 'block' : 'none';
            }
            
            showError(message) {
                console.error(message);
                // يمكن إضافة عرض رسالة خطأ للمستخدم
            }
            
            startAutoRefresh() {
                setInterval(() => {
                    this.loadSystemData();
                }, this.refreshInterval);
            }
            
            formatBytes(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }
        
        // بدء المراقب عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', () => {
            new SystemMonitor();
        });
    </script>
</body>
</html>
