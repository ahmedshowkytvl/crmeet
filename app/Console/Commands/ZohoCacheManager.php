<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZohoSyncService;
use Illuminate\Support\Facades\Cache;
use App\Models\ZohoTicketCache;

class ZohoCacheManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:cache-manager 
                            {action : Action to perform (status|clear|refresh|stats)}
                            {--agent= : Agent name for specific operations}
                            {--force : Force refresh even if cache is valid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Zoho tickets cache to avoid API rate limits';

    /**
     * Execute the console command.
     */
    public function handle(ZohoSyncService $syncService)
    {
        $action = $this->argument('action');
        $agent = $this->option('agent');
        $force = $this->option('force');

        switch ($action) {
            case 'status':
                $this->showCacheStatus();
                break;
                
            case 'clear':
                $this->clearCache();
                break;
                
            case 'refresh':
                $this->refreshCache($syncService, $agent, $force);
                break;
                
            case 'stats':
                $this->showCacheStats();
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: status, clear, refresh, stats');
                return 1;
        }

        return 0;
    }

    /**
     * Show cache status
     */
    private function showCacheStatus()
    {
        $this->info('📊 Zoho Cache Status');
        $this->info('==================');

        $cacheEnabled = config('zoho.cache.enabled');
        $expiryMinutes = config('zoho.cache.expiry_minutes');
        
        $this->info("Cache Enabled: " . ($cacheEnabled ? '✅ Yes' : '❌ No'));
        $this->info("Cache Expiry: {$expiryMinutes} minutes");

        // Check cache keys
        $cacheKeys = $this->getCacheKeys();
        $this->info("Active Cache Keys: " . count($cacheKeys));

        foreach ($cacheKeys as $key) {
            $lastSync = Cache::get($key . '_last_sync');
            $count = Cache::get($key . '_count', 0);
            
            if ($lastSync) {
                $age = $lastSync->diffInMinutes(now());
                $this->info("  - {$key}: {$count} tickets, {$age} minutes old");
            }
        }
    }

    /**
     * Clear cache
     */
    private function clearCache()
    {
        $this->info('🧹 Clearing Zoho cache...');

        $cacheKeys = $this->getCacheKeys();
        $cleared = 0;

        foreach ($cacheKeys as $key) {
            Cache::forget($key . '_last_sync');
            Cache::forget($key . '_count');
            $cleared++;
        }

        $this->info("✅ Cleared {$cleared} cache entries");
    }

    /**
     * Refresh cache
     */
    private function refreshCache($syncService, $agent = null, $force = false)
    {
        if ($agent) {
            $this->info("🔄 Refreshing cache for agent: {$agent}");
            
            $result = $syncService->syncTicketsForUser(
                \App\Models\User::where('zoho_agent_name', $agent)->first()?->id,
                null,
                null
            );
            
            if ($result['success']) {
                $this->info("✅ Refreshed cache for {$agent}: {$result['synced']} tickets");
            } else {
                $this->error("❌ Failed to refresh cache: {$result['message']}");
            }
        } else {
            $this->info('🔄 Refreshing cache for all agents...');
            
            $users = \App\Models\User::zohoEnabled()->get();
            $totalSynced = 0;

            foreach ($users as $user) {
                $result = $syncService->syncTicketsForUser($user->id);
                if ($result['success']) {
                    $totalSynced += $result['synced'];
                    $this->info("  ✅ {$user->zoho_agent_name}: {$result['synced']} tickets");
                } else {
                    $this->warn("  ⚠️  {$user->zoho_agent_name}: {$result['message']}");
                }
            }

            $this->info("✅ Total synced: {$totalSynced} tickets");
        }
    }

    /**
     * Show cache statistics
     */
    private function showCacheStats()
    {
        $this->info('📈 Zoho Cache Statistics');
        $this->info('=======================');

        $totalTickets = ZohoTicketCache::count();
        $cachedTickets = ZohoTicketCache::whereNotNull('user_id')->count();
        $unmappedTickets = ZohoTicketCache::whereNull('user_id')->count();

        $this->info("Total Tickets in Cache: {$totalTickets}");
        $this->info("Mapped Tickets: {$cachedTickets}");
        $this->info("Unmapped Tickets: {$unmappedTickets}");

        // Show by agent
        $agents = ZohoTicketCache::selectRaw('closed_by_name, COUNT(*) as count')
            ->whereNotNull('closed_by_name')
            ->groupBy('closed_by_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $this->info("\nTop Agents by Ticket Count:");
        foreach ($agents as $agent) {
            $this->info("  - {$agent->closed_by_name}: {$agent->count} tickets");
        }

        // Show recent activity
        $recentTickets = ZohoTicketCache::where('created_at', '>=', now()->subHours(24))->count();
        $this->info("\nTickets added in last 24h: {$recentTickets}");
    }

    /**
     * Get all cache keys
     */
    private function getCacheKeys()
    {
        // This is a simplified version - in production you might want to use Redis SCAN
        $keys = [];
        $prefix = 'zoho_tickets';
        
        // Common cache key patterns
        $patterns = [
            $prefix,
            $prefix . '_' . md5('Yaraa Khaled'),
            $prefix . '_' . md5('Nada Magdy'),
            $prefix . '_' . md5('Hadeer Mostafa'),
        ];

        foreach ($patterns as $pattern) {
            if (Cache::has($pattern . '_last_sync')) {
                $keys[] = $pattern;
            }
        }

        return $keys;
    }
}