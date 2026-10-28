<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$plain = 'admin123';
$users = User::all();
$result = [];
foreach ($users as $u) {
    $u->password = Hash::make($plain);
    $u->save();
    $result[] = [
        'id' => $u->id,
        'email' => $u->email,
        'role' => $u->role,
        'password_set_to' => $plain,
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
