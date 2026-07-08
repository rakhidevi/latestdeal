import os
import time
from playwright.sync_api import sync_playwright

def open_browser():
    with sync_playwright() as p:
        user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
        context = p.chromium.launch_persistent_context(
            user_data_dir=user_data_dir,
            headless=False,
            channel="chrome"
        )
        page = context.new_page()
        page.goto("https://www.amazon.in/dp/B0GVZRGDC")
        print("Browser opened! Please inspect the yellow 'Get Link.' button...")
        time.sleep(3600)

if __name__ == "__main__":
    open_browser()
