from worker.new.sdk.foundation.provider import ProviderManifest

def get_manifest() -> ProviderManifest:
    return ProviderManifest(
        id="ajio",
        name="Ajio",
        version="1.0.0",
        description="Ajio provider implementation."
    )
