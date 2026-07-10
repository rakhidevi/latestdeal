from playwright.sync_api import sync_playwright
from playwright_stealth import stealth
import time
import random
import os
import json
import asyncio
from pydantic import BaseModel, Field
from typing import List

# List of common desktop user agents for rotation
DESKTOP_USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/119.0"
]

try:
    from crawl4ai import AsyncWebCrawler, LLMConfig
    from crawl4ai.async_configs import CrawlerRunConfig
    from crawl4ai.extraction_strategy import LLMExtractionStrategy
except ImportError:
    pass # Handled below if Crawl4AI fails to load

class DealExtractionSchema(BaseModel):
    raw_title: str = Field(description="The product title")
    raw_original_price: str = Field(description="The original MRP price, usually struck out. Leave empty string if not found.")
    raw_discounted_price: str = Field(description="The discounted or deal price. Leave empty string if not found.")
    features: List[str] = Field(description="List of product features or bullet points.")
    image_url: str = Field(description="The main product image URL. Leave empty string if not found.")
    star_rating: str = Field(description="The star rating of the product (e.g. 4.5). Leave empty string if not found.")
    review_count: str = Field(description="The number of customer ratings/reviews. Leave empty string if not found.")
    brand_name: str = Field(description="The brand name of the product. Leave empty string if not found.")
    out_of_stock: bool = Field(description="Set to true if the main product says 'Currently unavailable' or 'Out of stock'.")

