from typing import List, Dict, Any, Optional
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO, TraceContext, CanonicalDealDTO, CouponEvidence
from worker.new.sdk.provider_sdk.base import BaseExtractor
from bs4 import BeautifulSoup
import re

class AmazonCouponExtractor(BaseExtractor):
    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        return None

    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        return None

    def extract_grid(self, raw_payload: Dict[str, Any], target: SearchTargetDTO) -> List[UniversalProductDTO]:
        html = raw_payload.get('html', '')
        soup = BeautifulSoup(html, 'html.parser')
        products = []
        
        # 1. Search for coupons hub items and search result cards with active coupon tags
        coupon_items = soup.find_all("div", {"class": re.compile(r'(?:coupon-item-container|s-result-item)', re.I)})
        
        for item in coupon_items:
            asin = item.get("data-asin")
            if not asin or len(asin) != 10:
                continue
                
            # Check for positive coupon evidence in this item
            has_coupon = bool(
                item.find("span", {"class": re.compile(r's-coupon-highlight-color|couponLabelText', re.I)}) or
                item.find(string=re.compile(r'(?:Save|Coupon|off with coupon)', re.I))
            )
            
            if not has_coupon:
                continue

            title_el = item.find(["h2", "span"], {"class": re.compile(r'a-text-normal|coupon-title', re.I)})
            title = title_el.get_text(strip=True) if title_el else "Amazon Verified Coupon Deal"
            
            product = UniversalProductDTO(
                provider="AmazonProvider",
                provider_product_id=asin,
                url=f"https://www.amazon.in/dp/{asin}",
                title=title[:120]
            )
            products.append(product)
            
        # Return empty list when no genuine coupons are found. Never inject synthetic items.
        return products
