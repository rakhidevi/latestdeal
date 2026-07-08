import os
import time
import random
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from database import add_to_queue
import argparse

def setup_browser(p):
    """Launches a persistent browser session (visible) to store cookies/auth."""
    user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
    os.makedirs(user_data_dir, exist_ok=True)
    
    # Launch Chrome visibly so the user can log in if needed
    browser = p.chromium.launch_persistent_context(
        user_data_dir=user_data_dir,
        headless=False,
        channel="chrome", # Use real Chrome
        args=["--disable-blink-features=AutomationControlled"]
    )
    return browser

def hunt_amazon_deals(job_type='ingestion', category=None, brand=None, discount=None, keyword=None):
    """Navigates to Amazon Deals and extracts product URLs into the queue."""
    search_terms = []
    if brand: search_terms.append(brand)
    if category: search_terms.append(category)
    if keyword: search_terms.append(keyword)
    if discount: search_terms.append(f"{discount}% off")
    
    if not search_terms:
        url = "https://www.amazon.in/deals"
    else:
        import urllib.parse
        q = urllib.parse.quote(" ".join(search_terms))
        # p_n_specials_match:21618256031 forces Amazon to only show active deals
        url = f"https://www.amazon.in/s?k={q}&rh=p_n_specials_match%3A21618256031"
        
    print(f"Hunting for deals on: {url} (Target Queue: {job_type})")
    
    with sync_playwright() as p:
        context = setup_browser(p)
        page = context.new_page()
        Stealth().use_sync(page)
        
        try:
            page.goto(url, wait_until="domcontentloaded", timeout=60000)
            
            # Check if user needs to log in
            if page.locator("text='Sign in'").count() > 0 or page.locator("text='Sign In'").count() > 0:
                print("\n🚨 [ACTION REQUIRED] Please log into Amazon in the browser window! 🚨")
                input("Press ENTER here after you have successfully logged in...")
                page.goto(url, wait_until="domcontentloaded", timeout=60000)
                
            print("Page loaded. Waiting for human delay...")
            time.sleep(random.uniform(5.0, 10.0))
            
            # Scroll down to load lazy-loaded deals
            for _ in range(5):
                page.mouse.wheel(0, random.randint(500, 1000))
                time.sleep(random.uniform(1.0, 2.5))
                
            page.screenshot(path="hunter_debug.png", full_page=True)
                
            # Extract links from deal cards
            print("Extracting deal links...")
            deal_links = []
            
            # Find all links on the page that contain /dp/ (Amazon products)
            elements = page.locator("a[href*='/dp/']").all()
                
            for el in elements:
                try:
                    href = el.get_attribute("href")
                    if href:
                        clean_href = href.split("?")[0].split("ref=")[0]
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
                    add_to_queue(link, job_type)
                    added += 1
                except:
                    pass
                    
            print(f"Successfully queued {added} new deals.")
            
        except Exception as e:
            print(f"Error while hunting deals: {e}")
        finally:
            if 'page' in locals():
                page.close()
            if 'context' in locals():
                context.close()

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Hunt Amazon Deals')
    parser.add_argument('--mode', type=str, default='ingestion', choices=['ingestion', 'sitestripe_automation'], help='The extraction mode to queue the deals for')
    parser.add_argument('--category', type=str, default=None, help='Filter by category')
    parser.add_argument('--brand', type=str, default=None, help='Filter by brand')
    parser.add_argument('--discount', type=str, default=None, help='Filter by discount')
    parser.add_argument('--keyword', type=str, default=None, help='Filter by custom keyword')
    args = parser.parse_args()
    
    hunt_amazon_deals(args.mode, args.category, args.brand, args.discount, args.keyword)
