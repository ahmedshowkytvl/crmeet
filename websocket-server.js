/**
 * خادم WebSocket لمراقبة النظام
 * يتطلب: npm install ws
 */

const WebSocket = require('ws');
const http = require('http');
const url = require('url');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

class SystemMonitorWebSocketServer {
    constructor(port = 8080) {
        this.port = port;
        this.clients = new Set();
        this.systemData = {};
        this.updateInterval = 5000; // 5 seconds
        this.heartbeatInterval = 30000; // 30 seconds
        
        this.init();
    }
    
    init() {
        // إنشاء خادم HTTP
        this.server = http.createServer();
        
        // إنشاء خادم WebSocket
        this.wss = new WebSocket.Server({ 
            server: this.server,
            path: '/ws/system-monitor'
        });
        
        this.setupWebSocketServer();
        this.startSystemMonitoring();
        this.startHeartbeat();
        this.startServer();
    }
    
    setupWebSocketServer() {
        this.wss.on('connection', (ws, req) => {
            console.log('عميل جديد متصل');
            this.clients.add(ws);
            
            // إرسال البيانات الحالية للعميل الجديد
            if (Object.keys(this.systemData).length > 0) {
                this.sendToClient(ws, {
                    type: 'system_data',
                    data: this.systemData
                });
            }
            
            ws.on('message', (message) => {
                try {
                    const data = JSON.parse(message);
                    this.handleClientMessage(ws, data);
                } catch (error) {
                    console.error('خطأ في معالجة رسالة العميل:', error);
                }
            });
            
            ws.on('close', () => {
                console.log('عميل انقطع');
                this.clients.delete(ws);
            });
            
            ws.on('error', (error) => {
                console.error('خطأ في WebSocket:', error);
                this.clients.delete(ws);
            });
        });
    }
    
    handleClientMessage(ws, data) {
        switch (data.type) {
            case 'heartbeat':
                // استجابة لـ heartbeat
                this.sendToClient(ws, { type: 'heartbeat' });
                break;
            case 'request_data':
                // إرسال البيانات الحالية
                this.sendToClient(ws, {
                    type: 'system_data',
                    data: this.systemData
                });
                break;
            default:
                console.log('رسالة غير معروفة من العميل:', data);
        }
    }
    
    sendToClient(ws, message) {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(message));
        }
    }
    
    broadcast(message) {
        this.clients.forEach(client => {
            this.sendToClient(client, message);
        });
    }
    
    startSystemMonitoring() {
        setInterval(() => {
            this.collectSystemData().then(data => {
                this.systemData = data;
                this.broadcast({
                    type: 'system_data',
                    data: data
                });
                
                // فحص التنبيهات
                this.checkAlerts(data);
            }).catch(error => {
                console.error('خطأ في جمع بيانات النظام:', error);
            });
        }, this.updateInterval);
    }
    
    startHeartbeat() {
        setInterval(() => {
            this.broadcast({ type: 'heartbeat' });
        }, this.heartbeatInterval);
    }
    
    async collectSystemData() {
        try {
            const data = {
                timestamp: new Date().toISOString(),
                server: await this.getServerInfo(),
                system: await this.getSystemInfo(),
                performance: await this.getPerformanceInfo(),
                alerts: []
            };
            
            return data;
        } catch (error) {
            console.error('خطأ في جمع بيانات النظام:', error);
            return { error: error.message };
        }
    }
    
    async getServerInfo() {
        return new Promise((resolve) => {
            exec('php -v', (error, stdout) => {
                if (error) {
                    resolve({ php_version: 'غير متاح' });
                } else {
                    const version = stdout.split('\n')[0];
                    resolve({ php_version: version });
                }
            });
        });
    }
    
    async getSystemInfo() {
        return new Promise((resolve) => {
            if (process.platform === 'win32') {
                // Windows
                exec('wmic cpu get loadpercentage /value', (error, stdout) => {
                    if (error) {
                        resolve({ cpu_usage: 0 });
                    } else {
                        const lines = stdout.split('\n');
                        const loadLine = lines.find(line => line.includes('LoadPercentage'));
                        const cpuUsage = loadLine ? parseInt(loadLine.split('=')[1]) : 0;
                        resolve({ cpu_usage: cpuUsage });
                    }
                });
            } else {
                // Linux/Unix
                exec('top -bn1 | grep "Cpu(s)" | awk \'{print $2}\' | awk -F\'%\' \'{print $1}\'', (error, stdout) => {
                    if (error) {
                        resolve({ cpu_usage: 0 });
                    } else {
                        const cpuUsage = parseFloat(stdout.trim()) || 0;
                        resolve({ cpu_usage: cpuUsage });
                    }
                });
            }
        });
    }
    
    async getPerformanceInfo() {
        const memUsage = process.memoryUsage();
        const uptime = process.uptime();
        
        return {
            memory_usage: {
                rss: Math.round(memUsage.rss / 1024 / 1024), // MB
                heapTotal: Math.round(memUsage.heapTotal / 1024 / 1024), // MB
                heapUsed: Math.round(memUsage.heapUsed / 1024 / 1024), // MB
                external: Math.round(memUsage.external / 1024 / 1024) // MB
            },
            uptime: Math.round(uptime),
            node_version: process.version,
            platform: process.platform
        };
    }
    
    checkAlerts(data) {
        const alerts = [];
        
        // فحص استخدام الذاكرة
        if (data.performance && data.performance.memory_usage) {
            const memoryUsage = data.performance.memory_usage.heapUsed;
            if (memoryUsage > 500) { // أكثر من 500 MB
                alerts.push({
                    type: 'warning',
                    title: 'استخدام الذاكرة عالي',
                    message: `استخدام الذاكرة: ${memoryUsage} MB`
                });
            }
        }
        
        // فحص استخدام المعالج
        if (data.system && data.system.cpu_usage > 80) {
            alerts.push({
                type: 'warning',
                title: 'استخدام المعالج عالي',
                message: `استخدام المعالج: ${data.system.cpu_usage}%`
            });
        }
        
        // إرسال التنبيهات إذا وجدت
        if (alerts.length > 0) {
            alerts.forEach(alert => {
                this.broadcast({
                    type: 'alert',
                    alert: alert
                });
            });
        }
    }
    
    startServer() {
        this.server.listen(this.port, () => {
            console.log(`خادم WebSocket يعمل على المنفذ ${this.port}`);
            console.log(`رابط الاتصال: ws://localhost:${this.port}/ws/system-monitor`);
        });
    }
    
    stop() {
        this.server.close(() => {
            console.log('تم إغلاق خادم WebSocket');
        });
    }
}

// بدء الخادم
const server = new SystemMonitorWebSocketServer(8080);

// إغلاق نظيف عند إنهاء العملية
process.on('SIGINT', () => {
    console.log('\nتم استلام إشارة الإنهاء...');
    server.stop();
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\nتم استلام إشارة الإنهاء...');
    server.stop();
    process.exit(0);
});

module.exports = SystemMonitorWebSocketServer;






