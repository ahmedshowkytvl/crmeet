<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ZohoApiClient;
use App\Services\ZohoSyncService;
use Illuminate\Http\Request;

class ZohoAdminController extends Controller
{
    protected $apiClient;
    protected $syncService;

    public function __construct(ZohoApiClient $apiClient, ZohoSyncService $syncService)
    {
        $this->apiClient = $apiClient;
        $this->syncService = $syncService;
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        $users = User::with(['zohoStats' => function($q) {
            $q->monthly()->orderBy('period_date', 'desc')->limit(1);
        }])->get();

        $zohoEnabled = $users->where('is_zoho_enabled', true)->count();
        $totalUsers = $users->count();

        // Test Zoho connection
        $connectionStatus = $this->apiClient->testConnection();

        return view('zoho.admin.index', compact('users', 'zohoEnabled', 'totalUsers', 'connectionStatus'));
    }

    /**
     * Auto-map users to Zoho agents
     */
    public function autoMapUsers(Request $request)
    {
        try {
            $count = $this->syncService->autoMapUsers();

            return back()->with('success', "تم ربط {$count} موظف بنجاح");
        } catch (\Exception $e) {
            \Log::error('Auto-map failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء الربط التلقائي: ' . $e->getMessage());
        }
    }

    /**
     * Manually map user to Zoho agent
     */
    public function mapUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'zoho_agent_name' => 'required|string',
            'zoho_agent_id' => 'nullable|string',
            'zoho_email' => 'nullable|email',
        ]);

        try {
            $this->syncService->mapUser(
                $request->user_id,
                $request->zoho_agent_name,
                $request->zoho_agent_id,
                $request->zoho_email
            );

            return back()->with('success', 'تم ربط الموظف بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الربط: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Zoho status for user
     */
    public function toggleUser(Request $request, User $user)
    {
        try {
            if ($user->is_zoho_enabled) {
                // Disable Zoho for this user
                $this->syncService->unmapUser($user->id);
                $message = 'تم تعطيل Zoho للموظف';
            } else {
                // Enable Zoho (requires agent name)
                if (empty($user->zoho_agent_name)) {
                    return back()->with('error', 'يجب إضافة اسم Agent أولاً');
                }

                $user->update([
                    'is_zoho_enabled' => true,
                    'zoho_linked_at' => now(),
                ]);
                $message = 'تم تفعيل Zoho للموظف';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Test Zoho connection
     */
    public function testConnection()
    {
        try {
            $isConnected = $this->apiClient->testConnection();

            if ($isConnected) {
                return back()->with('success', 'الاتصال بـ Zoho ناجح ✅');
            } else {
                return back()->with('error', 'فشل الاتصال بـ Zoho ❌');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في الاتصال: ' . $e->getMessage());
        }
    }

    /**
     * Trigger manual sync
     */
    public function syncNow(Request $request)
    {
        try {
            $result = $this->syncService->syncTickets();

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('warning', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء المزامنة: ' . $e->getMessage());
        }
    }

    /**
     * Get Zoho agents list (API)
     */
    public function getAgents()
    {
        try {
            $agents = $this->apiClient->getAgents();

            if ($agents && isset($agents['data'])) {
                return response()->json([
                    'success' => true,
                    'data' => $agents['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agents'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

