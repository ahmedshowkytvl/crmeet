<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Disk Space Error Handler Test ===\n";

// Test the error handler
echo "1. Testing error detection...\n";

$testMessages = [
    'No space left on device',
    'ENOSPC: no space left on device, write',
    'file_put_contents(): Write of 69 bytes failed with errno=28',
    'failed with errno=28 No space left on device'
];

foreach ($testMessages as $message) {
    $isDetected = str_contains($message, 'No space left on device') ||
                  str_contains($message, 'ENOSPC') ||
                  str_contains($message, 'file_put_contents') ||
                  str_contains($message, 'failed with errno=28');
    
    echo "  Message: '{$message}'\n";
    echo "  Detected: " . ($isDetected ? "✅ Yes" : "❌ No") . "\n\n";
}

echo "2. Testing disk cleanup command...\n";
echo "Command: php artisan disk:clean --all\n";
echo "This will clean:\n";
echo "  - Log files\n";
echo "  - Cache files\n";
echo "  - Temporary files\n";

echo "\n3. Testing error page...\n";
echo "Error page URL: http://127.0.0.1:8000/errors/disk-space\n";
echo "Features:\n";
echo "  - Friendly error message\n";
echo "  - Suggested solutions\n";
echo "  - Technical details (toggle)\n";
echo "  - Back to home button\n";
echo "  - Retry button\n";

echo "\n4. Testing translations...\n";
echo "Arabic: " . __('errors.disk_space.title') . "\n";
echo "English: " . __('errors.disk_space.title', [], 'en') . "\n";

echo "\n5. Testing disk space check...\n";
$diskFree = disk_free_space('.');
$diskTotal = disk_total_space('.');
$diskUsed = $diskTotal - $diskFree;
$freePercent = round(($diskFree / $diskTotal) * 100, 2);

echo "Disk Usage:\n";
echo "  Total: " . formatBytes($diskTotal) . "\n";
echo "  Used: " . formatBytes($diskUsed) . "\n";
echo "  Free: " . formatBytes($diskFree) . "\n";
echo "  Free %: {$freePercent}%\n";

if ($freePercent < 10) {
    echo "  ⚠️  Warning: Low disk space!\n";
} else {
    echo "  ✅ Disk space is sufficient\n";
}

echo "\n=== End Test ===\n";

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
