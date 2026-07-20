import os
from typing import Dict, Any
from bs4 import BeautifulSoup

from worker.new.core.interfaces import StoreScraper
from worker.new.core.dtos import DealDTO
from worker.new.core.errors import ValidationError
from .parser import AmazonParser
from worker.new.sdk.browser.manager import BrowserManager
from worker.new.sdk.browser.session import SessionManager
from worker.new.sdk.network.rate_limit import RateLimiter
from worker.new.sdk.anti_bot.captcha import CaptchaDetector
from worker.new.sdk.storage.replay_store import ReplayStore
from worker.new.sdk.telemetry.metrics import Telemetry
from worker.new.sdk.events.dispatcher import AlertDispatcher, AlertSeverity

class AmazonScraper(StoreScraper):
    VERSION = "1.2.0"
    SELECTORS_UPDATED = "2026-07"
    PARSER_VERSION = "v3"
    SCHEMA_VERSION = 1
    
    def __init__(self):
        self.browser_manager = BrowserManager(headless=False)
        self.parser = AmazonParser()
        self.is_healthy = False
        self.current_url = ""
        
    def initialize(self) -> None:
        self.is_healthy = True

    def authenticate(self) -> bool:
        return True

    def search(self, query: str) -> list:
        self.current_url = query
        page = self.browser_manager.start()
        
        page.goto(query, wait_until="domcontentloaded", timeout=60000)
        RateLimiter.sleep_random(8.0, 12.0)
        SessionManager.simulate_human_scroll(page, scroll_count=2)
        
        if CaptchaDetector.is_captcha_present(page, "amazon"):
            AlertDispatcher.dispatch("amazon", AlertSeverity.WARNING, "Captcha", "Amazon presented a CAPTCHA.")
            raise Exception("CAPTCHA detected by SDK.")
            
        html_content = page.content()
        return [html_content]

    def extract(self, html_content: str) -> Dict[str, Any]:
        from worker.new.sdk.parsing.dom_detector import DOMChangeDetector
        DOMChangeDetector.detect_and_alert("amazon", html_content)
        
        ReplayStore.save_snapshot("amazon", self.current_url, html_content)
        Telemetry.increment("scrape_success", "amazon")
        
        soup = BeautifulSoup(html_content, "html.parser")
        return self.parser.extract_raw(soup)

    def normalize(self, raw_data: Dict[str, Any]) -> DealDTO:
        return self.parser.to_dto(raw_data, self.current_url)

    def validate(self, dto: DealDTO) -> bool:
        if not dto.product.title or dto.price.current == 0.0:
            raise ValidationError("Missing essential fields: title or current price.")
        return True

    def health(self) -> Dict[str, Any]:
        return {
            "status": "OK" if self.is_healthy else "ERROR",
            "browser": True,
            "selectors": True
        }

    def cleanup(self) -> None:
        self.browser_manager.stop()
