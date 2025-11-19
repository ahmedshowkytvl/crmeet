<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Sync Real Zoho Tickets ===\n";

// Use the working refresh token from Python
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

// Clear existing sample data
echo "\nClearing existing sample data...\n";
$deletedCount = \App\Models\ZohoTicketCache::where('zoho_ticket_id', 'like', 'ZOHO_%')->delete();
echo "Deleted {$deletedCount} sample tickets\n";

// Fetch real tickets from Zoho API
echo "\nFetching real tickets from Zoho API...\n";

$allTickets = [];
$batchSize = 100;
$maxBatches = 10; // Get up to 1000 tickets

for ($batch = 0; $batch < $maxBatches; $batch++) {
    $from = $batch * $batchSize;
    
    echo "Batch " . ($batch + 1) . ": fetching tickets from {$from} to " . ($from + $batchSize - 1) . "\n";
    
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
    
    // Process each ticket
    foreach ($tickets as $ticketData) {
        $zohoTicketId = $ticketData['id'] ?? 'unknown';
        
        // Check if ticket already exists
        $existingTicket = \App\Models\ZohoTicketCache::where('zoho_ticket_id', $zohoTicketId)->first();
        
        if ($existingTicket) {
            // Update existing ticket
            $ticket = $existingTicket;
        } else {
            // Create new ticket
            $ticket = new \App\Models\ZohoTicketCache();
        }
        
        $ticket->zoho_ticket_id = $zohoTicketId;
        $ticket->ticket_number = $ticketData['ticketNumber'] ?? 'unknown';
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
            $ticket->response_time_minutes = (int) $ticket->created_at_zoho->diffInMinutes($ticket->closed_at_zoho);
        }
        
        $ticket->save();
        $allTickets[] = $ticket;
    }
    
    // If we got fewer tickets than requested, we've reached the end
    if (count($tickets) < $batchSize) {
        echo "Reached end of available tickets\n";
        break;
    }
    
    // Rate limiting
    sleep(1);
}

echo "\n=== Sync Results ===\n";
echo "Total real tickets synced: " . count($allTickets) . "\n";

if (count($allTickets) > 0) {
    echo "\nSample real tickets:\n";
    foreach (array_slice($allTickets, 0, 5) as $ticket) {
        echo "Number: {$ticket->ticket_number}\n";
        echo "Subject: {$ticket->subject}\n";
        echo "Status: {$ticket->status}\n";
        echo "Closed By: " . ($ticket->closed_by_name ?? 'N/A') . "\n";
        echo "Created: " . ($ticket->created_at_zoho?->format('Y-m-d H:i') ?? 'N/A') . "\n";
        echo "Closed: " . ($ticket->closed_at_zoho?->format('Y-m-d H:i') ?? 'N/A') . "\n";
        echo "---\n";
    }
    
    // Get unique agents
    $agents = collect($allTickets)->whereNotNull('closed_by_name')->pluck('closed_by_name')->unique();
    echo "\nUnique agents found: " . $agents->count() . "\n";
    foreach ($agents as $agent) {
        echo "- {$agent}\n";
    }
    
    // Statistics
    $stats = [
        'total' => count($allTickets),
        'closed' => collect($allTickets)->where('status', 'Closed')->count(),
        'open' => collect($allTickets)->where('status', 'Open')->count(),
        'pending' => collect($allTickets)->where('status', 'Pending')->count(),
        'in_progress' => collect($allTickets)->where('status', 'In Progress')->count(),
    ];
    
    echo "\nStatistics:\n";
    echo "Total: {$stats['total']}\n";
    echo "Closed: {$stats['closed']}\n";
    echo "Open: {$stats['open']}\n";
    echo "Pending: {$stats['pending']}\n";
    echo "In Progress: {$stats['in_progress']}\n";
}

echo "\n=== Page URLs ===\n";
echo "All Tickets Page: http://127.0.0.1:8000/zoho/tickets\n";
echo "Dashboard: http://127.0.0.1:8000/zoho/my-stats\n";

echo "\n=== End ===\n";

