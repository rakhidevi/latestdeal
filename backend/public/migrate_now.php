<?php
// Persistent migrate runner & admin user seeder
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: text/plain');
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrations ran successfully:\n";
    echo \Illuminate\Support\Facades\Artisan::output();

    // Run UIC daily aggregates calculation
    \Illuminate\Support\Facades\Artisan::call('uic:aggregate');
    echo "UIC daily aggregates computed:\n" . \Illuminate\Support\Facades\Artisan::output();

    // Ensure admin user exists and password is set to password123
    $u = \App\Models\User::firstOrNew(['email' => 'admin@latestdeal.in']);
    $u->name = 'Admin';
    $u->password = \Illuminate\Support\Facades\Hash::make('password123');
    $u->role = 'admin';
    $u->save();
    echo "\nAdmin user verified/seeded: admin@latestdeal.in / password123\n";

} catch (\Exception $e) {
    echo "Error running migrations: " . $e->getMessage();
}
