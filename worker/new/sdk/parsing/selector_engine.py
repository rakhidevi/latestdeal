from typing import List

class SelectorEngine:
    """Robust DOM traversal with automatic fallbacks."""
    
    @staticmethod
    def get_first_valid(soup, selectors: List[str]):
        """Attempts multiple CSS selectors and returns the first matching element."""
        for sel in selectors:
            el = soup.select_one(sel)
            if el:
                return el
        return None
        
    @staticmethod
    def get_text_safe(soup, selectors: List[str], default: str = "") -> str:
        el = SelectorEngine.get_first_valid(soup, selectors)
        if el:
            return el.get_text(strip=True)
        return default
