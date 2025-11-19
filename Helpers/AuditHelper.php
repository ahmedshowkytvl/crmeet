<?php

namespace App\Helpers;

use App\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Facades\App;

class AuditHelper
{
    /**
     * Log an action using the audit service.
     */
    public static function logAction(
        ?User $user = null,
        string $actionType = 'unknown',
        string $module = 'unknown',
        ?int $recordId = null,
        ?string $recordName = null,
        array $details = [],
        string $status = 'success',
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        $auditService = App::make(AuditService::class);
        
        return $auditService->logAction(
            user: $user,
            actionType: $actionType,
            module: $module,
            recordId: $recordId,
            recordName: $recordName,
            details: $details,
            status: $status,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );
    }

    /**
     * Log a CRUD operation.
     */
    public static function logCrud(
        string $action,
        string $module,
        $model = null,
        array $oldData = [],
        array $newData = [],
        ?User $user = null
    ) {
        $auditService = App::make(AuditService::class);
        
        return $auditService->logCrudOperation(
            action: $action,
            module: $module,
            model: $model,
            oldData: $oldData,
            newData: $newData,
            user: $user
        );
    }

    /**
     * Log a user login.
     */
    public static function logLogin(User $user, bool $success = true)
    {
        $auditService = App::make(AuditService::class);
        
        return $auditService->logLogin($user, $success);
    }

    /**
     * Log a user logout.
     */
    public static function logLogout(User $user)
    {
        $auditService = App::make(AuditService::class);
        
        return $auditService->logLogout($user);
    }

    /**
     * Log a system event.
     */
    public static function logSystemEvent(string $event, array $details = [], ?User $user = null)
    {
        $auditService = App::make(AuditService::class);
        
        return $auditService->logSystemEvent($event, $details, $user);
    }

    /**
     * Log an error.
     */
    public static function logError(string $error, array $context = [], ?User $user = null)
    {
        $auditService = App::make(AuditService::class);
        
        return $auditService->logError($error, $context, $user);
    }

    /**
     * Log a successful operation.
     */
    public static function logSuccess(
        string $action,
        string $module,
        ?string $message = null,
        ?User $user = null,
        $model = null
    ) {
        return self::logAction(
            user: $user,
            actionType: $action,
            module: $module,
            recordId: $model?->getKey(),
            recordName: self::getModelName($model),
            details: $message ? ['message' => $message] : [],
            status: 'success'
        );
    }

    /**
     * Log a failed operation.
     */
    public static function logFailure(
        string $action,
        string $module,
        string $error,
        ?User $user = null,
        $model = null
    ) {
        return self::logAction(
            user: $user,
            actionType: $action,
            module: $module,
            recordId: $model?->getKey(),
            recordName: self::getModelName($model),
            details: ['error' => $error],
            status: 'failed'
        );
    }

    /**
     * Log a view action.
     */
    public static function logView(
        string $module,
        $model = null,
        ?User $user = null
    ) {
        return self::logAction(
            user: $user,
            actionType: 'view',
            module: $module,
            recordId: $model?->getKey(),
            recordName: self::getModelName($model)
        );
    }

    /**
     * Log an export action.
     */
    public static function logExport(
        string $module,
        string $format = 'csv',
        ?User $user = null,
        array $filters = []
    ) {
        return self::logAction(
            user: $user,
            actionType: 'export',
            module: $module,
            details: [
                'format' => $format,
                'filters' => $filters,
                'timestamp' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log an import action.
     */
    public static function logImport(
        string $module,
        int $recordCount = 0,
        ?User $user = null,
        array $details = []
    ) {
        return self::logAction(
            user: $user,
            actionType: 'import',
            module: $module,
            details: array_merge($details, [
                'record_count' => $recordCount,
                'timestamp' => now()->toISOString(),
            ])
        );
    }

    /**
     * Log a permission check.
     */
    public static function logPermissionCheck(
        string $permission,
        bool $granted,
        ?User $user = null,
        string $resource = null
    ) {
        return self::logAction(
            user: $user,
            actionType: 'permission_check',
            module: 'permissions',
            details: [
                'permission' => $permission,
                'granted' => $granted,
                'resource' => $resource,
                'timestamp' => now()->toISOString(),
            ],
            status: $granted ? 'success' : 'failed'
        );
    }

    /**
     * Get a human-readable name for a model.
     */
    protected static function getModelName($model): ?string
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
     * Log a batch operation.
     */
    public static function logBatchOperation(
        string $action,
        string $module,
        int $recordCount,
        array $recordIds = [],
        ?User $user = null,
        array $details = []
    ) {
        return self::logAction(
            user: $user,
            actionType: $action,
            module: $module,
            details: array_merge($details, [
                'batch_operation' => true,
                'record_count' => $recordCount,
                'record_ids' => $recordIds,
                'timestamp' => now()->toISOString(),
            ])
        );
    }

    /**
     * Log a file operation.
     */
    public static function logFileOperation(
        string $action,
        string $module,
        string $fileName,
        int $fileSize = null,
        ?User $user = null
    ) {
        return self::logAction(
            user: $user,
            actionType: $action,
            module: $module,
            details: [
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'timestamp' => now()->toISOString(),
            ]
        );
    }

    /**
     * Log a configuration change.
     */
    public static function logConfigChange(
        string $configKey,
        $oldValue,
        $newValue,
        ?User $user = null
    ) {
        return self::logAction(
            user: $user,
            actionType: 'config_change',
            module: 'system',
            details: [
                'config_key' => $configKey,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'timestamp' => now()->toISOString(),
            ]
        );
    }
}


