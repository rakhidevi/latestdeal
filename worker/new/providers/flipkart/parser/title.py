from worker.new.sdk.parsing.selector_engine import SelectorEngine
from worker.new.providers.flipkart.selectors.product import TITLE_SELECTORS

def extract_title(soup) -> str:
    raw = SelectorEngine.get_text_safe(soup, TITLE_SELECTORS, "Unknown Title")
    if raw.startswith("More about "):
        raw = raw.replace("More about ", "")
    if raw.endswith("+"):
        raw = raw[:-1]
    return raw.strip()
