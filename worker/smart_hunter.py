import os
import time
import random
import requests
import urllib.parse
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth
from database import add_to_queue
import argparse

def get_scraping_config():
    """Fetches the dynamic scraping configuration from the Laravel backend."""
    backend_url = os.getenv("API_URL", "http://localhost:8000/api/v1")
    try:
        response = requests.get(f"{backend_url}/config/scraping")
        if response.status_code == 200:
            return response.json()
        print(f"Failed to fetch config. Status: {response.status_code}")
    except Exception as e:
        print(f"Error fetching config: {e}")
    return {"brand_tiers": [], "scraping_configs": []}

def setup_browser(p):
    """Launches a persistent browser session (visible) to store cookies/auth."""
    user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
    os.makedirs(user_data_dir, exist_ok=True)
    
    browser = p.chromium.launch_persistent_context(
        user_data_dir=user_data_dir,
        headless=False,
        executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe", 
        args=["--disable-blink-features=AutomationControlled"],
        ignore_default_args=["--enable-automation", "--no-sandbox"]
    )
    return browser

def hunt_smart_deals(job_type='ingestion'):
    """Fetches dynamic configs and iterates through targets for Amazon deals."""
    print("Fetching dynamic configs from backend...")
    config_data = get_scraping_config()
    targets = config_data.get('scraping_configs', [])
    
    if not targets:
        print("No active scraping configs found. Exiting.")
        return

    with sync_playwright() as p:
        context = setup_browser(p)
        page = context.pages[0] if context.pages else context.new_page()
        Stealth().apply_stealth_sync(page)
        
        for target in targets:
            provider = target.get('provider', '').lower()
            if provider != 'amazon':
                continue
                
            target_name = target.get('target_name', '')
            print(f"Hunting for targeted deals: {target_name} on Amazon...")
            
            q = urllib.parse.quote(target_name)
            # p_n_specials_match:21618256031 forces Amazon to only show active deals
            url = f"https://www.amazon.in/s?k={q}&rh=p_n_specials_match%3A21618256031"
            
            try:
                page.goto(url, wait_until="domcontentloaded", timeout=60000)
                
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
                    
                print("Extracting deal links...")
                deal_links = []
                
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
                        
                deal_links = list(set(deal_links))
                print(f"Found {len(deal_links)} potential deal URLs for {target_name}.")
                
                added = 0
                for link in deal_links:
                    try:
                        add_to_queue(link, job_type)
                        added += 1
                    except:
                        pass
                        
                print(f"Successfully queued {added} new deals for {target_name}.\n")
                
            except Exception as e:
                print(f"Error while hunting deals for {target_name}: {e}")
                
            time.sleep(15) # Delay between different targeted queries
            
        if 'page' in locals():
            page.close()
        if 'context' in locals():
            context.close()

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Smart Hunt Targeted Deals')
    parser.add_argument('--mode', type=str, default='ingestion', help='Queue mode')
    args = parser.parse_args()
    
    hunt_smart_deals(args.mode)
