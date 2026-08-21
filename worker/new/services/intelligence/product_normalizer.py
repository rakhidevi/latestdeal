import re
from typing import Dict, Any

class ProductNormalizer:
    def process(self, raw_deal: Dict[str, Any]) -> Dict[str, Any]:
        """
        Cleans the raw title and extracts basic structured data if possible.
        E.g. "PUMA Men's Tazon 6 Fracture FM Sneaker, Black/White, 10 M US" 
          -> "PUMA Tazon 6 Fracture Men's Running Shoe"
        """
        title = raw_deal.get('title', '')
        
        # Very basic regex based normalization for now
        # Remove common extraneous details like sizes, exact color combos at the end
        normalized_title = re.sub(r',\s*(Black/White|Red|Blue|Green|Yellow|Black|White|Grey|Silver|Gold)[^,]*', '', title, flags=re.IGNORECASE)
        normalized_title = re.sub(r',\s*\d+\s*(M|W|US|UK|EU).*$', '', normalized_title, flags=re.IGNORECASE)
        
        # Basic cleanup
        normalized_title = re.sub(r'\s+', ' ', normalized_title).strip()
        
        raw_deal['normalized_title'] = normalized_title if normalized_title else title
        return raw_deal
