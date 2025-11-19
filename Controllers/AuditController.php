<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Display the audit logs page.
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('user_id')) {
            $query->user($request->user_id);
        }

        if ($request->filled('action_type')) {
            $query->actionType($request->action_type);
        }

        if ($request->filled('module')) {
            $query->module($request->module);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->date_from)->startOfDay();
            $endDate = Carbon::parse($request->date_to)->endOfDay();
            $query->dateRange($startDate, $endDate);
        }

        $logs = $query->paginate(50);

        // Get filter options
        $users = User::select('id', 'name')->orderBy('name')->get();
        $actionTypes = AuditLog::distinct()->pluck('action_type')->sort()->values();
        $modules = AuditLog::distinct()->pluck('module')->sort()->values();

        // Get statistics
        $stats = $this->getStatistics($request);

        return view('audit.index', compact('logs', 'users', 'actionTypes', 'modules', 'stats'));
    }

    /**
     * Get audit log details.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $auditLog->id,
                'user_name' => $auditLog->user_name,
                'user_role' => $auditLog->user_role,
                'action_type' => $auditLog->formatted_action_type,
                'module' => $auditLog->formatted_module,
                'record_id' => $auditLog->record_id,
                'record_name' => $auditLog->record_name,
                'details' => $auditLog->details,
                'ip_address' => $auditLog->ip_address,
                'device_info' => $auditLog->device_info,
                'user_agent' => $auditLog->user_agent,
                'status' => $auditLog->status_text,
                'status_class' => $auditLog->status_badge_class,
                'created_at' => $auditLog->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $auditLog->created_at->diffForHumans(),
            ]
        ]);
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('user_id')) {
            $query->user($request->user_id);
        }

        if ($request->filled('action_type')) {
            $query->actionType($request->action_type);
        }

        if ($request->filled('module')) {
            $query->module($request->module);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->date_from)->startOfDay();
            $endDate = Carbon::parse($request->date_to)->endOfDay();
            $query->dateRange($startDate, $endDate);
        }

        $logs = $query->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                'التاريخ والوقت',
                'اسم المستخدم',
                'الدور',
                'نوع العملية',
                'الوحدة',
                'معرف السجل',
                'اسم السجل',
                'التفاصيل',
                'عنوان IP',
                'معلومات الجهاز',
                'الحالة'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name,
                    $log->user_role,
                    $log->formatted_action_type,
                    $log->formatted_module,
                    $log->record_id,
                    $log->record_name,
                    is_array($log->details) ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : $log->details,
                    $log->ip_address,
                    $log->device_info,
                    $log->status_text
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit statistics.
     */
    public function getStatistics(Request $request): array
    {
        $query = AuditLog::query();

        // Apply date filter if provided
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->date_from)->startOfDay();
            $endDate = Carbon::parse($request->date_to)->endOfDay();
            $query->dateRange($startDate, $endDate);
        } else {
            // Default to last 30 days
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $totalActions = $query->count();
        $successfulActions = $query->clone()->where('status', 'success')->count();
        $failedActions = $query->clone()->where('status', 'failed')->count();

        $mostActiveUser = $query->clone()
            ->selectRaw('user_name, COUNT(*) as action_count')
            ->whereNotNull('user_name')
            ->groupBy('user_name')
            ->orderBy('action_count', 'desc')
            ->first();

        $topActionType = $query->clone()
            ->selectRaw('action_type, COUNT(*) as action_count')
            ->groupBy('action_type')
            ->orderBy('action_count', 'desc')
            ->first();

        $topModule = $query->clone()
            ->selectRaw('module, COUNT(*) as action_count')
            ->groupBy('module')
            ->orderBy('action_count', 'desc')
            ->first();

        return [
            'total_actions' => $totalActions,
            'successful_actions' => $successfulActions,
            'failed_actions' => $failedActions,
            'most_active_user' => $mostActiveUser,
            'top_action_type' => $topActionType,
            'top_module' => $topModule,
        ];
    }

    /**
     * Get audit logs for API.
     */
    public function api(Request $request): JsonResponse
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('user_id')) {
            $query->user($request->user_id);
        }

        if ($request->filled('action_type')) {
            $query->actionType($request->action_type);
        }

        if ($request->filled('module')) {
            $query->module($request->module);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->date_from)->startOfDay();
            $endDate = Carbon::parse($request->date_to)->endOfDay();
            $query->dateRange($startDate, $endDate);
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }
}
