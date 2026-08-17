from typing import Optional
from worker.new.sdk.provider_sdk.base import BaseValidator
from worker.new.sdk.foundation.dto.models import UniversalProductDTO, CanonicalDealDTO

class AmazonValidator(BaseValidator):
    """
    Pre-extraction and post-extraction business logic validation for Amazon.
    Checks ASIN Exists, Price Exists, Seller Exists, Stock Available, etc.
    """
    def __init__(self):
        self._rejection_reason: Optional[str] = None
        
    def validate(self, product: UniversalProductDTO, deal: CanonicalDealDTO) -> bool:
        self._rejection_reason = None
        
        if not product.provider_product_id:
            self._rejection_reason = "Missing ASIN"
            return False
            
        if deal.price <= 0:
            self._rejection_reason = "Invalid or Missing Price"
            return False
            
        if not deal.availability:
            self._rejection_reason = "Out of Stock"
            return False
            
        # Example Amazon quirk check: If discount > 95%, ensure seller is trusted
        if deal.discount_percentage > 95:
            if not deal.seller or deal.seller.lower() == "unknown":
                self._rejection_reason = "Suspicious 95%+ discount with unknown seller"
                return False
                
        return True
        
    def get_rejection_reason(self) -> Optional[str]:
        return self._rejection_reason