async def async_crawl4ai_extract(url: str) -> dict:
    """Uses Crawl4AI to asynchronously extract deal data using local LLM."""
    
    # Bypass crawl4ai deprecation strictness which breaks on Python 3.11 with instructor
    LLMExtractionStrategy._UNWANTED_PROPS = {}
    
    extraction_strategy = LLMExtractionStrategy(
        llm_config=LLMConfig(
            provider="ollama/llama3", 
            api_token="no-token",
            base_url=os.getenv("OLLAMA_BASE_URL", "http://localhost:11434")
        ),
        schema=DealExtractionSchema.model_json_schema(),
        extraction_type="schema",
        instruction="Extract the MAIN product title, original price, discounted price, product features, main product image URL, star rating (e.g. 4.5), review count, and brand name. IMPORTANT: ONLY extract the actual selling price and original MRP. DO NOT extract 'per gram', 'per kg', 'per 100g', or any other unit prices. DO NOT extract prices of related products, sponsored items, or accessories. If the main product is 'Out of Stock' or 'Currently unavailable', set out_of_stock to true and leave the prices as empty strings. If a field is missing, return an empty string or empty list."
    )
    
    proxy_server = os.getenv("PROXY_SERVER")
    proxy_config = {"server": proxy_server} if proxy_server else None

    async with AsyncWebCrawler(verbose=True, headless=False) as crawler:
        config_kwargs = {
            "extraction_strategy": extraction_strategy,
            "cache_mode": "bypass",
            "magic": True # Enables stealth and anti-bot features in Crawl4AI
        }
        if proxy_server:
            config_kwargs["proxy"] = proxy_server
            
        config = CrawlerRunConfig(**config_kwargs)
        
        result = await crawler.arun(
            url=url,
            config=config
        )
        
        if result.extracted_content:
            try:
                # Crawl4AI returns JSON string of the extracted schema
                parsed_data = json.loads(result.extracted_content)
                if isinstance(parsed_data, list) and len(parsed_data) > 0:
                    # Merge all chunks to find the best data
                    # Find the chunk with the most complete data (the main product buy box)
                    best_chunk = None
                    best_score = -1
                    
                    for item in parsed_data:
                        if isinstance(item, dict) and not item.get("error"):
                            score = 0
                            if item.get("raw_title"): score += 1
                            if item.get("raw_original_price"): score += 2 # High value for having prices
                            if item.get("raw_discounted_price"): score += 2
                            if item.get("image_url"): score += 1
                            if item.get("star_rating"): score += 1
                            if item.get("features"): score += min(len(item["features"]) * 0.1, 1.0)
                            
                            if score > best_score:
                                best_score = score
                                best_chunk = item
                                
                    if best_chunk:
                        merged = {
                            "raw_title": best_chunk.get("raw_title", ""),
                            "raw_discounted_price": best_chunk.get("raw_discounted_price", ""),
                            "raw_original_price": best_chunk.get("raw_original_price", ""),
                            "features": best_chunk.get("features", []) if best_chunk.get("features") else [],
                            "image_url": best_chunk.get("image_url", ""),
                            "star_rating": best_chunk.get("star_rating", ""),
                            "review_count": best_chunk.get("review_count", ""),
                            "brand_name": best_chunk.get("brand_name", ""),
                            "out_of_stock": best_chunk.get("out_of_stock", False)
                        }
                        
                        # Only fill in missing fields from other chunks
                        for item in parsed_data:
                            if isinstance(item, dict) and not item.get("error"):
                                if not merged["raw_title"] and item.get("raw_title"): merged["raw_title"] = item["raw_title"]
                                if not merged["raw_discounted_price"] and item.get("raw_discounted_price"): merged["raw_discounted_price"] = item["raw_discounted_price"]
                                if not merged["raw_original_price"] and item.get("raw_original_price"): merged["raw_original_price"] = item["raw_original_price"]
                                if not merged["image_url"] and item.get("image_url"): merged["image_url"] = item["image_url"]
                                if not merged["star_rating"] and item.get("star_rating"): merged["star_rating"] = item["star_rating"]
                                if not merged["review_count"] and item.get("review_count"): merged["review_count"] = item["review_count"]
                                if not merged["brand_name"] and item.get("brand_name"): merged["brand_name"] = item["brand_name"]
                                if item.get("out_of_stock") and not merged["out_of_stock"]: merged["out_of_stock"] = True
                                if item.get("features"): merged["features"].extend(item["features"])
                        
                        if merged["out_of_stock"]:
                            merged["raw_discounted_price"] = ""
                            merged["raw_original_price"] = ""
                            
                        merged["features"] = list(dict.fromkeys(merged["features"]))
                        parsed_data = merged
                    else:
                        parsed_data = {}
                
                # Format to match existing pipeline
                return {
                    "url": url,
                    "raw_title": parsed_data.get("raw_title", ""),
                    "raw_discounted_price": parsed_data.get("raw_discounted_price", ""),
                    "raw_original_price": parsed_data.get("raw_original_price", ""),
                    "features": parsed_data.get("features", []),
                    "image_url": parsed_data.get("image_url", ""),
                    "star_rating": parsed_data.get("star_rating", ""),
                    "review_count": parsed_data.get("review_count", ""),
                    "brand_name": parsed_data.get("brand_name", ""),
                    "out_of_stock": parsed_data.get("out_of_stock", False),
                    "scraper_type": "Crawl4AI"
                }
            except json.JSONDecodeError:
                raise ValueError("Failed to parse Crawl4AI JSON output")
        else:
            raise ValueError("No content extracted by Crawl4AI")

def extract_deal_data(url: str) -> dict:
    print(f"Bypassing Crawl4AI (DOM too large for local LLM). Using manual Playwright scraper for {url}...")
    try:
        return extract_deal_data_fallback(url)
    except Exception as e:
        print(f"Extraction failed: {e}")
        raise e

def setup_browser(p):
    """Sets up the Playwright browser with session caching, proxies, and stealth."""
    user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
    os.makedirs(user_data_dir, exist_ok=True)

    proxy_server = os.getenv("PROXY_SERVER")
    
    launch_args = {
        "user_data_dir": user_data_dir,
        "headless": False, # Changed to False to stay bot safe and allow manual auth
        "channel": "chrome", # Use real Chrome
        "args": ["--disable-blink-features=AutomationControlled"]
    }
    
    if proxy_server:
        # Playwright proxy format
        launch_args["proxy"] = {"server": proxy_server}

    browser = p.chromium.launch_persistent_context(**launch_args)
    return browser

