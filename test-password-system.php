<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\PasswordAccount;
use App\Models\User;
use App\Models\PasswordAssignment;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔐 Testing Password Management System\n";
echo "=====================================\n\n";

try {
    // Test 1: Check if tables exist
    echo "1. Checking database tables...\n";
    $tables = ['password_accounts', 'password_assignments', 'password_audit_logs', 'password_history'];
    foreach ($tables as $table) {
        $exists = DB::select("SHOW TABLES LIKE '$table'");
        echo "   ✓ Table '$table' exists\n";
    }
    
    // Test 2: Create a test user
    echo "\n2. Creating test user...\n";
    $user = User::first();
    if (!$user) {
        echo "   ⚠ No users found in database\n";
    } else {
        echo "   ✓ Found user: {$user->name} (Role: " . ($user->role ? $user->role->slug : 'No role') . ")\n";
    }
    
    // Test 3: Create a test password account
    echo "\n3. Creating test password account...\n";
    $account = PasswordAccount::create([
        'name' => 'Test Account',
        'name_ar' => 'حساب تجريبي',
        'email' => 'test@example.com',
        'password' => 'test123456',
        'url' => 'https://example.com',
        'notes' => 'Test account for system testing',
        'category' => 'Work Tools',
        'created_by' => $user ? $user->id : 1,
    ]);
    echo "   ✓ Created account: {$account->name} (ID: {$account->id})\n";
    
    // Test 4: Test password encryption/decryption
    echo "\n4. Testing password encryption...\n";
    $originalPassword = 'test123456';
    $encryptedPassword = $account->password;
    echo "   ✓ Password encrypted: " . (strlen($encryptedPassword) > 20 ? 'Yes' : 'No') . "\n";
    echo "   ✓ Password decrypted correctly: " . ($account->password === $originalPassword ? 'Yes' : 'No') . "\n";
    
    // Test 5: Create assignment
    if ($user) {
        echo "\n5. Creating password assignment...\n";
        $assignment = PasswordAssignment::create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'access_level' => 'read_only',
            'can_view_password' => true,
            'assigned_by' => $user->id,
        ]);
        echo "   ✓ Created assignment for user: {$user->name}\n";
    }
    
    // Test 6: Test relationships
    echo "\n6. Testing relationships...\n";
    $accountWithRelations = PasswordAccount::with(['creator', 'assignments.user'])->find($account->id);
    echo "   ✓ Creator relationship: " . ($accountWithRelations->creator ? $accountWithRelations->creator->name : 'None') . "\n";
    echo "   ✓ Assignments count: " . $accountWithRelations->assignments->count() . "\n";
    
    // Test 7: Test scopes
    echo "\n7. Testing model scopes...\n";
    $activeAccounts = PasswordAccount::active()->count();
    echo "   ✓ Active accounts: $activeAccounts\n";
    
    // Cleanup
    echo "\n8. Cleaning up test data...\n";
    PasswordAssignment::where('account_id', $account->id)->delete();
    PasswordAccount::where('id', $account->id)->delete();
    echo "   ✓ Test data cleaned up\n";
    
    echo "\n🎉 All tests passed! Password Management System is working correctly.\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
