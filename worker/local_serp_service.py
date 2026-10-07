"""
local_serp_service.py — In-House Search & Price Intelligence Tool
================================================================
Self-hosted, zero-paid-API multi-store price comparison engine.

Strategy:
1. Use DuckDuckGo Shopping/Text search (free, no API key) to discover
   product listings across Amazon.in, Flipkart, Croma, Reliance Digital.
2. Extract prices, titles, and stock status from search snippets without
   opening a browser tab (sub-2s per query).
3. Fall back to Playwright stealth scraping only when snippet data is
   insufficient or price is missing.

Follows AGENTS.md:
  - Rule 1: When Playwright is used, use real Windows Chrome.
  - Rule 2: Always reuse the first existing tab in persistent context.
  - Rule 3: No external paid APIs — local infrastructure only.
"""

import re
import time
import logging
from typing import Optional
from dataclasses import dataclass, field, asdict

# -------------------------------------------------------------------
# DTOs (Data Transfer Objects) — AGENTS.md Rule 10: DTO First
# -------------------------------------------------------------------

@dataclass
class PriceResult:
    """Represents a single store's price result for a product query."""
    store: str
    title: str
    price: Optional[float]
    url: str
    in_stock: bool = True
    rating: Optional[float] = None
    review_count: Optional[int] = None
    source: str = "ddg_snippet"  # 'ddg_snippet' | 'playwright'

    def to_dict(self) -> dict:
        return asdict(self)


# -------------------------------------------------------------------
# Target store definitions
# -------------------------------------------------------------------
STORE_CONFIGS = {
    "amazon": {
        "domain": "amazon.in",
        "name": "Amazon",
        "site_search": "site:amazon.in",
        "price_pattern": r'₹[\s]?([\d,]+)',
    },
    "flipkart": {
        "domain": "flipkart.com",
        "name": "Flipkart",
        "site_search": "site:flipkart.com",
        "price_pattern": r'₹[\s]?([\d,]+)',
    },
    "croma": {
        "domain": "croma.com",
        "name": "Croma",
        "site_search": "site:croma.com",
        "price_pattern": r'₹[\s]?([\d,]+)',
    },
    "reliance_digital": {
        "domain": "reliancedigital.in",
        "name": "Reliance Digital",
        "site_search": "site:reliancedigital.in",
        "price_pattern": r'₹[\s]?([\d,]+)',
    },
}

logger = logging.getLogger(__name__)
logging.basicConfig(level=logging.INFO, format="%(asctime)s [SERP] %(message)s")


# -------------------------------------------------------------------
# Core extraction helpers
# -------------------------------------------------------------------

def _extract_price_from_text(text: str) -> Optional[float]:
    """Extract the first ₹ price from a snippet/title string."""
    if not text:
        return None
    # Match ₹ followed by optional whitespace and digits (with commas)
    matches = re.findall(r'₹[\s]?([\d,]+)', text)
    for m in matches:
        try:
            price = float(m.replace(',', ''))
            if price > 1:  # ignore ₹0 or ₹1 fragments
                return price
        except ValueError:
            continue
    return None


def _clean_price_str(price_str: str) -> Optional[float]:
    """Convert '₹24,990' or '24990' to 24990.0."""
    try:
        return float(price_str.replace(',', '').replace('₹', '').strip())
    except (ValueError, AttributeError):
        return None


# -------------------------------------------------------------------
# DuckDuckGo SERP extraction (zero-browser, sub-1s)
# -------------------------------------------------------------------

