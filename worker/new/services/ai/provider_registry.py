import os
from typing import Dict, List, Optional
from dotenv import load_dotenv

load_dotenv()

class ProviderRegistry:
    def __init__(self):
        self.providers: Dict[str, dict] = {}
        self.order: List[str] = []
        self._load_configuration()

    def _load_configuration(self):
        order_env = os.getenv('AI_PROVIDER_ORDER', 'nvidia,ollama,groq,cerebras,mock')
        self.order = [p.strip() for p in order_env.split(',') if p.strip()]

        for provider_key in self.order:
            prefix = f"AI_{provider_key.upper()}_"
            
            if os.getenv(f"{prefix}ENABLED", "false").lower() == "true":
                self.providers[provider_key] = {
                    'id': provider_key,
                    'base_url': os.getenv(f"{prefix}BASE_URL"),
                    'api_key': os.getenv(f"{prefix}API_KEY", ""),
                    'model': os.getenv(f"{prefix}MODEL"),
                    'vision_model': os.getenv(f"{prefix}VISION_MODEL"),
                }

    def get_providers(self) -> Dict[str, dict]:
        return self.providers

    def get_provider(self, provider_id: str) -> Optional[dict]:
        return self.providers.get(provider_id)

    def get_order(self) -> List[str]:
        return self.order

# Singleton instance
registry = ProviderRegistry()
