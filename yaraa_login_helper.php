<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Yaraa Khaled Login Helper ===\n";

$user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "User: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "ID: {$user->id}\n";

// Set password
$user->password = bcrypt('P@ssW0rd');
$user->save();
echo "✅ Password set to: P@ssW0rd\n";

echo "\n=== Login Instructions ===\n";
echo "1. Go to: http://127.0.0.1:8000/login\n";
echo "2. Email: {$user->email}\n";
echo "3. Password: P@ssW0rd\n";
echo "4. After login, go to: http://127.0.0.1:8000/zoho/my-stats\n";

echo "\n=== Alternative: Direct Login Link ===\n";
echo "You can also use this direct link (if you have a login route):\n";
echo "http://127.0.0.1:8000/login?email=" . urlencode($user->email) . "&password=" . urlencode('P@ssW0rd') . "\n";

echo "\n=== Dashboard Data Preview ===\n";

// Show what will be displayed
$recentTickets = $user->zohoTickets()
    ->excludeAutoClose()
    ->closed()
    ->orderBy('closed_at_zoho', 'desc')
    ->limit(10)
    ->get();

echo "Recent tickets that will be shown:\n";
foreach ($recentTickets as $ticket) {
    echo "  {$ticket->ticket_number} | {$ticket->subject} | {$ticket->status} | {$ticket->closed_at_zoho->format('Y-m-d H:i')}\n";
}

$statsSummary = [
    'today' => $user->zohoTickets()->closed()->whereDate('closed_at_zoho', today())->count(),
    'this_week' => $user->zohoTickets()->closed()->whereBetween('closed_at_zoho', [now()->startOfWeek(), now()->endOfWeek()])->count(),
    'this_month' => $user->zohoTickets()->closed()->whereMonth('closed_at_zoho', now()->month)->count(),
];

echo "\nStats that will be shown:\n";
echo "  Today: {$statsSummary['today']} tickets\n";
echo "  This week: {$statsSummary['this_week']} tickets\n";
echo "  This month: {$statsSummary['this_month']} tickets\n";

echo "\n=== End ===\n";

