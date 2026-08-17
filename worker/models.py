from pydantic import BaseModel, Field
from typing import Optional

# ==========================================
# STRUCTURED ERRORS
# ==========================================

class ScraperException(Exception):
    """Base exception for all scraping pipeline errors."""
    pass

class UrlResolveFailed(ScraperException):
    pass

class MerchantNotSupported(ScraperException):
    pass

class AffiliateLinkFailed(ScraperException):
    pass

class ScraperTimeout(ScraperException):
    pass

class PlaywrightTimeout(ScraperException):
    pass

class AICategoryFailed(ScraperException):
    pass

class DatabaseValidationFailed(ScraperException):
    pass


# ==========================================
# STANDARDIZED SCHEMAS
# ==========================================

class DealCategory(BaseModel):
    name: str = Field(description="The canonical name of the category")
    confidence: float = Field(default=0.0, ge=0.0, le=1.0, description="Confidence score from AI or Keyword Classifier")

class Deal(BaseModel):
    merchant: str = Field(description="The domain or slug of the merchant (e.g. amazon, flipkart)")
    title: str = Field(description="Product title")
    price: Optional[float] = Field(default=None, description="Discounted price")
    original_price: Optional[float] = Field(default=None, description="Original MSRP")
    discount_percent: Optional[float] = Field(default=None, description="Discount percentage")
    image_url: Optional[str] = Field(default=None, description="Main product image URL")
    
    canonical_url: str = Field(description="The raw un-affiliatized product URL")
    affiliate_url: Optional[str] = Field(default=None, description="The final affiliate tracking URL")
    
    coupon: Optional[str] = Field(default=None, description="Promo code if any")
    category: Optional[DealCategory] = Field(default=None, description="Categorization info")
    brand: Optional[str] = Field(default=None, description="Product brand")
    
    rating: Optional[float] = Field(default=None, description="Product rating (e.g. 4.5)")
    review_count: Optional[int] = Field(default=None, description="Number of customer reviews")
    is_prime: Optional[bool] = Field(default=False, description="Is it Amazon Prime eligible?")
    is_fulfilled: Optional[bool] = Field(default=False, description="Is it fulfilled by the platform (e.g., FBA)?")
    availability: str = Field(default="In Stock")
    
    source: str = Field(default="telegram", description="Where this deal came from (telegram, hunter, etc)")
    
    ai_caption: Optional[str] = Field(default=None, description="Generated social media caption")
    ai_score: Optional[int] = Field(default=None, ge=1, le=100, description="1-100 deal score")
    trust_metrics: Optional[dict] = Field(default=None, description="Checklist of trust factors")
    verdict: Optional[str] = Field(default=None, description="AI recommendation: Buy Now vs Wait")
    
    confidence_score: Optional[int] = Field(default=None, ge=1, le=100, description="Confidence in deal quality")
    confidence_reasons: Optional[list] = Field(default_factory=list, description="Reasons for confidence score")