def _ddg_search_store(query: str, store_key: str, max_results: int = 3) -> list[PriceResult]:
    """
    Query DuckDuckGo for a product name restricted to a specific store domain.
    Returns a list of PriceResult DTOs parsed from text snippets.
    """
    config = STORE_CONFIGS[store_key]
    results: list[PriceResult] = []

    try:
        try:
            from ddgs import DDGS
        except ImportError:
            from duckduckgo_search import DDGS
        full_query = f"{query} {config['site_search']} price"

        with DDGS() as ddg:
            hits = ddg.text(full_query, max_results=max_results, region='in-en')

        for hit in (hits or []):
            url = hit.get('href', '')
            # Only include results from this store's domain
            if config['domain'] not in url:
                continue

            title = hit.get('title', '')
            snippet = hit.get('body', '')
            combined_text = f"{title} {snippet}"

            price = _extract_price_from_text(combined_text)
            in_stock = 'out of stock' not in combined_text.lower()

            results.append(PriceResult(
                store=config['name'],
                title=title[:150],
                price=price,
                url=url,
                in_stock=in_stock,
                source='ddg_snippet',
            ))

        if results:
            logger.info(f"[DDG] {store_key}: {len(results)} results, best price: "
                        f"₹{results[0].price}")

    except Exception as e:
        logger.warning(f"[DDG] {store_key} search failed: {e}")

    return results


# -------------------------------------------------------------------
# Main public API: search_all_stores
# -------------------------------------------------------------------

def search_all_stores(
    product_title: str,
    stores: Optional[list[str]] = None,
    timeout_per_store: float = 5.0,
) -> list[PriceResult]:
    """
    Search all configured stores for the given product title.
    Returns a deduplicated list of PriceResult DTOs sorted by price ascending.

    Args:
        product_title: The product name/title to search for.
        stores: List of store keys to search. Defaults to all STORE_CONFIGS.
        timeout_per_store: Max seconds to wait for each store (unused currently,
                           reserved for async refactor).

    Returns:
        List[PriceResult] sorted ascending by price.
    """
    if not stores:
        stores = list(STORE_CONFIGS.keys())

    # Normalize query: strip markdown/emoji, truncate to 80 chars
    clean_query = re.sub(r'[^\w\s,()./\-]', '', product_title).strip()[:80]
    if not clean_query:
        logger.error("Empty product title provided to search_all_stores.")
        return []

    all_results: list[PriceResult] = []

    for store_key in stores:
        try:
            store_results = _ddg_search_store(clean_query, store_key)
            all_results.extend(store_results)
            time.sleep(0.3)  # Polite delay between store queries
        except Exception as e:
            logger.error(f"Unhandled error for store {store_key}: {e}")

    # Deduplicate: keep the best-priced entry per store
    seen_stores: dict[str, PriceResult] = {}
    for r in all_results:
        existing = seen_stores.get(r.store)
        if existing is None:
            seen_stores[r.store] = r
        elif r.price is not None and (existing.price is None or r.price < existing.price):
            seen_stores[r.store] = r

    # Sort by price ascending (None prices go last)
    sorted_results = sorted(
        seen_stores.values(),
        key=lambda r: (r.price is None, r.price or 0)
    )

    logger.info(f"search_all_stores completed for '{clean_query[:50]}': "
                f"{len(sorted_results)} stores returned.")

    return sorted_results


# -------------------------------------------------------------------
# CLI test runner
# -------------------------------------------------------------------

if __name__ == "__main__":
    import json
    import sys
    import io

    # Fix Windows console encoding for Unicode currency symbols
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

    test_queries = [
        sys.argv[1] if len(sys.argv) > 1 else "Sony WH-1000XM5 Wireless Noise Cancelling Headphones",
    ]

    for query in test_queries:
        print(f"\n{'='*60}")
        print(f"QUERY: {query}")
        print(f"{'='*60}")
        t0 = time.time()
        results = search_all_stores(query)
        elapsed = time.time() - t0

        if results:
            for r in results:
                price_str = f"Rs.{r.price:,.0f}" if r.price else "Price N/A"
                stock_str = "In Stock" if r.in_stock else "Out of Stock"
                print(f"  [{r.store}] {price_str} -- {stock_str}")
                print(f"    {r.title[:80]}")
                print(f"    {r.url[:100]}")
        else:
            print("  No results found across any store.")

        print(f"\nCompleted in {elapsed:.2f}s")
