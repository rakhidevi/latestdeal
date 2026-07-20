from typing import Dict, Any
from bs4 import BeautifulSoup
from worker.new.core.interfaces import StoreScraper
from worker.new.core.dtos import DealDTO
from worker.new.sdk.browser.manager import BrowserManager
from worker.new.sdk.network.rate_limit import RateLimiter
from worker.new.sdk.anti_bot.captcha import CaptchaDetector
from worker.new.sdk.telemetry.metrics import Telemetry
from worker.new.sdk.storage.replay_store import ReplayStore
from worker.new.sdk.parsing.dom_detector import DOMChangeDetector
from worker.new.providers.flipkart.parser import FlipkartParser

class FlipkartScraper(StoreScraper):
    def __init__(self):
        self.browser_manager = BrowserManager(headless=False)
        self.parser = FlipkartParser()
        self.current_url = ""
        
    def initialize(self) -> None: pass
    
    def authenticate(self) -> bool: return True
    
    def search(self, query: str) -> list:
        self.current_url = query
        page = self.browser_manager.start()
        page.goto(query, wait_until="domcontentloaded", timeout=60000)
        RateLimiter.sleep_random(3.0, 5.0)
        return [page.content()]
        
    def extract(self, html_content: str) -> Dict[str, Any]:
        DOMChangeDetector.detect_and_alert("flipkart", html_content)
        ReplayStore.save_snapshot("flipkart", self.current_url, html_content)
        Telemetry.increment("scrape_success", "flipkart")
        
        soup = BeautifulSoup(html_content, "html.parser")
        return self.parser.extract_raw(soup)
        
    def normalize(self, raw_data: Dict[str, Any]) -> DealDTO:
        return self.parser.to_dto(raw_data, self.current_url)
        
    def validate(self, dto: DealDTO) -> bool: 
        return bool(dto.product.title and dto.product.title != "Unknown Title" and dto.price.current > 0)
        
    def health(self) -> Dict[str, Any]: return {"status": "OK"}
    def cleanup(self) -> None: self.browser_manager.stop()
