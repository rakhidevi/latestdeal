from playwright.sync_api import sync_playwright
import os

user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')

with sync_playwright() as p:
    try:
        context = p.chromium.launch_persistent_context(
            user_data_dir=user_data_dir,
            headless=True,
            executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
            args=["--disable-blink-features=AutomationControlled"]
        )
        page = context.pages[0] if context.pages else context.new_page()
        page.goto("https://www.amazon.in/dp/B0BYSB5W3Y", wait_until="domcontentloaded", timeout=60000)
        page.wait_for_selector("#amzn-ss-wrap", timeout=20000)
        html = page.locator("#amzn-ss-wrap").inner_html()
        with open("scratch/sitestripe_dom.html", "w", encoding="utf-8") as f:
            f.write(html)
        print("DOM dumped successfully!")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        context.close()
