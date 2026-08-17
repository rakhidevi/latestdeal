from .logging_config import logger
from .config import AFFILIATE_STORE_ID

class AffiliateServiceException(Exception):
    pass

class AffiliateService:
    @staticmethod
    def generate_affiliate_link(url: str, merchant: str = "amazon") -> str:
        """
        Generates an affiliate link.
        If generation fails, it raises an exception to quarantine the deal,
        NEVER silently falling back to a raw canonical URL.
        """
        if merchant != "amazon":
            # For this rewrite we focus on Amazon
            raise AffiliateServiceException(f"Unsupported merchant for affiliate generation: {merchant}")
            
        try:
            # Simulate API call to SiteStripe or Amazon PAAPI
            # In production, this would make an actual HTTP request to generate the link
            if not AFFILIATE_STORE_ID:
                raise AffiliateServiceException("AFFILIATE_STORE_ID is missing")
                
            # Naive fallback simulation (In reality, use PAAPI)
            if "?" in url:
                affiliate_url = f"{url}&tag={AFFILIATE_STORE_ID}"
            else:
                affiliate_url = f"{url}?tag={AFFILIATE_STORE_ID}"
                
            return affiliate_url
            
        except Exception as e:
            logger.error(f"Affiliate link generation failed for {url}: {e}")
            raise AffiliateServiceException(f"Failed to generate monetized link: {e}")
