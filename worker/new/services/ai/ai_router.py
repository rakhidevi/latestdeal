import os
import json
import asyncio
import logging
from typing import List, Dict, Any
from .provider_registry import registry
from .circuit_breaker import CircuitBreaker, STATE_OPEN
from .adapters import create_adapter

logger = logging.getLogger(__name__)

class AIRouter:
    def __init__(self):
        self.circuit_breaker = CircuitBreaker()
        self.max_attempts = int(os.getenv('AI_MAX_TOTAL_ATTEMPTS', '6'))
        self.max_retries = int(os.getenv('AI_MAX_RETRIES_PER_PROVIDER', '1'))
        
        self.adapters = {}
        for pid, config in registry.get_providers().items():
            adapter = create_adapter(pid, config)
            if adapter:
                self.adapters[pid] = adapter

    async def chat(self, messages: List[dict], options: dict = None) -> dict:
        options = options or {}
        capabilities = options.get('capabilities', [])
        
        attempt_count = 0
        
        for provider_id in registry.get_order():
            adapter = self.adapters.get(provider_id)
            if not adapter:
                continue

            if not adapter.is_capable(capabilities):
                logger.debug(f"AI Router: Skipping {provider_id}, lacks requested capabilities.")
                continue

            state = await self.circuit_breaker.get_state(provider_id)
            if state == STATE_OPEN:
                logger.debug(f"AI Router: Skipping {provider_id}, circuit is OPEN.")
                continue

            retries = 0
            
            # Create a copy of messages in case we need to append repair hints
            current_messages = list(messages)
            
            while retries <= self.max_retries and attempt_count < self.max_attempts:
                attempt_count += 1
                try:
                    response = await adapter.chat(current_messages, options)

                    # Output structured check
                    caps_upper = [c.upper() for c in capabilities]
                    if 'JSON' in caps_upper or 'STRUCTURED' in caps_upper:
                        try:
                            parsed = json.loads(response['content'])
                        except json.JSONDecodeError:
                            raise ValueError("Invalid JSON output from provider")

                    await self.circuit_breaker.record_success(provider_id)
                    return response

                except Exception as e:
                    error_info = adapter.normalize_error(e)
                    
                    if "Invalid JSON output" in str(e):
                        logger.warning(f"AI Router: {provider_id} returned invalid JSON. Attempt {retries}")
                        if retries < self.max_retries:
                            retries += 1
                            # Repair hint
                            current_messages.append({'role': 'user', 'content': 'The previous response was not valid JSON. Please reply ONLY with valid JSON.'})
                            continue
                        
                        await self.circuit_breaker.record_failure(provider_id)
                        break # Failover to next provider

                    logger.warning(f"AI Router: {provider_id} failed: {error_info.get('message', str(e))}")

                    if error_info['type'] == 'RETRY' and retries < self.max_retries:
                        retries += 1
                        await asyncio.sleep(1 * retries) # simple backoff
                        continue

                    # Failover
                    await self.circuit_breaker.record_failure(provider_id)
                    break 

            if attempt_count >= self.max_attempts:
                break

        raise Exception("AI Router: Exhausted all available providers or reached max attempts.")

# Global router instance
router = AIRouter()
