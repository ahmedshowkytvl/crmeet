<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$users = User::select('id', 'name', 'email', 'username')->get();
foreach($users as $user) {
    echo 'ID: ' . $user->id . ', Name: ' . $user->name . ', Email: ' . $user->email . ', Username: ' . $user->username . PHP_EOL;
}












