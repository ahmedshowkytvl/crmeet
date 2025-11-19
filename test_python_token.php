<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Using Python Working Token ===\n";

// Use the same refresh token that works in Python
$refreshToken = '1000.52819ce62c5efadf103da41c39462664.026dbfb73e2747e9b0b09a714e0fa0ee';
$clientId = '1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT';
$clientSecret = '30624b06180b20ab5252fc8e6145ad175762a367a0';
$orgId = '786481962';

echo "Testing Python's refresh token...\n";

try {
    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
        'refresh_token' => $refreshToken,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'refresh_token'
    ]);
    
    echo "Response status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "✅ Token refresh successful!\n";
        echo "Access token: " . substr($data['access_token'], 0, 20) . "...\n";
        
        $accessToken = $data['access_token'];
        
        // Test API with the working token
        echo "\nTesting API with working token...\n";
        
        // Test agents endpoint
        $agentsResponse = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
            'Content-Type' => 'application/json',
        ])->get('https://desk.zoho.com/api/v1/agents', [
            'orgId' => $orgId,
            'limit' => 5
        ]);
        
        echo "Agents API status: " . $agentsResponse->status() . "\n";
        
        if ($agentsResponse->successful()) {
            echo "✅ Agents API successful!\n";
            $agentsData = $agentsResponse->json();
            echo "Agents count: " . (isset($agentsData['data']) ? count($agentsData['data']) : 0) . "\n";
            
            if (isset($agentsData['data']) && count($agentsData['data']) > 0) {
                echo "\nSample agents:\n";
                foreach (array_slice($agentsData['data'], 0, 3) as $agent) {
                    $firstName = $agent['firstName'] ?? '';
                    $lastName = $agent['lastName'] ?? '';
                    $fullName = trim("{$firstName} {$lastName}");
                    echo "ID: " . ($agent['id'] ?? 'N/A') . " | Name: {$fullName} | Email: " . ($agent['email'] ?? 'N/A') . "\n";
                }
            }
        } else {
            echo "❌ Agents API failed: " . $agentsResponse->body() . "\n";
        }
        
        // Test tickets endpoint
        echo "\nTesting tickets API...\n";
        
        $ticketsResponse = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$accessToken}",
            'Content-Type' => 'application/json',
        ])->get('https://desk.zoho.com/api/v1/tickets', [
            'orgId' => $orgId,
            'limit' => 10,
            'sortBy' => '-createdTime'
        ]);
        
        echo "Tickets API status: " . $ticketsResponse->status() . "\n";
        
        if ($ticketsResponse->successful()) {
            echo "✅ Tickets API successful!\n";
            $ticketsData = $ticketsResponse->json();
            echo "Tickets count: " . (isset($ticketsData['data']) ? count($ticketsData['data']) : 0) . "\n";
            
            if (isset($ticketsData['data']) && count($ticketsData['data']) > 0) {
                echo "\nSample tickets:\n";
                foreach (array_slice($ticketsData['data'], 0, 3) as $ticket) {
                    echo "ID: " . ($ticket['id'] ?? 'N/A') . "\n";
                    echo "Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
                    echo "Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
                    echo "Status: " . ($ticket['status'] ?? 'N/A') . "\n";
                    echo "Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
                    echo "Created: " . ($ticket['createdTime'] ?? 'N/A') . "\n";
                    echo "Closed: " . ($ticket['closedTime'] ?? 'N/A') . "\n";
                    echo "---\n";
                }
                
                // Look for Yaraa Khaled tickets
                echo "\nLooking for Yaraa Khaled tickets...\n";
                $yaraaTickets = array_filter($ticketsData['data'], function($ticket) {
                    $closedBy = $ticket['cf']['cf_closed_by'] ?? '';
                    return stripos($closedBy, 'yaraa') !== false || stripos($closedBy, 'yara') !== false;
                });
                
                echo "Found " . count($yaraaTickets) . " tickets for Yaraa Khaled in this batch\n";
                
                if (count($yaraaTickets) > 0) {
                    echo "\nYaraa Khaled tickets:\n";
                    foreach ($yaraaTickets as $ticket) {
                        echo "Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
                        echo "Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
                        echo "Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
                        echo "Status: " . ($ticket['status'] ?? 'N/A') . "\n";
                        echo "---\n";
                    }
                }
            }
        } else {
            echo "❌ Tickets API failed: " . $ticketsResponse->body() . "\n";
        }
        
    } else {
        echo "❌ Token refresh failed: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n";

