<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuditService;

class CleanupAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:cleanup {--days=90 : Number of days to keep audit logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old audit logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up audit logs older than {$days} days...");
        
        $auditService = app(AuditService::class);
        $deletedCount = $auditService->cleanupOldLogs($days);
        
        $this->info("Successfully deleted {$deletedCount} old audit log entries.");
        
        return Command::SUCCESS;
    }
}


