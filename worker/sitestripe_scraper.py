import os
import time
import random
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from utils import clean_amazon_url
from domains import AMAZON_PRODUCT_PREFIXES

def get_sitestripe_link_and_data(url: str) -> dict:
    """
    Uses a persistent Playwright browser to generate short links via SiteStripe.
    """
    with sync_playwright() as p:
        user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
        os.makedirs(user_data_dir, exist_ok=True)
        
        context = None
        for attempt in range(2):
            try:
                # Launch Chrome visibly so the user can log in if needed
                context = p.chromium.launch_persistent_context(
                    user_data_dir=user_data_dir,
                    headless=False,
                    executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe", # Use REAL local Chrome
                    permissions=["clipboard-read", "clipboard-write"], # Grant clipboard permissions
                    args=["--disable-blink-features=AutomationControlled"]
                )
                
                # Aggressively close any extra tabs that pop in from asynchronous session restore
                def close_extra_page(p):
                    try:
                        p.close()
                    except:
                        pass
                context.on("page", close_extra_page)
                
                # Clean up any restored tabs from previous sessions
                while len(context.pages) > 1:
                    context.pages[-1].close()
                page = context.pages[0] if context.pages else context.new_page()
                Stealth().use_sync(page)
                break
            except Exception as e:
                print(f"ERROR: Playwright browser profile is LOCKED! {e}")
                if attempt == 0:
                    import subprocess, sys
                    print("Attempting to auto-kill zombie Chrome processes and retry...")
                    subprocess.run([sys.executable, os.path.join(os.path.dirname(__file__), "kill_zombie_chrome.py")])
                    time.sleep(2)
                else:
                    print("Please close any extra Chrome windows or restart your computer to clear the locks.")
                    return False
            
        try:
            print(f"Navigating to raw URL: {url}...")
            # We use wait_until="networkidle" to ensure JS redirects (like indiafreestuff or amzn.to) finish
            page.goto(url, wait_until="networkidle", timeout=60000)
            
            # Wait a few seconds for any lingering client-side redirects
            time.sleep(3)
            
            final_raw_url = page.url
            print(f"Resolved raw URL: {final_raw_url}")
            
            # If we are on an aggregator like IndiaFreeStuff, try to find the actual deal link
            if "indiafreestuff.in" in final_raw_url.lower():
                print("Detected IndiaFreeStuff page. Looking for merchant link...")
                try:
                    shop_now_link = None
                    
                    # First, try to find a button with exactly "Shop now" text
                    for a in page.locator("a").all():
                        text = a.inner_text().strip().lower()
                        href = a.get_attribute("href") or ""
                        if "shop now" in text:
                            shop_now_link = href
                            break
                    
                    # Fallback if no "Shop now" text is found but there's a link to amazon
                    if not shop_now_link:
                        for a in page.locator("a").all():
                            href = a.get_attribute("href") or ""
                            href_lower = href.lower()
                            if any(prefix in href_lower for prefix in AMAZON_PRODUCT_PREFIXES):
                                shop_now_link = href
                                break
                                
                    if shop_now_link:
                        print(f"Found merchant link: {shop_now_link}. Navigating...")
                        page.goto(shop_now_link, wait_until="domcontentloaded", timeout=60000)
                        time.sleep(3)
                        final_raw_url = page.url
                        print(f"Resolved merchant URL: {final_raw_url}")
                    else:
                        print("Could not find a 'Shop now' or Amazon link on IndiaFreeStuff page.")
                except Exception as e:
                    print(f"Failed to extract merchant link from IndiaFreeStuff: {e}")
                    
            # Clean the URL
            clean_url = clean_amazon_url(final_raw_url, resolve_redirects=False)
            
            if clean_url != final_raw_url and "amazon" in clean_url.lower():
                print(f"URL cleaned successfully. Revisiting clean URL: {clean_url}...")
                page.goto(clean_url, wait_until="domcontentloaded", timeout=60000)
            else:
                clean_url = final_raw_url
            
            if page.locator("div#amzn-ss-wrap").count() == 0:
                # Give it a second just in case it's loading slowly
                time.sleep(2)
                if page.locator("div#amzn-ss-wrap").count() == 0:
                    print("[ACTION REQUIRED] SiteStripe not detected. Please log into Amazon in the browser window!")
                    print("We cannot pause for input() here because we are running in the background. Please restart the worker manually and login.")
                    return False
                
            print("Waiting for SiteStripe to load...")
            
            print("Page loaded. Waiting for human delay...")
            time.sleep(random.uniform(5.0, 10.0))
            
            # 1. Extract DOM Data (Title, Prices, Image)
            print("Extracting DOM product data...")
            
            title_element = page.locator("#productTitle").first
            title = title_element.inner_text().strip() if title_element.count() > 0 else page.title()
            
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
                    
            original_price_html = ""
            for selector in [
                ".a-text-price .a-offscreen",
                "#priceBlockStrikePriceString",
                "span.a-price.a-text-price span.a-offscreen"
            ]:
                el = page.locator(selector).first
                if el.count() > 0:
                    text_val = el.text_content().strip()
                    val_lower = text_val.lower()
                    if "per g" not in val_lower and "/100" not in val_lower and "per 100" not in val_lower and "/ 100" not in val_lower:
                        original_price_html = text_val
                        break
                        
            if not original_price_html or "per" in original_price_html.lower() or "/100" in original_price_html.lower():
                mrp_label = page.locator("span:has-text('M.R.P.:')").first
                if mrp_label.count() > 0:
                    parent = mrp_label.locator("..").first
                    if parent.count() > 0:
                        original_price_html = parent.text_content().replace('M.R.P.:', '').strip()
                    
            image_url = ""
            img_element = page.locator("#landingImage").first
            if img_element.count() > 0:
                image_url = img_element.get_attribute("data-old-hires") or img_element.get_attribute("src") or ""
                
            features = []
            for el in page.locator("#feature-bullets ul li span.a-list-item").all():
                text = el.inner_text().strip()
                if text: features.append(text)
                
            star_rating = ""
            for selector in [
                "#averageCustomerReviews .a-icon-alt",
                "#acrPopover",
                "i[data-hook='average-star-rating'] .a-icon-alt"
            ]:
                rating_el = page.locator(selector).first
                if rating_el.count() > 0:
                    # Some elements have the text hidden, or use title attribute
                    val = rating_el.get_attribute("title") or rating_el.text_content()
                    if val and "out of 5" in val.lower():
                        star_rating = val.strip()
                        break
            
            review_count = ""
            review_el = page.locator("#acrCustomerReviewText").first
            if review_el.count() > 0:
                review_count = review_el.text_content().strip()
                
            # 2. SiteStripe Automation
            print("Looking for SiteStripe bar...")
            short_url = ""
            try:
                page.wait_for_selector("#amzn-ss-text-link", timeout=10000)
                sitestripe_text_btn = page.locator("#amzn-ss-text-link").first
                    
                print("Clicking SiteStripe 'Get Link' button...")
                sitestripe_text_btn.click()
                
                # Wait for popover to appear
                print("Waiting for popover...")
                page.wait_for_selector("#amzn-ss-copy-affiliate-link-btn-announce", timeout=10000)
                
                copy_btn = page.locator("#amzn-ss-copy-affiliate-link-btn-announce").first
                copy_btn.click()
                
                # Wait for the "Copied to clipboard" toast to ensure it copied
                try:
                    page.wait_for_selector("#amzn-ss-copy-toast:not([style*='display: none'])", timeout=5000)
                except Exception as e:
                    print(f"Toast didn't appear, trying clipboard anyway: {e}")
                    
                time.sleep(1) # Extra buffer for clipboard to write
                
                short_url = page.evaluate("navigator.clipboard.readText()")
                if not short_url or ("amzn.to" not in short_url and "link.amazon" not in short_url):
                    print(f"Failed to extract valid short URL from clipboard. Found: {short_url}")
                    short_url = ""
                else:
                    print(f"Successfully generated SiteStripe Link: {short_url}")
            except Exception as e:
                # Check for "Frequently Returned Item" which disables the Get Link button
                page_text = page.content()
                if "Frequently Returned Item" in page_text or "lower return rates" in page_text:
                    print("Deal REJECTED: Frequently Returned Item (SiteStripe 'Get Link' disabled)")
                    return False
                print(f"SiteStripe bar not found or failed to copy! Error: {e}")
                print("Returning raw data without shortlink.")
            
            raw_data = {
                "url": clean_url,
                "sitestripe_url": short_url,
                "raw_title": title,
                "raw_discounted_price": discounted_price_html,
                "raw_original_price": original_price_html,
                "features": features,
                "image_url": image_url,
                "star_rating": star_rating,
                "review_count": review_count,
                "scraper_type": "SiteStripe Automation"
            }
            
            return raw_data
        
        finally:
            if context:
                context.close()
        

if __name__ == "__main__":
    import sys
    if len(sys.argv) < 2:
        print("Usage: python sitestripe_scraper.py <amazon-url>")
        sys.exit(1)
    
    data = get_sitestripe_link_and_data(sys.argv[1])
    import json
    print(json.dumps(data, indent=2))
