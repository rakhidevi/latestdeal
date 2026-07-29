<?php
// Persistent migrate runner & admin user seeder
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
try {
    // 1. Force update SMTP Configuration in .env
    $envFile = base_path('.env');
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        $modified = false;

        $smtpSettings = [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'mail.latestdeal.in',
            'MAIL_PORT' => '465',
            'MAIL_SCHEME' => 'ssl',
            'MAIL_USERNAME' => 'info-noreply@latestdeal.in',
            'MAIL_FROM_ADDRESS' => 'info-noreply@latestdeal.in',
            'MAIL_FROM_NAME' => 'LatestDeal.in',
        ];

        foreach ($smtpSettings as $key => $val) {
            if (preg_match("/^{$key}=.*/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}=\"{$val}\"", $env);
            } else {
                $env .= "\n{$key}=\"{$val}\"";
            }
            $modified = true;
        }

        if (isset($_GET['smtp_pass']) && !empty($_GET['smtp_pass'])) {
            $pass = $_GET['smtp_pass'];
            if (preg_match('/^MAIL_PASSWORD=.*/m', $env)) {
                $env = preg_replace('/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD="' . $pass . '"', $env);
            } else {
                $env .= "\nMAIL_PASSWORD=\"{$pass}\"";
            }
            $modified = true;
            echo "SMTP Password updated in .env successfully.\n";
        }

        if ($modified) {
            file_put_contents($envFile, $env);
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            echo "ENV SMTP configuration updated to mail.latestdeal.in:465.\n";
        }
    }

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrations ran successfully:\n" . \Illuminate\Support\Facades\Artisan::output();

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

    // Dispatch Test Email
    if (isset($_GET['send_test'])) {
        $target = $_GET['email'] ?? 'hi.pankajtiwari86@gmail.com';
        $type = $_GET['type'] ?? 'welcome';
        echo "\nDispatching {$type} test email to {$target}...\n";
        $exitCode = \Illuminate\Support\Facades\Artisan::call('email:send-test', [
            'email' => $target,
            '--type' => $type
        ]);
        echo "Artisan Result (Exit Code {$exitCode}):\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";
    }

} catch (\Exception $e) {
    echo "Error running runner script: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
