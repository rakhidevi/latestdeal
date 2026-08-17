from abc import ABC, abstractmethod
from typing import List, Dict, Any
from worker.new.sdk.foundation.dto.models import ProviderCapabilityDTO

class CommerceProvider(ABC):
    """
    Base contract for all Commerce Providers (Amazon, Flipkart, etc.).
    """
    @abstractmethod
    def get_capabilities(self) -> ProviderCapabilityDTO:
        """Returns the capabilities and rate limits for this provider."""
        pass

    @abstractmethod
    def build_query(self, parameters: Dict[str, Any]) -> str:
        """Translates abstract parameters into a provider-specific URL/query string."""
        pass

    @abstractmethod
    def get_knowledge(self) -> Dict[str, Any]:
        """Returns provider-specific knowledge overrides (e.g., node IDs)."""
        pass
