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
    // 1. Ensure SMTP Configuration in .env
    $envFile = base_path('.env');
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        $modified = false;

        $defaults = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'mail.latestdeal.in',
            'MAIL_PORT' => '465',
            'MAIL_SCHEME' => 'ssl',
            'MAIL_USERNAME' => 'info-noreply@latestdeal.in',
            'MAIL_FROM_ADDRESS' => 'info-noreply@latestdeal.in',
            'MAIL_FROM_NAME' => 'LatestDeal.in',
        ];

        foreach ($defaults as $key => $val) {
            if (!str_contains($env, "{$key}=")) {
                $env .= "\n{$key}={$val}";
                $modified = true;
            }
        }

        if (isset($_GET['smtp_pass'])) {
            $pass = $_GET['smtp_pass'];
            if (str_contains($env, 'MAIL_PASSWORD=')) {
                $env = preg_replace('/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD=' . $pass, $env);
            } else {
                $env .= "\nMAIL_PASSWORD={$pass}";
            }
            $modified = true;
            echo "SMTP Password set successfully.\n";
        }

        if ($modified) {
            file_put_contents($envFile, $env);
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            echo "ENV SMTP configuration updated.\n";
        }
    }

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrations ran successfully:\n";
    echo \Illuminate\Support\Facades\Artisan::output();

    \Illuminate\Support\Facades\Artisan::call('push:generate-vapid');
    echo "VAPID key generation output:\n" . \Illuminate\Support\Facades\Artisan::output();

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

    // Optional test email dispatcher
    if (isset($_GET['send_test'])) {
        $target = $_GET['email'] ?? 'hi.pankajtiwari86@gmail.com';
        $type = $_GET['type'] ?? 'welcome';
        \Illuminate\Support\Facades\Artisan::call('email:send-test', ['email' => $target, '--type' => $type]);
        echo "\nTest Email Dispatch Output:\n" . \Illuminate\Support\Facades\Artisan::output();
    }

} catch (\Exception $e) {
    echo "Error running runner script: " . $e->getMessage();
}
