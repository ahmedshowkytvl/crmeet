<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho API Debug Test ===\n";

// Test credentials
echo "1. Testing credentials...\n";
$clientId = config('zoho.client_id');
$clientSecret = config('zoho.client_secret');
$refreshToken = config('zoho.refresh_token');
$orgId = config('zoho.org_id');

echo "Client ID: " . ($clientId ? "✅ Set" : "❌ Missing") . "\n";
echo "Client Secret: " . ($clientSecret ? "✅ Set" : "❌ Missing") . "\n";
echo "Refresh Token: " . ($refreshToken ? "✅ Set" : "❌ Missing") . "\n";
echo "Org ID: " . ($orgId ? "✅ Set" : "❌ Missing") . "\n";

if (!$clientId || !$clientSecret || !$refreshToken || !$orgId) {
    echo "❌ Missing credentials\n";
    exit(1);
}

echo "\n2. Testing token refresh...\n";
try {
    $response = \Illuminate\Support\Facades\Http::post('https://accounts.zoho.com/oauth/v2/token', [
        'refresh_token' => $refreshToken,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'refresh_token'
    ]);

    echo "Token refresh status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "✅ Token refresh successful\n";
        echo "Access token: " . substr($data['access_token'], 0, 20) . "...\n";
        echo "Expires in: " . ($data['expires_in'] ?? 'N/A') . " seconds\n";
        
        $accessToken = $data['access_token'];
        
        echo "\n3. Testing API call...\n";
        
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
            $agentsData = $agentsResponse->json();
            echo "✅ Agents API successful\n";
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
            echo "❌ Agents API failed\n";
            echo "Response: " . $agentsResponse->body() . "\n";
        }
        
        echo "\n4. Testing tickets API...\n";
        
        // Test tickets endpoint
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
            $ticketsData = $ticketsResponse->json();
            echo "✅ Tickets API successful\n";
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
                    echo "---\n";
                }
            }
        } else {
            echo "❌ Tickets API failed\n";
            echo "Response: " . $ticketsResponse->body() . "\n";
        }
        
    } else {
        echo "❌ Token refresh failed\n";
        echo "Response: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== End Test ===\n";

