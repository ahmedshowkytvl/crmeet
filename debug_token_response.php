<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho Token Debug ===\n";

$authCode = '1000.b4661996af0e5f0aafe9310abee0b345.f3396f9660c9f5e300c9df742defb709';
$clientId = '1000.CFDOHTVE8ZZDXJVRR3VHR7U9C3W1UT';
$clientSecret = '30624b06180b20ab5252fc8e6145ad175762a367a0';
$redirectUri = 'https://www.google.com';

echo "Testing token request...\n";

try {
    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
        'grant_type' => 'authorization_code',
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'code' => $authCode
    ]);
    
    echo "Response status: " . $response->status() . "\n";
    echo "Response body: " . $response->body() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "Response data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        
        if (isset($data['access_token'])) {
            echo "✅ Access token found: " . substr($data['access_token'], 0, 20) . "...\n";
            
            if (isset($data['refresh_token'])) {
                echo "✅ Refresh token found: " . substr($data['refresh_token'], 0, 20) . "...\n";
                
                // Update .env file
                $envFile = '.env';
                $envContent = file_get_contents($envFile);
                
                $newRefreshToken = $data['refresh_token'];
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
                
                // Test the new token
                echo "\nTesting new token...\n";
                $testResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => "Zoho-oauthtoken {$data['access_token']}",
                    'Content-Type' => 'application/json',
                ])->get('https://desk.zoho.com/api/v1/agents', [
                    'orgId' => '786481962',
                    'limit' => 5
                ]);
                
                echo "Test API status: " . $testResponse->status() . "\n";
                
                if ($testResponse->successful()) {
                    echo "✅ New token works! API connection successful\n";
                    
                    $agentsData = $testResponse->json();
                    if (isset($agentsData['data'])) {
                        echo "Found " . count($agentsData['data']) . " agents\n";
                    }
                } else {
                    echo "❌ New token test failed: " . $testResponse->body() . "\n";
                }
                
            } else {
                echo "❌ No refresh token in response\n";
            }
        } else {
            echo "❌ No access token in response\n";
        }
    } else {
        echo "❌ Request failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n";

