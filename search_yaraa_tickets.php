<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Search for Yaraa Khaled Tickets ===\n";

// Use the working refresh token
$refreshToken = '1000.52819ce62c5efadf103da41c39462664.026dbfb73e2747e9b0b09a714e0fa0ee';
$clientId = '1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT';
$clientSecret = '30624b06180b20ab5252fc8e6145ad175762a367a0';
$orgId = '786481962';

// Get access token
$response = \Illuminate\Support\Facades\Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
    'refresh_token' => $refreshToken,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'grant_type' => 'refresh_token'
]);

if (!$response->successful()) {
    echo "❌ Failed to get access token\n";
    exit(1);
}

$data = $response->json();
$accessToken = $data['access_token'];

echo "✅ Access token obtained\n";

// Search for Yaraa Khaled tickets in multiple batches
$allYaraaTickets = [];
$batchSize = 100;
$maxBatches = 20; // Search up to 2000 tickets

echo "Searching for Yaraa Khaled tickets in batches...\n";

for ($batch = 0; $batch < $maxBatches; $batch++) {
    $from = $batch * $batchSize;
    
    echo "Batch " . ($batch + 1) . ": searching tickets from {$from} to " . ($from + $batchSize - 1) . "\n";
    
    $ticketsResponse = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => "Zoho-oauthtoken {$accessToken}",
        'Content-Type' => 'application/json',
    ])->get('https://desk.zoho.com/api/v1/tickets', [
        'orgId' => $orgId,
        'from' => $from,
        'limit' => $batchSize,
        'sortBy' => '-createdTime'
    ]);
    
    if (!$ticketsResponse->successful()) {
        echo "❌ Batch " . ($batch + 1) . " failed: " . $ticketsResponse->status() . "\n";
        break;
    }
    
    $ticketsData = $ticketsResponse->json();
    $tickets = $ticketsData['data'] ?? [];
    
    if (empty($tickets)) {
        echo "No more tickets available\n";
        break;
    }
    
    echo "Found " . count($tickets) . " tickets in this batch\n";
    
    // Look for Yaraa Khaled tickets
    $yaraaTickets = array_filter($tickets, function($ticket) {
        $closedBy = $ticket['cf']['cf_closed_by'] ?? '';
        return stripos($closedBy, 'yaraa') !== false || 
               stripos($closedBy, 'yara') !== false ||
               stripos($closedBy, 'خالد') !== false;
    });
    
    if (count($yaraaTickets) > 0) {
        echo "✅ Found " . count($yaraaTickets) . " Yaraa Khaled tickets in this batch!\n";
        $allYaraaTickets = array_merge($allYaraaTickets, $yaraaTickets);
        
        foreach ($yaraaTickets as $ticket) {
            echo "  Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
            echo "  Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
            echo "  Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
            echo "  Status: " . ($ticket['status'] ?? 'N/A') . "\n";
            echo "  Created: " . ($ticket['createdTime'] ?? 'N/A') . "\n";
            echo "  Closed: " . ($ticket['closedTime'] ?? 'N/A') . "\n";
            echo "  ---\n";
        }
    } else {
        echo "No Yaraa Khaled tickets in this batch\n";
    }
    
    // If we got fewer tickets than requested, we've reached the end
    if (count($tickets) < $batchSize) {
        echo "Reached end of available tickets\n";
        break;
    }
    
    // Rate limiting
    sleep(1);
}

echo "\n=== Final Results ===\n";
echo "Total Yaraa Khaled tickets found: " . count($allYaraaTickets) . "\n";

if (count($allYaraaTickets) > 0) {
    echo "\nAll Yaraa Khaled tickets:\n";
    foreach ($allYaraaTickets as $ticket) {
        echo "Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
        echo "Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
        echo "Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
        echo "Status: " . ($ticket['status'] ?? 'N/A') . "\n";
        echo "Created: " . ($ticket['createdTime'] ?? 'N/A') . "\n";
        echo "Closed: " . ($ticket['closedTime'] ?? 'N/A') . "\n";
        echo "---\n";
    }
    
    // Now sync these tickets to Laravel
    echo "\n=== Syncing to Laravel ===\n";
    
    $user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();
    
    if ($user) {
        echo "Found user: {$user->name} (ID: {$user->id})\n";
        
        $synced = 0;
        foreach ($allYaraaTickets as $ticketData) {
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
        
        echo "✅ Synced {$synced} real tickets to Laravel database\n";
        
        // Show what will appear in dashboard
        echo "\n=== Dashboard Preview ===\n";
        $recentTickets = $user->zohoTickets()
            ->excludeAutoClose()
            ->closed()
            ->orderBy('closed_at_zoho', 'desc')
            ->limit(10)
            ->get();
        
        echo "Recent tickets for dashboard: " . $recentTickets->count() . "\n";
        
        foreach ($recentTickets as $ticket) {
            echo "{$ticket->ticket_number} | {$ticket->subject} | {$ticket->status} | {$ticket->closed_at_zoho}\n";
        }
        
    } else {
        echo "❌ User not found for Yaraa Khaled\n";
    }
    
} else {
    echo "❌ No Yaraa Khaled tickets found in any batch\n";
    echo "This could mean:\n";
    echo "1. Yaraa Khaled doesn't have any tickets\n";
    echo "2. The name in cf_closed_by is different\n";
    echo "3. The tickets are in a different date range\n";
}

echo "\n=== End ===\n";

