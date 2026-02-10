#!/usr/bin/env php
<?php

// Simple test script to get an auth token
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Laravel\Sanctum\Sanctum;

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Trouver ou créer un utilisateur de test
$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test User',
        'password' => bcrypt('password123'),
    ]
);

// Créer un token
$token = $user->createToken('test-token')->plainTextToken;

echo "========================================\n";
echo "Token d'authentification pour test:\n";
echo "========================================\n";
echo "Email: {$user->email}\n";
echo "Password: password123\n";
echo "Token: {$token}\n";
echo "========================================\n";
echo "\nAjoutez ce token dans localStorage:\n";
echo "localStorage.setItem('auth_token', '{$token}')\n";
echo "localStorage.setItem('user', JSON.stringify({$user->toJson()}))\n";
