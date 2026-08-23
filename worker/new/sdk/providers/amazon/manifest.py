from worker.new.sdk.foundation.dto.models import PluginManifestV2, DiscoveryEntrypoint

def get_manifest() -> PluginManifestV2:
    return PluginManifestV2(
        name="amazon",
        version="1.0.0",
        description="Amazon India Commerce Provider Plugin",
        author="LatestDeal Core Team"
    )
