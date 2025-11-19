<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action to the audit log.
     */
    public function logAction(
        ?User $user = null,
        string $actionType = 'unknown',
        string $module = 'unknown',
        ?int $recordId = null,
        ?string $recordName = null,
        array $details = [],
        string $status = 'success',
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        $user = $user ?? Auth::user();
        $ipAddress = $ipAddress ?? Request::ip();
        $userAgent = $userAgent ?? Request::userAgent();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_role' => $user?->role?->name ?? 'غير محدد',
            'action_type' => $actionType,
            'module' => $module,
            'record_id' => $recordId,
            'record_name' => $recordName,
            'details' => $details,
            'ip_address' => $ipAddress,
            'device_info' => $this->extractDeviceInfo($userAgent),
            'user_agent' => $userAgent,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log a user login.
     */
    public function logLogin(User $user, bool $success = true): AuditLog
    {
        return $this->logAction(
            user: $user,
            actionType: 'login',
            module: 'authentication',
            details: [
                'login_attempt' => true,
                'success' => $success,
                'timestamp' => now()->toISOString(),
            ],
            status: $success ? 'success' : 'failed'
        );
    }

    /**
     * Log a user logout.
     */
    public function logLogout(User $user): AuditLog
    {
        return $this->logAction(
            user: $user,
            actionType: 'logout',
            module: 'authentication',
            details: [
                'logout_timestamp' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log a CRUD operation.
     */
    public function logCrudOperation(
        string $action,
        string $module,
        $model = null,
        array $oldData = [],
        array $newData = [],
        ?User $user = null
    ): AuditLog {
        $details = [];

        if ($action === 'update' && !empty($oldData) && !empty($newData)) {
            $details['changes'] = $this->getChanges($oldData, $newData);
        } elseif ($action === 'create' && !empty($newData)) {
            $details['created_data'] = $newData;
        } elseif ($action === 'delete' && !empty($oldData)) {
            $details['deleted_data'] = $oldData;
        }

        if ($model) {
            $details['model_class'] = get_class($model);
            if (method_exists($model, 'getKey')) {
                $details['model_id'] = $model->getKey();
            }
        }

        return $this->logAction(
            user: $user,
            actionType: $action,
            module: $module,
            recordId: $model?->getKey(),
            recordName: $this->getModelName($model),
            details: $details
        );
    }

    /**
     * Log a system event.
     */
    public function logSystemEvent(
        string $event,
        array $details = [],
        ?User $user = null
    ): AuditLog {
        return $this->logAction(
            user: $user,
            actionType: 'system_event',
            module: 'system',
            details: array_merge($details, [
                'event' => $event,
                'timestamp' => now()->toISOString(),
            ])
        );
    }

    /**
     * Log an error.
     */
    public function logError(
        string $error,
        array $context = [],
        ?User $user = null
    ): AuditLog {
        return $this->logAction(
            user: $user,
            actionType: 'error',
            module: 'system',
            details: array_merge($context, [
                'error' => $error,
                'timestamp' => now()->toISOString(),
            ]),
            status: 'failed'
        );
    }

    /**
     * Extract device information from user agent.
     */
    protected function extractDeviceInfo(string $userAgent): string
    {
        $deviceInfo = [];

        // Detect browser
        if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera)\/([0-9.]+)/', $userAgent, $matches)) {
            $deviceInfo[] = $matches[1] . ' ' . $matches[2];
        }

        // Detect operating system
        if (preg_match('/(Windows|Mac|Linux|Android|iOS)/', $userAgent, $matches)) {
            $deviceInfo[] = $matches[1];
        }

        // Detect mobile device
        if (preg_match('/(Mobile|Tablet|iPad|iPhone|Android)/', $userAgent)) {
            $deviceInfo[] = 'Mobile';
        }

        return implode(', ', $deviceInfo) ?: 'Unknown Device';
    }

    /**
     * Get changes between old and new data.
     */
    protected function getChanges(array $oldData, array $newData): array
    {
        $changes = [];

        foreach ($newData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get a human-readable name for a model.
     */
    protected function getModelName($model): ?string
    {
        if (!$model) {
            return null;
        }

        if (method_exists($model, 'name')) {
            return $model->name;
        }

        if (method_exists($model, 'title')) {
            return $model->title;
        }

        if (method_exists($model, 'getKey')) {
            return '#' . $model->getKey();
        }

        return null;
    }

    /**
     * Clean up old audit logs.
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get audit statistics.
     */
    public function getStatistics(array $filters = []): array
    {
        $query = AuditLog::query();

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('created_at', [
                $filters['date_from'],
                $filters['date_to']
            ]);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (isset($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }

        return [
            'total_actions' => $query->count(),
            'successful_actions' => $query->clone()->where('status', 'success')->count(),
            'failed_actions' => $query->clone()->where('status', 'failed')->count(),
            'unique_users' => $query->clone()->distinct('user_id')->count('user_id'),
            'most_active_user' => $query->clone()
                ->selectRaw('user_name, COUNT(*) as action_count')
                ->whereNotNull('user_name')
                ->groupBy('user_name')
                ->orderBy('action_count', 'desc')
                ->first(),
            'top_action_type' => $query->clone()
                ->selectRaw('action_type, COUNT(*) as action_count')
                ->groupBy('action_type')
                ->orderBy('action_count', 'desc')
                ->first(),
            'top_module' => $query->clone()
                ->selectRaw('module, COUNT(*) as action_count')
                ->groupBy('module')
                ->orderBy('action_count', 'desc')
                ->first(),
        ];
    }
}

