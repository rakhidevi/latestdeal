<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserInteracted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $sessionId;
    public $dealId;
    public $interactionType;
    public $source;
    public $metadata;
    
    // Request metadata
    public $ip;
    public $userAgent;
    public $device;
    public $platform;
    public $referrer;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $sessionId, $dealId, $interactionType, $source, $metadata, $requestData = [])
    {
        $this->userId = $userId;
        $this->sessionId = $sessionId;
        $this->dealId = $dealId;
        $this->interactionType = $interactionType;
        $this->source = $source;
        $this->metadata = $metadata;
        
        $this->ip = $requestData['ip'] ?? null;
        $this->userAgent = $requestData['user_agent'] ?? null;
        $this->device = $requestData['device'] ?? null;
        $this->platform = $requestData['platform'] ?? null;
        $this->referrer = $requestData['referrer'] ?? null;
    }
}
