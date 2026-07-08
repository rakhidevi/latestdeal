import os
import time
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth

def debug_sitestripe():
    with sync_playwright() as p:
        user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
        context = p.chromium.launch_persistent_context(
            user_data_dir=user_data_dir,
            headless=False,
            channel="chrome",
            args=["--disable-blink-features=AutomationControlled"]
        )
        page = context.new_page()
        Stealth().use_sync(page)
        
        url = "https://www.amazon.in/dp/B0GVZRGDC"
        print(f"Navigating to {url}...")
        page.goto(url, wait_until="domcontentloaded", timeout=60000)
        time.sleep(5)
        
        # Dump the top part of the page
        html = page.content()
        with open("sitestripe.html", "w", encoding="utf-8") as f:
            f.write(html)
        print("Successfully dumped page HTML to sitestripe.html")
            
        context.close()

if __name__ == "__main__":
    debug_sitestripe()
