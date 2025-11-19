<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoApiClient;
use App\Services\ZohoSyncService;
use App\Models\ZohoTicketCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ZohoBulkSyncController extends Controller
{
    protected $apiClient;
    protected $syncService;

    public function __construct()
    {
        $this->apiClient = new ZohoApiClient();
        $this->syncService = new ZohoSyncService($this->apiClient);
    }

    /**
     * Display the bulk sync page
     */
    public function index()
    {
        // Get recent sync logs
        $recentLogs = $this->getRecentSyncLogs();

        // Get sync statistics
        $stats = $this->getSyncStats();

        return view('zoho.bulk-sync', compact('recentLogs', 'stats'));
    }

    /**
     * Execute bulk sync
     */
    public function execute(Request $request)
    {
        $request->validate([
            'target_count' => 'required|integer|min:1|max:5000',
            'selected_date' => 'nullable|date',
        ]);

        $targetCount = (int) $request->input('target_count', 2000);
        $selectedDate = $request->input('selected_date');
        
        // Start sync in background using queue or run synchronously
        $result = $this->syncTickets($targetCount, $selectedDate);

        return response()->json([
            'success' => true,
            'message' => 'Sync completed successfully',
            'data' => $result
        ]);
    }

    /**
     * Sync tickets with pagination
     */
    protected function syncTickets($targetCount, $selectedDate = null)
    {
        $batchSize = 100;
        $batchesToFetch = (int) ceil($targetCount / $batchSize);
        $totalFetched = 0;
        $totalProcessed = 0;
        $failedBatches = [];
        $consecutiveEmptyBatches = 0;
        $maxEmptyBatches = 3;
        $startTime = microtime(true);

        $logData = [
            'started_at' => now(),
            'target_count' => $targetCount,
            'selected_date' => $selectedDate,
            'status' => 'running'
        ];

        // Log sync start
        Log::info('Zoho Bulk Sync Started', $logData);

        for ($batchNum = 0; $batchNum < $batchesToFetch; $batchNum++) {
            $fromIndex = $batchNum * $batchSize;
            $result = $this->fetchTicketBatch($fromIndex, $batchSize);

            if ($result['success']) {
                $tickets = $result['tickets'];
                $count = count($tickets);

                if ($count > 0) {
                    // Filter by date if specified
                    if ($selectedDate) {
                        $tickets = $this->filterTicketsByDate($tickets, $selectedDate);
                        $count = count($tickets);
                    }

                    $processed = $this->processTickets($tickets);
                    $totalFetched += $count;
                    $totalProcessed += $processed;
                    $consecutiveEmptyBatches = 0;

                    Log::info("Zoho Bulk Sync Progress", [
                        'batch' => $batchNum + 1,
                        'fetched' => $count,
                        'processed' => $processed,
                        'total_fetched' => $totalFetched,
                        'total_processed' => $totalProcessed,
                        'target' => $targetCount
                    ]);

                    if ($totalProcessed >= $targetCount) {
                        break;
                    }
                } else {
                    $consecutiveEmptyBatches++;
                    if ($consecutiveEmptyBatches >= $maxEmptyBatches) {
                        break;
                    }
                }
            } else {
                $failedBatches[] = [
                    'batch' => $batchNum,
                    'from_index' => $fromIndex,
                    'error' => $result['error']
                ];
                Log::error("Zoho Bulk Sync Batch Failed", $failedBatches[count($failedBatches) - 1]);
            }

            // Rate limiting
            if ($batchNum < $batchesToFetch - 1) {
                usleep(1000000); // 1 second
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $resultData = [
            'total_fetched' => $totalFetched,
            'total_processed' => $totalProcessed,
            'failed_batches' => count($failedBatches),
            'duration_seconds' => $duration,
            'status' => $totalProcessed > 0 ? 'success' : 'failed',
            'completed_at' => now()
        ];

        Log::info('Zoho Bulk Sync Completed', $resultData);

        return $resultData;
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

        foreach ($tickets as $ticketData) {
            try {
                $reflection = new \ReflectionClass($this->syncService);
                $method = $reflection->getMethod('processTicket');
                $method->setAccessible(true);
                $method->invoke($this->syncService, $ticketData);
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
     * Filter tickets by date
     */
    protected function filterTicketsByDate($tickets, $dateString)
    {
        $targetDate = Carbon::parse($dateString)->startOfDay();
        $nextDay = $targetDate->copy()->addDay();

        return array_filter($tickets, function($ticket) use ($targetDate, $nextDay) {
            $createdTime = $ticket['createdTime'] ?? null;
            if (!$createdTime) {
                return false;
            }

            $ticketDate = Carbon::parse($createdTime);
            return $ticketDate >= $targetDate && $ticketDate < $nextDay;
        });
    }

    /**
     * Get recent sync logs
     */
    protected function getRecentSyncLogs()
    {
        // This would be from a logs table or Laravel logs
        // For now, we'll return mock data or read from actual logs
        return [];
    }

    /**
     * Get sync statistics
     */
    protected function getSyncStats()
    {
        return [
            'total_tickets' => ZohoTicketCache::count(),
            'today_tickets' => ZohoTicketCache::whereDate('created_at_zoho', today())->count(),
            'yesterday_tickets' => ZohoTicketCache::whereDate('created_at_zoho', today()->subDay())->count(),
            'last_sync' => ZohoTicketCache::max('updated_at'),
        ];
    }
}
