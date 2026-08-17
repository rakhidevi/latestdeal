from worker.new.sdk.foundation.provider import ProviderManifest

def get_manifest() -> ProviderManifest:
    return ProviderManifest(
        id="nykaa",
        name="Nykaa",
        version="1.0.0",
        description="Nykaa provider implementation."
    )
