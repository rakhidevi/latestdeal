from bs4 import BeautifulSoup
import re
from typing import Dict, Any
from worker.new.core.dtos import DealDTO, ProductDTO, PriceDTO
from worker.new.core.errors import ParsingError

class AmazonParser:
    def extract_raw(self, soup: BeautifulSoup) -> Dict[str, Any]:
        # 1. Product Title
        title = ""
        title_tag = soup.find(id="productTitle") or soup.find(id="title")
        if title_tag:
            title = title_tag.get_text(strip=True)
        if not title and soup.title:
            title = soup.title.get_text(strip=True).replace("Amazon.in: Buy ", "").split(":")[0].strip()
            
        # 2. Discounted Price
        discounted_price = ""
        price_selectors = [
            ".priceToPay .a-price-whole", 
            "#corePriceDisplay_desktop_feature_div .a-price-whole",
            "#corePrice_desktop .a-price-whole",
            "#priceblock_dealprice", 
            "#priceblock_ourprice",
            "span.a-price-whole"
        ]
        for sel in price_selectors:
            price_tag = soup.select_one(sel)
            if price_tag:
                discounted_price = price_tag.get_text(strip=True)
                break
                
        # 3. Original Price
        original_price = ""
        orig_selectors = [
            ".a-text-price .a-offscreen",
            "#priceBlockStrikePriceString",
            "span.a-price.a-text-price span.a-offscreen"
        ]
        for sel in orig_selectors:
            orig_tag = soup.select_one(sel)
            if orig_tag:
                val = orig_tag.get_text(strip=True)
                val_lower = val.lower()
                if "per g" not in val_lower and "/100" not in val_lower and "per 100" not in val_lower and "/ 100" not in val_lower:
                    original_price = val
                    break
                    
        if not original_price or "per" in original_price.lower() or "/100" in original_price.lower():
            # Try to find M.R.P.: label directly
            mrp_label = soup.find(lambda tag: tag.name == "span" and "M.R.P.:" in tag.get_text())
            if mrp_label:
                parent = mrp_label.parent
                if parent:
                    original_price = parent.get_text(strip=True).replace('M.R.P.:', '').strip()
                    
        # 4. Image
        image_url = ""
        img_tag = soup.find(id="landingImage") or soup.find(id="imgBlkFront")
        if img_tag:
            dynamic_imgs = img_tag.get("data-a-dynamic-image")
            if dynamic_imgs:
                import json
                try:
                    imgs = json.loads(dynamic_imgs)
                    if imgs:
                        image_url = max(imgs.items(), key=lambda x: x[1][0] + x[1][1])[0]
                except:
                    pass
            if not image_url:
                image_url = img_tag.get("data-old-hires") or img_tag.get("src") or ""
        
        if not image_url:
            og_img = soup.find("meta", property="og:image")
            if og_img:
                image_url = og_img.get("content", "")
                
        # 5. Features
        features = []
        feature_tags = soup.select("#feature-bullets ul li span.a-list-item")
        if not feature_tags:
            feature_tags = soup.select("#featurebullets_feature_div ul li span.a-list-item")
        
        for tag in feature_tags:
            txt = tag.get_text(strip=True)
            if txt:
                features.append(txt)
                
        return {
            "title": title,
            "discounted_price": discounted_price,
            "original_price": original_price,
            "image_url": image_url,
            "features": features
        }

    def _clean_price(self, price_str: str) -> float:
        if not price_str:
            return 0.0
        cleaned = re.sub(r'[^\d.]', '', price_str.replace(',', ''))
        try:
            return float(cleaned)
        except:
            return 0.0

    def to_dto(self, raw: Dict[str, Any], url: str) -> DealDTO:
        dp = self._clean_price(raw.get("discounted_price", ""))
        op = self._clean_price(raw.get("original_price", ""))
        
        if not dp:
            raise ParsingError("Could not extract discounted price")
            
        discount_pct = 0.0
        if op and op > dp:
            discount_pct = round(((op - dp) / op) * 100, 2)
            
        price_dto = PriceDTO(current=dp, original=op if op > 0 else None, discount_pct=discount_pct)
        product_dto = ProductDTO(
            id=url.split('/')[-1] if '/' in url else "unknown",
            title=raw.get("title", ""),
            brand=None,
            category=None,
            image_url=raw.get("image_url", ""),
            url=url
        )
        
        return DealDTO(
            provider="amazon",
            product=product_dto,
            price=price_dto,
            features=raw.get("features", [])
        )
