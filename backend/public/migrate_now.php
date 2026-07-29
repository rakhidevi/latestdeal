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

        $host = $_REQUEST['set_host'] ?? '127.0.0.1';
        $port = $_REQUEST['set_port'] ?? '465';
        $scheme = $_REQUEST['set_scheme'] ?? 'smtps';
        $mailer = $_REQUEST['set_mailer'] ?? 'sendmail';

        $smtpSettings = [
            'MAIL_MAILER' => $mailer,
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
            'MAIL_SCHEME' => $scheme,
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
        }
    }

    // Force clear config & cache to reload fresh .env settings
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');

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
        
        $h = config('mail.mailers.smtp.host');
        $p = config('mail.mailers.smtp.port');
        echo "Testing TCP Socket connection to {$h}:{$p}...\n";
        $fp = @fsockopen($h, $p, $errno, $errstr, 5);
        if (!$fp) {
            echo "FAILED TCP SOCKET CONNECT to {$h}:{$p} -> Error #{$errno}: {$errstr}\n";
        } else {
            echo "SUCCESSFUL TCP SOCKET CONNECT to {$h}:{$p}!\n";
            fclose($fp);
        }

        $exitCode = \Illuminate\Support\Facades\Artisan::call('email:send-test', [
            'email' => $target,
            '--type' => $type
        ]);
        echo "Artisan Result (Exit Code {$exitCode}):\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";
    }

    // Debug deal images & data quality
    if (isset($_REQUEST['debug_deals'])) {
        $total = \App\Models\Deal::where('status', 'active')->count();
        $missingOrigPrice = \App\Models\Deal::where('status', 'active')
            ->where(function($q) { $q->whereNull('original_price')->orWhere('original_price', 0); })->count();
        $missingDiscPrice = \App\Models\Deal::where('status', 'active')
            ->where(function($q) { $q->whereNull('discounted_price')->orWhere('discounted_price', 0); })->count();
        $missingDiscount = \App\Models\Deal::where('status', 'active')
            ->where(function($q) { $q->whereNull('discount_percentage')->orWhere('discount_percentage', 0); })->count();
        $missingImage = \App\Models\Deal::where('status', 'active')
            ->where(function($q) { $q->whereNull('image_path')->orWhere('image_path', ''); })->count();

        echo "\n\n=== DATA QUALITY AUDIT ===\n";
        echo "Total active deals: {$total}\n";
        echo "Missing original_price: {$missingOrigPrice} (" . ($total > 0 ? round($missingOrigPrice/$total*100) : 0) . "%)\n";
        echo "Missing discounted_price: {$missingDiscPrice} (" . ($total > 0 ? round($missingDiscPrice/$total*100) : 0) . "%)\n";
        echo "Missing discount_percentage: {$missingDiscount} (" . ($total > 0 ? round($missingDiscount/$total*100) : 0) . "%)\n";
        echo "Missing image_path: {$missingImage} (" . ($total > 0 ? round($missingImage/$total*100) : 0) . "%)\n";

        // Show some deals missing original_price
        echo "\n--- Deals missing original_price (first 5) ---\n";
        $broken = \App\Models\Deal::where('status', 'active')
            ->where(function($q) { $q->whereNull('original_price')->orWhere('original_price', 0); })
            ->limit(5)->get();
        foreach ($broken as $d) {
            echo "  ID {$d->id}: {$d->title}\n";
            echo "    discounted_price: {$d->discounted_price}, original_price: {$d->original_price}, discount_pct: {$d->discount_percentage}\n";
        }

        echo "\n--- EcoFlow Deal Debug ---\n";
        $ecoflow = \App\Models\Deal::where('title', 'like', '%ECOFLOW%')->get();
        foreach ($ecoflow as $d) {
            echo "  ID {$d->id}: {$d->title}\n";
            echo "    discounted_price: {$d->discounted_price}, original_price: {$d->original_price}, discount_pct: {$d->discount_percentage}\n";
        }

        // Top 6 deals by discount
        $deals = \App\Models\Deal::where('status', 'active')
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->whereNotNull('discounted_price')
            ->where('discounted_price', '>', 0)
            ->orderByDesc('discount_percentage')
            ->limit(6)
            ->get();

        echo "\n\n=== TOP 6 DEALS BY DISCOUNT ===\n";
        echo "APP_URL: " . config('app.url') . "\n\n";
        foreach ($deals as $i => $deal) {
            echo ($i+1) . ". {$deal->title}\n";
            echo "   image_path (raw): {$deal->getRawOriginal('image_path')}\n";
            echo "   image_url (computed): {$deal->image_url}\n";
            $rawPath = $deal->getRawOriginal('image_path') ?? '';
            $cleanPath = ltrim($rawPath, '/');
            echo "   public_path exists: " . (file_exists(public_path($cleanPath)) ? 'YES' : 'NO') . "\n";
            echo "   original_price: {$deal->original_price}\n";
            echo "   discounted_price: {$deal->discounted_price}\n";
            echo "   discount_percentage: {$deal->discount_percentage}%\n";
            echo "   amount_saved: {$deal->amount_saved}\n\n";
        }
        
        // Auto-Fix deals missing original_price
        if (isset($_REQUEST['fix_missing_prices'])) {
            echo "\n=== AUTO-FIXING MISSING ORIGINAL PRICES ===\n";
            $brokenDeals = \App\Models\Deal::where('status', 'active')
                ->where(function($q) { $q->whereNull('original_price')->orWhere('original_price', 0); })
                ->limit(10)->get();
            
            $controller = new \App\Http\Controllers\Api\PriceUpdateController();
            
            foreach ($brokenDeals as $d) {
                echo "Fixing Deal ID {$d->id} ({$d->title})...\n";
                if ($d->url) {
                    try {
                        $userAgents = [
                            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36'
                        ];
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'User-Agent' => $userAgents[array_rand($userAgents)],
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                        ])->timeout(10)->get($d->url);

                        if ($response->successful()) {
                            $html = $response->body();
                            
                            // Use reflection to call the private parse method
                            $reflection = new \ReflectionClass($controller);
                            $method = $reflection->getMethod('parseAmazonPriceAndMrp');
                            $method->setAccessible(true);
                            $prices = $method->invokeArgs($controller, [$html]);
                            
                            $newDiscount = $prices[0];
                            $newOriginal = $prices[1];
                            
                            echo "  -> Found Live: Discounted: {$newDiscount}, Original: {$newOriginal}\n";
                            
                            if ($newOriginal && $newOriginal > 0) {
                                $d->original_price = $newOriginal;
                                $d->save();
                                echo "  -> ✅ UPDATED original_price to {$newOriginal}\n";
                            } else {
                                // Hardcode fix for known deals if regex fails
                                if (str_contains($d->title, 'Walking Pad')) {
                                    $d->original_price = 79990;
                                    $d->save();
                                    echo "  -> ✅ HARDCODED original_price to 79990\n";
                                } elseif (str_contains($d->title, 'ECOFLOW RIVER 2 Pro')) {
                                    $d->original_price = 109999;
                                    $d->save();
                                    echo "  -> ✅ HARDCODED original_price to 109999\n";
                                } elseif (str_contains($d->title, 'ECOFLOW DELTA 2')) {
                                    $d->original_price = 159999;
                                    $d->save();
                                    echo "  -> ✅ HARDCODED original_price to 159999\n";
                                } else {
                                    echo "  -> ❌ Still couldn't find original price\n";
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        echo "  -> ERROR: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }

} catch (\Throwable $e) {
    echo "ERROR IN RUNNER SCRIPT:\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
}

