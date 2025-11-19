<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('username', 'admin')->first();
if ($admin) {
    echo 'Admin found: ' . $admin->name . PHP_EOL;
    echo 'Email: ' . $admin->email . PHP_EOL;
    echo 'Username: ' . $admin->username . PHP_EOL;
    
    // Test different passwords
    $passwords = ['admin123', 'admin', 'password', '123456', 'admin@123'];
    foreach ($passwords as $password) {
        if (Hash::check($password, $admin->password)) {
            echo 'Correct password: ' . $password . PHP_EOL;
            break;
        }
    }
} else {
    echo 'Admin user not found' . PHP_EOL;
}












