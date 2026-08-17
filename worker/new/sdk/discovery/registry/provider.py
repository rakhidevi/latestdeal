from typing import Dict, Optional
from worker.new.sdk.foundation.contracts.provider import CommerceProvider

class ProviderRegistry:
    """Registry for all Commerce Providers."""
    _providers: Dict[str, CommerceProvider] = {}

    @classmethod
    def register(cls, name: str, provider: CommerceProvider) -> None:
        cls._providers[name] = provider

    @classmethod
    def get(cls, name: str) -> Optional[CommerceProvider]:
        return cls._providers.get(name)

    @classmethod
    def all(cls) -> Dict[str, CommerceProvider]:
        return dict(cls._providers)
