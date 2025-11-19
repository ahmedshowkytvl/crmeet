<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Asset;
use App\Models\User;
use App\Models\SnipeItSyncLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class SnipeItService
{
    protected $apiUrl;
    protected $apiToken;
    protected $timeout = 30;

    public function __construct()
    {
        $this->apiUrl = config('snipeit.api_url', 'http://127.0.0.1');
        $this->apiToken = config('snipeit.api_token');
    }

    /**
     * جلب بيانات المستخدم الحالي من Snipe-IT
     */
    public function getCurrentUser(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($this->apiUrl . '/api/v1/users/me');

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['payload'])) {
                    return $data['payload'];
                }
                return $data;
            } else {
                throw new Exception('فشل في جلب بيانات المستخدم: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Snipe-IT getCurrentUser failed', [
                'error' => $e->getMessage(),
                'api_url' => $this->apiUrl
            ]);
            throw $e;
        }
    }

    /**
     * اختبار الاتصال مع Snipe-IT
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->get($this->apiUrl . '/api/v1/statuslabels');

            if ($response->successful()) {
                return [
                    'connected' => true,
                    'status' => 'success',
                    'message' => 'تم الاتصال بنجاح',
                    'response_time' => $response->transferStats->getHandlerStat('total_time'),
                    'data' => $response->json()
                ];
            } else {
                return [
                    'connected' => false,
                    'status' => 'error',
                    'message' => 'فشل في الاتصال: ' . $response->body(),
                    'status_code' => $response->status()
                ];
            }
        } catch (Exception $e) {
            Log::error('Snipe-IT connection test failed', [
                'error' => $e->getMessage(),
                'api_url' => $this->apiUrl
            ]);

            return [
                'connected' => false,
                'status' => 'error',
                'message' => 'خطأ في الاتصال: ' . $e->getMessage()
            ];
        }
    }

    /**
     * مزامنة الأصول من Snipe-IT
     */
    public function syncAssets(string $syncType = 'incremental', array $assetIds = []): array
    {
        $startTime = now();
        $syncLog = $this->createSyncLog('assets', $syncType);

        try {
            if ($syncType === 'full') {
                $assets = $this->getAllAssets();
            } else {
                $assets = $this->getAssetsSince($this->getLastSyncTime('assets'));
            }

            if (!empty($assetIds)) {
                $assets = array_filter($assets, function($asset) use ($assetIds) {
                    return in_array($asset['id'], $assetIds);
                });
            }

            $syncedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;
            $errors = [];

            foreach ($assets as $assetData) {
                try {
                    $existingAsset = Asset::where('snipeit_id', $assetData['id'])->first();

                    if ($existingAsset) {
                        $this->updateLocalAsset($existingAsset, $assetData);
                        $updatedCount++;
                    } else {
                        $this->createLocalAsset($assetData);
                        $createdCount++;
                    }

                    $syncedCount++;
                } catch (Exception $e) {
                    $errors[] = [
                        'asset_id' => $assetData['id'],
                        'error' => $e->getMessage()
                    ];
                    Log::error('Failed to sync asset', [
                        'asset_id' => $assetData['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->updateSyncLog($syncLog, 'completed', [
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'duration' => now()->diffInSeconds($startTime)
            ]);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'duration' => now()->diffInSeconds($startTime)
            ];

        } catch (Exception $e) {
            $this->updateSyncLog($syncLog, 'failed', [
                'error' => $e->getMessage(),
                'duration' => now()->diffInSeconds($startTime)
            ]);

            Log::error('Assets sync failed', [
                'error' => $e->getMessage(),
                'sync_type' => $syncType
            ]);

            throw $e;
        }
    }

    /**
     * مزامنة المستخدمين من Snipe-IT
     */
    public function syncUsers(): array
    {
        $startTime = now();
        $syncLog = $this->createSyncLog('users', 'full');

        try {
            $users = $this->getAllUsers();
            $syncedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;
            $errors = [];

            foreach ($users as $userData) {
                try {
                    $existingUser = User::where('snipeit_id', $userData['id'])->first();

                    if ($existingUser) {
                        $this->updateLocalUser($existingUser, $userData);
                        $updatedCount++;
                    } else {
                        $this->createLocalUser($userData);
                        $createdCount++;
                    }

                    $syncedCount++;
                } catch (Exception $e) {
                    $errors[] = [
                        'user_id' => $userData['id'],
                        'error' => $e->getMessage()
                    ];
                    Log::error('Failed to sync user', [
                        'user_id' => $userData['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->updateSyncLog($syncLog, 'completed', [
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'duration' => now()->diffInSeconds($startTime)
            ]);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'duration' => now()->diffInSeconds($startTime)
            ];

        } catch (Exception $e) {
            $this->updateSyncLog($syncLog, 'failed', [
                'error' => $e->getMessage(),
                'duration' => now()->diffInSeconds($startTime)
            ]);

            Log::error('Users sync failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * مزامنة الفئات من Snipe-IT
     */
    public function syncCategories(): array
    {
        $startTime = now();
        $syncLog = $this->createSyncLog('categories', 'full');

        try {
            $categories = $this->getAllCategories();
            $syncedCount = 0;
            $errors = [];

            foreach ($categories as $categoryData) {
                try {
                    // حفظ الفئات في cache أو جدول منفصل
                    Cache::put("snipeit_category_{$categoryData['id']}", $categoryData, 3600);
                    $syncedCount++;
                } catch (Exception $e) {
                    $errors[] = [
                        'category_id' => $categoryData['id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            $this->updateSyncLog($syncLog, 'completed', [
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'duration' => now()->diffInSeconds($startTime)
            ]);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'duration' => now()->diffInSeconds($startTime)
            ];

        } catch (Exception $e) {
            $this->updateSyncLog($syncLog, 'failed', [
                'error' => $e->getMessage(),
                'duration' => now()->diffInSeconds($startTime)
            ]);

            Log::error('Categories sync failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * جلب جميع الأصول من Snipe-IT
     */
    public function getAllAssets(): array
    {
        $assets = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json'
                ])
                ->get($this->apiUrl . '/api/v1/hardware', [
                    'limit' => $perPage,
                    'offset' => ($page - 1) * $perPage
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $assets = array_merge($assets, $data['rows'] ?? []);
                $page++;
            } else {
                throw new Exception('Failed to fetch assets: ' . $response->body());
            }
        } while (count($assets) % $perPage === 0);

        return $assets;
    }

    /**
     * جلب الأصول المحدثة منذ وقت معين
     */
    public function getAssetsSince(Carbon $since): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ])
            ->get($this->apiUrl . '/api/v1/hardware', [
                'updated_at' => [
                    'operator' => '>=',
                    'value' => $since->toISOString()
                ]
            ]);

        if ($response->successful()) {
            return $response->json()['rows'] ?? [];
        }

        throw new Exception('Failed to fetch updated assets: ' . $response->body());
    }

    /**
     * جلب جميع المستخدمين من Snipe-IT
     */
    public function getAllUsers(): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ])
            ->get($this->apiUrl . '/api/v1/users');

        if ($response->successful()) {
            return $response->json()['rows'] ?? [];
        }

        throw new Exception('Failed to fetch users: ' . $response->body());
    }

    /**
     * جلب جميع الفئات من Snipe-IT
     */
    public function getAllCategories(): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ])
            ->get($this->apiUrl . '/api/v1/categories');

        if ($response->successful()) {
            return $response->json()['rows'] ?? [];
        }

        throw new Exception('Failed to fetch categories: ' . $response->body());
    }

    /**
     * جلب تفاصيل أصل محدد
     */
    public function getAssetDetails(int $assetId): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ])
            ->get($this->apiUrl . "/api/v1/hardware/{$assetId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Failed to fetch asset details: ' . $response->body());
    }

    /**
     * تحديث أصل في Snipe-IT
     */
    public function updateAsset(int $assetId, array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->put($this->apiUrl . "/api/v1/hardware/{$assetId}", $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Failed to update asset: ' . $response->body());
    }

    /**
     * إنشاء أصل جديد في Snipe-IT
     */
    public function createAsset(array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->post($this->apiUrl . '/api/v1/hardware', $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Failed to create asset: ' . $response->body());
    }

    /**
     * حذف أصل من Snipe-IT
     */
    public function deleteAsset(int $assetId): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ])
            ->delete($this->apiUrl . "/api/v1/hardware/{$assetId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('Failed to delete asset: ' . $response->body());
    }

    /**
     * إنشاء أصل محلي من بيانات Snipe-IT
     */
    protected function createLocalAsset(array $assetData): Asset
    {
        return Asset::create([
            'snipeit_id' => $assetData['id'],
            'name' => $assetData['name'],
            'asset_tag' => $assetData['asset_tag'],
            'serial' => $assetData['serial'] ?? null,
            'model_id' => $assetData['model']['id'] ?? null,
            'model_name' => $assetData['model']['name'] ?? null,
            'status_id' => $assetData['status_label']['id'] ?? null,
            'status_name' => $assetData['status_label']['name'] ?? null,
            'assigned_to' => $assetData['assigned_to'] ?? null,
            'location_id' => $assetData['location']['id'] ?? null,
            'location_name' => $assetData['location']['name'] ?? null,
            'notes' => $assetData['notes'] ?? null,
            'purchase_date' => $assetData['purchase_date'] ? Carbon::parse($assetData['purchase_date']) : null,
            'purchase_cost' => $assetData['purchase_cost'] ?? null,
            'supplier_id' => $assetData['supplier']['id'] ?? null,
            'supplier_name' => $assetData['supplier']['name'] ?? null,
            'order_number' => $assetData['order_number'] ?? null,
            'warranty_months' => $assetData['warranty_months'] ?? null,
            'requestable' => $assetData['requestable'] ?? false,
            'last_checkout' => $assetData['last_checkout'] ? Carbon::parse($assetData['last_checkout']) : null,
            'last_checkin' => $assetData['last_checkin'] ? Carbon::parse($assetData['last_checkin']) : null,
            'expected_checkin' => $assetData['expected_checkin'] ? Carbon::parse($assetData['expected_checkin']) : null,
            'created_at' => $assetData['created_at'] ? Carbon::parse($assetData['created_at']) : now(),
            'updated_at' => $assetData['updated_at'] ? Carbon::parse($assetData['updated_at']) : now(),
        ]);
    }

    /**
     * تحديث أصل محلي من بيانات Snipe-IT
     */
    protected function updateLocalAsset(Asset $asset, array $assetData): Asset
    {
        $asset->update([
            'name' => $assetData['name'],
            'asset_tag' => $assetData['asset_tag'],
            'serial' => $assetData['serial'] ?? null,
            'model_id' => $assetData['model']['id'] ?? null,
            'model_name' => $assetData['model']['name'] ?? null,
            'status_id' => $assetData['status_label']['id'] ?? null,
            'status_name' => $assetData['status_label']['name'] ?? null,
            'assigned_to' => $assetData['assigned_to'] ?? null,
            'location_id' => $assetData['location']['id'] ?? null,
            'location_name' => $assetData['location']['name'] ?? null,
            'notes' => $assetData['notes'] ?? null,
            'purchase_date' => $assetData['purchase_date'] ? Carbon::parse($assetData['purchase_date']) : null,
            'purchase_cost' => $assetData['purchase_cost'] ?? null,
            'supplier_id' => $assetData['supplier']['id'] ?? null,
            'supplier_name' => $assetData['supplier']['name'] ?? null,
            'order_number' => $assetData['order_number'] ?? null,
            'warranty_months' => $assetData['warranty_months'] ?? null,
            'requestable' => $assetData['requestable'] ?? false,
            'last_checkout' => $assetData['last_checkout'] ? Carbon::parse($assetData['last_checkout']) : null,
            'last_checkin' => $assetData['last_checkin'] ? Carbon::parse($assetData['last_checkin']) : null,
            'expected_checkin' => $assetData['expected_checkin'] ? Carbon::parse($assetData['expected_checkin']) : null,
            'updated_at' => $assetData['updated_at'] ? Carbon::parse($assetData['updated_at']) : now(),
        ]);

        return $asset;
    }

    /**
     * إنشاء مستخدم محلي من بيانات Snipe-IT
     */
    protected function createLocalUser(array $userData): User
    {
        return User::create([
            'snipeit_id' => $userData['id'],
            'username' => $userData['username'],
            'full_name' => $userData['first_name'] . ' ' . $userData['last_name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'] ?? null,
            'department_id' => $userData['department']['id'] ?? null,
            'location_id' => $userData['location']['id'] ?? null,
            'manager_id' => $userData['manager']['id'] ?? null,
            'employee_num' => $userData['employee_num'] ?? null,
            'notes' => $userData['notes'] ?? null,
            'activated' => $userData['activated'] ?? true,
            'created_at' => $userData['created_at'] ? Carbon::parse($userData['created_at']) : now(),
            'updated_at' => $userData['updated_at'] ? Carbon::parse($userData['updated_at']) : now(),
        ]);
    }

    /**
     * تحديث مستخدم محلي من بيانات Snipe-IT
     */
    protected function updateLocalUser(User $user, array $userData): User
    {
        $user->update([
            'username' => $userData['username'],
            'full_name' => $userData['first_name'] . ' ' . $userData['last_name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'] ?? null,
            'department_id' => $userData['department']['id'] ?? null,
            'location_id' => $userData['location']['id'] ?? null,
            'manager_id' => $userData['manager']['id'] ?? null,
            'employee_num' => $userData['employee_num'] ?? null,
            'notes' => $userData['notes'] ?? null,
            'activated' => $userData['activated'] ?? true,
            'updated_at' => $userData['updated_at'] ? Carbon::parse($userData['updated_at']) : now(),
        ]);

        return $user;
    }

    /**
     * إنشاء سجل مزامنة
     */
    protected function createSyncLog(string $type, string $syncType): SnipeItSyncLog
    {
        return SnipeItSyncLog::create([
            'type' => $type,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now(),
            'user_id' => Auth::id()
        ]);
    }

    /**
     * تحديث سجل المزامنة
     */
    protected function updateSyncLog(SnipeItSyncLog $syncLog, string $status, array $data = []): void
    {
        // Normalize duration to a non-negative integer (seconds)
        if (array_key_exists('duration', $data)) {
            $data['duration'] = (int) round(abs((float) $data['duration']));
        } else {
            $data['duration'] = (int) now()->diffInSeconds($syncLog->started_at, true);
        }

        $syncLog->update(array_merge([
            'status' => $status,
            'completed_at' => now(),
        ], $data));
    }

    /**
     * جلب وقت آخر مزامنة
     */
    protected function getLastSyncTime(string $type): Carbon
    {
        $lastSync = SnipeItSyncLog::where('type', $type)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();

        return $lastSync ? $lastSync->completed_at : Carbon::now()->subDays(30);
    }

    /**
     * جلب إحصائيات التكامل
     */
    public function getIntegrationStats(): array
    {
        $totalAssets = Asset::count();
        // Some environments may not have the snipeit_id column yet – guard it
        $syncedAssets = \Illuminate\Support\Facades\Schema::hasColumn('assets', 'snipeit_id')
            ? Asset::whereNotNull('snipeit_id')->count()
            : 0;
        $lastSync = SnipeItSyncLog::orderBy('completed_at', 'desc')->first();

        return [
            'total_assets' => $totalAssets,
            'synced_assets' => $syncedAssets,
            'sync_percentage' => $totalAssets > 0 ? round(($syncedAssets / $totalAssets) * 100, 2) : 0,
            'last_sync' => $lastSync ? $lastSync->completed_at : null,
            'last_sync_type' => $lastSync ? $lastSync->type : null,
            'connection_status' => $this->testConnection()['connected'] ?? false
        ];
    }

    /**
     * جلب سجل المزامنة
     */
    public function getSyncLogs(int $perPage = 15, int $page = 1): array
    {
        $logs = SnipeItSyncLog::orderBy('started_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem()
            ]
        ];
    }

    /**
     * جلب آخر عمليات المزامنة
     */
    public function getRecentSyncs(int $limit = 10): array
    {
        return SnipeItSyncLog::orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * جلب إعدادات التكامل
     */
    public function getSettings(): array
    {
        return [
            'api_url' => config('snipeit.api_url'),
            'api_token' => config('snipeit.api_token') ? '***' . substr(config('snipeit.api_token'), -4) : null,
            'auto_sync_enabled' => config('snipeit.auto_sync_enabled', false),
            'sync_interval' => config('snipeit.sync_interval', 60),
            'sync_assets' => config('snipeit.sync_assets', true),
            'sync_users' => config('snipeit.sync_users', true),
            'sync_categories' => config('snipeit.sync_categories', true),
            'sync_locations' => config('snipeit.sync_locations', true),
            'sync_models' => config('snipeit.sync_models', true),
            'sync_suppliers' => config('snipeit.sync_suppliers', true),
            'webhook_enabled' => config('snipeit.webhook_enabled', false),
            'webhook_url' => config('snipeit.webhook_url')
        ];
    }

    /**
     * حفظ إعدادات التكامل
     */
    public function saveSettings(array $settings): void
    {
        $configFile = config_path('snipeit.php');
        
        $config = [
            'api_url' => $settings['api_url'],
            'api_token' => $settings['api_token'],
            'auto_sync_enabled' => $settings['auto_sync_enabled'] ?? false,
            'sync_interval' => $settings['sync_interval'] ?? 60,
            'sync_assets' => $settings['sync_assets'] ?? true,
            'sync_users' => $settings['sync_users'] ?? true,
            'sync_categories' => $settings['sync_categories'] ?? true,
            'sync_locations' => $settings['sync_locations'] ?? true,
            'sync_models' => $settings['sync_models'] ?? true,
            'sync_suppliers' => $settings['sync_suppliers'] ?? true,
            'webhook_enabled' => $settings['webhook_enabled'] ?? false,
            'webhook_url' => $settings['webhook_url'] ?? null
        ];

        file_put_contents($configFile, '<?php return ' . var_export($config, true) . ';');
    }

    /**
     * إعادة تعيين إعدادات التكامل
     */
    public function resetSettings(): void
    {
        $configFile = config_path('snipeit.php');
        
        $defaultConfig = [
            'api_url' => 'http://127.0.0.1',
            'api_token' => null,
            'auto_sync_enabled' => false,
            'sync_interval' => 60,
            'sync_assets' => true,
            'sync_users' => true,
            'sync_categories' => true,
            'sync_locations' => true,
            'sync_models' => true,
            'sync_suppliers' => true,
            'webhook_enabled' => false,
            'webhook_url' => null
        ];

        file_put_contents($configFile, '<?php return ' . var_export($defaultConfig, true) . ';');
    }
}
