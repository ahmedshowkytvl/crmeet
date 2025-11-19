<?php
/**
 * أداة إعداد النظام للشبكة الواسعة
 * تقوم بتحديث الإعدادات المطلوبة للوصول الخارجي
 */

class WANSetupTool {
    private $configFile = '.env';
    private $backupFile = '.env.wan_backup';
    
    public function __construct() {
        echo "=== أداة إعداد النظام للشبكة الواسعة ===\n\n";
    }
    
    public function run() {
        try {
            $this->createBackup();
            $this->updateEnvironmentConfig();
            $this->checkNetworkSettings();
            $this->displayAccessInfo();
            $this->generateStartupScript();
            
            echo "\n✅ تم إعداد النظام للشبكة الواسعة بنجاح!\n";
            echo "يمكنك الآن تشغيل wan-setup.bat لبدء الخادم\n";
            
        } catch (Exception $e) {
            echo "❌ خطأ: " . $e->getMessage() . "\n";
        }
    }
    
    private function createBackup() {
        echo "[1/6] إنشاء نسخة احتياطية من الإعدادات...\n";
        
        if (file_exists($this->configFile)) {
            copy($this->configFile, $this->backupFile);
            echo "✅ تم إنشاء نسخة احتياطية: {$this->backupFile}\n";
        } else {
            echo "⚠️  ملف .env غير موجود\n";
        }
    }
    
    private function updateEnvironmentConfig() {
        echo "[2/6] تحديث إعدادات البيئة...\n";
        
        $envContent = file_get_contents($this->configFile);
        
        // تحديث APP_URL للشبكة الواسعة
        $envContent = preg_replace(
            '/APP_URL=.*/',
            'APP_URL=http://0.0.0.0:8000',
            $envContent
        );
        
        // تحديث إعدادات قاعدة البيانات للوصول الخارجي
        $envContent = preg_replace(
            '/DB_HOST=.*/',
            'DB_HOST=0.0.0.0',
            $envContent
        );
        
        // إضافة إعدادات الشبكة الواسعة
        if (!strpos($envContent, 'WAN_ENABLED')) {
            $envContent .= "\n# إعدادات الشبكة الواسعة\n";
            $envContent .= "WAN_ENABLED=true\n";
            $envContent .= "WAN_PORT=8000\n";
            $envContent .= "WAN_HOST=0.0.0.0\n";
        }
        
        file_put_contents($this->configFile, $envContent);
        echo "✅ تم تحديث إعدادات البيئة\n";
    }
    
    private function checkNetworkSettings() {
        echo "[3/6] فحص إعدادات الشبكة...\n";
        
        // الحصول على عناوين IP
        $ipAddresses = $this->getIPAddresses();
        
        echo "عناوين IP المتاحة:\n";
        foreach ($ipAddresses as $ip) {
            echo "  - {$ip}\n";
        }
        
        // فحص المنافذ المفتوحة
        $ports = [8000, 80, 5432, 8080];
        echo "\nفحص المنافذ:\n";
        
        foreach ($ports as $port) {
            $status = $this->checkPort($port) ? "مفتوح" : "مغلق";
            echo "  - المنفذ {$port}: {$status}\n";
        }
    }
    
    private function getIPAddresses() {
        $ips = [];
        
        // Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('ipconfig');
            preg_match_all('/IPv4 Address[^:]*:\s*([0-9.]+)/', $output, $matches);
            $ips = $matches[1];
        } else {
            // Linux/Unix
            $output = shell_exec('hostname -I');
            $ips = explode(' ', trim($output));
        }
        
        return array_filter($ips, function($ip) {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ||
                   filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        });
    }
    
    private function checkPort($port) {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }
    
    private function displayAccessInfo() {
        echo "[4/6] معلومات الوصول...\n";
        
        $ips = $this->getIPAddresses();
        
        echo "\n🌐 النظام متاح على:\n";
        foreach ($ips as $ip) {
            echo "  - http://{$ip}:8000\n";
        }
        
        echo "\n📱 للوصول من خارج الشبكة المحلية:\n";
        echo "  1. احصل على عنوان IP العام لجهازك\n";
        echo "  2. تأكد من فتح المنافذ في الراوتر\n";
        echo "  3. استخدم: http://YOUR_PUBLIC_IP:8000\n";
        
        echo "\n🔧 أدوات مفيدة:\n";
        echo "  - فحص IP العام: https://whatismyipaddress.com/\n";
        echo "  - اختبار المنافذ: https://www.yougetsignal.com/tools/open-ports/\n";
    }
    
    private function generateStartupScript() {
        echo "[5/6] إنشاء سكريبت التشغيل...\n";
        
        $scriptContent = '@echo off
echo ========================================
echo    تشغيل النظام على الشبكة الواسعة
echo ========================================
echo.

echo بدء خادم Laravel...
start "Laravel Server" php artisan serve --host=0.0.0.0 --port=8000

echo بدء خادم WebSocket...
start "WebSocket Server" node websocket-server.js

echo.
echo ✅ تم بدء جميع الخوادم
echo النظام متاح الآن على الشبكة الواسعة
echo.
echo اضغط أي مفتاح للخروج...
pause > nul';

        file_put_contents('start-wan-servers.bat', $scriptContent);
        echo "✅ تم إنشاء سكريبت التشغيل: start-wan-servers.bat\n";
    }
    
    public function restoreBackup() {
        echo "استعادة النسخة الاحتياطية...\n";
        
        if (file_exists($this->backupFile)) {
            copy($this->backupFile, $this->configFile);
            echo "✅ تم استعادة الإعدادات الأصلية\n";
        } else {
            echo "❌ لم يتم العثور على النسخة الاحتياطية\n";
        }
    }
}

// تشغيل الأداة
if (php_sapi_name() === 'cli') {
    $setup = new WANSetupTool();
    
    if (isset($argv[1]) && $argv[1] === 'restore') {
        $setup->restoreBackup();
    } else {
        $setup->run();
    }
} else {
    echo "هذه الأداة يجب تشغيلها من سطر الأوامر\n";
    echo "استخدم: php wan-setup-tool.php\n";
}





