<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ZohoTicketCache;
use App\Models\ZohoDepartmentMapping;

echo "البحث عن التذاكر التي لا تزال تحتوي على Zoho IDs:\n";
echo "=================================================\n";

// البحث عن التذاكر التي تحتوي على department_id يبدأ بـ 766285
$ticketsWithZohoIds = ZohoTicketCache::where('department_id', 'LIKE', '766285%')->get();

echo "عدد التذاكر التي لا تزال تحتوي على Zoho IDs: " . $ticketsWithZohoIds->count() . "\n\n";

if ($ticketsWithZohoIds->count() > 0) {
    foreach ($ticketsWithZohoIds as $ticket) {
        $zohoDepartmentId = $ticket->department_id;
        $zohoDepartmentName = $ticket->raw_data['departmentId'] ?? 'Unknown';
        
        echo "Ticket: {$ticket->ticket_number}\n";
        echo "  - department_id الحالي: {$ticket->department_id}\n";
        echo "  - departmentId من raw_data: {$zohoDepartmentName}\n";
        
        // البحث عن الـ mapping
        $mapping = ZohoDepartmentMapping::where('zoho_department_id', $zohoDepartmentId)->first();
        
        if ($mapping) {
            echo "  - Mapping موجود: {$mapping->zoho_department_name} -> {$mapping->local_department_name}\n";
            
            // تحديث التذكرة
            $ticket->update(['department_id' => $mapping->local_department_id]);
            echo "  ✅ تم تحديث department_id إلى: {$mapping->local_department_id}\n";
        } else {
            echo "  ❌ لا يوجد mapping لهذا ID\n";
        }
        echo "  ---\n";
    }
} else {
    echo "✅ جميع التذاكر تحتوي على Local Department IDs صحيحة!\n";
}

echo "\nفحص نهائي:\n";
echo "===========\n";

// فحص التذاكر التي تحتوي على department_id أكبر من 1000 (مؤشر على أنها Zoho IDs)
$suspiciousTickets = ZohoTicketCache::where('department_id', '>', 1000)->get();

echo "التذاكر المشبوهة (department_id > 1000): " . $suspiciousTickets->count() . "\n";

if ($suspiciousTickets->count() > 0) {
    foreach ($suspiciousTickets as $ticket) {
        echo "Ticket: {$ticket->ticket_number}, department_id: {$ticket->department_id}\n";
    }
} else {
    echo "✅ لا توجد تذاكر مشبوهة!\n";
}

echo "\nإحصائيات نهائية:\n";
echo "================\n";
$totalTickets = ZohoTicketCache::count();
$ticketsWithNullDept = ZohoTicketCache::whereNull('department_id')->count();
$ticketsWithValidDept = ZohoTicketCache::whereNotNull('department_id')->where('department_id', '<=', 20)->count();

echo "إجمالي التذاكر: {$totalTickets}\n";
echo "تذاكر بدون department_id: {$ticketsWithNullDept}\n";
echo "تذاكر مع department_id صحيح: {$ticketsWithValidDept}\n";
echo "تذاكر مع department_id مشبوه: " . ($totalTickets - $ticketsWithNullDept - $ticketsWithValidDept) . "\n";
