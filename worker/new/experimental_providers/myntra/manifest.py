from worker.new.sdk.foundation.provider import ProviderManifest

def get_manifest() -> ProviderManifest:
    return ProviderManifest(
        id="myntra",
        name="Myntra",
        version="1.0.0",
        description="Myntra provider implementation."
    )
