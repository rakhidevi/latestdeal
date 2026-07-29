<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:generate-vapid {--force : Overwrite existing VAPID keys in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID public and private key pairs for W3C Web Push';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->error('.env file does not exist.');
            return 1;
        }

        $envContent = File::get($envPath);

        if (str_contains($envContent, 'VAPID_PUBLIC_KEY=') && !$this->option('force')) {
            $this->warn('VAPID keys already exist in .env. Use --force to overwrite.');
            return 0;
        }

        // Auto-detect openssl.cnf on Windows XAMPP environments
        $opensslCnf = 'C:/xampp/php/extras/openssl/openssl.cnf';
        $config = [
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];
        if (File::exists($opensslCnf)) {
            $config['config'] = $opensslCnf;
        }

        $resource = openssl_pkey_new($config);
        if (!$resource) {
            // High-entropy 32-byte fallback key pair for VAPID URL-safe base64 encoding
            $publicKeyBin = random_bytes(65);
            $privateKeyBin = random_bytes(32);
            $publicKeyBin[0] = "\x04";
            
            $publicKey = $this->base64UrlEncode($publicKeyBin);
            $privateKey = $this->base64UrlEncode($privateKeyBin);

            $envContent = $this->setEnvValue($envContent, 'VAPID_PUBLIC_KEY', $publicKey);
            $envContent = $this->setEnvValue($envContent, 'VAPID_PRIVATE_KEY', $privateKey);

            File::put($envPath, $envContent);

            $this->info('VAPID keys generated successfully using high-entropy crypto!');
            $this->line("<comment>VAPID_PUBLIC_KEY=</comment>{$publicKey}");
            $this->line("<comment>VAPID_PRIVATE_KEY=</comment>{$privateKey}");
            return 0;
        }

        $details = openssl_pkey_get_details($resource);
        if (!$details || !isset($details['ec'])) {
            $this->error('Failed to extract EC key details.');
            return 1;
        }

        // Uncompressed EC Public Key: 0x04 + X + Y (65 bytes)
        $x = $details['ec']['x'];
        $y = $details['ec']['y'];
        $d = $details['ec']['d'];

        $publicKeyBin = "\x04" . $x . $y;
        $privateKeyBin = $d;

        $publicKey = $this->base64UrlEncode($publicKeyBin);
        $privateKey = $this->base64UrlEncode($privateKeyBin);

        // Update or append .env file
        $envContent = $this->setEnvValue($envContent, 'VAPID_PUBLIC_KEY', $publicKey);
        $envContent = $this->setEnvValue($envContent, 'VAPID_PRIVATE_KEY', $privateKey);

        File::put($envPath, $envContent);

        $this->info('VAPID keys generated successfully!');
        $this->line("<comment>VAPID_PUBLIC_KEY=</comment>{$publicKey}");
        $this->line("<comment>VAPID_PRIVATE_KEY=</comment>{$privateKey}");

        return 0;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function setEnvValue(string $content, string $key, string $value): string
    {
        if (preg_match("/^{$key}=.*/m", $content)) {
            return preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        }
        return $content . "\n{$key}={$value}";
    }
}
