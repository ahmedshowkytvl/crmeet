<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Yaraa Khaled Tickets Analysis ===\n";

// Check current tickets for Yaraa Khaled
$tickets = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->get();

echo "Current tickets for Yaraa Khaled: " . $tickets->count() . "\n\n";

if ($tickets->count() > 0) {
    echo "Existing tickets:\n";
    foreach ($tickets as $ticket) {
        echo "ID: {$ticket->zoho_ticket_id} | Subject: {$ticket->subject} | Status: {$ticket->status}\n";
    }
} else {
    echo "No tickets found for Yaraa Khaled\n";
}

echo "\n=== Creating Sample Data ===\n";

// Create 100 sample tickets for Yaraa Khaled
$user = \App\Models\User::where('zoho_agent_name', 'Yaraa Khaled')->first();

if (!$user) {
    echo "❌ User not found for Yaraa Khaled\n";
    exit(1);
}

echo "Found user: {$user->name} (ID: {$user->id})\n";

$sampleTickets = [];
$baseDate = now()->subDays(30);

for ($i = 1; $i <= 100; $i++) {
    $ticketNumber = 'TKT-YARA-' . str_pad($i, 3, '0', STR_PAD_LEFT);
    $createdDate = $baseDate->copy()->addDays(rand(0, 30))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
    $closedDate = $createdDate->copy()->addHours(rand(1, 48))->addMinutes(rand(0, 59));
    $responseTime = $createdDate->diffInMinutes($closedDate);
    
    $subjects = [
        'Customer Support Request',
        'Technical Issue Resolution',
        'Account Setup Assistance',
        'Billing Inquiry',
        'Product Information Request',
        'Service Upgrade Request',
        'Bug Report',
        'Feature Request',
        'Password Reset',
        'Account Verification',
        'Payment Processing Issue',
        'Order Status Inquiry',
        'Refund Request',
        'Service Downtime Report',
        'Performance Issue',
        'Integration Problem',
        'Data Migration Request',
        'User Training Request',
        'System Configuration',
        'Security Concern'
    ];
    
    $ticketData = [
        'zoho_ticket_id' => 'ZOHO_YARA_' . str_pad($i, 3, '0', STR_PAD_LEFT),
        'ticket_number' => $ticketNumber,
        'user_id' => $user->id,
        'closed_by_name' => 'Yaraa Khaled',
        'subject' => $subjects[array_rand($subjects)] . ' #' . $i,
        'status' => 'Closed',
        'department_id' => 'SUPPORT',
        'created_at_zoho' => $createdDate,
        'closed_at_zoho' => $closedDate,
        'response_time_minutes' => $responseTime,
        'thread_count' => rand(1, 5),
        'raw_data' => [
            'sample' => true,
            'ticket_id' => $i,
            'priority' => ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])],
            'category' => ['General', 'Technical', 'Billing'][array_rand(['General', 'Technical', 'Billing'])]
        ]
    ];
    
    \App\Models\ZohoTicketCache::updateOrCreate(
        ['zoho_ticket_id' => $ticketData['zoho_ticket_id']],
        $ticketData
    );
    
    if ($i % 10 == 0) {
        echo "Created {$i} tickets...\n";
    }
}

echo "✅ Created 100 sample tickets for Yaraa Khaled\n";

echo "\n=== Final Statistics ===\n";
$finalCount = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')->count();
echo "Total tickets for Yaraa Khaled: {$finalCount}\n";

$closedCount = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')
                                         ->where('status', 'Closed')
                                         ->count();
echo "Closed tickets: {$closedCount}\n";

$avgResponseTime = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')
                                             ->where('status', 'Closed')
                                             ->whereNotNull('response_time_minutes')
                                             ->avg('response_time_minutes');
echo "Average response time: " . round($avgResponseTime, 1) . " minutes\n";

echo "\n=== Recent 10 Tickets ===\n";
$recentTickets = \App\Models\ZohoTicketCache::where('closed_by_name', 'Yaraa Khaled')
                                           ->orderBy('closed_at_zoho', 'desc')
                                           ->limit(10)
                                           ->get();

foreach ($recentTickets as $ticket) {
    echo "{$ticket->ticket_number} | {$ticket->subject} | {$ticket->status} | {$ticket->closed_at_zoho->format('Y-m-d H:i')} | " . round($ticket->response_time_minutes / 60, 1) . " hours\n";
}

echo "\n=== End ===\n";

