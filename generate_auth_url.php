<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Zoho API Token Fix ===\n";

$clientId = config('zoho.client_id');
$scope = 'Desk.tickets.READ,Desk.contacts.READ,Desk.tickets.UPDATE,Desk.agents.READ,Desk.departments.READ';
$redirectUri = 'https://www.google.com';

echo "Client ID: {$clientId}\n";
echo "Scope: {$scope}\n";
echo "Redirect URI: {$redirectUri}\n";

// Generate authorization URL
$authUrl = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'scope' => $scope,
    'redirect_uri' => $redirectUri,
    'access_type' => 'offline'
]);

echo "\n🔗 Authorization URL:\n";
echo $authUrl . "\n";

echo "\n📋 Steps to get new tokens:\n";
echo "1. Copy the URL above\n";
echo "2. Open it in your browser\n";
echo "3. Login to your Zoho account\n";
echo "4. Grant permissions to the application\n";
echo "5. You'll be redirected to Google with a 'code' parameter\n";
echo "6. Copy the code value from the URL (looks like: 1000.abc123...)\n";
echo "7. Run: php get_new_refresh_token.php YOUR_CODE_HERE\n";

echo "\n=== End ===\n";

