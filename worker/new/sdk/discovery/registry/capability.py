from typing import Dict, List, Optional
from worker.new.sdk.foundation.dto.models import ProviderCapabilityDTO

class CapabilityRegistry:
    """Registry mapping providers to their capabilities."""
    _capabilities: Dict[str, ProviderCapabilityDTO] = {}

    @classmethod
    def register(cls, provider_name: str, capability: ProviderCapabilityDTO) -> None:
        cls._capabilities[provider_name] = capability

    @classmethod
    def get(cls, provider_name: str) -> Optional[ProviderCapabilityDTO]:
        return cls._capabilities.get(provider_name)
