<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho API Test - Yaraa Khaled Tickets ===\n";

// Test the new API methods
$apiClient = new \App\Services\ZohoApiClient();

echo "1. Testing connection...\n";
$connectionTest = $apiClient->testConnection();
echo "Connection: " . ($connectionTest ? "✅ Success" : "❌ Failed") . "\n";

if (!$connectionTest) {
    echo "Cannot proceed without API connection\n";
    exit(1);
}

echo "\n2. Testing date range search...\n";

// Test with different date ranges
$testCases = [
    [
        'name' => 'Last 30 days',
        'from' => now()->subDays(30)->format('Y-m-d'),
        'to' => now()->format('Y-m-d')
    ],
    [
        'name' => 'Last 7 days',
        'from' => now()->subDays(7)->format('Y-m-d'),
        'to' => now()->format('Y-m-d')
    ],
    [
        'name' => 'All time (no date filter)',
        'from' => null,
        'to' => null
    ]
];

foreach ($testCases as $testCase) {
    echo "\n--- {$testCase['name']} ---\n";
    echo "From: " . ($testCase['from'] ?: 'Not specified') . "\n";
    echo "To: " . ($testCase['to'] ?: 'Not specified') . "\n";
    
    try {
        $response = $apiClient->getTicketsByDateRangeAndAgent(
            'Yaraa Khaled',
            $testCase['from'],
            $testCase['to'],
            50 // Limit to 50 for testing
        );
        
        if ($response && isset($response['data'])) {
            $count = count($response['data']);
            echo "Tickets found: {$count}\n";
            
            if ($count > 0) {
                echo "Sample ticket:\n";
                $sample = $response['data'][0];
                echo "  ID: " . ($sample['id'] ?? 'N/A') . "\n";
                echo "  Number: " . ($sample['ticketNumber'] ?? 'N/A') . "\n";
                echo "  Subject: " . ($sample['subject'] ?? 'N/A') . "\n";
                echo "  Status: " . ($sample['status'] ?? 'N/A') . "\n";
                echo "  Closed By: " . ($sample['cf']['cf_closed_by'] ?? 'N/A') . "\n";
                echo "  Created: " . ($sample['createdTime'] ?? 'N/A') . "\n";
                echo "  Closed: " . ($sample['closedTime'] ?? 'N/A') . "\n";
            }
        } else {
            echo "No response or data\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n3. Testing custom field search...\n";

try {
    $response = $apiClient->getTicketsByCustomField(
        'cf_closed_by',
        'Yaraa Khaled',
        now()->subDays(30)->format('Y-m-d'),
        now()->format('Y-m-d'),
        50
    );
    
    if ($response && isset($response['data'])) {
        $count = count($response['data']);
        echo "Tickets found by custom field: {$count}\n";
    } else {
        echo "No tickets found by custom field\n";
    }
} catch (\Exception $e) {
    echo "Error in custom field search: " . $e->getMessage() . "\n";
}

echo "\n=== End Test ===\n";

