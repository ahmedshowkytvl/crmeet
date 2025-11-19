<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Notification;
use Carbon\Carbon;

class SnipeItIntegrationService
{
    protected $baseUrl;
    protected $apiToken;
    protected $timeout;
    protected $retryAttempts;

    public function __construct()
    {
        $this->baseUrl = config('snipeit.base_url', 'http://127.0.0.1');
        $this->apiToken = config('snipeit.api_token');
        $this->timeout = config('snipeit.timeout', 30);
        $this->retryAttempts = config('snipeit.retry_attempts', 3);
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
                    'Content-Type' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/v1/statuslabels');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بنجاح مع Snipe-IT',
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل الاتصال مع Snipe-IT: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في الاتصال مع Snipe-IT: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * جلب جميع الأصول من Snipe-IT
     */
    public function getAllAssets(array $filters = []): array
    {
        try {
            $params = array_merge([
                'limit' => 1000,
                'offset' => 0,
                'sort' => 'created_at',
                'order' => 'desc'
            ], $filters);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/v1/hardware', $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['rows'] ?? [],
                    'total' => $data['total'] ?? 0,
                    'message' => 'تم جلب الأصول بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في جلب الأصول: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Get Assets Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب الأصول: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * جلب أصل محدد من Snipe-IT
     */
    public function getAsset(int $assetId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/v1/hardware/' . $assetId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'تم جلب الأصل بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في جلب الأصل: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Get Asset Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب الأصل: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إنشاء أصل جديد في Snipe-IT
     */
    public function createAsset(array $assetData): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/v1/hardware', $assetData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'تم إنشاء الأصل بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في إنشاء الأصل: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Create Asset Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء الأصل: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * تحديث أصل في Snipe-IT
     */
    public function updateAsset(int $assetId, array $assetData): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->put($this->baseUrl . '/api/v1/hardware/' . $assetId, $assetData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'تم تحديث الأصل بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في تحديث الأصل: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Update Asset Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في تحديث الأصل: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * حذف أصل من Snipe-IT
     */
    public function deleteAsset(int $assetId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->delete($this->baseUrl . '/api/v1/hardware/' . $assetId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'تم حذف الأصل بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في حذف الأصل: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Delete Asset Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في حذف الأصل: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * جلب المستخدمين من Snipe-IT
     */
    public function getUsers(array $filters = []): array
    {
        try {
            $params = array_merge([
                'limit' => 1000,
                'offset' => 0,
                'sort' => 'first_name',
                'order' => 'asc'
            ], $filters);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/v1/users', $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['rows'] ?? [],
                    'total' => $data['total'] ?? 0,
                    'message' => 'تم جلب المستخدمين بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في جلب المستخدمين: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Get Users Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب المستخدمين: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * جلب التعيينات من Snipe-IT
     */
    public function getAssetAssignments(array $filters = []): array
    {
        try {
            $params = array_merge([
                'limit' => 1000,
                'offset' => 0,
                'sort' => 'created_at',
                'order' => 'desc'
            ], $filters);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/v1/asset-maintenances', $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['rows'] ?? [],
                    'total' => $data['total'] ?? 0,
                    'message' => 'تم جلب التعيينات بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في جلب التعيينات: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Get Assignments Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب التعيينات: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * مزامنة الأصول مع النظام المحلي
     */
    public function syncAssets(): array
    {
        try {
            $result = $this->getAllAssets();
            
            if (!$result['success']) {
                return $result;
            }

            $assets = $result['data'];
            $syncedCount = 0;
            $errors = [];

            foreach ($assets as $assetData) {
                try {
                    // البحث عن الأصل في النظام المحلي
                    $localAsset = Asset::where('snipeit_id', $assetData['id'])->first();
                    
                    if (!$localAsset) {
                        // إنشاء أصل جديد
                        $localAsset = Asset::create([
                            'snipeit_id' => $assetData['id'],
                            'name' => $assetData['name'] ?? 'غير محدد',
                            'asset_tag' => $assetData['asset_tag'] ?? '',
                            'serial' => $assetData['serial'] ?? '',
                            'model' => $assetData['model']['name'] ?? '',
                            'category' => $assetData['category']['name'] ?? '',
                            'status' => $assetData['status_label']['name'] ?? 'غير محدد',
                            'location' => $assetData['location']['name'] ?? '',
                            'purchase_date' => $assetData['purchase_date'] ? Carbon::parse($assetData['purchase_date']) : null,
                            'purchase_cost' => $assetData['purchase_cost'] ?? 0,
                            'notes' => $assetData['notes'] ?? '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        // تحديث الأصل الموجود
                        $localAsset->update([
                            'name' => $assetData['name'] ?? $localAsset->name,
                            'asset_tag' => $assetData['asset_tag'] ?? $localAsset->asset_tag,
                            'serial' => $assetData['serial'] ?? $localAsset->serial,
                            'model' => $assetData['model']['name'] ?? $localAsset->model,
                            'category' => $assetData['category']['name'] ?? $localAsset->category,
                            'status' => $assetData['status_label']['name'] ?? $localAsset->status,
                            'location' => $assetData['location']['name'] ?? $localAsset->location,
                            'purchase_date' => $assetData['purchase_date'] ? Carbon::parse($assetData['purchase_date']) : $localAsset->purchase_date,
                            'purchase_cost' => $assetData['purchase_cost'] ?? $localAsset->purchase_cost,
                            'notes' => $assetData['notes'] ?? $localAsset->notes,
                            'updated_at' => now(),
                        ]);
                    }

                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = "خطأ في مزامنة الأصل {$assetData['id']}: " . $e->getMessage();
                    Log::error("Asset Sync Error for ID {$assetData['id']}: " . $e->getMessage());
                }
            }

            return [
                'success' => true,
                'message' => "تم مزامنة {$syncedCount} أصل بنجاح",
                'synced_count' => $syncedCount,
                'total_count' => count($assets),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('Snipe-IT Sync Assets Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في مزامنة الأصول: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * البحث في الأصول
     */
    public function searchAssets(string $query, array $filters = []): array
    {
        try {
            $params = array_merge([
                'search' => $query,
                'limit' => 100,
                'offset' => 0,
                'sort' => 'created_at',
                'order' => 'desc'
            ], $filters);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/v1/hardware', $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data['rows'] ?? [],
                    'total' => $data['total'] ?? 0,
                    'message' => 'تم البحث بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'فشل في البحث: ' . $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Snipe-IT Search Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في البحث: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إرسال إشعار عند تغيير حالة الأصل
     */
    public function notifyAssetStatusChange(array $assetData, string $oldStatus, string $newStatus): void
    {
        try {
            $notification = Notification::create([
                'user_id' => auth()->id(),
                'type' => 'asset',
                'title' => 'تغيير حالة الأصل',
                'body' => "تم تغيير حالة الأصل '{$assetData['name']}' من '{$oldStatus}' إلى '{$newStatus}'",
                'actor_id' => auth()->id(),
                'resource_type' => 'asset',
                'resource_id' => $assetData['id'],
                'link' => "/assets/{$assetData['id']}",
                'metadata' => [
                    'asset_name' => $assetData['name'],
                    'asset_tag' => $assetData['asset_tag'],
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'notification_type' => 'asset_status_change',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Asset status change notification created', [
                'notification_id' => $notification->id,
                'asset_id' => $assetData['id'],
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create asset status change notification', [
                'error' => $e->getMessage(),
                'asset_id' => $assetData['id'],
            ]);
        }
    }

    /**
     * الحصول على إحصائيات Snipe-IT
     */
    public function getStatistics(): array
    {
        try {
            $cacheKey = 'snipeit_statistics_' . date('Y-m-d-H');
            $cachedStats = Cache::get($cacheKey);
            
            if ($cachedStats) {
                return $cachedStats;
            }

            $assetsResult = $this->getAllAssets(['limit' => 1]);
            $usersResult = $this->getUsers(['limit' => 1]);

            $stats = [
                'success' => true,
                'data' => [
                    'total_assets' => $assetsResult['total'] ?? 0,
                    'total_users' => $usersResult['total'] ?? 0,
                    'last_sync' => now()->toISOString(),
                    'connection_status' => 'connected'
                ],
                'message' => 'تم جلب الإحصائيات بنجاح'
            ];

            // تخزين في الكاش لمدة ساعة
            Cache::put($cacheKey, $stats, 3600);

            return $stats;
        } catch (\Exception $e) {
            Log::error('Snipe-IT Statistics Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب الإحصائيات: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}
