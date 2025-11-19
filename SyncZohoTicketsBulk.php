<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoApiClient;
use App\Services\ZohoSyncService;
use App\Models\ZohoTicketCache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncZohoTicketsBulk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:sync-bulk {--target=2000 : Number of tickets to fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync tickets from Zoho in bulk using pagination logic (0-99, 100-199, etc.)';

    protected $apiClient;
    protected $syncService;
    protected $totalFetched = 0;
    protected $failedBatches = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetCount = (int) $this->option('target');
        
        $this->info('');
        $this->info('========================================================');
        $this->info('🚀 Starting Zoho Bulk Sync');
        $this->info("📊 Target: {$targetCount} tickets");
        $this->info('========================================================');
        $this->info('');

        // Initialize services
        $this->apiClient = new ZohoApiClient();
        $this->syncService = new ZohoSyncService($this->apiClient);

        // Start sync
        $success = $this->syncTickets($targetCount);

        if ($success) {
            $this->displaySummary();
            $this->info('');
            $this->info('✅ Sync completed successfully!');
            return 0;
        } else {
            $this->error('❌ Sync failed!');
            return 1;
        }
    }

    /**
     * Sync tickets with pagination logic
     */
    protected function syncTickets($targetCount)
    {
        $batchSize = 100;
        $batchesToFetch = (int) ceil($targetCount / $batchSize);

        $this->info("📦 Fetching {$batchesToFetch} batches (100 tickets each)...");
        $this->info('');

        $consecutiveEmptyBatches = 0;
        $maxEmptyBatches = 3;

        for ($batchNum = 0; $batchNum < $batchesToFetch; $batchNum++) {
            $fromIndex = $batchNum * $batchSize;
            
            $this->info("📡 Fetching tickets {$fromIndex} to " . ($fromIndex + $batchSize - 1) . "...");

            $result = $this->fetchTicketBatch($fromIndex, $batchSize);

            if ($result['success']) {
                $tickets = $result['tickets'];
                $count = count($tickets);

                if ($count > 0) {
                    // Process and cache tickets
                    $processed = $this->processTickets($tickets);
                    $this->totalFetched += $processed;
                    $consecutiveEmptyBatches = 0;

                    $this->info("   ✅ Fetched {$count} tickets, processed {$processed} (Total: {$this->totalFetched}/{$targetCount})");

                    if ($this->totalFetched >= $targetCount) {
                        $this->info('');
                        $this->info("✅ Target reached! Collected {$this->totalFetched} tickets");
                        break;
                    }
                } else {
                    $consecutiveEmptyBatches++;
                    $this->warn("   ⚠️  Empty batch (no more tickets available)");

                    if ($consecutiveEmptyBatches >= $maxEmptyBatches) {
                        $this->info('');
                        $this->warn("⚠️  Stopping: {$maxEmptyBatches} consecutive empty batches");
                        break;
                    }
                }
            } else {
                $this->failedBatches[] = [
                    'batch' => $batchNum,
                    'from_index' => $fromIndex,
                    'error' => $result['error']
                ];
                $this->error("   ❌ Batch {$batchNum} failed: {$result['error']}");
            }

            // Rate limiting: Wait between requests
            if ($batchNum < $batchesToFetch - 1) {
                sleep(1); // 1 second delay
            }
        }

        return $this->totalFetched > 0;
    }

    /**
     * Fetch a batch of tickets
     */
    protected function fetchTicketBatch($fromIndex, $limit)
    {
        try {
            $params = [
                'from' => $fromIndex,
                'limit' => $limit,
                'sortBy' => '-createdTime'
            ];

            $response = $this->apiClient->getTickets($params);

            if ($response && isset($response['data'])) {
                return [
                    'success' => true,
                    'tickets' => $response['data'],
                    'count' => count($response['data']),
                    'total_available' => $response['info']['count'] ?? 0
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Invalid response from API',
                    'tickets' => []
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching ticket batch', [
                'from_index' => $fromIndex,
                'limit' => $limit,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tickets' => []
            ];
        }
    }

    /**
     * Process and cache tickets
     */
    protected function processTickets($tickets)
    {
        $processed = 0;
        $syncService = $this->syncService;

        foreach ($tickets as $ticketData) {
            try {
                // Process the ticket using the sync service
                $reflection = new \ReflectionClass($syncService);
                $method = $reflection->getMethod('processTicket');
                $method->setAccessible(true);
                $method->invoke($syncService, $ticketData);

                $processed++;
            } catch (\Exception $e) {
                Log::error('Error processing ticket', [
                    'ticket_number' => $ticketData['ticketNumber'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $processed;
    }

    /**
     * Display sync summary
     */
    protected function displaySummary()
    {
        $this->info('');
        $this->info('========================================================');
        $this->info('📊 SYNC SUMMARY');
        $this->info('========================================================');
        $this->info("✅ Total tickets fetched: {$this->totalFetched}");
        $this->info("❌ Failed batches: " . count($this->failedBatches));

        if (count($this->failedBatches) > 0) {
            $this->info('');
            $this->warn('⚠️  Failed batches:');
            foreach ($this->failedBatches as $fail) {
                $this->warn("   - Batch {$fail['batch']} (index {$fail['from_index']}): {$fail['error']}");
            }
        }

        // Show date range of fetched tickets
        $oldestTicket = ZohoTicketCache::orderBy('created_at_zoho', 'asc')->first();
        $newestTicket = ZohoTicketCache::orderBy('created_at_zoho', 'desc')->first();

        if ($oldestTicket && $newestTicket) {
            $this->info('');
            $this->info('📅 Date Range:');
            $this->info("   Oldest: " . Carbon::parse($oldestTicket->created_at_zoho)->format('Y-m-d H:i'));
            $this->info("   Newest: " . Carbon::parse($newestTicket->created_at_zoho)->format('Y-m-d H:i'));
        }
    }
}
