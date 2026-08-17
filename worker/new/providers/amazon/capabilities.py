from worker.new.sdk.provider_sdk.base import CapabilityDescriptor
from .manifest import get_amazon_manifest

class AmazonCapabilityDescriptor(CapabilityDescriptor):
    """
    Exhaustive matrix of all supported Amazon features.
    Used by the Discovery Planner to prune invalid Search Targets before crawling.
    """
    def __init__(self):
        super().__init__(get_amazon_manifest())
        
    def supports_amazon_specifics(self, feature: str) -> bool:
        # We can extend the base boolean flags with string lookups if needed.
        # But base capabilities are already covered by the PluginManifestV2.
        pass
