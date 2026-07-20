from dataclasses import dataclass, field
from typing import Optional, List
from datetime import datetime

@dataclass(frozen=True)
class PriceDTO:
    current: float
    original: Optional[float] = None
    discount_pct: Optional[float] = None

@dataclass(frozen=True)
class CouponDTO:
    code: str
    discount_value: float
    is_percentage: bool

@dataclass(frozen=True)
class ProductDTO:
    id: str  # e.g., ASIN or Product ID
    title: str
    brand: Optional[str]
    category: Optional[str]
    image_url: str
    url: str

@dataclass(frozen=True)
class DealDTO:
    provider: str
    product: ProductDTO
    price: PriceDTO
    coupon: Optional[CouponDTO] = None
    is_active: bool = True
    features: List[str] = field(default_factory=list)
    timestamp: datetime = field(default_factory=datetime.utcnow)
