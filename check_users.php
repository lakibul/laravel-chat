<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

echo "Checking users in database:\n";

$users = \App\Models\User::all();

foreach ($users as $user) {
    $passwordStart = substr($user->password, 0, 10);
    $isBcrypt = str_starts_with($user->password, '$2y$') || str_starts_with($user->password, '$2b$') || str_starts_with($user->password, '$2a$');
    echo "User: {$user->name} - Email: {$user->email} - Password starts with: {$passwordStart} - Is Bcrypt: " . ($isBcrypt ? 'YES' : 'NO') . "\n";
}
