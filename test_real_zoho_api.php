<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho API Real Data Test ===\n";

// Test API connection
$apiClient = new \App\Services\ZohoApiClient();

echo "1. Testing API connection...\n";
$connectionTest = $apiClient->testConnection();
echo "Connection: " . ($connectionTest ? "✅ Success" : "❌ Failed") . "\n";

if (!$connectionTest) {
    echo "❌ Cannot proceed without API connection\n";
    exit(1);
}

echo "\n2. Testing getTickets method...\n";
try {
    $response = $apiClient->getTickets(['limit' => 10]);
    
    if ($response && isset($response['data'])) {
        $tickets = $response['data'];
        echo "✅ Got " . count($tickets) . " tickets from API\n";
        
        echo "\nSample tickets from API:\n";
        foreach (array_slice($tickets, 0, 3) as $ticket) {
            echo "ID: " . ($ticket['id'] ?? 'N/A') . "\n";
            echo "Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
            echo "Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
            echo "Status: " . ($ticket['status'] ?? 'N/A') . "\n";
            echo "Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
            echo "Created: " . ($ticket['createdTime'] ?? 'N/A') . "\n";
            echo "Closed: " . ($ticket['closedTime'] ?? 'N/A') . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ No data returned from API\n";
        echo "Response: " . json_encode($response) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error getting tickets: " . $e->getMessage() . "\n";
}

echo "\n3. Testing getTicketsByDateRangeAndAgent...\n";
try {
    $response = $apiClient->getTicketsByDateRangeAndAgent(
        'Yaraa Khaled',
        now()->subDays(30)->format('Y-m-d'),
        now()->format('Y-m-d'),
        100
    );
    
    if ($response && isset($response['data'])) {
        $tickets = $response['data'];
        echo "✅ Found " . count($tickets) . " tickets for Yaraa Khaled\n";
        
        if (count($tickets) > 0) {
            echo "\nSample tickets for Yaraa Khaled:\n";
            foreach (array_slice($tickets, 0, 3) as $ticket) {
                echo "ID: " . ($ticket['id'] ?? 'N/A') . "\n";
                echo "Number: " . ($ticket['ticketNumber'] ?? 'N/A') . "\n";
                echo "Subject: " . ($ticket['subject'] ?? 'N/A') . "\n";
                echo "Status: " . ($ticket['status'] ?? 'N/A') . "\n";
                echo "Closed By: " . ($ticket['cf']['cf_closed_by'] ?? 'N/A') . "\n";
                echo "---\n";
            }
        }
    } else {
        echo "❌ No tickets found for Yaraa Khaled\n";
    }
} catch (\Exception $e) {
    echo "❌ Error getting tickets for Yaraa Khaled: " . $e->getMessage() . "\n";
}

echo "\n4. Testing getAgents...\n";
try {
    $response = $apiClient->getAgents();
    
    if ($response && isset($response['data'])) {
        $agents = $response['data'];
        echo "✅ Found " . count($agents) . " agents\n";
        
        echo "\nSample agents:\n";
        foreach (array_slice($agents, 0, 5) as $agent) {
            $firstName = $agent['firstName'] ?? '';
            $lastName = $agent['lastName'] ?? '';
            $fullName = trim("{$firstName} {$lastName}");
            echo "ID: " . ($agent['id'] ?? 'N/A') . "\n";
            echo "Name: {$fullName}\n";
            echo "Email: " . ($agent['email'] ?? 'N/A') . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ No agents found\n";
    }
} catch (\Exception $e) {
    echo "❌ Error getting agents: " . $e->getMessage() . "\n";
}

echo "\n=== End Test ===\n";

