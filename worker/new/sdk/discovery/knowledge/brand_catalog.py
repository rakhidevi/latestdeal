from typing import Dict, List, Optional
from worker.new.sdk.foundation.dto.models import PluginManifestV2

class BrandStoreEntry:
    def __init__(self, brand_name: str, provider: str, store_url: str, priority: int = 50):
        self.brand_name = brand_name
        self.provider = provider
        self.store_url = store_url
        self.priority = priority

class BrandCatalogService:
    """
    Maintains a catalog of known brand store entry points across providers.
    """
    def __init__(self):
        # In a real system, this would load from a database or JSON knowledge base.
        # For now, it's a hardcoded memory map for top brands.
        self._stores: List[BrandStoreEntry] = [
            BrandStoreEntry("Samsung", "AmazonProvider", "https://www.amazon.in/stores/Samsung/page/E51FE0CD-7721-41CE-AEF4-566A651613A8", 90),
            BrandStoreEntry("Apple", "AmazonProvider", "https://www.amazon.in/stores/Apple/page/DD3C6AA8-AD53-43EB-BFA7-31C387B64D3F", 90),
            BrandStoreEntry("LG", "AmazonProvider", "https://www.amazon.in/stores/LG/page/034CE209-D6C6-4074-8FE5-E798CE14C8F8", 80),
            BrandStoreEntry("Sony", "AmazonProvider", "https://www.amazon.in/stores/Sony/page/895318DE-C35A-4545-B4D3-98C39E98D658", 85)
        ]
        
    def get_brand_stores_for_provider(self, provider_name: str) -> List[BrandStoreEntry]:
        return [s for s in self._stores if s.provider == provider_name]

    def get_store_url(self, brand_name: str, provider_name: str) -> Optional[str]:
        for store in self._stores:
            if store.brand_name.lower() == brand_name.lower() and store.provider == provider_name:
                return store.store_url
        return None
