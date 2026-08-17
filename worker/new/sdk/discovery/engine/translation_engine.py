from typing import Dict, Any, Optional
from worker.new.sdk.foundation.contracts.provider import CommerceProvider

class TranslationEngine:
    """
    Commerce Capability Engine: Translation Engine
    Converts abstract parameters (e.g. brand, discount) into provider-specific targets
    by delegating to the provider's query_builder.
    """
    
    def __init__(self, provider_registry: Any):
        # provider_registry would be an instance of ProviderRegistry
        self.provider_registry = provider_registry
        
    def translate_to_url(self, provider_name: str, abstract_parameters: Dict[str, Any]) -> Optional[str]:
        """Translates abstract parameters into a concrete URL for the target provider."""
        provider: CommerceProvider = self.provider_registry.get(provider_name)
        if not provider:
            raise ValueError(f"Provider {provider_name} not registered")
            
        return provider.build_query(abstract_parameters)
