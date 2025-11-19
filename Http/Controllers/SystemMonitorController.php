<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class SystemMonitorController extends Controller
{
    /**
     * عرض صفحة مراقبة النظام
     */
    public function index()
    {
        // التحقق من تفعيل النظام
        if (!config('system-monitor.enabled', true)) {
            abort(404, 'نظام المراقبة غير مفعل');
        }
        
        // التحقق من الأمان
        $this->checkSecurity();
        
        return view('system-monitor.index');
    }
    
    /**
     * التحقق من الأمان
     */
    private function checkSecurity()
    {
        // التحقق من IP المسموح
        if (config('system-monitor.security.allowed_ips')) {
            $allowedIPs = explode(',', config('system-monitor.security.allowed_ips'));
            $clientIP = request()->ip();
            
            $isAllowed = false;
            foreach ($allowedIPs as $allowedIP) {
                $allowedIP = trim($allowedIP);
                if ($this->isIPInRange($clientIP, $allowedIP)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                abort(403, 'غير مسموح بالوصول من هذا العنوان');
            }
        }
        
        // التحقق من المصادقة
        if (config('system-monitor.security.require_auth') && !auth()->check()) {
            return redirect()->route('login');
        }
        
        // التحقق من API Key
        if (config('system-monitor.security.api_key')) {
            $apiKey = request()->header('X-API-Key') ?? request()->get('api_key');
            if ($apiKey !== config('system-monitor.security.api_key')) {
                abort(401, 'مفتاح API غير صحيح');
            }
        }
    }
    
    /**
     * التحقق من وجود IP في نطاق معين
     */
    private function isIPInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        
        return ($ip & $mask) === $subnet;
    }

    /**
     * الحصول على بيانات النظام في الوقت الفعلي
     */
    public function getSystemData()
    {
        try {
            $data = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'server_info' => $this->getServerInfo(),
                'database_info' => $this->getDatabaseInfo(),
                'application_info' => $this->getApplicationInfo(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'active_users' => $this->getActiveUsers(),
                'recent_activities' => $this->getRecentActivities(),
                'system_health' => $this->getSystemHealth()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في مراقبة النظام: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب بيانات النظام',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * معلومات الخادم
     */
    private function getServerInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'غير محدد',
            'operating_system' => php_uname('s') . ' ' . php_uname('r'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'uptime' => $this->getServerUptime(),
            'load_average' => $this->getLoadAverage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage()
        ];
    }

    /**
     * معلومات قاعدة البيانات
     */
    private function getDatabaseInfo()
    {
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            
            return [
                'driver' => $connection->getDriverName(),
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'connection_name' => $connection->getName(),
                'database_name' => $connection->getDatabaseName(),
                'charset' => $connection->getConfig('charset'),
                'collation' => $connection->getConfig('collation'),
                'max_connections' => $this->getDatabaseMaxConnections(),
                'current_connections' => $this->getCurrentConnections(),
                'query_cache_size' => $this->getQueryCacheSize(),
                'slow_query_log' => $this->getSlowQueryLogStatus()
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ];
        }
    }

    /**
     * معلومات التطبيق
     */
    private function getApplicationInfo()
    {
        return [
            'laravel_version' => app()->version(),
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'log_level' => config('logging.level'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'total_users' => \App\Models\User::count(),
            'total_assets' => \App\Models\Asset::count(),
            'total_departments' => \App\Models\Department::count()
        ];
    }

    /**
     * مقاييس الأداء
     */
    private function getPerformanceMetrics()
    {
        return [
            'response_time' => $this->getResponseTime(),
            'memory_peak_usage' => memory_get_peak_usage(true),
            'memory_current_usage' => memory_get_usage(true),
            'opcache_status' => $this->getOpcacheStatus(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'database_query_time' => $this->getDatabaseQueryTime(),
            'file_system_status' => $this->getFileSystemStatus()
        ];
    }

    /**
     * المستخدمون النشطون
     */
    private function getActiveUsers()
    {
        $activeThreshold = now()->subMinutes(5);
        
        return \App\Models\User::where('last_activity_at', '>=', $activeThreshold)
            ->orWhere('updated_at', '>=', $activeThreshold)
            ->select('id', 'name', 'email', 'last_activity_at', 'updated_at')
            ->orderBy('last_activity_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'last_activity' => $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'غير محدد',
                    'status' => $user->last_activity_at && $user->last_activity_at->isAfter(now()->subMinutes(2)) ? 'نشط' : 'غير نشط'
                ];
            });
    }

    /**
     * الأنشطة الأخيرة
     */
    private function getRecentActivities()
    {
        // يمكن تخصيص هذا حسب احتياجاتك
        return [
            'recent_logins' => $this->getRecentLogins(),
            'recent_errors' => $this->getRecentErrors(),
            'recent_queries' => $this->getRecentQueries()
        ];
    }

    /**
     * صحة النظام
     */
    private function getSystemHealth()
    {
        $health = [
            'overall_status' => 'جيد',
            'checks' => []
        ];

        // فحص قاعدة البيانات
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = ['status' => 'جيد', 'message' => 'قاعدة البيانات متصلة'];
        } catch (\Exception $e) {
            $health['checks']['database'] = ['status' => 'خطأ', 'message' => 'فشل الاتصال بقاعدة البيانات'];
            $health['overall_status'] = 'خطأ';
        }

        // فحص الذاكرة
        $memoryUsage = $this->getMemoryUsage();
        if ($memoryUsage['percentage'] > 90) {
            $health['checks']['memory'] = ['status' => 'تحذير', 'message' => 'استخدام الذاكرة عالي جداً'];
            $health['overall_status'] = 'تحذير';
        } else {
            $health['checks']['memory'] = ['status' => 'جيد', 'message' => 'استخدام الذاكرة طبيعي'];
        }

        // فحص القرص
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage['percentage'] > 90) {
            $health['checks']['disk'] = ['status' => 'تحذير', 'message' => 'مساحة القرص منخفضة'];
            $health['overall_status'] = 'تحذير';
        } else {
            $health['checks']['disk'] = ['status' => 'جيد', 'message' => 'مساحة القرص كافية'];
        }

        return $health;
    }

    // الدوال المساعدة

    private function getServerUptime()
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return $uptime ? trim($uptime) : 'غير متاح';
        }
        return 'غير متاح';
    }

    private function getLoadAverage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0] ?? 0,
                '5min' => $load[1] ?? 0,
                '15min' => $load[2] ?? 0
            ];
        }
        return ['1min' => 0, '5min' => 0, '15min' => 0];
    }

    private function getMemoryUsage()
    {
        $total = $this->getTotalMemory();
        $used = $total - $this->getFreeMemory();
        $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($total - $used),
            'percentage' => $percentage
        ];
    }

    private function getDiskUsage()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => $percentage
        ];
    }

    private function getTotalMemory()
    {
        $meminfo = file_get_contents('/proc/meminfo');
        if (preg_match('/MemTotal:\s+(\d+)/', $meminfo, $matches)) {
            return $matches[1] * 1024; // Convert from KB to bytes
        }
        return 0;
    }

    private function getFreeMemory()
    {
        $meminfo = file_get_contents('/proc/meminfo');
        if (preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $matches)) {
            return $matches[1] * 1024; // Convert from KB to bytes
        }
        return 0;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function getDatabaseMaxConnections()
    {
        try {
            $result = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            return $result[0]->Value ?? 'غير محدد';
        } catch (\Exception $e) {
            return 'غير محدد';
        }
    }

    private function getCurrentConnections()
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            return $result[0]->Value ?? 'غير محدد';
        } catch (\Exception $e) {
            return 'غير محدد';
        }
    }

    private function getQueryCacheSize()
    {
        try {
            $result = DB::select("SHOW VARIABLES LIKE 'query_cache_size'");
            return $result[0]->Value ?? 'غير محدد';
        } catch (\Exception $e) {
            return 'غير محدد';
        }
    }

    private function getSlowQueryLogStatus()
    {
        try {
            $result = DB::select("SHOW VARIABLES LIKE 'slow_query_log'");
            return $result[0]->Value ?? 'غير محدد';
        } catch (\Exception $e) {
            return 'غير محدد';
        }
    }

    private function getResponseTime()
    {
        $start = microtime(true);
        // محاكاة عملية بسيطة لقياس وقت الاستجابة
        DB::select('SELECT 1');
        return round((microtime(true) - $start) * 1000, 2); // في الميلي ثانية
    }

    private function getOpcacheStatus()
    {
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status();
            return [
                'enabled' => $status['opcache_enabled'] ?? false,
                'memory_usage' => $status['memory_usage'] ?? [],
                'hit_rate' => $status['opcache_statistics']['opcache_hit_rate'] ?? 0
            ];
        }
        return ['enabled' => false];
    }

    private function getCacheHitRate()
    {
        // يمكن تخصيص هذا حسب نوع الكاش المستخدم
        return 'غير محدد';
    }

    private function getDatabaseQueryTime()
    {
        $start = microtime(true);
        DB::select('SELECT COUNT(*) as count FROM users');
        return round((microtime(true) - $start) * 1000, 2);
    }

    private function getFileSystemStatus()
    {
        return [
            'storage_writable' => is_writable(storage_path()),
            'cache_writable' => is_writable(storage_path('framework/cache')),
            'logs_writable' => is_writable(storage_path('logs')),
            'bootstrap_writable' => is_writable(storage_path('framework'))
        ];
    }

    private function getRecentLogins()
    {
        // يمكن تخصيص هذا حسب نظام تسجيل الدخول
        return \App\Models\User::whereNotNull('last_login_at')
            ->orderBy('last_login_at', 'desc')
            ->limit(5)
            ->get(['name', 'email', 'last_login_at']);
    }

    private function getRecentErrors()
    {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $errorLines = array_filter($lines, function($line) {
                return strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false;
            });
            return array_slice(array_reverse($errorLines), 0, 5);
        }
        return [];
    }

    private function getRecentQueries()
    {
        // يمكن تفعيل query logging في Laravel
        return [];
    }
}
