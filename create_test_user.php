<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create a test user
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@test.com',
    'username' => 'test',
    'password' => Hash::make('test123'),
    'employee_code' => 'TEST001',
    'is_active' => true,
]);

echo 'Test user created successfully!' . PHP_EOL;
echo 'Username: test' . PHP_EOL;
echo 'Password: test123' . PHP_EOL;












