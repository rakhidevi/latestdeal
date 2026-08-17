from typing import Dict, List, Optional
from worker.new.sdk.foundation.dto.models import PluginManifestV2

class CapabilityMatrix:
    """
    Commerce Capability Engine: Capability Matrix
    Declares exact provider support for features to inform the Constraint and Translation engines.
    """
    
    def __init__(self):
        self._manifests: Dict[str, PluginManifestV2] = {}
        # In a real scenario, this would load from the ProviderManifestRecord database table
        
    def register_manifest(self, manifest: PluginManifestV2) -> None:
        """Register a provider's declared capabilities."""
        self._manifests[manifest.name] = manifest
        
    def supports(self, provider: str, capability: str) -> bool:
        """Check if a provider supports a specific abstract capability."""
        manifest = self._manifests.get(provider)
        if not manifest:
            return False
            
        # We check if the attribute exists and is True on the manifest DTO
        # (Assuming the DTO has boolean flags matching the capability name)
        attr_name = f"supports_{capability}"
        return getattr(manifest, attr_name, False)
        
    def get_supported_providers(self, capability: str) -> List[str]:
        """Returns a list of all providers that support a specific capability."""
        supported = []
        for provider, manifest in self._manifests.items():
            if self.supports(provider, capability):
                supported.append(provider)
        return supported
