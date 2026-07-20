from abc import ABC, abstractmethod
from typing import Dict, Any, Optional
from models import Deal

class MerchantScraper(ABC):
    """
    Base interface for all merchant-specific scrapers.
    """
    
    @classmethod
    @abstractmethod
    def can_handle(cls, domain: str) -> bool:
        """Returns True if this scraper can handle the given domain."""
        pass

    @abstractmethod
    def extract(self, url: str) -> Deal:
        """
        Extracts product data from the canonical URL and returns a Deal object.
        """
        pass

    @abstractmethod
    def generate_affiliate(self, deal: Deal) -> str:
        """
        Generates the final affiliate tracking URL for the deal.
        """
        pass
