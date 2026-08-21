import logging
from typing import Dict, Any

logger = logging.getLogger(__name__)

class DealIntelligence:
    def process(self, deal: Dict[str, Any]) -> Dict[str, Any]:
        """
        Validates price math and discount claims.
        Distinguishes between displayed, calculated, and effective discounts.
        """
        original_price_val = deal.get('original_price')
        selling_price_val = deal.get('discounted_price')
        coupon_discount = float(deal.get('coupon_discount', 0))
        
        # Parse prices safely
        try:
            original_price = float(original_price_val) if original_price_val is not None else None
        except (ValueError, TypeError):
            original_price = None
            
        try:
            selling_price = float(selling_price_val) if selling_price_val is not None else None
        except (ValueError, TypeError):
            selling_price = None

        # Calculate real discount without coupons
        if original_price is not None and selling_price is not None and original_price > 0:
            if selling_price > original_price:
                # Invalid state
                calc_discount_percent = None
            else:
                discount = ((original_price - selling_price) / original_price) * 100
                calc_discount_percent = round(max(0.0, discount), 2)
        else:
            calc_discount_percent = None
            
        # Calculate effective price and discount with coupons
        if selling_price is not None:
            effective_price = max(selling_price - coupon_discount, 0)
        else:
            effective_price = None
            
        if original_price is not None and effective_price is not None and original_price > 0:
            if effective_price > original_price:
                effective_discount_percent = None
            else:
                eff_discount = ((original_price - effective_price) / original_price) * 100
                effective_discount_percent = round(max(0.0, eff_discount), 2)
        else:
            effective_discount_percent = calc_discount_percent
            
        amount_saved = None
        if original_price is not None and effective_price is not None and original_price > effective_price:
            amount_saved = round(original_price - effective_price, 2)
        elif original_price is not None and effective_price is not None:
            amount_saved = 0.0

        # Store in deal dictionary
        deal['calculated_discount_percent'] = calc_discount_percent
        deal['effective_discount_percent'] = effective_discount_percent
        deal['effective_price'] = effective_price
        deal['amount_saved'] = amount_saved
        
        # We can pass these factual insights to Laravel in the price_intelligence JSON field
        deal['price_intelligence'] = {
            "mrp": original_price,
            "selling_price": selling_price,
            "coupon_discount": coupon_discount,
            "effective_price": effective_price,
            "calculated_discount": calc_discount_percent,
            "effective_discount": effective_discount_percent,
            "displayed_discount": deal.get('displayed_discount_percent')
        }
        
        return deal
