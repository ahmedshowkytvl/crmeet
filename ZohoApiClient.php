<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ZohoApiClient
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $orgId;
    private $accessToken;
    private $tokenExpiresAt;
    private $baseUrl;

    public function __construct()
    {
        $this->clientId = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->refreshToken = config('zoho.refresh_token');
        $this->orgId = config('zoho.org_id');
        $this->baseUrl = 'https://desk.zoho.com/api/v1';
    }

    /**
     * Get access token (from cache or refresh)
     */
    public function getAccessToken()
    {
        // Try to get from cache first
        $cachedToken = Cache::get('zoho_access_token');
        if ($cachedToken) {
            $this->accessToken = $cachedToken;
            return $this->accessToken;
        }

        return $this->refreshAccessToken();
    }

    /**
     * Refresh access token
     */
    private function refreshAccessToken()
    {
        try {
            $response = Http::asForm()->post(config('zoho.token_url'), [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;

                // Cache the token for slightly less than its expiry time
                Cache::put('zoho_access_token', $this->accessToken, now()->addSeconds($expiresIn - 60));

                Log::info('Zoho access token refreshed successfully');
                return $this->accessToken;
            }

            Log::error('Failed to refresh Zoho access token', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception('Failed to refresh Zoho access token: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Exception refreshing Zoho token', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get tickets from Zoho
     */
    public function getTickets($params = [])
    {
        $defaultParams = [
            'limit' => 100,
            'sortBy' => '-createdTime'
        ];

        return $this->makeRequest('GET', '/tickets', array_merge($defaultParams, $params));
    }

    /**
     * Get specific ticket by ID
     */
    public function getTicket($ticketId)
    {
        $params = [];
        return $this->makeRequest('GET', "/tickets/{$ticketId}", $params);
    }

    /**
     * Get ticket threads
     **/
    public function getTicketThreads($ticketId)
    {
        $params = [];
        return $this->makeRequest('GET', "/tickets/{$ticketId}/threads", $params);
    }

    /**
     * Get ticket threads with full content
     */
    public function getTicketThreadsWithFullContent($ticketId)
    {
        // Use basic parameters that Zoho API supports
        $params = [
            'limit' => 100,
            'sortBy' => 'createdTime'
        ];
        
        return $this->makeRequest('GET', "/tickets/{$ticketId}/threads", $params);
    }

    /**
     * Get thread content using Python script approach
     */
    public function getThreadContentPythonStyle($ticketId, $threadId)
    {
        try {
            // Use the same approach as the Python script
            $params = ['orgId' => $this->orgId];
            $response = $this->makeRequest('GET', "/tickets/{$ticketId}/threads/{$threadId}", $params);
            
            if ($response && isset($response['data'])) {
                $threadData = $response['data'];
                
                // Extract content like the Python script
                $content = $threadData['content'] ?? '';
                $contentType = $threadData['contentType'] ?? 'text/plain';
                $summary = $threadData['summary'] ?? '';
                $author = $threadData['author'] ?? [];
                $createdTime = $threadData['createdTime'] ?? '';
                $channel = $threadData['channel'] ?? '';
                $direction = $threadData['direction'] ?? '';
                $status = $threadData['status'] ?? '';
                
                return [
                    'success' => true,
                    'data' => [
                        'thread_id' => $threadId,
                        'ticket_id' => $ticketId,
                        'content' => $content,
                        'contentType' => $contentType,
                        'summary' => $summary,
                        'author' => $author,
                        'createdTime' => $createdTime,
                        'channel' => $channel,
                        'direction' => $direction,
                        'status' => $status,
                        'isHtml' => $this->isHtmlContent($content),
                        'raw_data' => $threadData
                    ]
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting thread content Python style', [
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
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
     * Get thread with maximum content details
     */
    public function getThreadWithMaxContent($ticketId, $threadId)
    {
        try {
            // Use the Python script approach first
            $pythonStyleResult = $this->getThreadContentPythonStyle($ticketId, $threadId);
            if ($pythonStyleResult && isset($pythonStyleResult['data'])) {
                return $pythonStyleResult;
            }
            
            // Fallback to basic approach
            $params = ['orgId' => $this->orgId];
            $result = $this->makeRequest('GET', "/tickets/{$ticketId}/threads/{$threadId}", $params);
            
            if ($result && isset($result['data'])) {
                $data = $result['data'];
                // Check if we got meaningful content
                if (!empty($data['body']) || !empty($data['content'])) {
                    return $result;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting thread with max content', [
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get all agents
     */
    public function getAgents()
    {
        return $this->makeRequest('GET', '/agents');
    }

    /**
     * Get specific agent
     */
    public function getAgent($agentId)
    {
        return $this->makeRequest('GET', "/agents/{$agentId}");
    }

    /**
     * Get departments
     */
    public function getDepartments()
    {
        return $this->makeRequest('GET', '/departments');
    }

    /**
     * Search tickets by date range and agent
     */
    public function getTicketsByDateRangeAndAgent($agentName, $fromDate = null, $toDate = null, $limit = 100)
    {
        $params = [
            'limit' => $limit,
            'sortBy' => '-createdTime'
        ];

        // Add date filters if provided
        if ($fromDate) {
            $params['from'] = $fromDate;
        }
        if ($toDate) {
            $params['to'] = $toDate;
        }

        // Get all tickets and filter by agent
        $response = $this->makeRequest('GET', '/tickets', $params);
        
        if (!$response || !isset($response['data'])) {
            return null;
        }

        // Filter tickets by closed_by agent
        $filteredTickets = array_filter($response['data'], function($ticket) use ($agentName) {
            $closedBy = $ticket['cf']['cf_closed_by'] ?? null;
            return $closedBy === $agentName;
        });

        return [
            'data' => array_values($filteredTickets),
            'count' => count($filteredTickets)
        ];
    }

    /**
     * Get tickets by custom field value
     */
    public function getTicketsByCustomField($fieldName, $fieldValue, $fromDate = null, $toDate = null, $limit = 100)
    {
        $params = [
            'limit' => $limit,
            'sortBy' => '-createdTime'
        ];

        // Add date filters if provided
        if ($fromDate) {
            $params['from'] = $fromDate;
        }
        if ($toDate) {
            $params['to'] = $toDate;
        }

        // Get all tickets and filter by custom field
        $response = $this->makeRequest('GET', '/tickets', $params);
        
        if (!$response || !isset($response['data'])) {
            return null;
        }

        // Filter tickets by custom field
        $filteredTickets = array_filter($response['data'], function($ticket) use ($fieldName, $fieldValue) {
            $fieldValueFromTicket = $ticket['cf'][$fieldName] ?? null;
            return $fieldValueFromTicket === $fieldValue;
        });

        return [
            'data' => array_values($filteredTickets),
            'count' => count($filteredTickets)
        ];
    }

    /**
     * Search tickets
     */
    public function searchTickets($query, $limit = 100)
    {
        $params = [
            'limit' => $limit,
            'sortBy' => '-createdTime'
        ];

        return $this->makeRequest('GET', '/tickets/search', $params);
    }

    /**
     * Get full thread details
     */
    public function getThreadDetails($threadId)
    {
        try {
            $response = $this->makeRequest('GET', "/threads/{$threadId}");
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting thread details', [
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get thread details by ticket ID and thread ID (for full content)
     */
    public function getThreadDetailsByTicket($ticketId, $threadId)
    {
        try {
            $params = ['orgId' => $this->orgId];
            $response = $this->makeRequest('GET', "/tickets/{$ticketId}/threads/{$threadId}", $params);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting thread details by ticket', [
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get thread content as JSON (alternative endpoint)
     */
    public function getThreadContentAsJson($ticketId, $threadId)
    {
        try {
            $response = $this->makeRequest('GET', "/threads/{$ticketId}/{$threadId}");
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting thread content as JSON', [
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get thread content as HTML view
     */
    public function getThreadContentView($ticketId, $threadId)
    {
        try {
            $response = $this->makeRequest('GET', "/threads/{$ticketId}/{$threadId}/view");
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting thread content view', [
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get CRM email details by module, record ID and message ID
     * This uses Zoho CRM API for business emails
     */
    public function getCrmEmailDetails($moduleApiName, $recordId, $messageId, $userId = null)
    {
        try {
            // Switch to CRM API base URL
            $originalBaseUrl = $this->baseUrl;
            $this->baseUrl = 'https://www.zohoapis.com/crm/v2';
            
            $params = [];
            if ($userId) {
                $params['user_id'] = $userId;
            }
            
            $response = $this->makeRequest('GET', "/{$moduleApiName}/{$recordId}/Emails/{$messageId}", $params);
            
            // Restore original base URL
            $this->baseUrl = $originalBaseUrl;
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting CRM email details', [
                'module' => $moduleApiName,
                'record_id' => $recordId,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get all emails for a CRM record
     */
    public function getCrmRecordEmails($moduleApiName, $recordId, $userId = null)
    {
        try {
            // Switch to CRM API base URL
            $originalBaseUrl = $this->baseUrl;
            $this->baseUrl = 'https://www.zohoapis.com/crm/v2';
            
            $params = [];
            if ($userId) {
                $params['user_id'] = $userId;
            }
            
            $response = $this->makeRequest('GET', "/{$moduleApiName}/{$recordId}/Emails", $params);
            
            // Restore original base URL
            $this->baseUrl = $originalBaseUrl;
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting CRM record emails', [
                'module' => $moduleApiName,
                'record_id' => $recordId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Try to get thread content from Zoho Mail API if available
     */
    public function getThreadContentFromMail($messageId)
    {
        try {
            // Switch to Mail API base URL
            $originalBaseUrl = $this->baseUrl;
            $this->baseUrl = 'https://mail.zoho.com/api/messages';
            
            $response = $this->makeRequest('GET', "/{$messageId}");
            
            // Restore original base URL
            $this->baseUrl = $originalBaseUrl;
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Error getting thread content from Mail API', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Enhanced thread details with multiple content sources
     */
    public function getEnhancedThreadDetails($ticketId, $threadId)
    {
        try {
            // Try the new method with maximum content first
            $maxContent = $this->getThreadWithMaxContent($ticketId, $threadId);
            if ($maxContent && isset($maxContent['data'])) {
                return $maxContent;
            }
            
            // Fallback to standard thread details
            $threadDetails = $this->getThreadDetailsByTicket($ticketId, $threadId);
            
            if ($threadDetails && isset($threadDetails['data'])) {
                $threadData = $threadDetails['data'];
                
                // Check if we have full content
                if (!empty($threadData['body']) || !empty($threadData['content'])) {
                    return $threadDetails;
                }
                
                // Try to get from Mail API if we have message ID
                if (isset($threadData['messageId'])) {
                    $mailContent = $this->getThreadContentFromMail($threadData['messageId']);
                    if ($mailContent && isset($mailContent['data'])) {
                        // Merge mail content with thread data
                        $threadData['body'] = $mailContent['data']['body'] ?? $threadData['body'];
                        $threadData['content'] = $mailContent['data']['content'] ?? $threadData['content'];
                        $threadData['isHtml'] = $mailContent['data']['isHtml'] ?? $threadData['isHtml'];
                        
                        return ['data' => $threadData];
                    }
                }
            }
            
            return $threadDetails;
        } catch (\Exception $e) {
            Log::error('Error getting enhanced thread details', [
                'ticket_id' => $ticketId,
                'thread_id' => $threadId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Search for specific ticket by number
     */
    public function searchTicketByNumber($ticketNumber)
    {
        try {
            // First try to get by ticket number using search
            $searchParams = [
                'ticketNumber' => $ticketNumber,
                'limit' => 1
            ];

            $response = $this->makeRequest('GET', '/tickets/search', $searchParams);
            
            if ($response && isset($response['data']) && !empty($response['data'])) {
                // Return the first ticket from the array
                return [
                    'data' => $response['data'][0] // Get the first ticket from the array
                ];
            }

            // If search doesn't work, try to get all tickets and filter
            $allTickets = $this->getTickets(['limit' => 1000]);
            
            if ($allTickets && isset($allTickets['data'])) {
                foreach ($allTickets['data'] as $ticket) {
                    if (isset($ticket['ticketNumber']) && $ticket['ticketNumber'] == $ticketNumber) {
                        return [
                            'data' => $ticket
                        ];
                    }
                }
            }

            return null;
            
        } catch (\Exception $e) {
            Log::error('Error searching ticket by number', [
                'ticket_number' => $ticketNumber,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Advanced search tickets by text
     */
    public function advancedSearchByText($searchQuery, $limit = 1000)
    {
        try {
            $allTickets = [];
            $page = 0;
            $pageSize = min($limit, 100); // Zoho max is 100 per request
            
            do {
                $params = [
                    'limit' => $pageSize,
                    'from' => $page * $pageSize,
                    'sortBy' => '-createdTime'
                ];
                
                $response = $this->makeRequest('GET', '/tickets', $params);
                
                if (!$response || !isset($response['data']) || empty($response['data'])) {
                    break;
                }
                
                // Filter tickets by search query (search in subject, description, ticketNumber)
                $filteredTickets = array_filter($response['data'], function($ticket) use ($searchQuery) {
                    $searchLower = strtolower($searchQuery);
                    $subject = strtolower($ticket['subject'] ?? '');
                    $description = strtolower($ticket['description'] ?? '');
                    $ticketNumber = strtolower($ticket['ticketNumber'] ?? '');
                    
                    return strpos($subject, $searchLower) !== false ||
                           strpos($description, $searchLower) !== false ||
                           strpos($ticketNumber, $searchLower) !== false;
                });
                
                $allTickets = array_merge($allTickets, $filteredTickets);
                
                // If we got less than page size, we've reached the end
                if (count($response['data']) < $pageSize) {
                    break;
                }
                
                $page++;
                
            } while (count($allTickets) < $limit);
            
            return [
                'data' => array_slice($allTickets, 0, $limit),
                'count' => count($allTickets)
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in advanced search by text', [
                'search_query' => $searchQuery,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Advanced search tickets by custom field
     */
    public function advancedSearchByCustomField($fieldName, $fieldValue, $limit = 1000)
    {
        try {
            $allTickets = [];
            $page = 0;
            $pageSize = min($limit, 100);
            
            do {
                $params = [
                    'limit' => $pageSize,
                    'from' => $page * $pageSize,
                    'sortBy' => '-createdTime'
                ];
                
                $response = $this->makeRequest('GET', '/tickets', $params);
                
                if (!$response || !isset($response['data']) || empty($response['data'])) {
                    break;
                }
                
                // Filter tickets by custom field
                $filteredTickets = array_filter($response['data'], function($ticket) use ($fieldName, $fieldValue) {
                    $fieldValueFromTicket = $ticket['cf'][$fieldName] ?? null;
                    return $fieldValueFromTicket === $fieldValue;
                });
                
                $allTickets = array_merge($allTickets, $filteredTickets);
                
                if (count($response['data']) < $pageSize) {
                    break;
                }
                
                $page++;
                
            } while (count($allTickets) < $limit);
            
            return [
                'data' => array_slice($allTickets, 0, $limit),
                'count' => count($allTickets)
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in advanced search by custom field', [
                'field_name' => $fieldName,
                'field_value' => $fieldValue,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Advanced search tickets by time range
     */
    public function advancedSearchByTimeRange($startDate, $endDate, $limit = 1000)
    {
        try {
            $allTickets = [];
            $page = 0;
            $pageSize = min($limit, 100);
            
            do {
                // Use createdTimeRange parameter
                $params = [
                    'limit' => $pageSize,
                    'from' => $page * $pageSize,
                    'sortBy' => '-createdTime',
                    'createdTimeRange' => "{$startDate}T00:00:00.000Z,{$endDate}T23:59:59.000Z"
                ];
                
                $response = $this->makeRequest('GET', '/tickets', $params);
                
                if (!$response || !isset($response['data']) || empty($response['data'])) {
                    break;
                }
                
                $allTickets = array_merge($allTickets, $response['data']);
                
                if (count($response['data']) < $pageSize) {
                    break;
                }
                
                $page++;
                
            } while (count($allTickets) < $limit);
            
            return [
                'data' => array_slice($allTickets, 0, $limit),
                'count' => count($allTickets)
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in advanced search by time range', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Make HTTP request to Zoho API
     */
    private function makeRequest($method, $endpoint, $params = [], $data = [])
    {
        try {
            $token = $this->getAccessToken();
            $url = config('zoho.base_url') . $endpoint;

            // Add orgId to params
            $params['orgId'] = $this->orgId;

            $request = Http::withHeaders([
                'Authorization' => "Zoho-oauthtoken {$token}",
                'Content-Type' => 'application/json',
            ])->timeout(30);

            // Make request based on method
            $response = match(strtoupper($method)) {
                'GET' => $request->get($url, $params),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url),
                default => throw new \Exception("Unsupported HTTP method: {$method}")
            };

            if ($response->successful()) {
                return $response->json();
            }

            // Log error
            Log::error('Zoho API Error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // If unauthorized, clear cache and retry once
            if ($response->status() === 401) {
                Cache::forget('zoho_access_token');
                Log::warning('Zoho token expired, retrying with fresh token');
                
                // Retry once with fresh token
                return $this->retryRequest($method, $endpoint, $params, $data);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Zoho API Exception', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Retry request with fresh token (called when 401)
     */
    private function retryRequest($method, $endpoint, $params, $data)
    {
        try {
            $token = $this->refreshAccessToken();
            $url = config('zoho.base_url') . $endpoint;
            $params['orgId'] = $this->orgId;

            $request = Http::withHeaders([
                'Authorization' => "Zoho-oauthtoken {$token}",
                'Content-Type' => 'application/json',
            ])->timeout(30);

            $response = match(strtoupper($method)) {
                'GET' => $request->get($url, $params),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url),
                default => throw new \Exception("Unsupported HTTP method: {$method}")
            };

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Zoho API Retry Failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Test connection to Zoho API
     */
    public function testConnection()
    {
        try {
            $agents = $this->getAgents();
            return !is_null($agents);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update ticket status
     */
    public function updateTicketStatus($ticketId, $status)
    {
        try {
            $data = ['status' => $status];
            return $this->makeRequest('PUT', "/tickets/{$ticketId}", [], $data);
        } catch (\Exception $e) {
            Log::error('Error updating ticket status', [
                'ticket_id' => $ticketId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

