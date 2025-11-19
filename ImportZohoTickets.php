<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoApiClient;
use App\Models\ZohoTicketCache;
use App\Models\User;
use Carbon\Carbon;

class ImportZohoTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:import-tickets {--limit=3000 : Number of tickets to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import tickets from Zoho Desk API';

    private ZohoApiClient $apiClient;

    public function __construct()
    {
        parent::__construct();
        $this->apiClient = new ZohoApiClient();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Zoho tickets import...');
        
        $limit = (int) $this->option('limit');
        $batchSize = 100; // Fetch 100 at a time from API
        
        $totalImported = 0;
        $from = 0;
        
        while ($totalImported < $limit) {
            $this->info("Fetching tickets batch (offset: {$from})...");
            
            $params = [
                'limit' => min($batchSize, $limit - $totalImported),
                'from' => $from,
                'sortBy' => '-createdTime'
            ];
            
            $response = $this->apiClient->getTickets($params);
            
            if (!$response || !isset($response['data']) || empty($response['data'])) {
                $this->warn('No more tickets available from Zoho');
                break;
            }
            
            $tickets = $response['data'];
            $batchImported = 0;
            
            foreach ($tickets as $ticket) {
                try {
                    // Extract ticket data
                    $zohoTicketId = $ticket['id'] ?? null;
                    $ticketNumber = $ticket['ticketNumber'] ?? '';
                    $subject = $ticket['subject'] ?? '';
                    $status = $ticket['status'] ?? '';
                    $createdTime = isset($ticket['createdTime']) ? Carbon::parse($ticket['createdTime']) : null;
                    $closedTime = isset($ticket['closedTime']) ? Carbon::parse($ticket['closedTime']) : null;
                    
                    // Extract custom field for closed_by
                    $closedByName = $ticket['cf']['cf_closed_by'] ?? null;
                    
                    // Extract department
                    $departmentId = $ticket['departmentId'] ?? null;
                    
                    // Extract assignee
                    $assignee = $ticket['assignee'] ?? [];
                    $assigneeEmail = $assignee['email'] ?? null;
                    
                    // Find matching user by email
                    $userId = null;
                    if ($assigneeEmail) {
                        $user = User::where('email', $assigneeEmail)->first();
                        $userId = $user ? $user->id : null;
                    }
                    
                    // Calculate response time
                    $responseTimeMinutes = null;
                    if ($createdTime && $closedTime) {
                        $diffMinutes = $closedTime->diffInMinutes($createdTime);
                        // Only store positive values (avoid negative time differences due to timezone issues)
                        $responseTimeMinutes = $diffMinutes > 0 ? (int) $diffMinutes : null;
                    }
                    
                    // Get thread count
                    $threadCount = $ticket['threadCount'] ?? 0;
                    
                    // Check if ticket already exists
                    $existingTicket = ZohoTicketCache::where('zoho_ticket_id', $zohoTicketId)->first();
                    
                    if ($existingTicket) {
                        // Update existing ticket
                        $existingTicket->update([
                            'ticket_number' => $ticketNumber,
                            'subject' => $subject,
                            'status' => $status,
                            'created_at_zoho' => $createdTime,
                            'closed_at_zoho' => $closedTime,
                            'response_time_minutes' => $responseTimeMinutes,
                            'thread_count' => $threadCount,
                            'closed_by_name' => $closedByName,
                            'user_id' => $userId,
                            'department_id' => $departmentId,
                            'raw_data' => $ticket
                        ]);
                        $this->info("Updated ticket: {$ticketNumber}");
                    } else {
                        // Create new ticket
                        ZohoTicketCache::create([
                            'zoho_ticket_id' => $zohoTicketId,
                            'ticket_number' => $ticketNumber,
                            'subject' => $subject,
                            'status' => $status,
                            'created_at_zoho' => $createdTime,
                            'closed_at_zoho' => $closedTime,
                            'response_time_minutes' => $responseTimeMinutes,
                            'thread_count' => $threadCount,
                            'closed_by_name' => $closedByName,
                            'user_id' => $userId,
                            'department_id' => $departmentId,
                            'raw_data' => $ticket
                        ]);
                        $batchImported++;
                        $this->info("Imported ticket: {$ticketNumber}");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("Error processing ticket: " . $e->getMessage());
                    continue;
                }
            }
            
            $totalImported += count($tickets);
            $from += $batchSize;
            
            $this->info("Batch complete. Total processed: {$totalImported}, New tickets: {$batchImported}");
            
            // If we got fewer tickets than requested, we've reached the end
            if (count($tickets) < $batchSize) {
                break;
            }
        }
        
        $this->info("Import complete! Total tickets processed: {$totalImported}");
        $this->info("Total tickets in database: " . ZohoTicketCache::count());
        
        return 0;
    }
}
