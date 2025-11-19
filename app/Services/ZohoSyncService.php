<?php

namespace App\Services;

use App\Models\User;
use App\Models\ZohoTicketCache;
use App\Models\ZohoDepartmentMapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ZohoSyncService
{
    protected $apiClient;

    public function __construct(ZohoApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Get tickets from cache or API with smart caching
     */
    public function getTicketsWithCache($agentName = null, $fromDate = null, $toDate = null, $forceRefresh = false)
    {
        $cacheKey = $this->generateCacheKey($agentName, $fromDate, $toDate);
        
        // Check if we should use cache
        if (!$forceRefresh && $this->shouldUseCache($cacheKey)) {
            Log::info('Using cached tickets', ['cache_key' => $cacheKey]);
            return $this->getTicketsFromCache($agentName, $fromDate, $toDate);
        }
        
        // Fetch from API and cache
        Log::info('Fetching tickets from API', ['agent' => $agentName]);
        $tickets = $this->fetchTicketsFromAPI($agentName, $fromDate, $toDate);
        
        if (!empty($tickets)) {
            $this->cacheTickets($tickets, $cacheKey);
        }
        
        return $tickets;
    }

    /**
     * Generate cache key for tickets
     */
    private function generateCacheKey($agentName = null, $fromDate = null, $toDate = null)
    {
        $key = 'zoho_tickets';
        if ($agentName) $key .= '_' . md5($agentName);
        if ($fromDate) $key .= '_from_' . $fromDate;
        if ($toDate) $key .= '_to_' . $toDate;
        return $key;
    }

    /**
     * Check if we should use cache instead of API
     */
    private function shouldUseCache($cacheKey)
    {
        $lastSync = Cache::get($cacheKey . '_last_sync');
        if (!$lastSync) return false;
        
        $cacheExpiry = config('zoho.cache.expiry_minutes', 10); // 10 minutes default
        return $lastSync->addMinutes($cacheExpiry)->isFuture();
    }

    /**
     * Get tickets from local cache
     */
    private function getTicketsFromCache($agentName = null, $fromDate = null, $toDate = null)
    {
        $query = ZohoTicketCache::query();
        
        if ($agentName) {
            $query->where('closed_by_name', $agentName);
        }
        
        if ($fromDate) {
            $query->where('created_at_zoho', '>=', $fromDate);
        }
        
        if ($toDate) {
            $query->where('created_at_zoho', '<=', $toDate);
        }
        
        return $query->orderBy('created_at_zoho', 'desc')->get()->toArray();
    }

    /**
     * Cache tickets data
     */
    private function cacheTickets($tickets, $cacheKey)
    {
        $cacheExpiry = config('zoho.cache.expiry_minutes', 10);
        Cache::put($cacheKey . '_last_sync', now(), now()->addMinutes($cacheExpiry));
        Cache::put($cacheKey . '_count', count($tickets), now()->addMinutes($cacheExpiry));
        
        Log::info('Tickets cached', [
            'cache_key' => $cacheKey,
            'count' => count($tickets),
            'expiry_minutes' => $cacheExpiry
        ]);
    }

    /**
     * Fetch tickets from API (internal method)
     */
    private function fetchTicketsFromAPI($agentName = null, $fromDate = null, $toDate = null)
    {
        if ($agentName) {
            $response = $this->apiClient->getTicketsByDateRangeAndAgent(
                $agentName, 
                $fromDate, 
                $toDate, 
                1000
            );
            return $response['data'] ?? [];
        }
        
        // Fallback to regular fetch
        return $this->fetchTickets($fromDate, $toDate);
    }

    /**
     * Sync tickets from Zoho (only for enabled users)
     */
    public function syncTickets($fromDate = null, $toDate = null, $limit = null)
    {
        $users = User::zohoEnabled()->get();

        if ($users->isEmpty()) {
            Log::info('No Zoho-enabled users to sync');
            return [
                'success' => false,
                'message' => 'No Zoho-enabled users found',
                'synced' => 0
            ];
        }

        Log::info('Starting Zoho tickets sync', [
            'enabled_users' => $users->count(),
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);

        $tickets = $this->fetchTickets($fromDate, $toDate, $limit);

        if (empty($tickets)) {
            Log::warning('No tickets fetched from Zoho');
            return [
                'success' => false,
                'message' => 'No tickets fetched',
                'synced' => 0
            ];
        }

        $synced = 0;
        foreach ($tickets as $ticketData) {
            if ($this->processTicket($ticketData)) {
                $synced++;
            }
        }

        Log::info('Zoho tickets sync completed', ['synced' => $synced]);

        return [
            'success' => true,
            'message' => "Synced {$synced} tickets",
            'synced' => $synced
        ];
    }

    /**
     * Fetch tickets from Zoho API
     */
    private function fetchTickets($fromDate = null, $toDate = null, $limit = null)
    {
        $params = [];
        
        if ($limit) {
            $params['limit'] = $limit;
        } else {
            $params['limit'] = config('zoho.sync.tickets_per_batch', 100);
        }

        // If no dates provided, fetch recent tickets
        if (!$fromDate) {
            $fromDate = now()->subDays(config('zoho.sync.max_days_back', 30));
        }

        $allTickets = [];
        $from = 0;
        
        // Fetch in batches
        do {
            $params['from'] = $from;
            $response = $this->apiClient->getTickets($params);

            if (!$response || !isset($response['data'])) {
                break;
            }

            $tickets = $response['data'];
            $allTickets = array_merge($allTickets, $tickets);

            // Check if there are more tickets
            $hasMore = isset($response['count']) && count($tickets) >= $params['limit'];
            $from += count($tickets);

        } while ($hasMore && count($allTickets) < 500); // Limit to 500 tickets per sync

        return $allTickets;
    }

    /**
     * Process single ticket
     */
    private function processTicket($ticketData)
    {
        try {
            // Check if ticket already exists in database
            $existingTicket = ZohoTicketCache::where('zoho_ticket_id', $ticketData['id'])->first();
            $existingClosedBy = $existingTicket ? $existingTicket->closed_by_name : null;
            
            // Get cf_closed_by value (if available)
            $closedBy = $ticketData['cf']['cf_closed_by'] ?? null;

            // If no cf_closed_by, try to get from customFields
            if (empty($closedBy) && isset($ticketData['customFields']['Closed By'])) {
                $closedBy = $ticketData['customFields']['Closed By'];
            }

            // If no closed_by from custom fields, try to get assignee or use a default
            if (empty($closedBy)) {
                // Try to get from assignee
                $closedBy = $ticketData['assigneeName'] ?? $ticketData['assignee'] ?? null;
                
                // If still empty and ticket is closed, use 'Unknown Agent'
                if (empty($closedBy) && ($ticketData['status'] ?? '') === 'Closed') {
                    $closedBy = 'Unknown Agent';
                }
            }
            
            // If existing ticket has closed_by_name and it's not empty, keep it (don't override)
            if (!empty($existingClosedBy) && $existingClosedBy !== 'غير محدد' && $existingClosedBy !== 'Unknown Agent') {
                $closedBy = $existingClosedBy;
            }

            // Find user by zoho_agent_name (only if we have a valid closedBy that's not Auto Close or Unknown)
            $user = null;
            if (!empty($closedBy) && $closedBy !== 'Unknown Agent' && $closedBy !== 'Auto Close') {
                $user = User::where('zoho_agent_name', $closedBy)
                            ->where('is_zoho_enabled', true)
                            ->first();
            }

            // Calculate response time if threads available
            $responseTime = $this->calculateTicketResponseTime($ticketData);

            // Map Zoho department ID to local department ID
            $zohoDepartmentId = $ticketData['departmentId'] ?? null;
            $localDepartmentId = null;
            
            if ($zohoDepartmentId) {
                $localDepartmentId = ZohoDepartmentMapping::getLocalDepartmentId($zohoDepartmentId);
                
                if ($localDepartmentId) {
                    Log::info('Department mapped successfully', [
                        'zoho_department_id' => $zohoDepartmentId,
                        'local_department_id' => $localDepartmentId,
                        'ticket_number' => $ticketData['ticketNumber'] ?? 'unknown'
                    ]);
                } else {
                    Log::warning('No mapping found for Zoho department', [
                        'zoho_department_id' => $zohoDepartmentId,
                        'ticket_number' => $ticketData['ticketNumber'] ?? 'unknown'
                    ]);
                }
            }

            // Store or update in cache
            ZohoTicketCache::updateOrCreate(
                ['zoho_ticket_id' => $ticketData['id']],
                [
                    'ticket_number' => $ticketData['ticketNumber'] ?? null,
                    'user_id' => $user?->id,
                    'closed_by_name' => $closedBy,
                    'subject' => $ticketData['subject'] ?? null,
                    'status' => $ticketData['status'] ?? $ticketData['statusType'] ?? 'Unknown',
                    'department_id' => $localDepartmentId, // Use mapped local department ID
                    'created_at_zoho' => $ticketData['createdTime'] ?? now(),
                    'closed_at_zoho' => $ticketData['closedTime'] ?? null,
                    'thread_count' => $ticketData['threadCount'] ?? 0,
                    'response_time_minutes' => $responseTime ? round($responseTime) : null, // Round to integer
                    'raw_data' => $ticketData,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Error processing ticket', [
                'ticket_id' => $ticketData['id'] ?? 'unknown',
                'ticket_number' => $ticketData['ticketNumber'] ?? 'unknown',
                'error' => $e->getMessage(),
                'available_keys' => array_keys($ticketData)
            ]);
            return false;
        }
    }

    /**
     * Calculate response time for a ticket
     */
    private function calculateTicketResponseTime($ticketData)
    {
        if (!isset($ticketData['createdTime']) || !isset($ticketData['closedTime'])) {
            return null;
        }

        try {
            $created = Carbon::parse($ticketData['createdTime']);
            $closed = Carbon::parse($ticketData['closedTime']);
            return $created->diffInMinutes($closed);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Auto-map users based on email matching
     */
    public function autoMapUsers()
    {
        $agents = $this->apiClient->getAgents();

        if (!$agents || !isset($agents['data'])) {
            Log::error('Failed to fetch agents from Zoho');
            return 0;
        }

        $mapped = 0;
        foreach ($agents['data'] as $agent) {
            $email = $agent['email'] ?? $agent['emailId'] ?? null;
            
            if (!$email) {
                continue;
            }

            // Find user by email
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $firstName = $agent['firstName'] ?? '';
                $lastName = $agent['lastName'] ?? '';
                $fullName = trim("{$firstName} {$lastName}");

                $user->update([
                    'zoho_agent_name' => $fullName ?: $email,
                    'zoho_agent_id' => $agent['id'] ?? null,
                    'zoho_email' => $email,
                    'is_zoho_enabled' => true,
                    'zoho_linked_at' => now(),
                ]);

                Log::info('Auto-mapped user to Zoho', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'zoho_name' => $fullName
                ]);

                $mapped++;
            }
        }

        Log::info('Auto-mapping completed', ['mapped' => $mapped]);
        return $mapped;
    }

    /**
     * Manually map user to Zoho agent
     */
    public function mapUser($userId, $zohoAgentName, $zohoAgentId = null, $zohoEmail = null)
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        $user->update([
            'zoho_agent_name' => $zohoAgentName,
            'zoho_agent_id' => $zohoAgentId,
            'zoho_email' => $zohoEmail,
            'is_zoho_enabled' => true,
            'zoho_linked_at' => now(),
        ]);

        Log::info('Manually mapped user to Zoho', [
            'user_id' => $userId,
            'zoho_name' => $zohoAgentName
        ]);

        return true;
    }

    /**
     * Unmap user from Zoho
     */
    public function unmapUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        $user->update([
            'zoho_agent_name' => null,
            'zoho_agent_id' => null,
            'zoho_email' => null,
            'is_zoho_enabled' => false,
            'zoho_linked_at' => null,
        ]);

        Log::info('Unmapped user from Zoho', ['user_id' => $userId]);

        return true;
    }

    /**
     * Sync tickets for specific user with caching
     */
    public function syncTicketsForUser($userId, $fromDate = null, $toDate = null)
    {
        $user = User::find($userId);

        if (!$user || !$user->hasZohoAccess()) {
            return [
                'success' => false,
                'message' => 'User not found or Zoho not enabled',
                'synced' => 0
            ];
        }

        // Use cached method first
        $tickets = $this->getTicketsWithCache(
            $user->zoho_agent_name, 
            $fromDate, 
            $toDate, 
            false // Don't force refresh initially
        );

        if (empty($tickets)) {
            // Try with force refresh if no cached data
            $tickets = $this->getTicketsWithCache(
                $user->zoho_agent_name, 
                $fromDate, 
                $toDate, 
                true // Force refresh
            );
        }

        if (empty($tickets)) {
            return [
                'success' => false,
                'message' => 'No tickets found for this agent',
                'synced' => 0
            ];
        }

        $synced = 0;

        Log::info('Syncing tickets for user', [
            'user_id' => $userId,
            'agent_name' => $user->zoho_agent_name,
            'tickets_found' => count($tickets),
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);

        foreach ($tickets as $ticketData) {
            if ($this->processTicket($ticketData)) {
                $synced++;
            }
        }

        return [
            'success' => true,
            'message' => "Synced {$synced} tickets for {$user->zoho_agent_name}",
            'synced' => $synced
        ];
    }

    /**
     * Sync tickets by custom field (e.g., cf_closed_by)
     */
    public function syncTicketsByCustomField($fieldName, $fieldValue, $fromDate = null, $toDate = null)
    {
        Log::info('Syncing tickets by custom field', [
            'field_name' => $fieldName,
            'field_value' => $fieldValue,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);

        $response = $this->apiClient->getTicketsByCustomField(
            $fieldName, 
            $fieldValue, 
            $fromDate, 
            $toDate, 
            1000
        );

        if (!$response || !isset($response['data'])) {
            return [
                'success' => false,
                'message' => "No tickets found for {$fieldName} = {$fieldValue}",
                'synced' => 0
            ];
        }

        $tickets = $response['data'];
        $synced = 0;

        foreach ($tickets as $ticketData) {
            if ($this->processTicket($ticketData)) {
                $synced++;
            }
        }

        return [
            'success' => true,
            'message' => "Synced {$synced} tickets for {$fieldName} = {$fieldValue}",
            'synced' => $synced
        ];
    }
}

