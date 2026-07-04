import re

def clean_number(val, return_type=float):
    """Extracts numeric value from a string."""
    if not val:
        return 0
    # Remove everything except digits and dot
    cleaned = re.sub(r'[^\d.]', '', str(val))
    try:
        return return_type(cleaned)
    except:
        return 0

def evaluate_deal(raw_data: dict) -> dict:
    """
    Evaluates the scraped raw_data against strict guardrails.
    Returns a dictionary with 'is_approved', 'reason', and structured 'metrics'.
    """
    if raw_data.get("out_of_stock"):
        return {"is_approved": False, "reason": "Out of Stock"}
        
    title = raw_data.get("raw_title", "")
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
    
    # Rule 1: Discount Threshold (Must be > 40%)
    if discount_percent < 40:
        return {"is_approved": False, "reason": f"Discount too low ({discount_percent:.1f}%)"}
        
    # Rule 2: Trust Factor (Must be > 4.0 stars)
    # If star_rating is 0, it means it wasn't found or has no reviews. We reject unproven products.
    if star_rating < 4.0:
        return {"is_approved": False, "reason": f"Star rating too low ({star_rating})"}
        
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
