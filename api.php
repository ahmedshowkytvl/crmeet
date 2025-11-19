<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ZohoStatsController;
use App\Http\Controllers\Api\TaskProgressController;

/*
|--------------------------------------------------------------------------
| API Routes للإشعارات
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    // الحصول على الإشعارات
    Route::get('/', [NotificationController::class, 'index']);
    
    // الحصول على عدد غير المقروءة
    Route::get('/count', [NotificationController::class, 'count']);
    
    // إنشاء إشعار جديد
    Route::post('/', [NotificationController::class, 'store']);
    
    // تحديد كمقروء
    Route::post('/mark-read', [NotificationController::class, 'markAsRead']);
    
    // حذف إشعار
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    
    // تحديث التفضيلات
    Route::post('/preferences', [NotificationController::class, 'updatePreferences']);
    
    // الحصول على التفضيلات
    Route::get('/preferences', [NotificationController::class, 'getPreferences']);
});

/*
|--------------------------------------------------------------------------
| Zoho Integration API Routes
|--------------------------------------------------------------------------
*/

// Desktop app internal API (no auth required for localhost)
Route::middleware(['throttle:60,1'])->prefix('zoho')->group(function () {
    Route::get('/desktop/ticket/{ticketId}/threads', [ZohoStatsController::class, 'apiTicketThreads']);
});

// Web API (requires authentication)
Route::middleware(['auth', 'web', 'throttle:60,1'])->prefix('zoho')->group(function () {
    // Get user stats
    Route::get('/user/{userId}/stats', [ZohoStatsController::class, 'apiStats']);
    
    // Get user tickets
    Route::get('/user/{userId}/tickets', [ZohoStatsController::class, 'apiTickets']);
    
    // Get ticket details
    Route::get('/ticket/{ticketId}', [ZohoStatsController::class, 'apiTicketDetails']);
    
    // Get ticket details from cache
    Route::get('/ticket-cache/{ticketId}', [ZohoStatsController::class, 'apiTicketDetailsFromCache']);
    
    // Get full ticket details from Zoho API (with customFields and threads)
    Route::get('/ticket-full/{ticketId}', [ZohoStatsController::class, 'apiTicketFullDetails']);
    
    // Get ticket threads
    Route::get('/ticket/{ticketId}/threads', [ZohoStatsController::class, 'apiTicketThreads']);
    
    // Get thread content (alternative endpoints)
    Route::get('/threads/{ticketId}/{threadId}/json', [ZohoStatsController::class, 'apiThreadContentAsJson']);
    Route::get('/threads/{ticketId}/{threadId}/view', [ZohoStatsController::class, 'apiThreadContentView']);
    Route::get('/threads/{ticketId}/{threadId}/max-content', [ZohoStatsController::class, 'apiThreadMaxContent']);
    Route::get('/threads/{ticketId}/{threadId}/python-style', [ZohoStatsController::class, 'apiThreadMaxContent']);
    
    // CRM Email APIs
    Route::get('/crm/{module}/{recordId}/emails', [ZohoStatsController::class, 'apiCrmRecordEmails']);
    Route::get('/crm/{module}/{recordId}/emails/{messageId}', [ZohoStatsController::class, 'apiCrmEmailDetails']);
    
            // Load more tickets
            Route::get('/tickets/load-more', [ZohoStatsController::class, 'apiLoadMoreTickets']);
            
            // Load more in progress tickets
            Route::get('/tickets/in-progress/load-more', [ZohoStatsController::class, 'apiLoadMoreInProgressTickets']);
    
    // Search for specific ticket
    Route::get('/tickets/search', [ZohoStatsController::class, 'apiSearchTicket']);
    
    // Get leaderboard
    Route::get('/leaderboard', [ZohoStatsController::class, 'apiLeaderboard']);
    
    // Get department tickets
    Route::get('/department/{departmentId}/tickets', [ZohoStatsController::class, 'apiDepartmentTickets']);
    
    // Update ticket status
    Route::put('/ticket/{ticketId}/status', [ZohoStatsController::class, 'apiUpdateTicketStatus']);
    
    // Trigger sync (admin only)
    Route::post('/sync/trigger', [ZohoStatsController::class, 'apiTriggerSync'])
         ->middleware('permission:manage-zoho');
});

/*
|--------------------------------------------------------------------------
| Task Progress API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('task-progress')->group(function () {
    // حساب التقدم لمستخدم معين
    Route::get('/user', [TaskProgressController::class, 'getUserProgress']);
    
    // حساب التقدم لجميع المستخدمين
    Route::get('/all-users', [TaskProgressController::class, 'getAllUsersProgress']);
    
    // حساب التقدم لفترة زمنية
    Route::get('/period', [TaskProgressController::class, 'getProgressForPeriod']);
    
    // إحصائيات القوالب
    Route::get('/template-stats', [TaskProgressController::class, 'getTemplateStats']);
});

