<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoSyncService;
use Carbon\Carbon;

class ZohoSyncByAgent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:sync-by-agent 
                            {agent : Agent name (e.g., "Yaraa Khaled")}
                            {--from= : Start date (Y-m-d format, e.g., 2024-01-01)}
                            {--to= : End date (Y-m-d format, e.g., 2024-12-31)}
                            {--field=cf_closed_by : Custom field name to search by}
                            {--limit=1000 : Maximum number of tickets to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync tickets from Zoho by specific agent and date range';

    /**
     * Execute the console command.
     */
    public function handle(ZohoSyncService $syncService)
    {
        if (!config('zoho.sync.enabled')) {
            $this->error('Zoho sync is disabled in configuration');
            return 1;
        }

        $agentName = $this->argument('agent');
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $fieldName = $this->option('field');
        $limit = (int) $this->option('limit');

        $this->info("🔄 Starting Zoho tickets sync for agent: {$agentName}");

        // Validate dates
        if ($fromDate && !$this->isValidDate($fromDate)) {
            $this->error('Invalid from date format. Use Y-m-d format (e.g., 2024-01-01)');
            return 1;
        }

        if ($toDate && !$this->isValidDate($toDate)) {
            $this->error('Invalid to date format. Use Y-m-d format (e.g., 2024-12-31)');
            return 1;
        }

        // Display search parameters
        $this->info("📋 Search Parameters:");
        $this->info("   Agent: {$agentName}");
        $this->info("   Field: {$fieldName}");
        $this->info("   From Date: " . ($fromDate ?: 'Not specified'));
        $this->info("   To Date: " . ($toDate ?: 'Not specified'));
        $this->info("   Limit: {$limit}");

        try {
            $result = $syncService->syncTicketsByCustomField(
                $fieldName, 
                $agentName, 
                $fromDate, 
                $toDate
            );

            if ($result['success']) {
                $this->info("✅ {$result['message']}");
                $this->info("📊 Synced: {$result['synced']} tickets");
                
                // Show some statistics
                $this->showStatistics($agentName);
                
                return 0;
            } else {
                $this->warn("⚠️  {$result['message']}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error during sync: {$e->getMessage()}");
            \Log::error('Zoho sync by agent command failed', [
                'agent' => $agentName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Validate date format
     */
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Show statistics after sync
     */
    private function showStatistics($agentName)
    {
        $this->info("\n📈 Statistics:");
        
        $totalTickets = \App\Models\ZohoTicketCache::where('closed_by_name', $agentName)->count();
        $closedTickets = \App\Models\ZohoTicketCache::where('closed_by_name', $agentName)
                                                   ->where('status', 'Closed')
                                                   ->count();
        $openTickets = \App\Models\ZohoTicketCache::where('closed_by_name', $agentName)
                                                 ->where('status', 'Open')
                                                 ->count();
        
        $this->info("   Total Tickets: {$totalTickets}");
        $this->info("   Closed Tickets: {$closedTickets}");
        $this->info("   Open Tickets: {$openTickets}");
        
        if ($closedTickets > 0) {
            $avgResponseTime = \App\Models\ZohoTicketCache::where('closed_by_name', $agentName)
                                                         ->where('status', 'Closed')
                                                         ->whereNotNull('response_time_minutes')
                                                         ->avg('response_time_minutes');
            
            if ($avgResponseTime) {
                $this->info("   Avg Response Time: " . round($avgResponseTime, 1) . " minutes");
            }
        }
    }
}