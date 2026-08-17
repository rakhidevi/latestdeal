from typing import List, Dict, Any, Optional
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO, TraceContext, CanonicalDealDTO
from worker.new.sdk.provider_sdk.base import BaseExtractor

class AmazonTrendingExtractor(BaseExtractor):
    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        return None
    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        return None
    def extract_grid(self, raw_payload: Dict[str, Any], target: SearchTargetDTO) -> List[UniversalProductDTO]:
        from worker.new.providers.amazon.extractors.common_grid import parse_amazon_grid
        html = raw_payload.get('html', '')
        return parse_amazon_grid(html, grid_type="TRENDING")
