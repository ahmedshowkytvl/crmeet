<?php

/**
 * سكريبت PHP عكسي لاستعادة المشروع من Git وتحديث قاعدة البيانات من النسخة الاحتياطية
 * Script to restore project from Git and update database from backup
 */

// قراءة إعدادات قاعدة البيانات من .env
function loadEnvFile($path = '.env')
{
    if (!file_exists($path)) {
        die("❌ خطأ: ملف .env غير موجود\n");
    }
    
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // إزالة علامات الاقتباس
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        $env[$name] = $value;
    }
    
    return $env;
}

// البحث عن أحدث ملف backup
function findLatestBackup($backupDir)
{
    if (!is_dir($backupDir)) {
        throw new Exception("مجلد النسخ الاحتياطي غير موجود: $backupDir");
    }
    
    $files = [];
    
    // البحث عن ملفات .sql.gz
    $gzFiles = glob("$backupDir/*.sql.gz");
    foreach ($gzFiles as $file) {
        $files[] = [
            'file' => $file,
            'time' => filemtime($file),
            'compressed' => true
        ];
    }
    
    // البحث عن ملفات .sql
    $sqlFiles = glob("$backupDir/*.sql");
    foreach ($sqlFiles as $file) {
        // تخطي الملفات المضغوطة
        if (!in_array($file . '.gz', $gzFiles)) {
            $files[] = [
                'file' => $file,
                'time' => filemtime($file),
                'compressed' => false
            ];
        }
    }
    
    if (empty($files)) {
        throw new Exception("لم يتم العثور على أي نسخة احتياطية");
    }
    
    // ترتيب حسب التاريخ (الأحدث أولاً)
    usort($files, function($a, $b) {
        return $b['time'] - $a['time'];
    });
    
    return $files[0];
}

// فك الضغط
function decompressFile($file)
{
    if (!file_exists($file)) {
        throw new Exception("الملف غير موجود: $file");
    }
    
    $content = file_get_contents($file);
    $decompressed = gzdecode($content);
    
    if ($decompressed === false) {
        throw new Exception("فشل فك الضغط");
    }
    
    $tempFile = sys_get_temp_dir() . '/' . basename($file, '.gz');
    file_put_contents($tempFile, $decompressed);
    
    return $tempFile;
}

// استعادة قاعدة البيانات من MySQL
function restoreMySQL($host, $port, $database, $username, $password, $backupFile)
{
    $command = sprintf(
        'mysql -h %s -P %s -u %s %s %s < %s 2>&1',
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($username),
        $password ? '-p' . escapeshellarg($password) : '',
        escapeshellarg($database),
        escapeshellarg($backupFile)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("فشل استعادة قاعدة البيانات من MySQL: " . implode("\n", $output));
    }
    
    return true;
}

// استعادة قاعدة البيانات من PostgreSQL
function restorePostgreSQL($host, $port, $database, $username, $password, $backupFile)
{
    putenv("PGPASSWORD=" . $password);
    
    $command = sprintf(
        'psql -h %s -p %s -U %s -d %s -f %s 2>&1',
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($username),
        escapeshellarg($database),
        escapeshellarg($backupFile)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("فشل استعادة قاعدة البيانات من PostgreSQL: " . implode("\n", $output));
    }
    
    return true;
}

// تنفيذ أمر Git
function runGitCommand($command)
{
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("فشل تنفيذ أمر Git: " . implode("\n", $output));
    }
    
    return $output;
}

