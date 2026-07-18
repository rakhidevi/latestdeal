import os
from playwright.async_api import async_playwright

class BrowserPool:
    def __init__(self):
        self.playwright = None
        self.context = None

    async def start(self):
        print("Starting Browser Pool (Strict Compliance Mode)...")
        self.playwright = await async_playwright().start()
        user_data_dir = os.path.join(os.path.dirname(__file__), 'browser_profile')
        
        # AGENTS.md Rule 1: Real Windows Chrome, Bot Stealth, Hide Warnings, Visible Mode
        self.context = await self.playwright.chromium.launch_persistent_context(
            user_data_dir=user_data_dir,
            executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
            headless=False,
            args=["--disable-blink-features=AutomationControlled"],
            ignore_default_args=["--enable-automation", "--no-sandbox"],
            user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        )
        print("Browser Pool ready.")

    async def get_page(self):
        if not self.context:
            await self.start()
            
        # AGENTS.md Rule 2: ALWAYS re-use the first existing tab instead of creating a new one
        page = self.context.pages[0] if self.context.pages else await self.context.new_page()
        return page

    async def close(self):
        if self.context:
            await self.context.close()
        if self.playwright:
            await self.playwright.stop()
        print("Browser Pool stopped.")
