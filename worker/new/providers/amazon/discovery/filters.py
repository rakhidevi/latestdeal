from typing import List, Optional
from dataclasses import dataclass

@dataclass
class AmazonFilter:
    """Base interface for all Amazon Query Filters"""
    def to_query_string(self) -> str:
        raise NotImplementedError

class BrandFilter(AmazonFilter):
    def __init__(self, brands: List[str]):
        self.brands = brands
        
    def to_query_string(self) -> str:
        if not self.brands:
            return ""
        # Example: p_89:Samsung|Apple
        brand_str = "|".join(self.brands)
        return f"p_89:{brand_str}"

class NodeFilter(AmazonFilter):
    def __init__(self, node_id: str):
        self.node_id = node_id
        
    def to_query_string(self) -> str:
        if not self.node_id:
            return ""
        return f"n:{self.node_id}"

class DiscountFilter(AmazonFilter):
    def __init__(self, min_percent: int, max_percent: Optional[int] = None):
        self.min = min_percent
        self.max = max_percent
        
    def to_query_string(self) -> str:
        if self.max:
            return f"p_8:{self.min}-{self.max}"
        return f"p_8:{self.min}-"

class PrimeFilter(AmazonFilter):
    def __init__(self, required: bool = True):
        self.required = required
        
    def to_query_string(self) -> str:
        return "p_n_free_shipping_eligible:2049110031" if self.required else ""

class PriceBandFilter(AmazonFilter):
    def __init__(self, min_price: int, max_price: Optional[int] = None):
        self.min = min_price * 100 # Amazon uses paise internally for filters
        self.max = max_price * 100 if max_price else None
        
    def to_query_string(self) -> str:
        if self.max:
            return f"p_36:{self.min}-{self.max}"
        return f"p_36:{self.min}-"
