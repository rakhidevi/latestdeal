from typing import Type, Optional
from urllib.parse import urlparse
from interfaces import MerchantScraper

class MerchantDetector:
    @staticmethod
    def detect(url: str) -> str:
        """
        Determines the merchant slug from the given canonical URL.
        """
        parsed = urlparse(url)
        domain = parsed.netloc.lower()
        
        if "amazon" in domain or "amzn" in domain:
            return "amazon"
        elif "flipkart" in domain:
            return "flipkart"
        elif "udemy" in domain:
            return "udemy"
        elif "myntra" in domain:
            return "myntra"
        elif "ajio" in domain:
            return "ajio"
            
        return "unknown"

from amazon_scraper import AmazonScraper
from other_scrapers import FlipkartScraper, UdemyScraper, GenericScraper

# This registry maps merchant slugs to their respective Scraper class implementations.
SCRAPER_REGISTRY: dict[str, Type[MerchantScraper]] = {
    "amazon": AmazonScraper,
    "flipkart": FlipkartScraper,
    "udemy": UdemyScraper,
}

def get_scraper(merchant: str) -> MerchantScraper:
    """Returns an instance of the scraper for the given merchant, if registered."""
    scraper_class = SCRAPER_REGISTRY.get(merchant, GenericScraper)
    return scraper_class()

def register_scraper(merchant: str, scraper_class: Type[MerchantScraper]):
    SCRAPER_REGISTRY[merchant] = scraper_class
