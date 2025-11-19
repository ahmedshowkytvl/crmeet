/**
 * نظام مراقبة النظام مع WebSocket للاتصال المباشر
 */
class SystemMonitorWebSocket {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 3000;
        this.heartbeatInterval = 30000; // 30 seconds
        this.heartbeatTimer = null;
        this.isConnected = false;
        
        this.init();
    }
    
    init() {
        this.connect();
        this.setupEventListeners();
    }
    
    connect() {
        try {
            // محاولة الاتصال بـ WebSocket
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${protocol}//${window.location.host}/ws/system-monitor`;
            
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('تم الاتصال بـ WebSocket بنجاح');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.startHeartbeat();
                this.updateConnectionStatus('متصل', 'success');
            };
            
            this.ws.onmessage = (event) => {
                this.handleMessage(event.data);
            };
            
            this.ws.onclose = () => {
                console.log('تم إغلاق الاتصال بـ WebSocket');
                this.isConnected = false;
                this.stopHeartbeat();
                this.updateConnectionStatus('غير متصل', 'danger');
                this.attemptReconnect();
            };
            
            this.ws.onerror = (error) => {
                console.error('خطأ في WebSocket:', error);
                this.updateConnectionStatus('خطأ في الاتصال', 'warning');
            };
            
        } catch (error) {
            console.error('فشل في إنشاء اتصال WebSocket:', error);
            this.fallbackToPolling();
        }
    }
    
    handleMessage(data) {
        try {
            const message = JSON.parse(data);
            
            switch (message.type) {
                case 'system_data':
                    this.updateSystemData(message.data);
                    break;
                case 'alert':
                    this.showAlert(message.alert);
                    break;
                case 'heartbeat':
                    // استجابة لـ heartbeat
                    break;
                default:
                    console.log('رسالة غير معروفة:', message);
            }
        } catch (error) {
            console.error('خطأ في معالجة الرسالة:', error);
        }
    }
    
    updateSystemData(data) {
        // تحديث واجهة المستخدم بالبيانات الجديدة
        if (window.systemMonitor) {
            window.systemMonitor.updateUI(data);
        }
    }
    
    showAlert(alert) {
        // عرض تنبيه للمستخدم
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${alert.type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <strong>${alert.title}</strong> ${alert.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // إزالة التنبيه تلقائياً بعد 5 ثوان
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
    
    updateConnectionStatus(status, type) {
        const statusElement = document.getElementById('connectionStatus');
        if (statusElement) {
            statusElement.innerHTML = `
                <span class="badge bg-${type}">
                    <i class="fas fa-circle"></i> ${status}
                </span>
            `;
        }
    }
    
    startHeartbeat() {
        this.heartbeatTimer = setInterval(() => {
            if (this.isConnected && this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify({ type: 'heartbeat' }));
            }
        }, this.heartbeatInterval);
    }
    
    stopHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
    }
    
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`محاولة إعادة الاتصال ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            
            setTimeout(() => {
                this.connect();
            }, this.reconnectInterval);
        } else {
            console.log('تم الوصول للحد الأقصى من محاولات إعادة الاتصال، التبديل إلى Polling');
            this.fallbackToPolling();
        }
    }
    
    fallbackToPolling() {
        console.log('التبديل إلى نظام Polling التقليدي');
        this.updateConnectionStatus('Polling', 'info');
        
        // تفعيل نظام Polling التقليدي
        if (window.systemMonitor) {
            window.systemMonitor.startAutoRefresh();
        }
    }
    
    setupEventListeners() {
        // إعادة الاتصال عند استعادة الاتصال
        window.addEventListener('online', () => {
            console.log('تم استعادة الاتصال بالإنترنت');
            if (!this.isConnected) {
                this.connect();
            }
        });
        
        // إغلاق الاتصال عند إغلاق الصفحة
        window.addEventListener('beforeunload', () => {
            if (this.ws) {
                this.ws.close();
            }
        });
    }
    
    sendMessage(message) {
        if (this.isConnected && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(message));
        } else {
            console.warn('WebSocket غير متصل، لا يمكن إرسال الرسالة');
        }
    }
    
    disconnect() {
        this.stopHeartbeat();
        if (this.ws) {
            this.ws.close();
        }
    }
}

// بدء نظام WebSocket عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    // التحقق من دعم WebSocket
    if (typeof WebSocket !== 'undefined') {
        window.systemMonitorWS = new SystemMonitorWebSocket();
    } else {
        console.log('المتصفح لا يدعم WebSocket، سيتم استخدام Polling');
    }
});






