<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::select('id','name','email','role','created_at')->get();
echo $users->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
