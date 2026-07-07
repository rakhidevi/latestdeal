<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::firstOrNew(['email'=>'admin@latestdeal.in']);
$u->name = 'Admin';
$u->password = \Illuminate\Support\Facades\Hash::make('password123');
$u->role = 'admin';
$u->save();

echo "Admin user created!\nEmail: admin@latestdeal.in\nPassword: password123\n";
