import asyncio
from playwright.async_api import async_playwright
from .logging_config import logger
from .config import CONTEXT_RECYCLE_THRESHOLD

class BrowserManager:
    def __init__(self):
        self.playwright = None
        self.browser = None
        self.context = None
        self.scan_count = 0
        
    async def start(self):
        logger.info("Starting browser manager")
        self.playwright = await async_playwright().start()
        # Headless chromium, disable automation blink features to evade basic detection
        self.browser = await self.playwright.chromium.launch(
            headless=True,
            args=["--disable-blink-features=AutomationControlled", "--no-sandbox"]
        )
        await self._create_new_context()
        
    async def _create_new_context(self):
        if self.context:
            await self.context.close()
        logger.info("Creating new browser context")
        self.context = await self.browser.new_context(
            user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            extra_http_headers={
                "Accept-Language": "en-IN,en;q=0.9",
                "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
            }
        )
        # We can add stealth scripts here if needed.
        self.scan_count = 0
        
    async def get_page(self):
        self.scan_count += 1
        if self.scan_count > CONTEXT_RECYCLE_THRESHOLD:
            logger.info("Context recycle threshold reached", extra={"scan_count": self.scan_count})
            await self._create_new_context()
            
        page = await self.context.new_page()
        return page

    async def recycle_context_now(self):
        """Force recycle due to health degradation (e.g. timeout, memory)."""
        logger.warning("Forcing context recycle due to degradation")
        await self._create_new_context()

    async def stop(self):
        if self.context:
            await self.context.close()
        if self.browser:
            await self.browser.close()
        if self.playwright:
            await self.playwright.stop()
        logger.info("Browser manager stopped")
