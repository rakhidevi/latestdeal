<?php
// Persistent migrate runner & admin user seeder
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

set_time_limit(60); // Extend execution time limit for SMTP timeout diagnostics

header('Content-Type: text/plain');
try {
    $envFile = base_path('.env');
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        $modified = false;

        if (isset($_REQUEST['set_mailer'])) {
            $m = $_REQUEST['set_mailer'];
            if (preg_match('/^MAIL_MAILER=.*/m', $env)) {
                $env = preg_replace('/^MAIL_MAILER=.*/m', 'MAIL_MAILER="' . $m . '"', $env);
            } else {
                $env .= "\nMAIL_MAILER=\"{$m}\"";
            }
            $modified = true;
            echo "MAIL_MAILER set to {$m} in .env.\n";
        }

        if (isset($_REQUEST['set_host'])) {
            $h = $_REQUEST['set_host'];
            if (preg_match('/^MAIL_HOST=.*/m', $env)) {
                $env = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST="' . $h . '"', $env);
            } else {
                $env .= "\nMAIL_HOST=\"{$h}\"";
            }
            $modified = true;
            echo "MAIL_HOST set to {$h} in .env.\n";
        }

        if (isset($_REQUEST['set_port'])) {
            $p = $_REQUEST['set_port'];
            if (preg_match('/^MAIL_PORT=.*/m', $env)) {
                $env = preg_replace('/^MAIL_PORT=.*/m', 'MAIL_PORT="' . $p . '"', $env);
            } else {
                $env .= "\nMAIL_PORT=\"{$p}\"";
            }
            $modified = true;
            echo "MAIL_PORT set to {$p} in .env.\n";
        }

        if (isset($_REQUEST['set_scheme'])) {
            $s = $_REQUEST['set_scheme'];
            if (preg_match('/^MAIL_SCHEME=.*/m', $env)) {
                $env = preg_replace('/^MAIL_SCHEME=.*/m', 'MAIL_SCHEME="' . $s . '"', $env);
            } else {
                $env .= "\nMAIL_SCHEME=\"{$s}\"";
            }
            $modified = true;
            echo "MAIL_SCHEME set to {$s} in .env.\n";
        }

        if (isset($_REQUEST['smtp_pass']) && !empty($_REQUEST['smtp_pass'])) {
            $pass = $_REQUEST['smtp_pass'];
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
        }
    }

    echo "Current Mail Configuration Evaluated:\n";
    echo "MAILER: " . config('mail.default') . "\n";
    echo "HOST: " . config('mail.mailers.smtp.host') . "\n";
    echo "PORT: " . config('mail.mailers.smtp.port') . "\n";
    echo "SCHEME: " . config('mail.mailers.smtp.scheme') . "\n";
    echo "USERNAME: " . config('mail.mailers.smtp.username') . "\n";
    echo "FROM: " . config('mail.from.address') . "\n\n";

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "Migrations ran successfully:\n" . \Illuminate\Support\Facades\Artisan::output();

    \Illuminate\Support\Facades\Artisan::call('push:generate-vapid');
    echo "VAPID key generation output:\n" . \Illuminate\Support\Facades\Artisan::output();

    // Ensure admin user exists
    $u = \App\Models\User::firstOrNew(['email' => 'admin@latestdeal.in']);
    $u->name = 'Admin';
    $u->password = \Illuminate\Support\Facades\Hash::make('password123');
    $u->role = 'admin';
    $u->save();
    echo "\nAdmin user verified/seeded: admin@latestdeal.in / password123\n";

    // Dispatch Test Email
    if (isset($_REQUEST['send_test']) || isset($_GET['send_test'])) {
        $target = $_REQUEST['email'] ?? $_GET['email'] ?? 'hi.pankajtiwari86@gmail.com';
        $type = $_REQUEST['type'] ?? $_GET['type'] ?? 'welcome';
        echo "\nDispatching {$type} test email to {$target}...\n";
        
        // Test socket connection first
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        echo "Testing TCP Socket connection to {$host}:{$port}...\n";
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$fp) {
            echo "FAILED TCP SOCKET CONNECT to {$host}:{$port} -> Error #{$errno}: {$errstr}\n";
        } else {
            echo "SUCCESSFUL TCP SOCKET CONNECT to {$host}:{$port}!\n";
            fclose($fp);
        }

        $exitCode = \Illuminate\Support\Facades\Artisan::call('email:send-test', [
            'email' => $target,
            '--type' => $type
        ]);
        echo "Artisan Result (Exit Code {$exitCode}):\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";
    }

} catch (\Throwable $e) {
    echo "ERROR IN RUNNER SCRIPT:\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
}
