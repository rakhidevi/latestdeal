from worker.new.sdk.foundation.dto.models import ProviderManifestDTO

def get_manifest() -> ProviderManifestDTO:
    return ProviderManifestDTO(
        name="amazon",
        version="1.0.0",
        description="Amazon India Commerce Provider Plugin",
        author="LatestDeal Core Team"
    )
