import re

def clean_number(val, return_type=float):
    """Extracts numeric value from a string."""
    if not val:
        return 0
    val_str = str(val).replace(',', '')
    match = re.search(r'(\d+(?:\.\d+)?)', val_str)
    if match:
        try:
            return return_type(match.group(1))
        except:
            pass
    return 0

BLOCKED_KEYWORDS = [
    'mod apk', 'modded apk', 'cracked apk', 'premium unlocked', 'unlocked all', 'pro unlocked',
    'no watermark', 'ad free mod', 'ads removed mod', 'crack', 'cracked', 'keygen', 'serial key',
    'pirated', 'warez', 'nulled', 'paid apk free', 'patched apk',
]

def evaluate_deal(raw_data: dict, brand_tiers: list = None, lowest_historical_price: float = None) -> dict:
    """
    Evaluates the scraped raw_data against trust score logic.
    Returns a dictionary with 'is_approved', 'reason', and structured 'metrics'.
    """
    if brand_tiers is None:
        brand_tiers = []

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
    
    if original_price <= 0 or discounted_price <= 0:
        return {"is_approved": False, "reason": "Missing Price Information"}
        
    discount_percent = ((original_price - discounted_price) / original_price) * 100
    absolute_discount = original_price - discounted_price
    
    star_rating = clean_number(raw_data.get("star_rating", 0))
    review_count = clean_number(raw_data.get("review_count", 0), return_type=int)
    brand_name = raw_data.get("brand_name", "").strip()
    
    # 1. Brand Score (max 25)
    brand_score = 10
    multiplier = 1.0
    for bt in brand_tiers:
        if bt.get('name', '').lower() == brand_name.lower():
            multiplier = float(bt.get('multiplier', 1.0))
            if bt.get('tier') == 'Tier 1':
                brand_score = 25
            elif bt.get('tier') == 'Tier 2':
                brand_score = 18
            break

    # 2. Discount Score (max 35)
    # Give high score for huge absolute savings (> 10,000 INR) or high %
    discount_score = 0
    if absolute_discount > 10000:
        discount_score += 20
    elif absolute_discount > 5000:
        discount_score += 15
    elif absolute_discount > 1000:
        discount_score += 10
        
    if discount_percent > 70:
        discount_score += 15
    elif discount_percent > 40:
        discount_score += 10
    elif discount_percent > 20:
        discount_score += 5
        
    # Cap discount score at 35
    discount_score = min(35, discount_score * multiplier)
    
    # 3. Historical Price Check (Bonus or Penalty)
    history_score = 0
    if lowest_historical_price:
        if discounted_price < lowest_historical_price:
            history_score = 15 # Brand new lowest price!
        elif discounted_price == lowest_historical_price:
            history_score = 10 # Matches lowest price
        elif discounted_price > lowest_historical_price * 1.2:
            history_score = -10 # 20% more expensive than historical best

    # 4. Rating & Review Volume Score (max 40)
    rating_score = 0
    if star_rating >= 4.5:
        rating_score = 20
    elif star_rating >= 4.0:
        rating_score = 15
    elif star_rating >= 3.5:
        rating_score = 10

    volume_score = 0
    if review_count > 1000:
        volume_score = 20
    elif review_count > 500:
        volume_score = 15
    elif review_count > 100:
        volume_score = 10
        
    trust_score = int(brand_score + discount_score + rating_score + volume_score + history_score)
    trust_score = min(100, max(0, trust_score))
    
    is_approved = True
    reason = "Approved"
    
    # We aren't strictly rejecting based on threshold, but below 40 is probably garbage
    if trust_score < 40:
        is_approved = False
        reason = f"Trust Score too low ({trust_score})"

    is_jackpot = False
    if discount_percent >= 90 and brand_name.lower() not in ["", "generic", "unknown"]:
        is_jackpot = True
        
    metrics = {
        "original_price": original_price,
        "discounted_price": discounted_price,
        "discount_percent": round(discount_percent, 1),
        "absolute_discount": absolute_discount,
        "star_rating": star_rating,
        "review_count": review_count,
        "brand_name": brand_name,
        "is_jackpot": is_jackpot,
        "trust_score": trust_score,
        "lowest_price_seen": lowest_historical_price
    }
    
    return {
        "is_approved": is_approved,
        "reason": reason,
        "metrics": metrics
    }
