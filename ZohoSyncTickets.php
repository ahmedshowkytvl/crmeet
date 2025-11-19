<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoSyncService;

class ZohoSyncTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:sync-tickets 
                            {--user= : Sync tickets for specific user ID}
                            {--from= : Start date (Y-m-d format)}
                            {--to= : End date (Y-m-d format)}
                            {--limit= : Maximum number of tickets to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync tickets from Zoho Desk API';

    /**
     * Execute the console command.
     */
    public function handle(ZohoSyncService $syncService)
    {
        if (!config('zoho.sync.enabled')) {
            $this->error('Zoho sync is disabled in configuration');
            return 1;
        }

        $this->info('🔄 Starting Zoho tickets sync...');

        $userId = $this->option('user');
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $limit = $this->option('limit');

        try {
            if ($userId) {
                // Sync for specific user
                $this->info("Syncing tickets for user ID: {$userId}");
                $result = $syncService->syncTicketsForUser($userId, $fromDate, $toDate);
            } else {
                // Sync for all enabled users
                $this->info('Syncing tickets for all Zoho-enabled users...');
                $result = $syncService->syncTickets($fromDate, $toDate, $limit);
            }

            if ($result['success']) {
                $this->info("✅ {$result['message']}");
                $this->info("📊 Synced: {$result['synced']} tickets");
                return 0;
            } else {
                $this->warn("⚠️  {$result['message']}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error during sync: {$e->getMessage()}");
            \Log::error('Zoho sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}

