<?php
// Dedicated admin user seeder and auth verifier
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: text/plain');

try {
    // 1. Delete existing admin user to ensure clean state
    \App\Models\User::where('email', 'admin@latestdeal.in')->delete();

    // 2. Create fresh admin user with explicit hashed password
    $u = new \App\Models\User();
    $u->name = 'Admin';
    $u->email = 'admin@latestdeal.in';
    $u->password = \Illuminate\Support\Facades\Hash::make('password123');
    $u->role = 'admin';
    $u->save();

    // 3. Verify Auth::attempt with password123
    $attempt = \Illuminate\Support\Facades\Auth::attempt([
        'email' => 'admin@latestdeal.in',
        'password' => 'password123'
    ]);

    echo "Admin User Reset Successfully!\n";
    echo "Email: admin@latestdeal.in\n";
    echo "Password: password123\n";
    echo "Auth Attempt Result: " . ($attempt ? "SUCCESS (LOGIN WORKS!)" : "FAILED") . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
