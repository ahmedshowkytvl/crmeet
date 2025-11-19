<?php

/**
 * سكريبت PHP لأخذ نسخة احتياطية من قاعدة البيانات ورفع المشروع على Git
 * Script to backup database and push project to Git
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

// أخذ نسخة احتياطية من MySQL
function backupMySQL($host, $port, $database, $username, $password, $backupFile)
{
    $command = sprintf(
        'mysqldump -h %s -P %s -u %s %s %s > %s 2>&1',
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($username),
        $password ? '-p' . escapeshellarg($password) : '',
        escapeshellarg($database),
        escapeshellarg($backupFile)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("فشل أخذ النسخة الاحتياطية من MySQL: " . implode("\n", $output));
    }
    
    return true;
}

// أخذ نسخة احتياطية من PostgreSQL
function backupPostgreSQL($host, $port, $database, $username, $password, $backupFile)
{
    putenv("PGPASSWORD=" . $password);
    
    $command = sprintf(
        'pg_dump -h %s -p %s -U %s -d %s -F p > %s 2>&1',
        escapeshellarg($host),
        escapeshellarg($port),
        escapeshellarg($username),
        escapeshellarg($database),
        escapeshellarg($backupFile)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("فشل أخذ النسخة الاحتياطية من PostgreSQL: " . implode("\n", $output));
    }
    
    return true;
}

// ضغط الملف
function compressFile($file)
{
    if (function_exists('gzencode')) {
        $content = file_get_contents($file);
        $compressed = gzencode($content, 9);
        file_put_contents($file . '.gz', $compressed);
        unlink($file);
        return $file . '.gz';
    }
    
    return $file;
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
    echo "=== بدء عملية النسخ الاحتياطي والرفع على Git ===\n\n";
    
    // قراءة إعدادات قاعدة البيانات
    $env = loadEnvFile();
    
    $dbConnection = $env['DB_CONNECTION'] ?? 'mysql';
    $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
    $dbPort = $env['DB_PORT'] ?? ($dbConnection === 'pgsql' ? '5432' : '3306');
    $dbDatabase = $env['DB_DATABASE'] ?? 'laravel';
    $dbUsername = $env['DB_USERNAME'] ?? 'root';
    $dbPassword = $env['DB_PASSWORD'] ?? '';
    
    // إنشاء مجلد النسخ الاحتياطي
    $backupDir = 'database_backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // إنشاء اسم الملف مع التاريخ والوقت
    $timestamp = date('Ymd_His');
    $backupFile = "$backupDir/{$dbDatabase}_backup_{$timestamp}.sql";
    
    echo "📦 نوع قاعدة البيانات: $dbConnection\n";
    echo "📦 قاعدة البيانات: $dbDatabase\n";
    echo "📦 الملف الاحتياطي: $backupFile\n\n";
    
    // أخذ نسخة احتياطية حسب نوع قاعدة البيانات
    echo "🔄 جاري أخذ نسخة احتياطية...\n";
    
    if (in_array($dbConnection, ['pgsql', 'postgres'])) {
        backupPostgreSQL($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $backupFile);
    } elseif (in_array($dbConnection, ['mysql', 'mariadb'])) {
        backupMySQL($dbHost, $dbPort, $dbDatabase, $dbUsername, $dbPassword, $backupFile);
    } else {
        throw new Exception("نوع قاعدة البيانات غير مدعوم: $dbConnection");
    }
    
    echo "✓ تم أخذ النسخة الاحتياطية بنجاح\n\n";
    
    // ضغط الملف
    echo "🔄 جاري ضغط الملف الاحتياطي...\n";
    $backupFile = compressFile($backupFile);
    echo "✓ تم ضغط الملف: $backupFile\n\n";
    
    // إنشاء ملف README
    $readmeContent = <<<EOF
# نسخ احتياطية قاعدة البيانات

هذا المجلد يحتوي على نسخ احتياطية من قاعدة البيانات.

## استعادة النسخة الاحتياطية

### MySQL/MariaDB:
\`\`\`bash
gunzip database_backups/filename.sql.gz
mysql -u username -p database_name < database_backups/filename.sql
\`\`\`

### PostgreSQL:
\`\`\`bash
gunzip database_backups/filename.sql.gz
psql -U username -d database_name -f database_backups/filename.sql
\`\`\`

**ملاحظة:** تأكد من قراءة ملف .env لمعرفة إعدادات قاعدة البيانات.
EOF;
    
    file_put_contents("$backupDir/README.md", $readmeContent);
    
    // التأكد من أن مجلد النسخ الاحتياطي غير موجود في .gitignore
    $gitignoreFile = '.gitignore';
    if (file_exists($gitignoreFile)) {
        $gitignoreContent = file_get_contents($gitignoreFile);
        if (preg_match('/^database_backups/m', $gitignoreContent)) {
            echo "⚠ تم العثور على database_backups في .gitignore، سيتم إزالته\n";
            $gitignoreContent = preg_replace('/^database_backups.*\n/m', '', $gitignoreContent);
            file_put_contents($gitignoreFile, $gitignoreContent);
        }
    }
    
    // إضافة جميع الملفات إلى Git
    echo "🔄 جاري إضافة الملفات إلى Git...\n";
    runGitCommand('git add .');
    
    // التحقق من وجود تغييرات
    $status = runGitCommand('git diff --staged --name-only');
    
    if (empty($status)) {
        echo "⚠ لا توجد تغييرات لإضافتها\n";
    } else {
        // إنشاء رسالة commit
        $commitMessage = "Backup and push: Database backup " . date('Y-m-d H:i:s');
        
        echo "🔄 جاري عمل commit...\n";
        runGitCommand("git commit -m " . escapeshellarg($commitMessage));
        echo "✓ تم عمل commit بنجاح\n\n";
        
        // رفع التغييرات إلى Git
        echo "🔄 جاري رفع التغييرات إلى Git...\n";
        $currentBranch = trim(implode('', runGitCommand('git branch --show-current')));
        runGitCommand("git push origin $currentBranch");
        echo "✓ تم رفع التغييرات بنجاح\n\n";
    }
    
    // عرض معلومات الملف الاحتياطي
    $fileSize = filesize($backupFile);
    $fileSizeFormatted = number_format($fileSize / 1024 / 1024, 2) . ' MB';
    
    echo "=== تم الانتهاء بنجاح ===\n";
    echo "📁 الملف الاحتياطي: $backupFile\n";
    echo "📊 حجم الملف: $fileSizeFormatted\n";
    echo "✅ تم رفع المشروع على Git\n\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    exit(1);
}

