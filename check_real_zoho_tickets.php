<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho Real Tickets Check ===\n";

// Check what tickets we have
$allTickets = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->get();

echo "Total tickets for Yaraa Khaled: " . $allTickets->count() . "\n";

// Check if we have real Zoho tickets (not sample data)
$realTickets = $allTickets->filter(function($ticket) {
    // Real Zoho tickets usually have different patterns
    return !str_starts_with($ticket->zoho_ticket_id, 'ZOHO_YARA_') && 
           !str_starts_with($ticket->ticket_number, 'TKT-YARA-');
});

echo "Real Zoho tickets: " . $realTickets->count() . "\n";
echo "Sample tickets: " . ($allTickets->count() - $realTickets->count()) . "\n";

if ($realTickets->count() > 0) {
    echo "\nReal Zoho tickets:\n";
    foreach ($realTickets->take(5) as $ticket) {
        echo "ID: {$ticket->zoho_ticket_id} | Number: {$ticket->ticket_number} | Subject: {$ticket->subject}\n";
    }
} else {
    echo "\n❌ No real Zoho tickets found!\n";
    echo "All tickets are sample data.\n";
}

// Check the sync result
echo "\n=== Checking Sync Status ===\n";

// Try to sync real tickets
$apiClient = new \App\Services\ZohoApiClient();
$syncService = new \App\Services\ZohoSyncService($apiClient);

echo "Testing API connection...\n";
$connectionTest = $apiClient->testConnection();
echo "API Connection: " . ($connectionTest ? "✅ Success" : "❌ Failed") . "\n";

if ($connectionTest) {
    echo "\nFetching real tickets from Zoho API...\n";
    
    try {
        $response = $apiClient->getTicketsByDateRangeAndAgent(
            'Yaraa Khaled',
            now()->subDays(30)->format('Y-m-d'),
            now()->format('Y-m-d'),
            100
        );
        
        if ($response && isset($response['data'])) {
            $realApiTickets = $response['data'];
            echo "✅ Found " . count($realApiTickets) . " real tickets from API\n";
            
            if (count($realApiTickets) > 0) {
                echo "\nSample real tickets from API:\n";
                foreach (array_slice($realApiTickets, 0, 3) as $ticket) {
                    echo "ID: " . ($ticket['id'] ?? 'N/A') . "\n";
                    echo "Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
                    echo "Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
                    echo "Status: " . ($ticket['status'] ?? 'N/A') . "\n";
                    echo "Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
                    echo "Created: " . ($ticket['createdTime'] ?? 'N/A') . "\n";
                    echo "Closed: " . ($ticket['closedTime'] ?? 'N/A') . "\n";
                    echo "---\n";
                }
                
                echo "\nSyncing real tickets to database...\n";
                $user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();
                
                if ($user) {
                    $synced = 0;
                    foreach ($realApiTickets as $ticketData) {
                        // Process each ticket
                        $ticket = new \App\Models\ZohoTicketCache();
                        $ticket->zoho_ticket_id = $ticketData['id'] ?? 'unknown';
                        $ticket->ticket_number = $ticketData['ticketNumber'] ?? 'unknown';
                        $ticket->user_id = $user->id;
                        $ticket->closed_by_name = $ticketData['cf']['cf_closed_by'] ?? null;
                        $ticket->subject = $ticketData['subject'] ?? 'No Subject';
                        $ticket->status = $ticketData['status'] ?? 'Unknown';
                        $ticket->department_id = $ticketData['departmentId'] ?? null;
                        $ticket->created_at_zoho = isset($ticketData['createdTime']) ? 
                            \Carbon\Carbon::parse($ticketData['createdTime']) : null;
                        $ticket->closed_at_zoho = isset($ticketData['closedTime']) ? 
                            \Carbon\Carbon::parse($ticketData['closedTime']) : null;
                        $ticket->raw_data = $ticketData;
                        
                        // Calculate response time if possible
                        if ($ticket->created_at_zoho && $ticket->closed_at_zoho) {
                            $ticket->response_time_minutes = $ticket->created_at_zoho->diffInMinutes($ticket->closed_at_zoho);
                        }
                        
                        $ticket->save();
                        $synced++;
                    }
                    
                    echo "✅ Synced {$synced} real tickets to database\n";
                }
            }
        } else {
            echo "❌ No tickets returned from API\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error fetching tickets: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Final Check ===\n";
$finalTickets = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->get();
echo "Total tickets now: " . $finalTickets->count() . "\n";

$realFinalTickets = $finalTickets->filter(function($ticket) {
    return !str_starts_with($ticket->zoho_ticket_id, 'ZOHO_YARA_') && 
           !str_starts_with($ticket->ticket_number, 'TKT-YARA-');
});

echo "Real tickets now: " . $realFinalTickets->count() . "\n";

if ($realFinalTickets->count() > 0) {
    echo "\nReal tickets that will show in dashboard:\n";
    foreach ($realFinalTickets->take(5) as $ticket) {
        echo "{$ticket->ticket_number} | {$ticket->subject} | {$ticket->status} | {$ticket->closed_at_zoho}\n";
    }
}

echo "\n=== End ===\n";

