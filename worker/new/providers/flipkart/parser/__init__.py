from .price import extract_price
from .title import extract_title
from worker.new.core.dtos import DealDTO, ProductDTO, PriceDTO

class FlipkartParser:
    def extract_raw(self, soup) -> dict:
        return {
            "title": extract_title(soup),
            "price": extract_price(soup),
        }
        
    def to_dto(self, raw_data: dict, url: str) -> DealDTO:
        pid = url.split("/p/")[-1][:16] if "/p/" in url else "unknown"
        return DealDTO(
            provider="flipkart",
            product=ProductDTO(
                id=pid,
                title=raw_data.get("title", "Unknown Title"),
                brand=None,
                category=None,
                image_url="",
                url=url
            ),
            price=PriceDTO(
                current=raw_data.get("price", 0.0)
            )
        )
