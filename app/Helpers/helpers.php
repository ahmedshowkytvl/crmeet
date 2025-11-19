<?php

use App\Helpers\AuditHelper;

if (!function_exists('audit_log')) {
    /**
     * Log an action to the audit log.
     */
    function audit_log(
        ?App\Models\User $user = null,
        string $actionType = 'unknown',
        string $module = 'unknown',
        ?int $recordId = null,
        ?string $recordName = null,
        array $details = [],
        string $status = 'success',
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        return AuditHelper::logAction(
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
}

if (!function_exists('audit_crud')) {
    /**
     * Log a CRUD operation.
     */
    function audit_crud(
        string $action,
        string $module,
        $model = null,
        array $oldData = [],
        array $newData = [],
        ?App\Models\User $user = null
    ) {
        return AuditHelper::logCrud(
            action: $action,
            module: $module,
            model: $model,
            oldData: $oldData,
            newData: $newData,
            user: $user
        );
    }
}

if (!function_exists('audit_login')) {
    /**
     * Log a user login.
     */
    function audit_login(App\Models\User $user, bool $success = true)
    {
        return AuditHelper::logLogin($user, $success);
    }
}

if (!function_exists('audit_logout')) {
    /**
     * Log a user logout.
     */
    function audit_logout(App\Models\User $user)
    {
        return AuditHelper::logLogout($user);
    }
}

if (!function_exists('audit_success')) {
    /**
     * Log a successful operation.
     */
    function audit_success(
        string $action,
        string $module,
        ?string $message = null,
        ?App\Models\User $user = null,
        $model = null
    ) {
        return AuditHelper::logSuccess(
            action: $action,
            module: $module,
            message: $message,
            user: $user,
            model: $model
        );
    }
}

if (!function_exists('audit_failure')) {
    /**
     * Log a failed operation.
     */
    function audit_failure(
        string $action,
        string $module,
        string $error,
        ?App\Models\User $user = null,
        $model = null
    ) {
        return AuditHelper::logFailure(
            action: $action,
            module: $module,
            error: $error,
            user: $user,
            model: $model
        );
    }
}

if (!function_exists('audit_view')) {
    /**
     * Log a view action.
     */
    function audit_view(
        string $module,
        $model = null,
        ?App\Models\User $user = null
    ) {
        return AuditHelper::logView(
            module: $module,
            model: $model,
            user: $user
        );
    }
}

if (!function_exists('audit_export')) {
    /**
     * Log an export action.
     */
    function audit_export(
        string $module,
        string $format = 'csv',
        ?App\Models\User $user = null,
        array $filters = []
    ) {
        return AuditHelper::logExport(
            module: $module,
            format: $format,
            user: $user,
            filters: $filters
        );
    }
}

if (!function_exists('audit_import')) {
    /**
     * Log an import action.
     */
    function audit_import(
        string $module,
        int $recordCount = 0,
        ?App\Models\User $user = null,
        array $details = []
    ) {
        return AuditHelper::logImport(
            module: $module,
            recordCount: $recordCount,
            user: $user,
            details: $details
        );
    }
}

if (!function_exists('audit_batch')) {
    /**
     * Log a batch operation.
     */
    function audit_batch(
        string $action,
        string $module,
        int $recordCount,
        array $recordIds = [],
        ?App\Models\User $user = null,
        array $details = []
    ) {
        return AuditHelper::logBatchOperation(
            action: $action,
            module: $module,
            recordCount: $recordCount,
            recordIds: $recordIds,
            user: $user,
            details: $details
        );
    }
}

if (!function_exists('audit_file')) {
    /**
     * Log a file operation.
     */
    function audit_file(
        string $action,
        string $module,
        string $fileName,
        int $fileSize = null,
        ?App\Models\User $user = null
    ) {
        return AuditHelper::logFileOperation(
            action: $action,
            module: $module,
            fileName: $fileName,
            fileSize: $fileSize,
            user: $user
        );
    }
}

if (!function_exists('audit_config')) {
    /**
     * Log a configuration change.
     */
    function audit_config(
        string $configKey,
        $oldValue,
        $newValue,
        ?App\Models\User $user = null
    ) {
        return AuditHelper::logConfigChange(
            configKey: $configKey,
            oldValue: $oldValue,
            newValue: $newValue,
            user: $user
        );
    }
}

if (!function_exists('audit_system')) {
    /**
     * Log a system event.
     */
    function audit_system(
        string $event,
        array $details = [],
        ?App\Models\User $user = null
    ) {
        return AuditHelper::logSystemEvent(
            event: $event,
            details: $details,
            user: $user
        );
    }
}

if (!function_exists('audit_error')) {
    /**
     * Log an error.
     */
    function audit_error(
        string $error,
        array $context = [],
        ?App\Models\User $user = null
    ) {
        return AuditHelper::logError(
            error: $error,
            context: $context,
            user: $user
        );
    }
}