def extract_deal_data_fallback(url: str) -> dict:
    """Scrapes raw data from a given URL using stealth."""
    with sync_playwright() as p:
        browser = setup_browser(p)
        page = browser.new_page()
        stealth(page)
        
        try:
            # Random User-Agent setup
            ua = random.choice(DESKTOP_USER_AGENTS)
            page.set_extra_http_headers({"User-Agent": ua})
            
            # Navigate to the URL
            page.goto(url, wait_until="domcontentloaded", timeout=60000)
            
            # Heavy Randomized Backoff Delay (Home IP Protection)
            delay = random.uniform(15.0, 25.0)
            print(f"Waiting {delay:.2f}s to mimic human reading behavior...")
            time.sleep(delay)
            
            # Multi-step human-like scrolling
            for _ in range(random.randint(2, 5)):
                scroll_amount = random.randint(300, 800)
                page.mouse.wheel(0, scroll_amount)
                time.sleep(random.uniform(1.0, 4.0))
                
            # Randomly scroll up slightly
            if random.choice([True, False]):
                page.mouse.wheel(0, -random.randint(100, 300))
                time.sleep(random.uniform(0.5, 2.0))
            
            # --- CAPTCHA Intervention Logic ---
            # If CAPTCHA detected, pause execution, play sound (bell), wait for user input
            if page.locator("form[action='/errors/validateCaptcha']").count() > 0 or "captcha" in page.title().lower():
                print("\n\a🚨 [CRITICAL] CAPTCHA DETECTED! 🚨")
                print("1. Open the browser (ensure headless=False in setup_browser).")
                print("2. Solve the CAPTCHA manually.")
                print("Note: With proxies, CAPTCHAs should be rare.")
                input("3. Press ENTER here when you have solved it to continue scraping...")
                
                # Wait for the page to reload after CAPTCHA solve
                page.wait_for_load_state("domcontentloaded")
                time.sleep(3)
            
            # Extract actual DOM details using robust Amazon selectors
            
            # 1. Product Title
            title_element = page.locator("#productTitle").first
            title = title_element.inner_text().strip() if title_element.count() > 0 else page.title()
            
            # 2. Discounted Price (Deal Price)
            # Amazon frequently changes this, we try multiple common selectors
            discounted_price_html = ""
            for selector in [
                "#corePriceDisplay_desktop_feature_div .a-price-whole",
                ".priceToPay .a-price-whole",
                "#priceblock_dealprice",
                "#priceblock_ourprice"
            ]:
                el = page.locator(selector).first
                if el.count() > 0:
                    discounted_price_html = el.inner_text().strip()
                    break
                    
            # 3. Original Price (M.R.P)
            original_price_html = ""
            for selector in [
                ".a-text-price .a-offscreen",
                "#priceBlockStrikePriceString",
                "span.a-price.a-text-price span.a-offscreen"
            ]:
                el = page.locator(selector).first
                if el.count() > 0:
                    # Inner text of .a-offscreen is sometimes hidden or structured differently, 
                    # text_content() grabs it regardless of visibility
                    original_price_html = el.text_content().strip()
                    break
                    
            # 4. Image URL (High-res extraction)
            image_url = ""
            img_element = page.locator("#landingImage").first
            if img_element.count() > 0:
                dynamic_images = img_element.get_attribute("data-a-dynamic-image")
                if dynamic_images:
                    import json
                    try:
                        img_dict = json.loads(dynamic_images)
                        if img_dict:
                            # Pick the one with the highest resolution sum
                            image_url = max(img_dict.items(), key=lambda x: x[1][0] + x[1][1])[0]
                    except:
                        pass
                if not image_url:
                    image_url = img_element.get_attribute("data-old-hires") or img_element.get_attribute("src") or ""
                
            # 5. Features / Bullet Points
            features = []
            feature_elements = page.locator("#feature-bullets ul li span.a-list-item").all()
            for el in feature_elements:
                text = el.inner_text().strip()
                if text:
                    features.append(text)
            
            raw_data = {
                "url": url,
                "raw_title": title,
                "raw_discounted_price": discounted_price_html,
                "raw_original_price": original_price_html,
                "features": features,
                "image_url": image_url,
                "scraper_type": "Playwright Scraper"
            }
            
            print(f"Scraped Data: {raw_data}")
            return raw_data
        
        finally:
            browser.close()
