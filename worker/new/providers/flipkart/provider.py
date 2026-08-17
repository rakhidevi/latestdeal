from worker.new.sdk.provider_sdk.base import (
    BaseProvider, BaseDiscoveryProvider, BaseExtractor, 
    BaseValidator, BasePublisher, ProviderHealth, PluginManifestV2
)
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO, CanonicalDealDTO, TraceContext
from typing import Dict, Any, List, Optional
from .manifest import get_manifest
from .capabilities import get_capabilities
from .compatibility_layer import FlipkartCompatibilityLayer
from bs4 import BeautifulSoup
import re

class FlipkartDiscoveryProvider(BaseDiscoveryProvider):
    def generate_urls(self, target: SearchTargetDTO) -> List[str]:
        # Implementation of Flipkart URL query building
        base_url = "https://www.flipkart.com/search"
        query_params = []
        
        if target.constraints:
            if "keyword" in target.constraints:
                query_params.append(f"q={target.constraints['keyword']}")
            if "category" in target.constraints:
                query_params.append(f"p%5B%5D=facets.category_tree%255B%255D%3D{target.constraints['category']}")
                
        if query_params:
            return [f"{base_url}?{'&'.join(query_params)}"]
        return [base_url]

class FlipkartExtractor(BaseExtractor):
    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        html = raw_payload.get('html', '')
        url = raw_payload.get('url', 'https://www.flipkart.com/')
        soup = BeautifulSoup(html, 'html.parser')
        
        # In a real environment, we use actual Flipkart classes like 'B_NuCI' for title
        title_elem = soup.find('span', {'class': 'B_NuCI'})
        title = title_elem.text.strip() if title_elem else "Flipkart Product"
        
        # Flipkart FSN (like Amazon ASIN)
        fsn_match = re.search(r'/p/itme?([a-zA-Z0-9]+)', url)
        fsn = fsn_match.group(1) if fsn_match else "UNKNOWN_FSN"
        
        return UniversalProductDTO(
            provider="Flipkart",
            provider_product_id=fsn,
            url=url,
            title=title
        )
        
    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        html = raw_payload.get('html', '')
        soup = BeautifulSoup(html, 'html.parser')
        
        # Extract price (class: _30jeq3 _16Jk6d)
        price_elem = soup.find('div', {'class': '_30jeq3 _16Jk6d'})
        price_text = price_elem.text.replace('₹', '').replace(',', '') if price_elem else '0'
        
        # Extract MRP (class: _3I9_wc _2p6lqe)
        mrp_elem = soup.find('div', {'class': '_3I9_wc _2p6lqe'})
        mrp_text = mrp_elem.text.replace('₹', '').replace(',', '') if mrp_elem else price_text
        
        try:
            price = float(price_text)
            mrp = float(mrp_text)
        except ValueError:
            price = 0.0
            mrp = 0.0
            
        discount = 0.0
        if mrp > 0 and price < mrp:
            discount = ((mrp - price) / mrp) * 100.0
            
        return CanonicalDealDTO(
            universal_product_uuid=product_uuid,
            trace_context=trace_context,
            price=price,
            mrp=mrp,
            discount_percentage=discount,
            provider="Flipkart"
        )

class FlipkartHealthTracker(ProviderHealth):
    def __init__(self):
        self.successes = 0
        self.failures = 0
        
    def record_success(self) -> None:
        self.successes += 1
        
    def record_failure(self, reason: str) -> None:
        self.failures += 1
        
    def get_health_score(self) -> float:
        total = self.successes + self.failures
        if total == 0: return 100.0
        return (self.successes / total) * 100.0

class FlipkartProvider(BaseProvider):
    def __init__(self):
        super().__init__(get_manifest())
        self._capabilities = get_capabilities() # Kept for backward compatibility
        self.compatibility = FlipkartCompatibilityLayer()
        self._discovery = FlipkartDiscoveryProvider()
        self._extractor = FlipkartExtractor()
        self._health = FlipkartHealthTracker()
        
    def get_manifest(self) -> PluginManifestV2:
        return get_manifest()
        
    def get_discovery_provider(self) -> BaseDiscoveryProvider:
        return self._discovery
        
    def get_extractor(self) -> BaseExtractor:
        return self._extractor
        
    def get_health_tracker(self) -> ProviderHealth:
        return self._health
