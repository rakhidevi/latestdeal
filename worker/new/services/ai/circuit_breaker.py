import os
import time
import asyncio
from typing import Optional

try:
    import redis.asyncio as redis
    HAS_REDIS = True
except ImportError:
    HAS_REDIS = False

STATE_HEALTHY = 'HEALTHY'
STATE_OPEN = 'OPEN'
STATE_RECOVERING = 'RECOVERING'

class CircuitBreaker:
    def __init__(self):
        self.store_type = os.getenv('AI_HEALTH_STORE', 'redis')
        self.threshold = int(os.getenv('AI_PROVIDER_FAILURE_THRESHOLD', '3'))
        self.cooldown = int(os.getenv('AI_PROVIDER_COOLDOWN_SECONDS', '300'))
        
        self.redis = None
        if self.store_type == 'redis' and HAS_REDIS:
            redis_host = os.getenv('REDIS_HOST', '127.0.0.1')
            redis_port = int(os.getenv('REDIS_PORT', '6379'))
            redis_password = os.getenv('REDIS_PASSWORD', None)
            self.redis = redis.Redis(host=redis_host, port=redis_port, password=redis_password, db=0, decode_responses=True)
            
        # fallback memory store
        self._memory_store = {}

    def _get_key(self, provider_id: str, suffix: str) -> str:
        # Important: Laravel's Cache might add a prefix (like `laravel_cache:` or `latestdeal_cache:`). 
        # But for direct redis, if it's stored directly in PHP using Redis::get/set, it has the standard prefix.
        # If Laravel uses `Redis::set('ai:provider:nvidia:state')`, Laravel appends `laravel_database_` by default.
        # Actually, in CircuitBreaker.php we did: `Redis::get($key)`. Laravel's Redis facade uses the `database.redis.options.prefix`.
        # By default in Laravel, the prefix is `Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'`.
        # To make it perfectly shared, it's safer if we just use the raw key if we configured prefix empty, or just accept that they might have separate scopes if we don't know the exact prefix.
        # For this implementation, we assume we use Laravel's prefix or standard raw keys. Let's use standard raw keys.
        prefix = os.getenv('REDIS_PREFIX', '') # Set this in worker .env to match Laravel if needed
        return f"{prefix}ai:provider:{provider_id}:{suffix}"

    async def _get(self, key: str) -> Optional[str]:
        if self.redis:
            try:
                return await self.redis.get(key)
            except Exception:
                pass
        return self._memory_store.get(key)

    async def _set(self, key: str, value: str, ttl: int = None):
        if self.redis:
            try:
                if ttl:
                    await self.redis.setex(key, ttl, value)
                else:
                    await self.redis.set(key, value)
                return
            except Exception:
                pass
        
        # memory fallback
        self._memory_store[key] = value
        if ttl:
            self._memory_store[f"{key}:exp"] = time.time() + ttl

    async def _delete(self, key: str):
        if self.redis:
            try:
                await self.redis.delete(key)
                return
            except Exception:
                pass
        self._memory_store.pop(key, None)

    async def get_state(self, provider_id: str) -> str:
        cooldown_key = self._get_key(provider_id, 'cooldown_until')
        cooldown = await self._get(cooldown_key)
        
        # Check memory expiration
        if not self.redis and cooldown_key in self._memory_store:
            exp = self._memory_store.get(f"{cooldown_key}:exp", 0)
            if time.time() > exp:
                self._memory_store.pop(cooldown_key, None)
                cooldown = None

        if cooldown:
            if time.time() < int(cooldown):
                return STATE_OPEN
            return STATE_RECOVERING

        failures = await self._get(self._get_key(provider_id, 'failures'))
        failures = int(failures) if failures else 0
        
        if failures >= self.threshold:
            return STATE_OPEN

        return STATE_HEALTHY

    async def record_success(self, provider_id: str):
        await self._set(self._get_key(provider_id, 'failures'), '0')
        await self._set(self._get_key(provider_id, 'last_success'), str(int(time.time())))
        await self._delete(self._get_key(provider_id, 'cooldown_until'))

    async def record_failure(self, provider_id: str):
        failures_key = self._get_key(provider_id, 'failures')
        failures = await self._get(failures_key)
        failures = int(failures) if failures else 0
        failures += 1
        
        await self._set(failures_key, str(failures))

        if failures >= self.threshold:
            exp_time = int(time.time()) + self.cooldown
            await self._set(self._get_key(provider_id, 'cooldown_until'), str(exp_time), ttl=self.cooldown)
