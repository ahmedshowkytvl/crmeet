<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Get New Zoho Refresh Token ===\n";

if ($argc < 2) {
    echo "❌ Usage: php get_new_refresh_token.php YOUR_AUTHORIZATION_CODE\n";
    echo "Example: php get_new_refresh_token.php 1000.abc123def456...\n";
    exit(1);
}

$authCode = $argv[1];
$clientId = config('zoho.client_id');
$clientSecret = config('zoho.client_secret');
$redirectUri = 'https://www.google.com';

echo "Authorization Code: " . substr($authCode, 0, 20) . "...\n";
echo "Client ID: {$clientId}\n";
echo "Client Secret: " . substr($clientSecret, 0, 10) . "...\n";

try {
    echo "\n🔄 Requesting new tokens...\n";
    
    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
        'grant_type' => 'authorization_code',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'code' => $authCode
    ]);
    
    echo "Response status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $tokenData = $response->json();
        
        echo "✅ Successfully obtained new tokens!\n";
        echo "Access Token: " . substr($tokenData['access_token'], 0, 20) . "...\n";
        echo "Refresh Token: " . substr($tokenData['refresh_token'], 0, 20) . "...\n";
        echo "Expires In: " . ($tokenData['expires_in'] ?? 'N/A') . " seconds\n";
        
        // Update .env file
        $envFile = '.env';
        $envContent = file_get_contents($envFile);
        
        // Replace the refresh token
        $newRefreshToken = $tokenData['refresh_token'];
        $envContent = preg_replace(
            '/ZOHO_REFRESH_TOKEN=.*/',
            "ZOHO_REFRESH_TOKEN={$newRefreshToken}",
            $envContent
        );
        
        if (file_put_contents($envFile, $envContent)) {
            echo "✅ Updated .env file with new refresh token\n";
        } else {
            echo "❌ Failed to update .env file\n";
        }
        
        echo "\n🧪 Testing new token...\n";
        
        // Test the new token
        $testResponse = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Zoho-oauthtoken {$tokenData['access_token']}",
            'Content-Type' => 'application/json',
        ])->get('https://desk.zoho.com/api/v1/agents', [
            'orgId' => config('zoho.org_id'),
            'limit' => 5
        ]);
        
        if ($testResponse->successful()) {
            echo "✅ New token works! API connection successful\n";
            
            $agentsData = $testResponse->json();
            if (isset($agentsData['data'])) {
                echo "Found " . count($agentsData['data']) . " agents\n";
            }
        } else {
            echo "❌ New token test failed: " . $testResponse->status() . "\n";
        }
        
    } else {
        echo "❌ Failed to get new tokens\n";
        echo "Response: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n";

