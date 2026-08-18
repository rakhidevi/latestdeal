<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class CircuitBreaker
{
    public const STATE_HEALTHY = 'HEALTHY';
    public const STATE_OPEN = 'OPEN';
    public const STATE_RECOVERING = 'RECOVERING';

    protected $store;
    protected $threshold;
    protected $cooldown;

    public function __construct()
    {
        $this->store = env('AI_HEALTH_STORE', 'redis');
        $this->threshold = (int) env('AI_PROVIDER_FAILURE_THRESHOLD', 3);
        $this->cooldown = (int) env('AI_PROVIDER_COOLDOWN_SECONDS', 300);
    }

    protected function getKey(string $providerId, string $suffix): string
    {
        return "ai:provider:{$providerId}:{$suffix}";
    }

    protected function get(string $key, $default = null)
    {
        try {
            if ($this->store === 'redis') {
                return Redis::get($key) ?? $default;
            }
            return Cache::get($key, $default);
        } catch (\Exception $e) {
            return Cache::get($key, $default);
        }
    }

    protected function set(string $key, $value, $ttl = null): void
    {
        try {
            if ($this->store === 'redis') {
                if ($ttl) {
                    Redis::setex($key, $ttl, $value);
                } else {
                    Redis::set($key, $value);
                }
            } else {
                if ($ttl) {
                    Cache::put($key, $value, $ttl);
                } else {
                    Cache::forever($key, $value);
                }
            }
        } catch (\Exception $e) {
            if ($ttl) {
                Cache::put($key, $value, $ttl);
            } else {
                Cache::forever($key, $value);
            }
        }
    }

    public function getState(string $providerId): string
    {
        $cooldownKey = $this->getKey($providerId, 'cooldown_until');
        $cooldown = $this->get($cooldownKey);
        
        if ($cooldown && time() < (int)$cooldown) {
            return self::STATE_OPEN;
        }

        if ($cooldown && time() >= (int)$cooldown) {
            return self::STATE_RECOVERING;
        }

        $failures = (int) $this->get($this->getKey($providerId, 'failures'), 0);
        
        if ($failures >= $this->threshold) {
            return self::STATE_OPEN;
        }

        return self::STATE_HEALTHY;
    }

    public function recordSuccess(string $providerId): void
    {
        $this->set($this->getKey($providerId, 'failures'), 0);
        $this->set($this->getKey($providerId, 'last_success'), time());
        
        // Clear cooldown
        if ($this->store === 'redis') {
            Redis::del($this->getKey($providerId, 'cooldown_until'));
        } else {
            Cache::forget($this->getKey($providerId, 'cooldown_until'));
        }
    }

    public function recordFailure(string $providerId): void
    {
        $failuresKey = $this->getKey($providerId, 'failures');
        $failures = (int) $this->get($failuresKey, 0) + 1;
        $this->set($failuresKey, $failures);

        if ($failures >= $this->threshold) {
            $this->set($this->getKey($providerId, 'cooldown_until'), time() + $this->cooldown, $this->cooldown);
        }
    }
}
