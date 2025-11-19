<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserZohoStat;
use App\Models\ZohoTicketCache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ZohoStatsService
{
    protected $apiClient;

    public function __construct(ZohoApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Calculate stats for a user in a specific period
     */
    public function calculateUserStats($userId, $periodType, $periodDate)
    {
        $user = User::find($userId);

        // Check if user has Zoho access
        if (!$user || !$user->hasZohoAccess()) {
            Log::warning('User does not have Zoho access', ['user_id' => $userId]);
            return null;
        }

        // Get tickets for the period
        $tickets = $this->getTicketsForPeriod($user, $periodType, $periodDate);

        if ($tickets->isEmpty()) {
            return $this->createEmptyStats($userId, $periodType, $periodDate);
        }

        // Calculate statistics
        $stats = [
            'tickets_closed_count' => $tickets->count(),
            'avg_response_time_minutes' => $this->calculateAvgResponseTime($tickets),
            'tickets_per_hour' => $this->calculateTPH($tickets),
            'total_threads_count' => $tickets->sum('thread_count'),
            'performance_score' => 0,
        ];

        // Calculate performance score based on stats
        $stats['performance_score'] = $this->calculatePerformanceScore($stats);

        // Update or create stats record
        return UserZohoStat::updateOrCreate(
            [
                'user_id' => $userId,
                'period_type' => $periodType,
                'period_date' => $periodDate
            ],
            array_merge($stats, ['last_synced_at' => now()])
        );
    }

    /**
     * Get tickets for a specific period
     */
    private function getTicketsForPeriod($user, $periodType, $periodDate)
    {
        $date = Carbon::parse($periodDate);

        switch ($periodType) {
            case 'daily':
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                break;
            case 'weekly':
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                break;
            case 'monthly':
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                break;
            default:
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
        }

        return ZohoTicketCache::where('user_id', $user->id)
            ->excludeAutoClose()
            ->closed()
            ->whereBetween('closed_at_zoho', [$startDate, $endDate])
            ->get();
    }

    /**
     * Calculate Tickets Per Hour (TPH)
     */
    public function calculateTPH($tickets)
    {
        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        $responseCount = 0;

        foreach ($tickets as $ticket) {
            // Get threads for this ticket
            $threadsData = $this->apiClient->getTicketThreads($ticket->zoho_ticket_id);

            if (!$threadsData || !isset($threadsData['data'])) {
                continue;
            }

            $threads = $threadsData['data'];
            
            // Filter outgoing threads only
            $outgoing = array_filter($threads, function($t) {
                return isset($t['direction']) && $t['direction'] === 'out';
            });

            $outgoing = array_values($outgoing);

            // Calculate time between consecutive outgoing threads
            for ($i = 0; $i < count($outgoing) - 1; $i++) {
                try {
                    $time1 = Carbon::parse($outgoing[$i]['createdTime']);
                    $time2 = Carbon::parse($outgoing[$i + 1]['createdTime']);
                    $totalMinutes += $time1->diffInMinutes($time2);
                    $responseCount++;
                } catch (\Exception $e) {
                    Log::warning('Error calculating thread time', [
                        'ticket' => $ticket->ticket_number,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        if ($responseCount === 0) {
            return 0;
        }

        // Calculate average minutes per ticket
        $avgMinutesPerTicket = $totalMinutes / $responseCount;

        // Convert to tickets per hour
        return $avgMinutesPerTicket > 0 ? round(60 / $avgMinutesPerTicket, 2) : 0;
    }

    /**
     * Calculate average response time
     */
    private function calculateAvgResponseTime($tickets)
    {
        $total = 0;
        $count = 0;

        foreach ($tickets as $ticket) {
            if ($ticket->response_time_minutes) {
                $total += $ticket->response_time_minutes;
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 2) : null;
    }

    /**
     * Calculate performance score (0-100)
     */
    private function calculatePerformanceScore($stats)
    {
        $score = 0;
        $weights = config('zoho.performance_weights');

        // Tickets count score (40%)
        $ticketsWeight = $weights['tickets_count'] ?? 40;
        $ticketScore = min(($stats['tickets_closed_count'] / 10) * $ticketsWeight, $ticketsWeight);
        $score += $ticketScore;

        // Response time score (40%) - lower is better
        $responseWeight = $weights['response_time'] ?? 40;
        if ($stats['avg_response_time_minutes']) {
            // Score decreases as response time increases
            $speedScore = max(0, $responseWeight - ($stats['avg_response_time_minutes'] / 5));
            $score += $speedScore;
        }

        // TPH score (20%)
        $tphWeight = $weights['tickets_per_hour'] ?? 20;
        $tphScore = min($stats['tickets_per_hour'] * 2, $tphWeight);
        $score += $tphScore;

        return round(min($score, 100), 2);
    }

    /**
     * Create empty stats record
     */
    private function createEmptyStats($userId, $periodType, $periodDate)
    {
        return UserZohoStat::updateOrCreate(
            [
                'user_id' => $userId,
                'period_type' => $periodType,
                'period_date' => $periodDate
            ],
            [
                'tickets_closed_count' => 0,
                'avg_response_time_minutes' => null,
                'tickets_per_hour' => 0,
                'total_threads_count' => 0,
                'performance_score' => 0,
                'last_synced_at' => now()
            ]
        );
    }

    /**
     * Get top performers for a period
     */
    public function getTopPerformers($periodType = 'monthly', $periodDate = null, $limit = 10)
    {
        if (!$periodDate) {
            $periodDate = now()->startOf($periodType);
        }

        return UserZohoStat::where('period_type', $periodType)
            ->where('period_date', $periodDate)
            ->with('user')
            ->orderBy('performance_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate stats for all enabled users
     */
    public function calculateAllUsersStats($periodType = 'monthly', $periodDate = null)
    {
        if (!$periodDate) {
            $periodDate = now()->startOf($periodType);
        }

        $users = User::zohoEnabled()->get();
        $calculated = 0;

        foreach ($users as $user) {
            try {
                $this->calculateUserStats($user->id, $periodType, $periodDate);
                $calculated++;
            } catch (\Exception $e) {
                Log::error('Error calculating stats for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Stats calculation completed', [
            'period' => $periodType,
            'date' => $periodDate,
            'calculated' => $calculated
        ]);

        return $calculated;
    }

    /**
     * Get user stats summary (daily, weekly, monthly)
     */
    public function getUserStatsSummary($userId)
    {
        $user = User::find($userId);

        if (!$user || !$user->hasZohoAccess()) {
            return null;
        }

        return [
            'daily' => $user->zohoStats()
                ->daily()
                ->where('period_date', now()->startOfDay())
                ->first(),
            'weekly' => $user->zohoStats()
                ->weekly()
                ->where('period_date', now()->startOfWeek())
                ->first(),
            'monthly' => $user->zohoStats()
                ->monthly()
                ->where('period_date', now()->startOfMonth())
                ->first(),
        ];
    }

    /**
     * Get user performance trend (last N periods)
     */
    public function getUserPerformanceTrend($userId, $periodType = 'monthly', $periods = 6)
    {
        return UserZohoStat::where('user_id', $userId)
            ->where('period_type', $periodType)
            ->orderBy('period_date', 'desc')
            ->limit($periods)
            ->get()
            ->reverse()
            ->values();
    }
}

