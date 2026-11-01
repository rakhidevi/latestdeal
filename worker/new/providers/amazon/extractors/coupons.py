from typing import List, Dict, Any, Optional
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO, TraceContext, CanonicalDealDTO
from worker.new.sdk.provider_sdk.base import BaseExtractor
from bs4 import BeautifulSoup
import re
import json

class AmazonCouponExtractor(BaseExtractor):
    """
    Extracts coupon-bearing items from Amazon Coupon hubs and search grids,
    capturing granular coupon attributes: discount type, amount/percentage,
    coupon/promo code, clip requirement, and shopper eligibility.
    """

    def parse_coupon_details(self, text: str) -> Dict[str, Any]:
        """
        Parses raw coupon element text into structured offer fields.
        """
        cleaned = text.strip()
        data = {
            'discount_type': 'clip',
            'discount_value': 0.0,
            'discount_percentage': 0.0,
            'clip_required': True,
            'coupon_code': None,
            'eligibility': 'All Shoppers',
            'raw_text': cleaned
        }

        # 1. Promo / coupon code if explicit code is specified
        code_m = re.search(r'(?:use\s+)?(?:promo\s+code|coupon\s+code|promo|code)\s*[:=]?\s*([A-Z0-9]{4,15})\b', cleaned, re.I)
        if code_m:
            data['coupon_code'] = code_m.group(1).upper()
            data['clip_required'] = False
            data['discount_type'] = 'promo_code'

        # 2. Percentage coupon: e.g. Save 15% with coupon, 20% coupon
        pct_m = re.search(r'(\d+(?:\.\d+)?)\s*%\s*(?:off|coupon|\soff\b)', cleaned, re.I) or re.search(r'Save\s*(\d+(?:\.\d+)?)\s*%', cleaned, re.I)
        if pct_m:
            data['discount_type'] = 'percentage'
            data['discount_percentage'] = float(pct_m.group(1))

        # 3. Fixed amount coupon: e.g. Save ₹250 with coupon, ₹1,500 off
        amt_m = re.search(r'(?:Save\s*)?(?:₹|Rs\.?\s*)\s*([\d,]+(?:\.\d+)?)\s*(?:with\s+coupon|off|\scoupon\b)', cleaned, re.I) or re.search(r'Coupon:?\s*(?:₹|Rs\.?\s*)\s*([\d,]+(?:\.\d+)?)', cleaned, re.I)
        if amt_m and data['discount_type'] != 'percentage':
            data['discount_type'] = 'fixed'
            data['discount_value'] = float(amt_m.group(1).replace(',', ''))

        # 4. Eligibility & Requirements
        if re.search(r'Prime\s*(?:Savings|exclusive|members)', cleaned, re.I):
            data['eligibility'] = 'Prime Exclusive'
        elif re.search(r'min(?:imum)?\s*(?:order|spend|purchase)', cleaned, re.I):
            min_spend_m = re.search(r'(?:₹|Rs\.?\s*)\s*([\d,]+)', cleaned, re.I)
            data['eligibility'] = f"Min spend ₹{min_spend_m.group(1)}" if min_spend_m else "Minimum spend required"

        return data

    def extract_product(self, raw_payload: Dict[str, Any], trace_context: TraceContext) -> Optional[UniversalProductDTO]:
        html = raw_payload.get('html', '')
        if not html:
            return None
        soup = BeautifulSoup(html, 'html.parser')

        # Check for ASIN
        asin_elem = soup.find('input', {'id': 'ASIN'}) or soup.find('input', {'name': 'ASIN'})
        asin = asin_elem.get('value') if asin_elem else None
        if not asin:
            url = raw_payload.get('url', '')
            asin_m = re.search(r'/dp/([A-Z0-9]{10})', url)
            if asin_m:
                asin = asin_m.group(1)

        if not asin:
            return None

        # Title
        title_el = soup.find('span', {'id': 'productTitle'})
        title = title_el.get_text(strip=True) if title_el else "Amazon Verified Product"

        # Check for coupon element on product detail page
        coupon_el = soup.find('div', {'id': re.compile(r'coupon|vpc', re.I)}) or soup.find('label', {'for': re.compile(r'coupon', re.I)})
        features = []
        if coupon_el:
            coupon_data = self.parse_coupon_details(coupon_el.get_text())
            features.extend([
                f"coupon:type:{coupon_data['discount_type']}",
                f"coupon:value:{coupon_data['discount_value']}",
                f"coupon:percent:{coupon_data['discount_percentage']}",
                f"coupon:clip:{coupon_data['clip_required']}",
                f"coupon:code:{coupon_data['coupon_code'] or ''}",
                f"coupon:eligibility:{coupon_data['eligibility']}",
                f"coupon:json:{json.dumps(coupon_data)}"
            ])

        return UniversalProductDTO(
            provider="AmazonProvider",
            provider_product_id=asin,
            url=f"https://www.amazon.in/dp/{asin}",
            title=title[:120],
            features=features
        )

    def extract_deal(self, raw_payload: Dict[str, Any], product_uuid: str, trace_context: TraceContext) -> Optional[CanonicalDealDTO]:
        html = raw_payload.get('html', '')
        if not html:
            return None
        soup = BeautifulSoup(html, 'html.parser')

        # Extract price
        price = 0.0
        price_el = soup.select_one('.priceToPay .a-price-whole') or soup.select_one('#priceblock_ourprice') or soup.select_one('#priceblock_dealprice')
        if price_el:
            price_str = price_el.get_text(strip=True).replace(',', '')
            try:
                price = float(price_str)
            except ValueError:
                pass

        if price <= 0:
            return None

        # Extract coupon details if present
        coupon_el = soup.find('div', {'id': re.compile(r'coupon|vpc', re.I)}) or soup.find('label', {'for': re.compile(r'coupon', re.I)})
        coupon_details = None
        effective_price = price

        if coupon_el:
            details = self.parse_coupon_details(coupon_el.get_text())
            coupon_details = json.dumps(details)
            if details['discount_type'] == 'fixed' and details['discount_value'] > 0:
                effective_price = max(0.0, price - details['discount_value'])
            elif details['discount_type'] == 'percentage' and details['discount_percentage'] > 0:
                effective_price = max(0.0, price * (1.0 - details['discount_percentage'] / 100.0))

        # Extract MRP
        mrp = None
        mrp_el = soup.select_one('.a-text-price .a-offscreen')
        if mrp_el:
            mrp_str = re.sub(r'[^\d.]', '', mrp_el.get_text(strip=True))
            try:
                mrp_val = float(mrp_str)
                if mrp_val >= price:
                    mrp = mrp_val
            except ValueError:
                pass

        discount_pct = round(((mrp - effective_price) / mrp) * 100.0, 1) if (mrp and mrp > effective_price) else 0.0

        return CanonicalDealDTO(
            universal_product_uuid=product_uuid,
            trace_context=trace_context,
            price=price,
            effective_price=effective_price,
            mrp=mrp,
            discount_percentage=discount_pct,
            coupon_details=coupon_details
        )

    def extract_grid(self, raw_payload: Dict[str, Any], target: SearchTargetDTO) -> List[UniversalProductDTO]:
        html = raw_payload.get('html', '')
        soup = BeautifulSoup(html, 'html.parser')
        products = []
        
        # Search for coupons hub items and search result cards with active coupon tags
        coupon_items = soup.find_all("div", {"class": re.compile(r'(?:coupon-item-container|s-result-item)', re.I)})
        
        for item in coupon_items:
            asin = item.get("data-asin")
            if not asin or len(asin) < 8 or len(asin) > 12:
                continue
                
            # Locate coupon badge or text element
            coupon_elem = (
                item.find("span", {"class": re.compile(r's-coupon-highlight-color|couponLabelText', re.I)}) or
                item.find("span", {"class": re.compile(r'a-color-success|coupon-badge', re.I)}) or
                item.find(string=re.compile(r'(?:Save|Coupon|off with coupon)', re.I))
            )
            
            if not coupon_elem:
                continue

            raw_coupon_text = coupon_elem.get_text(strip=True) if hasattr(coupon_elem, 'get_text') else str(coupon_elem).strip()
            coupon_data = self.parse_coupon_details(raw_coupon_text)

            title_el = item.find(["h2", "span"], {"class": re.compile(r'a-text-normal|coupon-title', re.I)})
            title = title_el.get_text(strip=True) if title_el else "Amazon Verified Coupon Deal"
            
            features = [
                f"coupon:type:{coupon_data['discount_type']}",
                f"coupon:value:{coupon_data['discount_value']}",
                f"coupon:percent:{coupon_data['discount_percentage']}",
                f"coupon:clip:{coupon_data['clip_required']}",
                f"coupon:code:{coupon_data['coupon_code'] or ''}",
                f"coupon:eligibility:{coupon_data['eligibility']}",
                f"coupon:raw:{coupon_data['raw_text']}",
                json.dumps({
                    "offer_type": "coupon_clip" if coupon_data['clip_required'] else "promo_code",
                    "discount_type": coupon_data['discount_type'],
                    "discount_value": coupon_data['discount_value'] if coupon_data['discount_value'] > 0 else coupon_data['discount_percentage'],
                    "coupon_code": coupon_data['coupon_code'],
                    "eligibility": coupon_data['eligibility']
                })
            ]

            product = UniversalProductDTO(
                provider="AmazonProvider",
                provider_product_id=asin,
                url=f"https://www.amazon.in/dp/{asin}",
                title=title[:120],
                features=features
            )
            products.append(product)
            
        return products
