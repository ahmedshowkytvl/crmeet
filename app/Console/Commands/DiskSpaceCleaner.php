<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DiskSpaceCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disk:clean 
                            {--logs : Clean log files}
                            {--cache : Clean cache files}
                            {--temp : Clean temporary files}
                            {--all : Clean all types of files}
                            {--force : Force clean without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean disk space by removing unnecessary files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting disk space cleanup...');
        
        $cleaned = 0;
        $totalSize = 0;

        // Clean logs
        if ($this->option('logs') || $this->option('all')) {
            $result = $this->cleanLogs();
            $cleaned += $result['files'];
            $totalSize += $result['size'];
        }

        // Clean cache
        if ($this->option('cache') || $this->option('all')) {
            $result = $this->cleanCache();
            $cleaned += $result['files'];
            $totalSize += $result['size'];
        }

        // Clean temp files
        if ($this->option('temp') || $this->option('all')) {
            $result = $this->cleanTempFiles();
            $cleaned += $result['files'];
            $totalSize += $result['size'];
        }

        // If no specific option, clean all
        if (!$this->option('logs') && !$this->option('cache') && !$this->option('temp') && !$this->option('all')) {
            $result = $this->cleanAll();
            $cleaned += $result['files'];
            $totalSize += $result['size'];
        }

        $this->info("✅ Cleanup completed!");
        $this->info("📊 Files cleaned: {$cleaned}");
        $this->info("💾 Space freed: " . $this->formatBytes($totalSize));
    }

    /**
     * Clean log files
     */
    private function cleanLogs()
    {
        $this->info('📝 Cleaning log files...');
        
        $logPath = storage_path('logs');
        $files = 0;
        $size = 0;

        if (File::exists($logPath)) {
            $logFiles = File::glob($logPath . '/*.log');
            
            foreach ($logFiles as $file) {
                $fileSize = File::size($file);
                File::delete($file);
                $files++;
                $size += $fileSize;
                $this->line("  Deleted: " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
            }
        }

        return ['files' => $files, 'size' => $size];
    }

    /**
     * Clean cache files
     */
    private function cleanCache()
    {
        $this->info('🗂️ Cleaning cache files...');
        
        $files = 0;
        $size = 0;

        // Clear Laravel cache
        try {
            Artisan::call('cache:clear');
            $this->line("  Cleared Laravel cache");
            $files++;
        } catch (\Exception $e) {
            $this->warn("  Failed to clear Laravel cache: " . $e->getMessage());
        }

        // Clear config cache
        try {
            Artisan::call('config:clear');
            $this->line("  Cleared config cache");
            $files++;
        } catch (\Exception $e) {
            $this->warn("  Failed to clear config cache: " . $e->getMessage());
        }

        // Clear view cache
        try {
            Artisan::call('view:clear');
            $this->line("  Cleared view cache");
            $files++;
        } catch (\Exception $e) {
            $this->warn("  Failed to clear view cache: " . $e->getMessage());
        }

        // Clear route cache
        try {
            Artisan::call('route:clear');
            $this->line("  Cleared route cache");
            $files++;
        } catch (\Exception $e) {
            $this->warn("  Failed to clear route cache: " . $e->getMessage());
        }

        return ['files' => $files, 'size' => $size];
    }

    /**
     * Clean temporary files
     */
    private function cleanTempFiles()
    {
        $this->info('🗑️ Cleaning temporary files...');
        
        $files = 0;
        $size = 0;

        // Clean Laravel temp files
        $tempPaths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('app/temp'),
        ];

        foreach ($tempPaths as $path) {
            if (File::exists($path)) {
                $tempFiles = File::allFiles($path);
                
                foreach ($tempFiles as $file) {
                    $fileSize = $file->getSize();
                    File::delete($file->getPathname());
                    $files++;
                    $size += $fileSize;
                }
                
                $this->line("  Cleaned: " . basename($path));
            }
        }

        return ['files' => $files, 'size' => $size];
    }

    /**
     * Clean all types of files
     */
    private function cleanAll()
    {
        $this->info('🧹 Cleaning all unnecessary files...');
        
        $files = 0;
        $size = 0;

        // Clean logs
        $result = $this->cleanLogs();
        $files += $result['files'];
        $size += $result['size'];

        // Clean cache
        $result = $this->cleanCache();
        $files += $result['files'];
        $size += $result['size'];

        // Clean temp files
        $result = $this->cleanTempFiles();
        $files += $result['files'];
        $size += $result['size'];

        return ['files' => $files, 'size' => $size];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}