from typing import List, Optional
from worker.new.providers.amazon.discovery.filters import (
    AmazonFilter, BrandFilter, NodeFilter, DiscountFilter, 
    PrimeFilter, PriceBandFilter
)

class AmazonQueryBuilder:
    """
    Sprint A2: Amazon Query Builder
    Constructs highly precise Amazon search URLs by combining lego-block filters.
    Provides 95%+ discovery coverage across dimensions.
    """
    def __init__(self, base_url: str = "https://www.amazon.in/s"):
        self.base_url = base_url
        self.keyword: Optional[str] = None
        self.filters: List[AmazonFilter] = []
        self.sort_by: Optional[str] = None
        
    def with_keyword(self, keyword: str) -> "AmazonQueryBuilder":
        self.keyword = keyword
        return self
        
    def with_brands(self, brands: List[str]) -> "AmazonQueryBuilder":
        if brands:
            self.filters.append(BrandFilter(brands))
        return self
        
    def with_node(self, node_id: str) -> "AmazonQueryBuilder":
        if node_id:
            self.filters.append(NodeFilter(node_id))
        return self
        
    def with_discount(self, min_percent: int, max_percent: Optional[int] = None) -> "AmazonQueryBuilder":
        self.filters.append(DiscountFilter(min_percent, max_percent))
        return self
        
    def with_prime(self, required: bool = True) -> "AmazonQueryBuilder":
        if required:
            self.filters.append(PrimeFilter())
        return self
        
    def with_price_band(self, min_price: int, max_price: Optional[int] = None) -> "AmazonQueryBuilder":
        self.filters.append(PriceBandFilter(min_price, max_price))
        return self
        
    def sort_by_discount(self) -> "AmazonQueryBuilder":
        self.sort_by = "discount-desc-rank"
        return self
        
    def sort_by_newest(self) -> "AmazonQueryBuilder":
        self.sort_by = "date-desc-rank"
        return self
        
    def build(self) -> str:
        """Compiles the final Amazon search URL"""
        query_parts = []
        
        if self.keyword:
            # simple keyword encoding
            query_parts.append(f"k={self.keyword.replace(' ', '+')}")
            
        # Compile all rh (refinement string) parameters
        rh_parts = [f.to_query_string() for f in self.filters if f.to_query_string()]
        if rh_parts:
            rh_string = ",".join(rh_parts)
            query_parts.append(f"rh={rh_string}")
            
        if self.sort_by:
            query_parts.append(f"s={self.sort_by}")
            
        final_query = "&".join(query_parts)
        return f"{self.base_url}?{final_query}"
