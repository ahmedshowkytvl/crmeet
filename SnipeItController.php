<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\SnipeItService;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SnipeItController extends Controller
{
    protected $snipeItService;

    public function __construct(SnipeItService $snipeItService)
    {
        $this->snipeItService = $snipeItService;
    }

    /**
     * عرض صفحة التكامل مع Snipe-IT
     */
    public function index()
    {
        // التحقق يتم عبر Middleware على المسارات؛ لا نكرر الفحص هنا

        // جلب إحصائيات التكامل
        $stats = $this->snipeItService->getIntegrationStats();
        
        // جلب آخر عمليات المزامنة
        $recentSyncs = $this->snipeItService->getRecentSyncs(10);

        return view('snipe-it.index', compact('stats', 'recentSyncs'));
    }

    /**
     * صفحة اختبار Snipe-IT API
     */
    public function testPage()
    {
        return view('snipe-it.test-page');
    }

    /**
     * جلب بيانات المستخدم الحالي من Snipe-IT
     */
    public function getUser(): JsonResponse
    {
        try {
            $result = $this->snipeItService->getCurrentUser();
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user from Snipe-IT', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب بيانات المستخدم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * اختبار الاتصال مع Snipe-IT
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->snipeItService->testConnection();
            
            return response()->json([
                'success' => true,
                'message' => 'تم اختبار الاتصال بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Snipe-IT connection test failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في اختبار الاتصال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * مزامنة الأصول من Snipe-IT
     */
    public function syncAssets(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sync_type' => 'required|in:full,incremental',
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $syncType = $request->input('sync_type', 'incremental');
            $assetIds = $request->input('asset_ids', []);

            $result = $this->snipeItService->syncAssets($syncType, $assetIds);

            return response()->json([
                'success' => true,
                'message' => 'تمت المزامنة بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Snipe-IT assets sync failed', [
                'error' => $e->getMessage(),
                'sync_type' => $request->input('sync_type'),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في المزامنة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * مزامنة المستخدمين من Snipe-IT
     */
    public function syncUsers(Request $request): JsonResponse
    {
        try {
            $result = $this->snipeItService->syncUsers();

            return response()->json([
                'success' => true,
                'message' => 'تمت مزامنة المستخدمين بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Snipe-IT users sync failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في مزامنة المستخدمين: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * مزامنة الفئات من Snipe-IT
     */
    public function syncCategories(Request $request): JsonResponse
    {
        try {
            $result = $this->snipeItService->syncCategories();

            return response()->json([
                'success' => true,
                'message' => 'تمت مزامنة الفئات بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Snipe-IT categories sync failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في مزامنة الفئات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب تفاصيل أصل محدد من Snipe-IT
     */
    public function getAssetDetails(Request $request, $assetId): JsonResponse
    {
        try {
            $asset = $this->snipeItService->getAssetDetails($assetId);

            return response()->json([
                'success' => true,
                'data' => $asset
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get asset details from Snipe-IT', [
                'asset_id' => $assetId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب تفاصيل الأصل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث أصل في Snipe-IT
     */
    public function updateAsset(Request $request, $assetId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|max:255',
            'model_id' => 'required|integer',
            'status_id' => 'required|integer',
            'assigned_to' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'supplier_id' => 'nullable|integer',
            'order_number' => 'nullable|string',
            'warranty_months' => 'nullable|integer',
            'requestable' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assetData = $request->all();
            $result = $this->snipeItService->updateAsset($assetId, $assetData);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الأصل بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update asset in Snipe-IT', [
                'asset_id' => $assetId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في تحديث الأصل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء أصل جديد في Snipe-IT
     */
    public function createAsset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|max:255',
            'model_id' => 'required|integer',
            'status_id' => 'required|integer',
            'assigned_to' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'supplier_id' => 'nullable|integer',
            'order_number' => 'nullable|string',
            'warranty_months' => 'nullable|integer',
            'requestable' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assetData = $request->all();
            $result = $this->snipeItService->createAsset($assetData);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الأصل بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create asset in Snipe-IT', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الأصل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف أصل من Snipe-IT
     */
    public function deleteAsset(Request $request, $assetId): JsonResponse
    {
        try {
            $result = $this->snipeItService->deleteAsset($assetId);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الأصل بنجاح',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete asset from Snipe-IT', [
                'asset_id' => $assetId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الأصل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب إحصائيات التكامل
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->snipeItService->getIntegrationStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get integration stats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الإحصائيات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب سجل المزامنة
     */
    public function getSyncLogs(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        try {
            $logs = $this->snipeItService->getSyncLogs($perPage, $page);

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sync logs', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب سجل المزامنة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إعدادات التكامل
     */
    public function settings()
    {
        $user = Auth::user();
        
        // التحقق من الصلاحيات (يمكن تعديلها حسب نظام الصلاحيات المستخدم)
        if (!$user) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        $settings = $this->snipeItService->getSettings();

        return view('snipe-it.settings', compact('settings'));
    }

    /**
     * حفظ إعدادات التكامل
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_url' => 'required|url',
            'api_token' => 'required|string',
            'auto_sync_enabled' => 'boolean',
            'sync_interval' => 'required|integer|min:1|max:1440', // دقائق
            'sync_assets' => 'boolean',
            'sync_users' => 'boolean',
            'sync_categories' => 'boolean',
            'sync_locations' => 'boolean',
            'sync_models' => 'boolean',
            'sync_suppliers' => 'boolean',
            'webhook_enabled' => 'boolean',
            'webhook_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $settings = $request->all();
            $this->snipeItService->saveSettings($settings);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإعدادات بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save Snipe-IT settings', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في حفظ الإعدادات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إعادة تعيين إعدادات التكامل
     */
    public function resetSettings(): JsonResponse
    {
        try {
            $this->snipeItService->resetSettings();

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة تعيين الإعدادات بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reset Snipe-IT settings', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل في إعادة تعيين الإعدادات: ' . $e->getMessage()
            ], 500);
        }
    }
}
