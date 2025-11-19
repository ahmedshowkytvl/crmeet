<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\AuditLog;

// Test audit logging
echo "Testing Audit System...\n";

// Create a test user
$user = User::first();
if (!$user) {
    echo "No users found in database. Please create a user first.\n";
    exit;
}

echo "Testing with user: " . $user->name . "\n";

// Test audit logging functions
try {
    // Test basic audit log
    audit_log(
        user: $user,
        actionType: 'test',
        module: 'system',
        details: ['test' => 'This is a test audit log'],
        status: 'success'
    );
    echo "✓ Basic audit log created successfully\n";

    // Test CRUD audit
    audit_crud(
        action: 'create',
        module: 'test_module',
        model: null,
        oldData: [],
        newData: ['name' => 'Test Record'],
        user: $user
    );
    echo "✓ CRUD audit log created successfully\n";

    // Test success audit
    audit_success(
        action: 'test_success',
        module: 'test_module',
        message: 'Test operation completed successfully',
        user: $user
    );
    echo "✓ Success audit log created successfully\n";

    // Test failure audit
    audit_failure(
        action: 'test_failure',
        module: 'test_module',
        error: 'Test error message',
        user: $user
    );
    echo "✓ Failure audit log created successfully\n";

    // Test system event
    audit_system(
        event: 'system_test',
        details: ['component' => 'audit_system'],
        user: $user
    );
    echo "✓ System event audit log created successfully\n";

    // Check total audit logs
    $totalLogs = AuditLog::count();
    echo "Total audit logs in database: " . $totalLogs . "\n";

    // Test recent logs
    $recentLogs = AuditLog::orderBy('created_at', 'desc')->limit(5)->get();
    echo "Recent 5 audit logs:\n";
    foreach ($recentLogs as $log) {
        echo "- " . $log->action_type . " in " . $log->module . " by " . $log->user_name . " at " . $log->created_at . "\n";
    }

    echo "\n✅ All audit system tests passed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error testing audit system: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}


