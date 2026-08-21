import requests
import logging
from typing import Dict, Any, List

logger = logging.getLogger(__name__)

class BrandResolver:
    def __init__(self, api_base_url: str, api_token: str):
        self.api_base_url = api_base_url
        self.api_token = api_token
        self.headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json"
        }
        self.brands_cache = []

    def _fetch_brands(self):
        if not self.brands_cache:
            try:
                url = f"{self.api_base_url}/api/worker/intelligence/brands"
                response = requests.get(url, headers=self.headers, timeout=10)
                if response.status_code == 200:
                    self.brands_cache = response.json()
            except Exception as e:
                logger.error(f"Failed to fetch brands: {e}")

    def process(self, deal: Dict[str, Any]) -> Dict[str, Any]:
        """
        Priority:
        1. Structured source brand (if provided by scraper)
        2. Known brand aliases
        3. Title extraction
        """
        self._fetch_brands()
        
        source_brand = deal.get('source_brand')
        title = deal.get('normalized_title', deal.get('title', ''))
        title_lower = title.lower()
        
        resolved_brand_id = None
        resolved_brand_name = None
        
        import re
        
        # Priority 1: Structured source brand
        if source_brand:
            source_brand_lower = source_brand.lower()
            for brand in self.brands_cache:
                if brand['name'].lower() == source_brand_lower:
                    resolved_brand_id = brand['id']
                    resolved_brand_name = brand['name']
                    break
            
            # If source_brand doesn't map to a DB ID exactly, we still trust it as the name
            if not resolved_brand_name:
                resolved_brand_name = source_brand

        # Priority 2: Title extraction fallback
        if not resolved_brand_name:
            for brand in self.brands_cache:
                brand_name = brand['name']
                if brand_name.lower() in title_lower:
                    if re.search(r'\b' + re.escape(brand_name.lower()) + r'\b', title_lower):
                        resolved_brand_id = brand['id']
                        resolved_brand_name = brand_name
                        break

        deal['resolved_brand_id'] = resolved_brand_id
        deal['resolved_brand_name'] = resolved_brand_name
        
        return deal
