import re

def clean_number(val, return_type=float):
    """Extracts numeric value from a string."""
    if not val:
        return 0
    # Remove commas to keep numbers contiguous
    val_str = str(val).replace(',', '')
    
    # Extract the first sequence of digits with an optional decimal
    match = re.search(r'(\d+(?:\.\d+)?)', val_str)
    if match:
        try:
            return return_type(match.group(1))
        except:
            pass
    return 0


# Blocked keywords for illegal / pirated content (case-insensitive check)
BLOCKED_KEYWORDS = [
    'mod apk', 'modded apk', 'cracked apk',
    'premium unlocked', 'unlocked all', 'pro unlocked',
    'no watermark', 'ad free mod', 'ads removed mod',
    'crack', 'cracked', 'keygen', 'serial key',
    'pirated', 'warez', 'nulled',
    'paid apk free', 'patched apk',
]

def evaluate_deal(raw_data: dict) -> dict:
    """
    Evaluates the scraped raw_data against strict guardrails.
    Returns a dictionary with 'is_approved', 'reason', and structured 'metrics'.
    """
    # --- Rule 0: Block Illegal / Pirated Content ---
    title = raw_data.get("raw_title", "")
    title_lower = title.lower()
    for kw in BLOCKED_KEYWORDS:
        if kw in title_lower:
            return {"is_approved": False, "reason": f"Blocked: illegal content detected ('{kw}')"}

    if raw_data.get("out_of_stock"):
        return {"is_approved": False, "reason": "Out of Stock"}
        
    if not title:
        return {"is_approved": False, "reason": "Missing Title"}
        
    original_price = clean_number(raw_data.get("raw_original_price", 0))
    discounted_price = clean_number(raw_data.get("raw_discounted_price", 0))
    
    # If there is no original price, we can't calculate a deal
    if original_price <= 0 or discounted_price <= 0:
        return {"is_approved": False, "reason": "Missing Price Information"}
        
    discount_percent = ((original_price - discounted_price) / original_price) * 100
    
    # Extract Trust Metrics
    star_rating = clean_number(raw_data.get("star_rating", 0))
    review_count = clean_number(raw_data.get("review_count", 0), return_type=int)
    brand_name = raw_data.get("brand_name", "").strip()
    
    # Rule 1: Discount Threshold (Must be > 20%)
    if discount_percent < 20:
        return {"is_approved": False, "reason": f"Discount too low ({discount_percent:.1f}%)"}
        
    # Rule 2: Trust Factor (Relaxed for Fashion/Clearance)
    # Allow >= 3.0 stars. If star_rating is 0 (no reviews found), allow it ONLY if discount is > 60%
    if star_rating > 0 and star_rating < 3.0:
        return {"is_approved": False, "reason": f"Star rating too low ({star_rating})"}
        
    if star_rating == 0 and discount_percent < 60:
        return {"is_approved": False, "reason": f"No reviews and discount is not high enough ({discount_percent:.1f}%)"}
        
    # Optional: Rule 3: MRP Error / Jackpot Check
    is_jackpot = False
    if discount_percent >= 90 and brand_name.lower() not in ["", "generic", "unknown"]:
        is_jackpot = True
        
    metrics = {
        "original_price": original_price,
        "discounted_price": discounted_price,
        "discount_percent": round(discount_percent, 1),
        "star_rating": star_rating,
        "review_count": review_count,
        "brand_name": brand_name,
        "is_jackpot": is_jackpot
    }
    
    return {
        "is_approved": True,
        "reason": "Approved",
        "metrics": metrics
    }
