import re
from worker.new.sdk.parsing.selector_engine import SelectorEngine
from worker.new.providers.flipkart.selectors.product import PRICE_SELECTORS

def extract_price(soup) -> float:
    text = SelectorEngine.get_text_safe(soup, PRICE_SELECTORS, "")
    if not text:
        return 0.0
    
    # Strip everything except digits and decimal point
    cleaned = re.sub(r'[^\d.]', '', text)
    try:
        return float(cleaned)
    except:
        return 0.0
