<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UIC\UicVisitorSession;

class ResolveGeoLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sessionId;
    protected $ipAddress;

    public function __construct($sessionId, $ipAddress)
    {
        $this->sessionId = $sessionId;
        $this->ipAddress = $ipAddress;
    }

    public function handle(): void
    {
        $session = UicVisitorSession::find($this->sessionId);
        if (!$session) return;

        // In a real app, use a package like stevebauman/location or an API (ip-api.com)
        // For demonstration, we'll mock a generic response or try a fast free API
        
        try {
            $ch = curl_init("http://ip-api.com/json/{$this->ipAddress}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    $session->update([
                        'country' => $data['country'] ?? null,
                        'state' => $data['regionName'] ?? null,
                        'city' => $data['city'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ignore failure, we will leave geo null
        }
    }
}
