<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ZohoTicketCache;
use App\Services\ZohoApiClient;
use Illuminate\Support\Facades\Log;

class UpdateTicketsClosedBy extends Command
{
    protected $signature = 'zoho:update-tickets-closed-by {--limit=50 : Number of tickets to update}';
    protected $description = 'Update closed_by_name from Zoho API for cached tickets';

    public function handle()
    {
        $this->info('🔄 Starting update of tickets closed_by field...');
        
        $limit = $this->option('limit');
        $apiClient = new ZohoApiClient();
        
        // Get tickets with null closed_by_name
        $tickets = ZohoTicketCache::whereNull('closed_by_name')
            ->orWhere('closed_by_name', '')
            ->limit($limit)
            ->get();
        
        if ($tickets->isEmpty()) {
            $this->info('✅ No tickets need updating');
            return;
        }
        
        $updatedCount = 0;
        $errorCount = 0;
        
        foreach ($tickets as $ticket) {
            try {
                // Get fresh ticket data from Zoho API
                $ticketData = $apiClient->getTicket($ticket->zoho_ticket_id);
                
                if (!$ticketData) {
                    $this->warn("⚠️  Could not fetch ticket {$ticket->ticket_number}");
                    $errorCount++;
                    continue;
                }
                
                // Extract closed_by value
                $closedBy = null;
                
                // Try different locations for closed_by
                if (isset($ticketData['cf']['cf_closed_by'])) {
                    $closedBy = $ticketData['cf']['cf_closed_by'];
                } elseif (isset($ticketData['customFields']['Closed By'])) {
                    $closedBy = $ticketData['customFields']['Closed By'];
                } elseif (isset($ticketData['closedBy'])) {
                    $closedBy = $ticketData['closedBy'];
                }
                
                if ($closedBy) {
                    // Prepare raw_data update
                    $rawData = $ticket->raw_data ?: [];
                    
                    // Ensure cf structure exists
                    if (!isset($rawData['cf'])) {
                        $rawData['cf'] = [];
                    }
                    $rawData['cf']['cf_closed_by'] = $closedBy;
                    
                    // Update customFields
                    if (!isset($rawData['customFields'])) {
                        $rawData['customFields'] = [];
                    }
                    $rawData['customFields']['Closed By'] = $closedBy;
                    
                    // Merge with new ticket data
                    $rawData = array_merge($rawData, $ticketData);
                    
                    // Update ticket
                    $ticket->closed_by_name = $closedBy;
                    $ticket->raw_data = $rawData;
                    
                    $ticket->save();
                    
                    $this->info("✅ Updated ticket {$ticket->ticket_number}: {$closedBy}");
                    $updatedCount++;
                } else {
                    $this->line("⏭️  Skipping ticket {$ticket->ticket_number}: No closed_by found");
                }
                
                // Rate limiting
                usleep(500000); // 0.5 seconds
                
            } catch (\Exception $e) {
                $this->error("❌ Error updating ticket {$ticket->ticket_number}: " . $e->getMessage());
                $errorCount++;
                Log::error('Error updating ticket closed_by', [
                    'ticket_id' => $ticket->zoho_ticket_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("\n📊 Summary:");
        $this->info("   Updated: {$updatedCount}");
        $this->info("   Errors: {$errorCount}");
    }
}

