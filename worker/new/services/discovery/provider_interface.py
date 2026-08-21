from abc import ABC, abstractmethod
from typing import List, Dict, Any

class DiscoveryProvider(ABC):
    """
    Abstract base class for all Discovery Providers.
    A Discovery Provider is responsible for taking a set of criteria (brand, category, keywords, min_discount)
    and returning a list of raw product dictionaries.
    """

    @abstractmethod
    def search(self, criteria: Dict[str, Any]) -> List[Dict[str, Any]]:
        """
        Execute a search based on the provided criteria.
        
        Args:
            criteria: Dictionary containing:
                - brand_name (str, optional)
                - category_name (str, optional)
                - product_type (str, optional)
                - min_discount_percent (float, optional)
                - max_discount_percent (float, optional)
                - min_price (float, optional)
                - max_price (float, optional)
                - keywords (str, optional)
                
        Returns:
            List of dictionaries representing raw discovered products.
            Expected fields per product:
            - source_id (e.g. ASIN)
            - url
            - title
            - original_price
            - discounted_price
            - image_url
            - merchant_name
        """
        pass
