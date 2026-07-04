import os
import time
import random
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from database import add_to_queue

def setup_browser(p):
    """Sets up the Playwright browser with session caching and stealth."""
    user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
    os.makedirs(user_data_dir, exist_ok=True)
    browser = p.chromium.launch_persistent_context(
        user_data_dir=user_data_dir,
        headless=True,
        args=["--disable-blink-features=AutomationControlled"]
    )
    return browser

def hunt_amazon_deals():
    """Navigates to Amazon Deals and extracts product URLs into the queue."""
    url = "https://www.amazon.in/deals"
    print(f"Hunting for deals on: {url}")
    
    with sync_playwright() as p:
        browser = setup_browser(p)
        page = browser.new_page()
        Stealth().use_sync(page)
        
        try:
            page.goto(url, wait_until="domcontentloaded", timeout=60000)
            print("Page loaded. Waiting for human delay...")
            time.sleep(random.uniform(5.0, 10.0))
            
            # Scroll down to load lazy-loaded deals
            for _ in range(5):
                page.mouse.wheel(0, random.randint(500, 1000))
                time.sleep(random.uniform(1.0, 2.5))
                
            # Extract links from deal cards
            print("Extracting deal links...")
            deal_links = []
            
            # Selector for Amazon deal grid items
            elements = page.locator("a.a-link-normal.DealLink-module__dealLink_3v4naPUXGZ_w_z37wzL_i").all()
            if not elements:
                elements = page.locator("a.a-link-normal").all() # Fallback
                
            for el in elements:
                try:
                    href = el.get_attribute("href")
                    if href and "/dp/" in href:
                        # Clean URL
                        clean_href = href.split("?")[0]
                        if not clean_href.startswith("http"):
                            clean_href = "https://www.amazon.in" + clean_href
                        deal_links.append(clean_href)
                except:
                    continue
                    
            deal_links = list(set(deal_links)) # Remove duplicates
            print(f"Found {len(deal_links)} potential deal URLs.")
            
            # Add to queue
            added = 0
            for link in deal_links:
                try:
                    add_to_queue(link)
                    added += 1
                except:
                    pass
                    
            print(f"Successfully queued {added} new deals.")
            
        except Exception as e:
            print(f"Error while hunting deals: {e}")
        finally:
            browser.close()

if __name__ == "__main__":
    hunt_amazon_deals()
