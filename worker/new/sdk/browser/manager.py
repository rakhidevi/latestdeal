import os
import random
import time
from playwright.sync_api import sync_playwright
from playwright_stealth import Stealth

DESKTOP_USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36"
]

class BrowserManager:
    """Manages stealth browser lifecycle."""
    def __init__(self, headless=False):
        self.headless = headless
        self._playwright = None
        self._browser = None
        self._page = None

    def start(self):
        self._playwright = sync_playwright().start()
        user_data_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..', 'browser_profile'))
        os.makedirs(user_data_dir, exist_ok=True)
        
        self._browser = None
        for attempt in range(2):
            try:
                self._browser = self._playwright.chromium.launch_persistent_context(
                    user_data_dir=user_data_dir,
                    headless=self.headless,
                    executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
                    args=["--disable-blink-features=AutomationControlled"],
                    ignore_default_args=["--enable-automation", "--no-sandbox"],
                    viewport={"width": 1920, "height": 1080}
                )
                break
            except Exception as e:
                print(f"BrowserManager: Playwright browser profile LOCKED! {e}")
                if attempt == 0:
                    import subprocess, sys
                    print("BrowserManager: Auto-killing zombie Chrome processes and retrying...")
                    subprocess.run([sys.executable, os.path.join(os.path.dirname(__file__), '..', '..', '..', '..', 'kill_zombie_chrome.py')])
                    time.sleep(2)
                else:
                    raise e
        
        # Aggressively close any extra tabs that pop in from asynchronous session restore
        def close_extra_page(p):
            try:
                p.close()
            except:
                pass
        self._browser.on("page", close_extra_page)

        while len(self._browser.pages) > 1:
            self._browser.pages[-1].close()
        self._page = self._browser.pages[0] if self._browser.pages else self._browser.new_page()
        Stealth().apply_stealth_sync(self._page)
        
        ua = random.choice(DESKTOP_USER_AGENTS)
        self._page.set_extra_http_headers({"User-Agent": ua})
        
        return self._page

    def get_page(self):
        return self._page

    def stop(self):
        if self._browser:
            try:
                self._browser.close()
            except:
                pass
        if self._playwright:
            try:
                self._playwright.stop()
            except:
                pass
