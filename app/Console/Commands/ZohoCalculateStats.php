<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoStatsService;

class ZohoCalculateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:calculate-stats 
                            {--user= : Calculate stats for specific user ID}
                            {--period=monthly : Period type (daily, weekly, monthly)}
                            {--date= : Period date (Y-m-d format, defaults to current period)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate statistics for Zoho tickets';

    /**
     * Execute the console command.
     */
    public function handle(ZohoStatsService $statsService)
    {
        if (!config('zoho.sync.enabled')) {
            $this->error('Zoho sync is disabled in configuration');
            return 1;
        }

        $this->info('📊 Calculating Zoho statistics...');

        $userId = $this->option('user');
        $periodType = $this->option('period');
        $periodDate = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : now();

        // Validate period type
        if (!in_array($periodType, ['daily', 'weekly', 'monthly'])) {
            $this->error('Invalid period type. Must be: daily, weekly, or monthly');
            return 1;
        }

        try {
            if ($userId) {
                // Calculate for specific user
                $this->info("Calculating stats for user ID: {$userId}");
                $stat = $statsService->calculateUserStats($userId, $periodType, $periodDate);
                
                if ($stat) {
                    $this->info("✅ Stats calculated successfully");
                    $this->table(
                        ['Metric', 'Value'],
                        [
                            ['Tickets Closed', $stat->tickets_closed_count],
                            ['Avg Response Time', $stat->avg_response_time_minutes ? $stat->avg_response_time_minutes . ' min' : 'N/A'],
                            ['TPH', $stat->tickets_per_hour],
                            ['Total Threads', $stat->total_threads_count],
                            ['Performance Score', $stat->performance_score],
                        ]
                    );
                    return 0;
                } else {
                    $this->warn('⚠️  User not found or Zoho not enabled');
                    return 1;
                }
            } else {
                // Calculate for all users
                $this->info('Calculating stats for all Zoho-enabled users...');
                $calculated = $statsService->calculateAllUsersStats($periodType, $periodDate);
                $this->info("✅ Calculated stats for {$calculated} users");
                return 0;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error calculating stats: {$e->getMessage()}");
            \Log::error('Zoho stats calculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}

