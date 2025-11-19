<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Yaraa Khaled Dashboard Debug ===\n";

// Check tickets in database
$tickets = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->get();
echo "Total tickets for Yaraa Khaled: " . $tickets->count() . "\n";

if ($tickets->count() > 0) {
    echo "\nSample tickets:\n";
    foreach ($tickets->take(5) as $ticket) {
        echo "ID: {$ticket->zoho_ticket_id} | Subject: {$ticket->subject} | Status: {$ticket->status} | User ID: {$ticket->user_id}\n";
    }
}

// Check user
$user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();
echo "\nUser found: " . ($user ? $user->name . " (ID: {$user->id})" : "No") . "\n";

if ($user) {
    echo "User Zoho enabled: " . ($user->is_zoho_enabled ? "Yes" : "No") . "\n";
    echo "User Zoho agent name: " . ($user->zoho_agent_name ?? "Not set") . "\n";
    
    // Check user tickets
    $userTickets = $user->zohoTickets()->get();
    echo "User tickets count: " . $userTickets->count() . "\n";
    
    if ($userTickets->count() > 0) {
        echo "\nUser tickets:\n";
        foreach ($userTickets->take(5) as $ticket) {
            echo "ID: {$ticket->zoho_ticket_id} | Subject: {$ticket->subject} | Status: {$ticket->status}\n";
        }
    }
    
    // Check if tickets are linked to user
    $unlinkedTickets = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')
                                                ->whereNull('user_id')
                                                ->count();
    echo "Unlinked tickets: " . $unlinkedTickets . "\n";
    
    if ($unlinkedTickets > 0) {
        echo "\nLinking tickets to user...\n";
        $updated = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')
                                             ->whereNull('user_id')
                                             ->update(['user_id' => $user->id]);
        echo "Linked {$updated} tickets to user\n";
    }
}

echo "\n=== Testing Dashboard Data ===\n";

if ($user) {
    // Test dashboard data
    $recentTickets = $user->zohoTickets()
        ->excludeAutoClose()
        ->closed()
        ->orderBy('closed_at_zoho', 'desc')
        ->limit(10)
        ->get();
    
    echo "Recent tickets for dashboard: " . $recentTickets->count() . "\n";
    
    if ($recentTickets->count() > 0) {
        echo "\nDashboard tickets:\n";
        foreach ($recentTickets as $ticket) {
            echo "{$ticket->ticket_number} | {$ticket->subject} | {$ticket->status} | {$ticket->closed_at_zoho}\n";
        }
    }
    
    // Test stats
    $statsSummary = [
        'today' => $user->zohoTickets()->closed()->whereDate('closed_at_zoho', today())->count(),
        'this_week' => $user->zohoTickets()->closed()->whereBetween('closed_at_zoho', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        'this_month' => $user->zohoTickets()->closed()->whereMonth('closed_at_zoho', now()->month)->count(),
    ];
    
    echo "\nStats summary:\n";
    echo "Today: " . $statsSummary['today'] . "\n";
    echo "This week: " . $statsSummary['this_week'] . "\n";
    echo "This month: " . $statsSummary['this_month'] . "\n";
}

echo "\n=== End Debug ===\n";