try {
    echo "=== بدء عملية الاستعادة من Git وتحديث قاعدة البيانات ===\n\n";
    
    // قراءة إعدادات قاعدة البيانات
    $env = loadEnvFile();
    
    // قراءة GitHub Token
    $githubToken = $env['GITHUB_TOKEN'] ?? getenv('GITHUB_TOKEN') ?? '';
    if (empty($githubToken)) {
        echo "⚠ تحذير: GITHUB_TOKEN غير موجود في .env\n";
        echo "   سيتم محاولة استخدام المصادقة الحالية\n\n";
    }
    
    $dbConnection = $env['DB_CONNECTION'] ?? 'mysql';
    $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
    $dbPort = $env['DB_PORT'] ?? ($dbConnection === 'pgsql' ? '5432' : '3306');
    $dbDatabase = $env['DB_DATABASE'] ?? 'laravel';
    $dbUsername = $env['DB_USERNAME'] ?? 'root';
    $dbPassword = $env['DB_PASSWORD'] ?? '';
    
    // البحث عن أحدث نسخة احتياطية
    $backupDir = 'database_backups';
    echo "🔍 البحث عن أحدث نسخة احتياطية...\n";
    
    $latestBackup = findLatestBackup($backupDir);
    echo "✓ تم العثور على النسخة الاحتياطية: {$latestBackup['file']}\n\n";
    
    // استعادة المشروع من Git
    echo "🔄 جاري استعادة المشروع من Git...\n";
    try {
        $currentBranch = trim(implode('', runGitCommand('git branch --show-current')));
        
        // استخدام token إذا كان متوفراً
        if (!empty($githubToken)) {
            // الحصول على URL الحالي
            $remoteUrl = trim(implode('', runGitCommand('git remote get-url origin')));
            
            // استخراج اسم المستخدم والمستودع
            if (strpos($remoteUrl, '@') !== false) {
                // SSH format
                preg_match('/@[^:]+:(.+?)\.git$/', $remoteUrl, $matches);
                $repoPath = $matches[1] ?? '';
            } else {
                // HTTPS format
                preg_match('/github\.com\/(.+?)\.git$/', $remoteUrl, $matches);
                $repoPath = $matches[1] ?? '';
            }
            
            if (!empty($repoPath)) {
                // تحديث URL لاستخدام token
                $githubUrl = "https://{$githubToken}@github.com/{$repoPath}.git";
                runGitCommand("git remote set-url origin " . escapeshellarg($githubUrl));
                
                // استعادة المشروع
                runGitCommand("git pull origin $currentBranch");
                
                // استعادة URL الأصلي
                $originalUrl = "https://github.com/{$repoPath}.git";
                runGitCommand("git remote set-url origin " . escapeshellarg($originalUrl));
            } else {
                runGitCommand("git pull origin $currentBranch");
            }
        } else {
            runGitCommand("git pull origin $currentBranch");
        }
        
        echo "✓ تم استعادة المشروع بنجاح\n\n";
    } catch (Exception $e) {
        echo "⚠ تحذير: فشل pull من Git، سيتم المتابعة مع استعادة قاعدة البيانات\n";
        echo "   الخطأ: " . $e->getMessage() . "\n\n";
    }
    
    // فك الضغط إذا كان الملف مضغوطاً
    $restoreFile = $latestBackup['file'];
    $isTempFile = false;
    
    if ($latestBackup['compressed']) {
        echo "🔄 جاري فك الضغط...\n";
        $restoreFile = decompressFile($latestBackup['file']);
        $isTempFile = true;
        echo "✓ تم فك الضغط: $restoreFile\n\n";
    }
    
    // التحذير قبل الاستعادة
    echo "⚠ تحذير: سيتم استبدال قاعدة البيانات الحالية بالنسخة الاحتياطية\n";
    echo "هل تريد المتابعة؟ (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        if ($isTempFile && file_exists($restoreFile)) {
            unlink($restoreFile);
        }
        echo "تم الإلغاء\n";
        exit(0);
    }
    
    echo "\n🔄 جاري استعادة قاعدة البيانات...\n";
    
    // استعادة قاعدة البيانات حسب نوعها
    if (in_array($dbConnection, ['pgsql', 'postgres'])) {
        restorePostgreSQL($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $restoreFile);
    } elseif (in_array($dbConnection, ['mysql', 'mariadb'])) {
        restoreMySQL($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $restoreFile);
    } else {
        throw new Exception("نوع قاعدة البيانات غير مدعوم: $dbConnection");
    }
    
    echo "✓ تم استعادة قاعدة البيانات بنجاح\n\n";
    
    // تنظيف الملف المؤقت
    if ($isTempFile && file_exists($restoreFile)) {
        unlink($restoreFile);
    }
    
    echo "=== تم الانتهاء بنجاح ===\n";
    echo "✅ تم استعادة المشروع من Git\n";
    echo "✅ تم تحديث قاعدة البيانات من النسخة الاحتياطية\n\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    
    // تنظيف الملف المؤقت في حالة الخطأ
    if (isset($restoreFile) && $isTempFile && file_exists($restoreFile)) {
        unlink($restoreFile);
    }
    
    exit(1);
}

