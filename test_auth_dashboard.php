<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== User Authentication Debug ===\n";

$user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "User ID: {$user->id}\n";
echo "User email: {$user->email}\n";
echo "User name: {$user->name}\n";
echo "Zoho enabled: " . ($user->is_zoho_enabled ? "Yes" : "No") . "\n";
echo "Zoho agent name: " . ($user->zoho_agent_name ?? "Not set") . "\n";

// Test login
echo "\n=== Testing Login ===\n";

// Set password if not set
if (!$user->password) {
    $user->password = bcrypt('P@ssW0rd');
    $user->save();
    echo "✅ Password set to: P@ssW0rd\n";
}

// Test authentication
$credentials = [
    'email' => $user->email,
    'password' => 'P@ssW0rd'
];

if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
    echo "✅ Login successful\n";
    echo "Authenticated user: " . \Illuminate\Support\Facades\Auth::user()->name . "\n";
} else {
    echo "❌ Login failed\n";
}

echo "\n=== Testing Dashboard Access ===\n";

// Simulate authenticated request
\Illuminate\Support\Facades\Auth::login($user);

$apiClient = new \App\Services\ZohoApiClient();
$controller = new \App\Http\Controllers\ZohoStatsController(
    new \App\Services\ZohoStatsService($apiClient),
    new \App\Services\ZohoSyncService($apiClient)
);

try {
    $request = new \Illuminate\Http\Request();
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    $response = $controller->dashboard($request);
    
    if ($response instanceof \Illuminate\View\View) {
        echo "✅ Dashboard view created successfully\n";
        echo "View name: " . $response->getName() . "\n";
        
        $data = $response->getData();
        echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
        
        if (isset($data['recentTickets'])) {
            echo "Recent tickets count: " . $data['recentTickets']->count() . "\n";
        }
        
        if (isset($data['statsSummary'])) {
            echo "Stats summary keys: " . implode(', ', array_keys($data['statsSummary'])) . "\n";
        }
    } else {
        echo "❌ Dashboard returned: " . get_class($response) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Dashboard error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";

