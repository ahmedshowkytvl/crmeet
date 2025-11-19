<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho Caching System Test ===\n";

// Test the caching system
$syncService = new \App\Services\ZohoSyncService(new \App\Services\ZohoApiClient());

echo "1. Testing cache status...\n";
$cacheEnabled = config('zoho.cache.enabled');
$expiryMinutes = config('zoho.cache.expiry_minutes');
echo "Cache Enabled: " . ($cacheEnabled ? "✅ Yes" : "❌ No") . "\n";
echo "Cache Expiry: {$expiryMinutes} minutes\n";

echo "\n2. Testing getTicketsWithCache...\n";

// Test with Yaraa Khaled
$agentName = 'Yaraa Khaled';
$fromDate = now()->subDays(30)->format('Y-m-d');
$toDate = now()->format('Y-m-d');

echo "Agent: {$agentName}\n";
echo "From: {$fromDate}\n";
echo "To: {$toDate}\n";

try {
    // First call - should fetch from API
    echo "\n--- First call (from API) ---\n";
    $startTime = microtime(true);
    $tickets = $syncService->getTicketsWithCache($agentName, $fromDate, $toDate, false);
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "Tickets found: " . count($tickets) . "\n";
    echo "Duration: {$duration} ms\n";
    
    // Second call - should use cache
    echo "\n--- Second call (from cache) ---\n";
    $startTime = microtime(true);
    $tickets2 = $syncService->getTicketsWithCache($agentName, $fromDate, $toDate, false);
    $endTime = microtime(true);
    $duration2 = round(($endTime - $startTime) * 1000, 2);
    
    echo "Tickets found: " . count($tickets2) . "\n";
    echo "Duration: {$duration2} ms\n";
    
    // Show performance improvement
    if ($duration > 0 && $duration2 > 0) {
        $improvement = round(($duration - $duration2) / $duration * 100, 1);
        echo "Performance improvement: {$improvement}% faster\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing cache manager commands...\n";

// Test cache status
echo "Cache status command:\n";
echo "php artisan zoho:cache-manager status\n";

echo "\nCache refresh command:\n";
echo "php artisan zoho:cache-manager refresh --agent=\"{$agentName}\"\n";

echo "\nCache stats command:\n";
echo "php artisan zoho:cache-manager stats\n";

echo "\n4. Testing database cache...\n";

// Check cached tickets in database
$cachedTickets = \App\Models\ZohoTicketCache::where('closed_by_name', $agentName)->count();
echo "Cached tickets in database: {$cachedTickets}\n";

if ($cachedTickets > 0) {
    $sampleTicket = \App\Models\ZohoTicketCache::where('closed_by_name', $agentName)->first();
    echo "Sample cached ticket:\n";
    echo "  ID: {$sampleTicket->zoho_ticket_id}\n";
    echo "  Number: {$sampleTicket->ticket_number}\n";
    echo "  Subject: {$sampleTicket->subject}\n";
    echo "  Status: {$sampleTicket->status}\n";
    echo "  Closed By: {$sampleTicket->closed_by_name}\n";
    echo "  Created: {$sampleTicket->created_at_zoho}\n";
    echo "  Closed: {$sampleTicket->closed_at_zoho}\n";
}

echo "\n=== End Test ===\n";

