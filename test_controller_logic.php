<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing ZohoStatsController ===\n";

// Simulate the controller logic
$user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "User: {$user->name} (ID: {$user->id})\n";

// Test the exact query from controller
$recentTickets = $user->zohoTickets()
    ->excludeAutoClose()
    ->closed()
    ->orderBy('closed_at_zoho', 'desc')
    ->limit(10)
    ->get();

echo "Recent tickets count: " . $recentTickets->count() . "\n";

if ($recentTickets->count() > 0) {
    echo "\nRecent tickets:\n";
    foreach ($recentTickets as $ticket) {
        echo "{$ticket->ticket_number} | {$ticket->subject} | {$ticket->status} | {$ticket->closed_at_zoho}\n";
    }
} else {
    echo "❌ No recent tickets found\n";
    
    // Debug the query step by step
    echo "\nDebugging query step by step:\n";
    
    $step1 = $user->zohoTickets();
    echo "Step 1 - User tickets: " . $step1->count() . "\n";
    
    $step2 = $user->zohoTickets()->excludeAutoClose();
    echo "Step 2 - After excludeAutoClose: " . $step2->count() . "\n";
    
    $step3 = $user->zohoTickets()->excludeAutoClose()->closed();
    echo "Step 3 - After closed: " . $step3->count() . "\n";
    
    // Check what's being excluded
    $autoCloseTickets = $user->zohoTickets()->where('closed_by_name', 'Auto Close')->count();
    echo "Auto Close tickets: " . $autoCloseTickets . "\n";
    
    $nullClosedBy = $user->zohoTickets()->whereNull('closed_by_name')->count();
    echo "Null closed_by_name tickets: " . $nullClosedBy . "\n";
    
    $nonClosedTickets = $user->zohoTickets()->where('status', '!=', 'Closed')->count();
    echo "Non-closed tickets: " . $nonClosedTickets . "\n";
}

// Test stats summary
$statsSummary = [
    'today' => $user->zohoTickets()->closed()->whereDate('closed_at_zoho', today())->count(),
    'this_week' => $user->zohoTickets()->closed()->whereBetween('closed_at_zoho', [now()->startOfWeek(), now()->endOfWeek()])->count(),
    'this_month' => $user->zohoTickets()->closed()->whereMonth('closed_at_zoho', now()->month)->count(),
];

echo "\nStats summary:\n";
echo "Today: " . $statsSummary['today'] . "\n";
echo "This week: " . $statsSummary['this_week'] . "\n";
echo "This month: " . $statsSummary['this_month'] . "\n";

// Test achievements
$achievements = $user->achievements()->latest()->limit(5)->get();
echo "\nAchievements count: " . $achievements->count() . "\n";

echo "\n=== End Test ===\n";

