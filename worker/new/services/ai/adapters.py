from typing import Dict, List, Optional, Any
import httpx
import logging

class ProviderAdapterInterface:
    def __init__(self, config: dict):
        self.config = config

    def get_provider_id(self) -> str:
        return self.config['id']

    def is_capable(self, capabilities: List[str]) -> bool:
        raise NotImplementedError

    async def chat(self, messages: List[dict], options: dict = None) -> dict:
        raise NotImplementedError

    async def list_models(self) -> List[str]:
        raise NotImplementedError

    def normalize_error(self, e: Exception) -> dict:
        raise NotImplementedError

class BaseOpenAIAdapter(ProviderAdapterInterface):
    
    def is_capable(self, capabilities: List[str]) -> bool:
        for cap in capabilities:
            cap = cap.upper()
            if cap == 'VISION' and not self.config.get('vision_model'):
                return False
        return True

    async def chat(self, messages: List[dict], options: dict = None) -> dict:
        options = options or {}
        base_url = self.config['base_url'].rstrip('/')
        endpoint = f"{base_url}/chat/completions"
        
        model = self.config['model']
        caps = options.get('capabilities', [])
        
        if 'VISION' in [c.upper() for c in caps]:
            model = self.config['vision_model']

        payload = {
            'model': model,
            'messages': messages,
            'temperature': options.get('temperature', 0.7)
        }

        if 'JSON' in [c.upper() for c in caps] or 'STRUCTURED' in [c.upper() for c in caps]:
            payload['response_format'] = {'type': 'json_object'}

        if 'max_tokens' in options:
            payload['max_tokens'] = options['max_tokens']

        headers = {}
        if self.config.get('api_key'):
            headers['Authorization'] = f"Bearer {self.config['api_key']}"

        timeout = options.get('timeout', 30.0)

        async with httpx.AsyncClient(timeout=timeout) as client:
            response = await client.post(endpoint, json=payload, headers=headers)
            response.raise_for_status()
            
            data = response.json()
            if not data.get('choices') or not data['choices'][0].get('message', {}).get('content'):
                raise ValueError(f"Invalid response structure from {self.get_provider_id()}")
                
            return {
                'content': data['choices'][0]['message']['content'],
                'model': data.get('model', model),
                'provider': self.get_provider_id()
            }

    async def list_models(self) -> List[str]:
        base_url = self.config['base_url'].rstrip('/')
        endpoint = f"{base_url}/models"
        
        headers = {}
        if self.config.get('api_key'):
            headers['Authorization'] = f"Bearer {self.config['api_key']}"

        async with httpx.AsyncClient(timeout=10.0) as client:
            try:
                response = await client.get(endpoint, headers=headers)
                response.raise_for_status()
                data = response.json()
                return [m['id'] for m in data.get('data', [])]
            except Exception:
                return []

    def normalize_error(self, e: Exception) -> dict:
        if isinstance(e, httpx.HTTPStatusError):
            status = e.response.status_code
            if status in [401, 403, 404]:
                return {'type': 'FAILOVER', 'message': f"HTTP {status}: {str(e)}"}
            if status in [408, 429, 500, 502, 503, 504]:
                return {'type': 'RETRY', 'message': f"HTTP {status}: {str(e)}"}
            if status == 400:
                return {'type': 'FAILOVER', 'message': f"HTTP 400: {str(e)}"}
                
        if isinstance(e, (httpx.TimeoutException, httpx.ConnectError)):
            return {'type': 'RETRY', 'message': f"Connection Error: {str(e)}"}
            
        return {'type': 'FAILOVER', 'message': f"Unknown error: {str(e)}"}

class NVIDIAAdapter(BaseOpenAIAdapter): pass
class OllamaAdapter(BaseOpenAIAdapter): pass
class GroqAdapter(BaseOpenAIAdapter): pass
class CerebrasAdapter(BaseOpenAIAdapter): pass

def create_adapter(provider_id: str, config: dict) -> Optional[ProviderAdapterInterface]:
    pid = provider_id.lower()
    if pid == 'nvidia': return NVIDIAAdapter(config)
    if pid == 'ollama': return OllamaAdapter(config)
    if pid == 'groq': return GroqAdapter(config)
    if pid == 'cerebras': return CerebrasAdapter(config)
    return None
