<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing All Tickets Page ===\n";

// Test the controller method
$apiClient = new \App\Services\ZohoApiClient();
$controller = new \App\Http\Controllers\ZohoStatsController(
    new \App\Services\ZohoStatsService($apiClient),
    new \App\Services\ZohoSyncService($apiClient)
);

// Simulate authenticated request
$user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();
if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

\Illuminate\Support\Facades\Auth::login($user);

$request = new \Illuminate\Http\Request();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $controller->allTickets($request);
    
    if ($response instanceof \Illuminate\View\View) {
        echo "✅ All tickets view created successfully\n";
        echo "View name: " . $response->getName() . "\n";
        
        $data = $response->getData();
        echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
        
        if (isset($data['tickets'])) {
            echo "Tickets count: " . $data['tickets']->count() . "\n";
        }
        
        if (isset($data['stats'])) {
            echo "Stats: " . json_encode($data['stats']) . "\n";
        }
        
        if (isset($data['agents'])) {
            echo "Agents count: " . $data['agents']->count() . "\n";
            echo "Sample agents: " . $data['agents']->take(5)->implode(', ') . "\n";
        }
        
    } else {
        echo "❌ All tickets returned: " . get_class($response) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ All tickets error: " . $e->getMessage() . "\n";
}

echo "\n=== Testing API Ticket Details ===\n";

// Test API method
try {
    $tickets = \App\Models\ZohoTicketCache::limit(1)->get();
    
    if ($tickets->count() > 0) {
        $ticket = $tickets->first();
        $apiResponse = $controller->apiTicketDetails($ticket->zoho_ticket_id);
        
        if ($apiResponse instanceof \Illuminate\Http\JsonResponse) {
            echo "✅ API ticket details working\n";
            $data = $apiResponse->getData(true);
            echo "Ticket ID: " . ($data['zoho_ticket_id'] ?? 'N/A') . "\n";
            echo "Ticket Number: " . ($data['ticket_number'] ?? 'N/A') . "\n";
            echo "Subject: " . ($data['subject'] ?? 'N/A') . "\n";
        } else {
            echo "❌ API returned: " . get_class($apiResponse) . "\n";
        }
    } else {
        echo "❌ No tickets found for API test\n";
    }
} catch (\Exception $e) {
    echo "❌ API error: " . $e->getMessage() . "\n";
}

echo "\n=== Page URLs ===\n";
echo "All Tickets Page: http://127.0.0.1:8000/zoho/tickets\n";
echo "Dashboard: http://127.0.0.1:8000/zoho/my-stats\n";

echo "\n=== End Test ===\n";

