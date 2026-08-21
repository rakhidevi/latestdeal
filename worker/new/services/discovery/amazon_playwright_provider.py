import os
import asyncio
from typing import List, Dict, Any
from .provider_interface import DiscoveryProvider
from playwright.async_api import async_playwright

class SearchGridTimeoutError(Exception):
    pass

class AmazonPlaywrightProvider(DiscoveryProvider):
    def search(self, criteria: Dict[str, Any]) -> List[Dict[str, Any]]:
        # Run the async playwright scrape synchronously for the interface
        return asyncio.run(self._async_search(criteria))

    async def _async_search(self, criteria: Dict[str, Any]) -> List[Dict[str, Any]]:
        query_parts = []
        if criteria.get('brand_name'):
            query_parts.append(criteria['brand_name'])
        if criteria.get('product_type'):
            query_parts.append(criteria['product_type'])
        if criteria.get('category_name') and str(criteria['category_name']).lower() != 'all':
            query_parts.append(criteria['category_name'])
        if criteria.get('keywords'):
            query_parts.append(criteria['keywords'])
            
        search_query = " ".join(query_parts).strip()
        if not search_query:
            return []

        url = f"https://www.amazon.in/s?k={search_query.replace(' ', '+')}"
        
        # We can add parameters for discount later, but for now we'll just scrape the first page
        # and let the Deals Intelligence layer filter them out.
        # Alternatively, Amazon supports 'pct-off=50-' in the URL.
        min_discount = criteria.get('min_discount_percent')
        if min_discount:
            url += f"&rh=p_8%3A{int(min_discount)}-"
            
        print(f'{{\n  "event": "Amazon search",\n  "brand": "{criteria.get("brand_name", "")}",\n  "category": "{criteria.get("category_name", "")}",\n  "discount": {min_discount or ""},\n  "query": "{search_query}",\n  "url": "{url}"\n}}')

        headless = os.getenv("SCRAPER_HEADLESS", "true").lower() == "true"
        debug_pause = os.getenv("SCRAPER_DEBUG_PAUSE", "false").lower() == "true"
        
        results = []
        async with async_playwright() as p:
            browser = await p.chromium.launch(headless=headless)
            page = await browser.new_page()
            
            # Simple stealth headers
            await page.set_extra_http_headers({
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "Accept-Language": "en-US,en;q=0.9",
            })
            
            try:
                await page.goto(url, wait_until="domcontentloaded", timeout=30000)
                
                # Wait for at least one result to load (catch timeout if no results)
                try:
                    await page.wait_for_selector('div[data-component-type="s-search-result"]', timeout=10000)
                except Exception as e:
                    # If timeout, it means no search results found. Throw SearchGridTimeoutError for telemetry.
                    raise SearchGridTimeoutError("Timeout waiting for product grid. Amazon search page may have returned 0 results or blocked the request.")
                
                items = await page.query_selector_all('div[data-component-type="s-search-result"]')
                
                if debug_pause and len(items) > 0:
                    first_item = items[0]
                    # Extract some basic info for debug output
                    asin_debug = await first_item.get_attribute("data-asin")
                    title_el = await first_item.query_selector('div[data-cy="title-recipe"] a h2 span')
                    title_debug = await title_el.inner_text() if title_el else "Unknown"
                    print(f"\n[DEBUG] Found {len(items)} products")
                    print(f"[DEBUG] First product:")
                    print(f"       ASIN: {asin_debug}")
                    print(f"       Title: {title_debug}")
                    # Removed blocking input

                
                for item in items:
                    asin = await item.get_attribute("data-asin")
                    if not asin:
                        continue
                        
                    # SOURCE BRAND EXTRACTION
                    brand_el = await item.query_selector('div[data-cy="title-recipe"] > div:first-child h2 span')
                    if not brand_el:
                        brand_el = await item.query_selector('div[data-cy="title-recipe"] > div:first-child h2')
                    source_brand = await brand_el.inner_text() if brand_el else ""
                    
                    # TITLE EXTRACTION
                    title_el = await item.query_selector('div[data-cy="title-recipe"] a h2 span')
                    if not title_el:
                        title_el = await item.query_selector('div[data-cy="title-recipe"] a h2')
                    title = await title_el.inner_text() if title_el else ""
                    
                    # URL EXTRACTION
                    link_el = await item.query_selector('div[data-cy="title-recipe"] a.a-link-normal')
                    if not link_el:
                        link_el = await item.query_selector('div[data-cy="title-recipe"] a[href*="/dp/"]')
                    link = await link_el.get_attribute("href") if link_el else ""
                    full_link = f"https://www.amazon.in{link}" if link else ""
                    
                    # DEBUG HTML DUMP
                    if (not title or not link) and os.getenv("SCRAPER_DEBUG_HTML", "false").lower() == "true":
                        html = await item.inner_html()
                        with open(f"quarantine/html_fail_{asin}.txt", "w", encoding="utf-8") as f:
                            f.write(html)
                    
                    price_el = await item.query_selector('.a-price[data-a-size="xl"] .a-offscreen')
                    selling_price_str = await price_el.inner_text() if price_el else ""
                    
                    mrp_el = await item.query_selector('.a-text-price .a-offscreen')
                    mrp_str = await mrp_el.inner_text() if mrp_el else ""
                    
                    img_el = await item.query_selector('.s-image')
                    img_url = await img_el.get_attribute("src") if img_el else ""
                    
                    # Clean up prices
                    def clean_price(p_str):
                        if not p_str: return 0.0
                        cln = p_str.replace('₹', '').replace(',', '').strip()
                        try:
                            return float(cln)
                        except:
                            return 0.0
                            
                    selling_price = clean_price(selling_price_str)
                    mrp = clean_price(mrp_str)
                    
                    if selling_price > 0:
                        results.append({
                            "source_id": asin,
                            "url": full_link,
                            "title": title,
                            "source_brand": source_brand,
                            "original_price": mrp if mrp > selling_price else selling_price,
                            "discounted_price": selling_price,
                            "image_url": img_url,
                            "merchant_name": "Amazon"
                        })
                        
            except SearchGridTimeoutError:
                raise
            except Exception as e:
                print(f"Error scraping {url}: {e}")
            finally:
                await browser.close()
                
        return results
