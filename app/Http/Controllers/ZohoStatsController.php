<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ZohoTicketCache;
use App\Services\ZohoStatsService;
use App\Services\ZohoSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZohoStatsController extends Controller
{
    protected $statsService;
    protected $syncService;

    public function __construct(ZohoStatsService $statsService, ZohoSyncService $syncService)
    {
        $this->statsService = $statsService;
        $this->syncService = $syncService;
    }

    /**
     * Show user's personal Zoho dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Check if user has Zoho access
        if (!$user->hasZohoAccess()) {
            return view('zoho.not-enabled');
        }

        // Get stats summary (daily, weekly, monthly)
        $statsSummary = $this->statsService->getUserStatsSummary($user->id);

        // Get performance trend (last 6 months)
        $performanceTrend = $this->statsService->getUserPerformanceTrend($user->id, 'monthly', 6);

        // Get latest achievements with translations
        $achievements = $user->achievements()->latest()->limit(5)->get()->map(function($achievement) {
            $achievement->title = __('zoho.achievement_types.' . $achievement->achievement_type);
            $achievement->level_name = __('zoho.achievement_levels.' . $achievement->achievement_level);
            return $achievement;
        });

        // Get recent tickets from cache first
        $recentTickets = $user->zohoTickets()
            ->excludeAutoClose()
            ->closed()
            ->orderBy('closed_at_zoho', 'desc')
            ->limit(10)
            ->get();

        // If no cached tickets, try to sync
        if ($recentTickets->isEmpty()) {
            Log::info('No cached tickets found, attempting sync', ['user_id' => $user->id]);
            
            try {
                $syncResult = $this->syncService->syncTicketsForUser($user->id);
                if ($syncResult['success']) {
                    // Refresh tickets after sync
                    $recentTickets = $user->zohoTickets()
                        ->excludeAutoClose()
                        ->closed()
                        ->orderBy('closed_at_zoho', 'desc')
                        ->limit(10)
                        ->get();
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync tickets for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('zoho.dashboard', compact(
            'user',
            'statsSummary',
            'performanceTrend',
            'achievements',
            'recentTickets'
        ));
    }

    /**
     * Show reports page (for managers)
     */
    public function reports(Request $request)
    {
        $periodType = $request->get('period', 'monthly');
        $periodDate = $request->get('date') ? \Carbon\Carbon::parse($request->get('date')) : now();

        // Get all enabled users with their stats
        $users = User::zohoEnabled()
            ->with(['zohoStats' => function($q) use ($periodType, $periodDate) {
                $q->where('period_type', $periodType)
                  ->where('period_date', $periodDate->startOf($periodType));
            }])
            ->get();

        // Get top performers
        $topPerformers = $this->statsService->getTopPerformers($periodType, $periodDate->startOf($periodType), 10);

        return view('zoho.reports', compact('users', 'topPerformers', 'periodType', 'periodDate'));
    }

    /**
     * Show leaderboard
     */
    public function leaderboard(Request $request)
    {
        $periodType = $request->get('period', 'monthly');
        $periodDate = $request->get('date') ? \Carbon\Carbon::parse($request->get('date')) : now();

        $topPerformers = $this->statsService->getTopPerformers($periodType, $periodDate->startOf($periodType), 20);

        return view('zoho.leaderboard', compact('topPerformers', 'periodType', 'periodDate'));
    }

    /**
     * API: Get user stats
     */
    public function apiStats(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user || !$user->hasZohoAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or Zoho not enabled'
            ], 404);
        }

        $statsSummary = $this->statsService->getUserStatsSummary($userId);

        return response()->json([
            'success' => true,
            'data' => $statsSummary
        ]);
    }

    /**
     * API: Get user tickets
     */
    public function apiTickets(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user || !$user->hasZohoAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or Zoho not enabled'
            ], 404);
        }

        $tickets = $user->zohoTickets()
            ->excludeAutoClose()
            ->closed()
            ->orderBy('closed_at_zoho', 'desc')
            ->limit($request->get('limit', 50))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * API: Get leaderboard
     */
    public function apiLeaderboard(Request $request)
    {
        $periodType = $request->get('period', 'monthly');
        $limit = $request->get('limit', 10);

        $topPerformers = $this->statsService->getTopPerformers($periodType, now()->startOf($periodType), $limit);

        return response()->json([
            'success' => true,
            'data' => $topPerformers
        ]);
    }

    /**
     * API: Trigger manual sync
     */
    public function apiTriggerSync(Request $request)
    {
        $userId = $request->get('user_id');

        if ($userId) {
            $result = $this->syncService->syncTicketsForUser($userId);
        } else {
            $result = $this->syncService->syncTickets();
        }

        return response()->json($result);
    }

    /**
     * Show all tickets page
     */
    public function allTickets(Request $request)
    {
        $user = $request->user();
        
        // Get limit from request (default 3000)
        $limit = $request->get('limit', 3000);
        
        // Force refresh from API if requested
        $forceRefresh = $request->get('refresh', false);
        
        // If force refresh is enabled, fetch from Zoho API
        if ($forceRefresh) {
            try {
                $apiClient = new \App\Services\ZohoApiClient();
                $fromDate = now()->subDays(30)->format('Y-m-d\TH:i:s\Z'); // Last 30 days
                $response = $apiClient->getTickets([
                    'limit' => 3000,
                    'from' => $fromDate,
                    'sortBy' => '-createdTime'
                ]);
                
                if ($response && isset($response['data'])) {
                    // Sync the tickets to cache
                    foreach ($response['data'] as $ticketData) {
                        ZohoTicketCache::updateOrCreate(
                            ['zoho_ticket_id' => $ticketData['id'] ?? null],
                            [
                                'ticket_number' => $ticketData['ticketNumber'] ?? $ticketData['id'] ?? null,
                                'subject' => $ticketData['subject'] ?? null,
                                'status' => $ticketData['status'] ?? null,
                                'created_at_zoho' => isset($ticketData['createdTime']) ? \Carbon\Carbon::parse($ticketData['createdTime']) : null,
                                'closed_at_zoho' => isset($ticketData['closedTime']) ? \Carbon\Carbon::parse($ticketData['closedTime']) : null,
                                'closed_by_name' => $ticketData['cf']['cf_closed_by'] ?? null,
                                'raw_data' => $ticketData,
                                'thread_count' => isset($ticketData['threadCount']) ? $ticketData['threadCount'] : 0
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error refreshing tickets from Zoho API', ['error' => $e->getMessage()]);
            }
        }

        // Get all tickets from cache (up to limit)
        $tickets = ZohoTicketCache::with(['user', 'department'])
            ->orderBy('created_at_zoho', 'desc')
            ->limit($limit)
            ->get();

        // Get total count for statistics
        $totalCount = ZohoTicketCache::count();

        // Get statistics from all tickets (not just displayed ones)
        $allTickets = ZohoTicketCache::all();
        $stats = [
            'total_tickets' => $allTickets->count(),
            'closed_tickets' => $allTickets->where('status', 'Closed')->count(),
            'open_tickets' => $allTickets->where('status', 'Open')->count(),
            'pending_tickets' => $allTickets->where('status', 'Pending')->count(),
            'in_progress_tickets' => $allTickets->where('status', 'In Progress')->count(),
        ];

        // Get unique agents (including those with null closed_by_name)
        $agents = $allTickets->pluck('closed_by_name')
            ->filter() // Remove null values
            ->unique()
            ->values();

        // Get unique department names
        $departments = $allTickets->whereNotNull('department_id')
            ->load('department')
            ->pluck('department.name')
            ->filter() // Remove null values
            ->unique()
            ->values();

        // Check if there are more tickets to load
        $hasMore = $totalCount > $limit;

        return view('zoho.all-tickets', compact('tickets', 'stats', 'agents', 'departments', 'hasMore', 'totalCount'));
    }

    /**
     * Show in progress tickets page
     */
    public function inProgressTickets(Request $request)
    {
        $user = $request->user();
        
        // Get limit from request (default 500)
        $limit = $request->get('limit', 500);

        // Get in progress tickets from cache (up to limit)
        $tickets = ZohoTicketCache::with(['user', 'department'])
            ->where('status', 'In Progress')
            ->orderBy('created_at_zoho', 'desc')
            ->limit($limit)
            ->get();

        $totalCount = ZohoTicketCache::where('status', 'In Progress')->count();

        // Get statistics from all tickets
        $allTickets = ZohoTicketCache::all();
        $stats = [
            'total_tickets' => $allTickets->count(),
            'closed_tickets' => $allTickets->where('status', 'Closed')->count(),
            'open_tickets' => $allTickets->where('status', 'Open')->count(),
            'pending_tickets' => $allTickets->where('status', 'Pending')->count(),
            'in_progress_tickets' => $allTickets->where('status', 'In Progress')->count(),
        ];

        // Get unique agents for in progress tickets
        $agents = $allTickets->where('status', 'In Progress')
            ->pluck('closed_by_name')
            ->filter() // Remove null values
            ->unique()
            ->values();

        // Get unique department names for in progress tickets
        $departments = $allTickets->where('status', 'In Progress')
            ->whereNotNull('department_id')
            ->load('department')
            ->pluck('department.name')
            ->filter() // Remove null values
            ->unique()
            ->values();

        // Check if there are more tickets to load
        $hasMore = $totalCount > $limit;

        return view('zoho.in-progress-tickets', compact('tickets', 'stats', 'agents', 'departments', 'hasMore', 'totalCount'));
    }

    /**
     * API: Get ticket details
     */
    public function apiTicketDetails($ticketId)
    {
        $ticket = ZohoTicketCache::with('department')->where('zoho_ticket_id', $ticketId)->first();
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Add department name to the response
        $ticketData = $ticket->toArray();
        $ticketData['department_name'] = $ticket->department ? $ticket->department->name : null;
        
        return response()->json($ticketData);
    }

    /**
     * API: Get ticket details from cache for modal display
     */
    public function apiTicketDetailsFromCache($ticketId)
    {
        $ticket = ZohoTicketCache::with(['department', 'user'])->where('zoho_ticket_id', $ticketId)->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found in cache'
            ], 404);
        }
        
        // Format the response similar to the modal structure
        $ticketData = [
            'success' => true,
            'data' => [
                'id' => $ticket->zoho_ticket_id,
                'ticketNumber' => $ticket->ticket_number,
                'subject' => $ticket->subject ?: '(No Subject)',
                'status' => $ticket->status,
                'statusType' => $ticket->status,
                'createdTime' => $ticket->created_at_zoho ? $ticket->created_at_zoho->toISOString() : null,
                'closedTime' => $ticket->closed_at_zoho ? $ticket->closed_at_zoho->toISOString() : null,
                'agent' => $ticket->user ? $ticket->user->name : ($ticket->closed_by_name ?: 'غير محدد'),
                'department' => $ticket->department ? $ticket->department->name : 'غير محدد',
                'responseTime' => $ticket->response_time_minutes ? round($ticket->response_time_minutes / 60, 1) . ' ساعة' : 'غير محدد',
                'threadCount' => $ticket->thread_count,
                'rawData' => $ticket->raw_data ?: []
            ]
        ];
        
        return response()->json($ticketData);
    }

    /**
     * API: Get full ticket details directly from Zoho API (including customFields and threads)
     */
    public function apiTicketFullDetails($ticketId)
    {
        try {
            $apiClient = new \App\Services\ZohoApiClient();
            
            // Get ticket details from Zoho API
            $ticketResponse = $apiClient->getTicket($ticketId);
            
            if (!$ticketResponse) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ticket not found in Zoho'
                ], 404);
            }
            
            // Handle response structure (might be wrapped in 'data' key)
            $ticketData = $ticketResponse;
            if (isset($ticketResponse['data'])) {
                $ticketData = $ticketResponse['data'];
            } elseif (isset($ticketResponse['ticket'])) {
                $ticketData = $ticketResponse['ticket'];
            }
            
            // Get threads for this ticket
            $threadsResponse = $apiClient->getTicketThreads($ticketId);
            
            // Handle threads response structure
            $threads = [];
            if (isset($threadsResponse['data'])) {
                $threads = $threadsResponse['data'];
            } elseif (isset($threadsResponse['threads'])) {
                $threads = $threadsResponse['threads'];
            }
            
            // Combine ticket data with threads
            $fullTicketData = [
                'ticket' => $ticketData,
                'threads' => $threads
            ];
            
            return response()->json([
                'success' => true,
                'data' => $fullTicketData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching full ticket details from Zoho API', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب تفاصيل التذكرة من Zoho: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get ticket threads
     */
    public function apiTicketThreads($ticketId)
    {
        try {
            $apiClient = new \App\Services\ZohoApiClient();
            
            // Get threads using basic method to avoid API errors
            $threads = $apiClient->getTicketThreads($ticketId);
            
            if (!$threads || !isset($threads['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا توجد محادثات لهذه التذكرة'
                ], 404);
            }
            
            // Process threads with available content
            $fullThreads = [];
            foreach ($threads['data'] as $thread) {
                try {
                    // Use available content from the thread
                    $thread['fullContent'] = $thread['body'] ?? $thread['content'] ?? $thread['summary'] ?? 'لا يوجد محتوى';
                    $thread['fullSubject'] = $thread['subject'] ?? '';
                    
                    // Check if content is actually HTML
                    $thread['isHtml'] = $this->isHtmlContent($thread['fullContent']);
                    $thread['contentType'] = $thread['isHtml'] ? 'html' : 'text';
                    $thread['direction'] = $thread['direction'] ?? 'in';
                    $thread['channel'] = $thread['channel'] ?? 'EMAIL';
                    
                } catch (\Exception $e) {
                    // If processing fails, use available data
                    $thread['fullContent'] = $thread['content'] ?? $thread['summary'] ?? 'لا يوجد محتوى';
                    $thread['fullSubject'] = $thread['subject'] ?? '';
                    $thread['isHtml'] = false;
                    $thread['contentType'] = 'text';
                }
                
                $fullThreads[] = $thread;
            }
            
            return response()->json([
                'success' => true,
                'threads' => $fullThreads,
                'count' => count($fullThreads)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching ticket threads', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب المحادثات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Load more tickets
     */
    public function apiLoadMoreTickets(Request $request)
    {
        $limit = $request->get('limit', 3000);
        $offset = $request->get('offset', 0);
        
        $tickets = ZohoTicketCache::with(['user', 'department'])
            ->orderBy('created_at_zoho', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
        // Add department names to each ticket
        $tickets->each(function($ticket) {
            $ticket->department_name = $ticket->department ? $ticket->department->name : null;
        });
        
        $totalCount = ZohoTicketCache::count();
        $hasMore = ($offset + $limit) < $totalCount;
        
        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'loaded' => $offset + $tickets->count()
        ]);
    }

    /**
     * API: Load more in progress tickets
     */
    public function apiLoadMoreInProgressTickets(Request $request)
    {
        $limit = $request->get('limit', 500);
        $offset = $request->get('offset', 0);
        
        $tickets = ZohoTicketCache::with(['user', 'department'])
            ->where('status', 'In Progress')
            ->orderBy('created_at_zoho', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        
        // Add department names to each ticket
        $tickets->each(function($ticket) {
            $ticket->department_name = $ticket->department ? $ticket->department->name : null;
        });
        
        $totalCount = ZohoTicketCache::where('status', 'In Progress')->count();
        $hasMore = ($offset + $limit) < $totalCount;
        
        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'loaded' => $offset + $tickets->count()
        ]);
    }

    /**
     * API: Search for specific ticket by number in Zoho
     */
    public function apiSearchTicket(Request $request)
    {
        $ticketNumber = $request->get('ticket_number');
        
        if (empty($ticketNumber)) {
            return response()->json([
                'success' => false,
                'error' => 'رقم التذكرة مطلوب'
            ], 400);
        }
        
        try {
            // Search in Zoho API first
            $apiClient = new \App\Services\ZohoApiClient();
            $response = $apiClient->searchTicketByNumber($ticketNumber);
            
            if (!$response || !isset($response['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'التذكرة غير موجودة في Zoho'
                ], 404);
            }
            
            $ticketData = $response['data'];
            
            // Process and cache the ticket if found
            $syncService = new \App\Services\ZohoSyncService($apiClient);
            $reflection = new \ReflectionClass($syncService);
            $processTicketMethod = $reflection->getMethod('processTicket');
            $processTicketMethod->setAccessible(true);
            $processTicketMethod->invoke($syncService, $ticketData);
            
            // Get the processed ticket from cache
            $ticket = ZohoTicketCache::with('user')
                ->where('ticket_number', $ticketNumber)
                ->first();
            
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'error' => 'فشل في معالجة التذكرة'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'ticket' => $ticket,
                'source' => 'zoho_api'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error searching ticket in Zoho', [
                'ticket_number' => $ticketNumber,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'خطأ في الاتصال بـ Zoho: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get CRM record emails
     */
    public function apiCrmRecordEmails($module, $recordId, Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $apiClient = new \App\Services\ZohoApiClient();
            $emails = $apiClient->getCrmRecordEmails($module, $recordId, $userId);
            
            if (!$emails || !isset($emails['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا توجد إيميلات لهذا السجل'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'emails' => $emails['data'],
                'count' => count($emails['data']),
                'module' => $module,
                'record_id' => $recordId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب الإيميلات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get CRM email details with full content
     */
    public function apiCrmEmailDetails($module, $recordId, $messageId, Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $apiClient = new \App\Services\ZohoApiClient();
            $email = $apiClient->getCrmEmailDetails($module, $recordId, $messageId, $userId);
            
            if (!$email || !isset($email['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'الإيميل غير موجود'
                ], 404);
            }
            
            $emailData = $email['data'];
            
            // Format the email data for display
            $formattedEmail = [
                'success' => true,
                'data' => [
                    'id' => $emailData['id'] ?? $messageId,
                    'subject' => $emailData['subject'] ?? 'لا يوجد عنوان',
                    'from' => $emailData['from'] ?? 'غير محدد',
                    'to' => $emailData['to'] ?? 'غير محدد',
                    'cc' => $emailData['cc'] ?? null,
                    'bcc' => $emailData['bcc'] ?? null,
                    'content' => $emailData['content'] ?? 'لا يوجد محتوى',
                    'isHtml' => $emailData['isHtml'] ?? false,
                    'sentTime' => $emailData['sentTime'] ?? null,
                    'receivedTime' => $emailData['receivedTime'] ?? null,
                    'direction' => $emailData['direction'] ?? 'in',
                    'status' => $emailData['status'] ?? 'sent',
                    'attachments' => $emailData['attachments'] ?? [],
                    'module' => $module,
                    'record_id' => $recordId
                ]
            ];
            
            return response()->json($formattedEmail);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب تفاصيل الإيميل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get thread content as JSON
     */
    public function apiThreadContentAsJson($ticketId, $threadId)
    {
        try {
            $apiClient = new \App\Services\ZohoApiClient();
            $threadContent = $apiClient->getThreadContentAsJson($ticketId, $threadId);
            
            if (!$threadContent) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا يمكن جلب محتوى المحادثة'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $threadContent['data'] ?? $threadContent,
                'ticket_id' => $ticketId,
                'thread_id' => $threadId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب محتوى المحادثة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get thread content as HTML view
     */
    public function apiThreadContentView($ticketId, $threadId)
    {
        try {
            $apiClient = new \App\Services\ZohoApiClient();
            $threadView = $apiClient->getThreadContentView($ticketId, $threadId);
            
            if (!$threadView) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا يمكن جلب عرض المحادثة'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $threadView['data'] ?? $threadView,
                'ticket_id' => $ticketId,
                'thread_id' => $threadId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب عرض المحادثة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if content is HTML
     */
    private function isHtmlContent($content)
    {
        if (empty($content)) {
            return false;
        }
        
        // Check for common HTML tags
        $htmlTags = ['<html', '<body', '<div', '<p', '<br', '<img', '<a', '<table', '<tr', '<td', '<th', '<ul', '<li', '<strong', '<em', '<b', '<i'];
        
        foreach ($htmlTags as $tag) {
            if (stripos($content, $tag) !== false) {
                return true;
            }
        }
        
        // Check for HTML entities
        if (strpos($content, '&lt;') !== false || strpos($content, '&gt;') !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * API: Get thread with maximum content using Python script approach
     */
    public function apiThreadMaxContent($ticketId, $threadId)
    {
        try {
            $apiClient = new \App\Services\ZohoApiClient();
            
            // Just return the thread data from the basic threads endpoint
            // since individual thread endpoints are not working
            $threads = $apiClient->getTicketThreads($ticketId);
            
            if (!$threads || !isset($threads['data'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'لا يمكن جلب تفاصيل المحادثة'
                ], 404);
            }
            
            // Find the specific thread
            $targetThread = null;
            foreach ($threads['data'] as $thread) {
                if ($thread['id'] == $threadId) {
                    $targetThread = $thread;
                    break;
                }
            }
            
            if (!$targetThread) {
                return response()->json([
                    'success' => false,
                    'error' => 'المحادثة غير موجودة'
                ], 404);
            }
            
            // Process the thread data
            $processedData = [
                'id' => $targetThread['id'] ?? $threadId,
                'fullContent' => $targetThread['summary'] ?? $targetThread['content'] ?? 'لا يوجد محتوى',
                'isHtml' => $this->isHtmlContent($targetThread['summary'] ?? ''),
                'contentType' => 'text/plain',
                'subject' => $targetThread['subject'] ?? '',
                'direction' => $targetThread['direction'] ?? 'in',
                'channel' => $targetThread['channel'] ?? 'EMAIL',
                'createdTime' => $targetThread['createdTime'] ?? null,
                'status' => $targetThread['status'] ?? '',
                'author' => $targetThread['author'] ?? null,
                'raw_data' => $targetThread
            ];
            
            return response()->json([
                'success' => true,
                'data' => $processedData,
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'method' => 'basic_threads'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب تفاصيل المحادثة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get tickets for a specific department
     */
    public function apiDepartmentTickets(Request $request, $departmentId)
    {
        try {
            // Check if cache_only is requested - if true, skip all Zoho API calls
            $cacheOnly = $request->get('cache_only', false) || $request->get('cache_only') === 'true';
            
            // Increase timeout for refresh operations (only if not cache_only)
            $refresh = $request->get('refresh', false) && !$cacheOnly;
            if ($refresh) {
                set_time_limit(180); // 3 minutes for refresh
            }
            
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            
            // If refresh is requested AND cache_only is false, fetch latest data from Zoho ONLY for tickets on this page
            if ($refresh && !$cacheOnly) {
                Log::info('Refreshing tickets from Zoho for department: ' . $departmentId, [
                    'page' => $page,
                    'per_page' => $perPage
                ]);
                
                try {
                    // Get ticket numbers for current page only to avoid timeout
                    $offset = ($page - 1) * $perPage;
                    $cachedTickets = ZohoTicketCache::where('department_id', $departmentId)
                        ->orderBy('created_at_zoho', 'desc')
                        ->offset($offset)
                        ->limit($perPage)
                        ->pluck('ticket_number')
                        ->toArray();
                    
                    if (!empty($cachedTickets)) {
                        // Fetch latest data from Zoho for tickets on current page only
                        $apiClient = new \App\Services\ZohoApiClient();
                        $syncService = new \App\Services\ZohoSyncService($apiClient);
                        $reflection = new \ReflectionClass($syncService);
                        $processTicketMethod = $reflection->getMethod('processTicket');
                        $processTicketMethod->setAccessible(true);
                        
                        foreach ($cachedTickets as $index => $ticketNumber) {
                            try {
                                // Search for ticket in Zoho
                                $response = $apiClient->searchTicketByNumber($ticketNumber);
                                
                                if ($response && isset($response['data'])) {
                                    // Process and cache the ticket
                                    $processTicketMethod->invoke($syncService, $response['data']);
                                    
                                    Log::info('Refreshed ticket from Zoho', [
                                        'ticket_number' => $ticketNumber,
                                        'department_id' => $departmentId,
                                        'index' => $index + 1
                                    ]);
                                }
                                
                                // Small delay between requests to avoid rate limiting
                                if ($index < count($cachedTickets) - 1) {
                                    usleep(500000); // 0.5 second delay
                                }
                            } catch (\Exception $e) {
                                // Log error but continue with other tickets
                                Log::warning('Error refreshing ticket from Zoho', [
                                    'ticket_number' => $ticketNumber,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error during bulk refresh from Zoho', [
                        'department_id' => $departmentId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue even if refresh fails, return cached data
                }
            }
            
            // Get tickets for this department (after refresh if requested)
            $tickets = ZohoTicketCache::with(['user', 'department'])
                ->where('department_id', $departmentId)
                ->orderBy('created_at_zoho', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            
            // Add department names to each ticket
            $tickets->each(function($ticket) {
                $ticket->department_name = $ticket->department ? $ticket->department->name : null;
                // Add cf_closed_by from raw_data
                if ($ticket->raw_data && isset($ticket->raw_data['cf']['cf_closed_by'])) {
                    $ticket->cf_closed_by = $ticket->raw_data['cf']['cf_closed_by'];
                }
            });
            
            return response()->json([
                'success' => true,
                'tickets' => $tickets,
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'from' => $tickets->firstItem(),
                'to' => $tickets->lastItem(),
                'refreshed' => $refresh
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching department tickets', [
                'department_id' => $departmentId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'خطأ في جلب التذاكر: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Update ticket status in both Zoho and local database
     */
    public function apiUpdateTicketStatus(Request $request, $ticketId)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:Open,Pending,Closed,Resolved,Waiting for Customer'
            ]);

            $status = $request->input('status');
            
            // Update in Zoho API
            $zohoApi = new \App\Services\ZohoApiClient();
            $zohoResult = $zohoApi->updateTicketStatus($ticketId, $status);
            
            // Update in local database
            $ticket = ZohoTicketCache::where('zoho_ticket_id', $ticketId)->first();
            
            if ($ticket) {
                $ticket->status = $status;
                $ticket->save();
                
                // Update raw_data status if it exists
                if ($ticket->raw_data) {
                    $rawData = is_array($ticket->raw_data) ? $ticket->raw_data : json_decode($ticket->raw_data, true);
                    $rawData['status'] = $status;
                    $ticket->raw_data = $rawData;
                    $ticket->save();
                }
            }
            
            Log::info('Ticket status updated', [
                'ticket_id' => $ticketId,
                'status' => $status,
                'zoho_update' => $zohoResult ? 'success' : 'failed'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة التذكرة بنجاح',
                'data' => [
                    'ticket_id' => $ticketId,
                    'status' => $status,
                    'zoho_updated' => $zohoResult !== null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating ticket status', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'خطأ في تحديث حالة التذكرة: ' . $e->getMessage()
            ], 500);
        }
    }
}

