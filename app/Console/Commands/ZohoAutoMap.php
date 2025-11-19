<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoSyncService;

class ZohoAutoMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:auto-map';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically map Laravel users to Zoho agents based on email';

    /**
     * Execute the console command.
     */
    public function handle(ZohoSyncService $syncService)
    {
        $this->info('🔗 Starting auto-mapping of users to Zoho agents...');

        try {
            $mapped = $syncService->autoMapUsers();

            if ($mapped > 0) {
                $this->info("✅ Successfully mapped {$mapped} user(s) to Zoho agents");
                return 0;
            } else {
                $this->warn('⚠️  No users were mapped. Check if emails match between Laravel and Zoho.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error during auto-mapping: {$e->getMessage()}");
            \Log::error('Zoho auto-map command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}

