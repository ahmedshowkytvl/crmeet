<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SnipeItService;
use Illuminate\Support\Facades\Log;

class SnipeItAutoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:sync {--type=incremental : Type of sync (full or incremental)} {--assets : Sync assets} {--users : Sync users} {--categories : Sync categories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform automatic synchronization with Snipe-IT';

    protected $snipeItService;

    public function __construct(SnipeItService $snipeItService)
    {
        parent::__construct();
        $this->snipeItService = $snipeItService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Snipe-IT synchronization...');

        // التحقق من إعدادات التكامل
        if (!config('snipeit.api_url') || !config('snipeit.api_token')) {
            $this->error('Snipe-IT API configuration is missing. Please check your settings.');
            return 1;
        }

        $syncType = $this->option('type');
        $syncAssets = $this->option('assets') || config('snipeit.sync_assets', true);
        $syncUsers = $this->option('users') || config('snipeit.sync_users', true);
        $syncCategories = $this->option('categories') || config('snipeit.sync_categories', true);

        $this->info("Sync type: {$syncType}");
        $this->info("Sync assets: " . ($syncAssets ? 'Yes' : 'No'));
        $this->info("Sync users: " . ($syncUsers ? 'Yes' : 'No'));
        $this->info("Sync categories: " . ($syncCategories ? 'Yes' : 'No'));

        $totalSynced = 0;
        $errors = [];

        try {
            // اختبار الاتصال أولاً
            $this->info('Testing connection...');
            $connectionTest = $this->snipeItService->testConnection();
            
            if (!$connectionTest['connected']) {
                $this->error('Connection test failed: ' . $connectionTest['message']);
                return 1;
            }
            
            $this->info('Connection test passed!');

            // مزامنة الأصول
            if ($syncAssets) {
                $this->info('Syncing assets...');
                try {
                    $result = $this->snipeItService->syncAssets($syncType);
                    $totalSynced += $result['synced_count'];
                    $this->info("Assets synced: {$result['synced_count']} (Created: {$result['created_count']}, Updated: {$result['updated_count']})");
                    
                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }
                } catch (\Exception $e) {
                    $this->error('Failed to sync assets: ' . $e->getMessage());
                    $errors[] = ['type' => 'assets', 'error' => $e->getMessage()];
                }
            }

            // مزامنة المستخدمين
            if ($syncUsers) {
                $this->info('Syncing users...');
                try {
                    $result = $this->snipeItService->syncUsers();
                    $totalSynced += $result['synced_count'];
                    $this->info("Users synced: {$result['synced_count']} (Created: {$result['created_count']}, Updated: {$result['updated_count']})");
                    
                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }
                } catch (\Exception $e) {
                    $this->error('Failed to sync users: ' . $e->getMessage());
                    $errors[] = ['type' => 'users', 'error' => $e->getMessage()];
                }
            }

            // مزامنة الفئات
            if ($syncCategories) {
                $this->info('Syncing categories...');
                try {
                    $result = $this->snipeItService->syncCategories();
                    $totalSynced += $result['synced_count'];
                    $this->info("Categories synced: {$result['synced_count']}");
                    
                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }
                } catch (\Exception $e) {
                    $this->error('Failed to sync categories: ' . $e->getMessage());
                    $errors[] = ['type' => 'categories', 'error' => $e->getMessage()];
                }
            }

            // عرض النتائج
            $this->info('Synchronization completed!');
            $this->info("Total items synced: {$totalSynced}");

            if (!empty($errors)) {
                $this->warn('Errors encountered:');
                foreach ($errors as $error) {
                    $this->warn('- ' . (is_array($error) ? json_encode($error) : $error));
                }
            }

            // تسجيل النتائج
            Log::info('Snipe-IT auto sync completed', [
                'sync_type' => $syncType,
                'total_synced' => $totalSynced,
                'errors_count' => count($errors),
                'errors' => $errors
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
            Log::error('Snipe-IT auto sync failed', [
                'error' => $e->getMessage(),
                'sync_type' => $syncType
            ]);
            return 1;
        }
    }
}
