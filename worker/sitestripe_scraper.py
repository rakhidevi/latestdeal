import os
import time
import random
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from utils import clean_amazon_url

def get_sitestripe_link_and_data(url: str) -> dict:
    """
    Uses a persistent Playwright browser to generate short links via SiteStripe.
    """
    with sync_playwright() as p:
        user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
        os.makedirs(user_data_dir, exist_ok=True)
        
        try:
            # Launch Chrome headlessly by default so it doesn't steal focus
            is_headless = os.getenv("HEADLESS", "true").lower() == "true"
            context = p.chromium.launch_persistent_context(
                user_data_dir=user_data_dir,
                headless=is_headless,
                executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe", # Use REAL local Chrome
                permissions=["clipboard-read", "clipboard-write"], # Grant clipboard permissions
                args=["--disable-blink-features=AutomationControlled", "--restore-last-session=false"],
                ignore_default_args=["--enable-automation", "--no-sandbox"]
            )
            page = context.pages[0] if context.pages else context.new_page()
            
            # Close any accumulated about:blank tabs from previous crashed sessions
            for p_ext in context.pages:
                if p_ext != page:
                    try:
                        p_ext.close()
                    except:
                        pass
                        
            Stealth().use_sync(page)
            
            print(f"Navigating to raw URL: {url}...")
            # We use wait_until="networkidle" to ensure JS redirects (like indiafreestuff or amzn.to) finish
            page.goto(url, wait_until="networkidle", timeout=60000)
            
            # Wait a few seconds for any lingering client-side redirects
            time.sleep(3)
            
            final_raw_url = page.url
            print(f"Resolved raw URL: {final_raw_url}")
            
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
                    print("🚨 [ACTION REQUIRED] SiteStripe not detected. Please log into Amazon in the browser window! 🚨")
                    input("Press ENTER here in the terminal after you have successfully logged in and SiteStripe is visible...")
                    # Refresh the page after they login to ensure sitestripe loads
                    page.reload(wait_until="domcontentloaded")
            
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
                    original_price_html = el.text_content().strip()
                    break
                    
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
            try:
                page.wait_for_selector("#amzn-ss-text-link", timeout=10000)
            except Exception:
                # Check for "Frequently Returned Item" which disables the Get Link button
                page_text = page.content()
                if "Frequently Returned Item" in page_text or "lower return rates" in page_text:
                    print("Deal REJECTED: Frequently Returned Item (SiteStripe 'Get Link' disabled)")
                    return False
                raise Exception("SiteStripe bar not found! Make sure you are logged in and Affiliate account is active.")
                
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
                raise Exception(f"Failed to extract valid short URL from clipboard. Found: {short_url}")
                
            print(f"Successfully generated SiteStripe Link: {short_url}")
            
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
            
        except Exception as e:
            print(f"SiteStripe automation failed: {e}")
            raise e
        finally:
            if 'page' in locals():
                page.close()
            if 'context' in locals():
                context.close()

if __name__ == "__main__":
    import sys
    if len(sys.argv) < 2:
        print("Usage: python sitestripe_scraper.py <amazon-url>")
        sys.exit(1)
    
    data = get_sitestripe_link_and_data(sys.argv[1])
    import json
    print(json.dumps(data, indent=2))
