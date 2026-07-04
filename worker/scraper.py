from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
import time
import random
import os
import json
import asyncio
from pydantic import BaseModel, Field
from typing import List
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
        instruction="Extract the MAIN product title, original price, discounted price, product features, main product image URL, star rating (e.g. 4.5), review count, and brand name. IMPORTANT: ONLY extract the price for the main product being sold. DO NOT extract prices of related products, sponsored items, or accessories. If the main product is 'Out of Stock' or 'Currently unavailable', set out_of_stock to true and leave the prices as empty strings. If a field is missing, return an empty string or empty list."
    )
    
    async with AsyncWebCrawler(verbose=True) as crawler:
        config = CrawlerRunConfig(
            extraction_strategy=extraction_strategy,
            cache_mode="bypass",
            magic=True # Enables stealth and anti-bot features in Crawl4AI
        )
        
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
                    merged = {
                        "raw_title": "",
                        "raw_discounted_price": "",
                        "raw_original_price": "",
                        "features": [],
                        "image_url": "",
                        "star_rating": "",
                        "review_count": "",
                        "brand_name": "",
                        "out_of_stock": False
                    }
                    for item in parsed_data:
                        if isinstance(item, dict):
                            if not merged["raw_title"] and item.get("raw_title"): merged["raw_title"] = item["raw_title"]
                            if not merged["raw_discounted_price"] and item.get("raw_discounted_price"): merged["raw_discounted_price"] = item["raw_discounted_price"]
                            if not merged["raw_original_price"] and item.get("raw_original_price"): merged["raw_original_price"] = item["raw_original_price"]
                            if not merged["image_url"] and item.get("image_url"): merged["image_url"] = item["image_url"]
                            if not merged["star_rating"] and item.get("star_rating"): merged["star_rating"] = item["star_rating"]
                            if not merged["review_count"] and item.get("review_count"): merged["review_count"] = item["review_count"]
                            if not merged["brand_name"] and item.get("brand_name"): merged["brand_name"] = item["brand_name"]
                            if item.get("out_of_stock"): merged["out_of_stock"] = True
                            if item.get("features"): merged["features"].extend(item["features"])
                    
                    # If it's out of stock, clear the extracted prices so we don't accidentally use a related product's price
                    if merged["out_of_stock"]:
                        merged["raw_discounted_price"] = ""
                        merged["raw_original_price"] = ""
                    
                    # Deduplicate features
                    merged["features"] = list(dict.fromkeys(merged["features"]))
                    parsed_data = merged
                
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
                    "out_of_stock": parsed_data.get("out_of_stock", False)
                }
            except json.JSONDecodeError:
                raise ValueError("Failed to parse Crawl4AI JSON output")
        else:
            raise ValueError("No content extracted by Crawl4AI")

def extract_deal_data(url: str) -> dict:
    """Primary extraction wrapper: Tries Crawl4AI, falls back to Playwright."""
    try:
        print(f"Attempting primary extraction via Crawl4AI for {url}...")
        return asyncio.run(async_crawl4ai_extract(url))
    except Exception as e:
        print(f"Crawl4AI failed: {e}. Falling back to manual Playwright scraper...")
        return extract_deal_data_fallback(url)

def setup_browser(p):
    """Sets up the Playwright browser with session caching and stealth."""
    # Ensure user_data_dir exists for session caching
    user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
    os.makedirs(user_data_dir, exist_ok=True)

    browser = p.chromium.launch_persistent_context(
        user_data_dir=user_data_dir,
        headless=True, # Can be set to False for manual CAPTCHA solving
        args=["--disable-blink-features=AutomationControlled"]
    )
    return browser

def extract_deal_data_fallback(url: str) -> dict:
    """Scrapes raw data from a given URL using stealth."""
    with sync_playwright() as p:
        browser = setup_browser(p)
        page = browser.new_page()
        Stealth().use_sync(page)
        
        try:
            # Navigate to the URL
            page.goto(url, wait_until="domcontentloaded")
            
            # Randomized Backoff Delay (Home IP Protection)
            delay = random.uniform(10.0, 15.0)
            print(f"Waiting {delay:.2f}s to mimic human behavior...")
            time.sleep(delay)
            
            # Randomized scrolling
            page.mouse.wheel(0, random.randint(300, 700))
            time.sleep(random.uniform(1.0, 3.0))
            
            # --- CAPTCHA Intervention Logic ---
            # If CAPTCHA detected, pause execution, play sound (bell), wait for user input
            if page.locator("form[action='/errors/validateCaptcha']").count() > 0 or "captcha" in page.title().lower():
                print("\n\a🚨 [CRITICAL] CAPTCHA DETECTED! 🚨")
                print("1. Open the browser (ensure headless=False in setup_browser).")
                print("2. Solve the CAPTCHA manually.")
                input("3. Press ENTER here when you have solved it to continue scraping...")
                
                # Wait for the page to reload after CAPTCHA solve
                page.wait_for_load_state("domcontentloaded")
                time.sleep(2)
            
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
                    
            # 4. Image URL
            image_url = ""
            img_element = page.locator("#landingImage").first
            if img_element.count() > 0:
                # High-res image is often stored in data-old-hires or dynamically loaded
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
                "image_url": image_url
            }
            
            print(f"Scraped Data: {raw_data}")
            return raw_data
        
        finally:
            browser.close()
