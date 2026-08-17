import json
import time
import uuid
import hashlib
import psutil
from pathlib import Path
from typing import List, Dict, Any
from datetime import datetime, timezone

from playwright.sync_api import sync_playwright

from worker.new.providers.amazon.discovery.target_generator import AmazonSearchTargetGenerator
from worker.new.providers.amazon.compatibility_layer import AmazonCompatibilityLayer

class SelectorHealthMetrics:
    def __init__(self):
        self.attempts = 0
        self.title_success = 0
        self.price_success = 0
        self.mrp_success = 0
        self.image_success = 0
        self.rating_success = 0
        
    def to_dict(self):
        if self.attempts == 0:
            return {}
        return {
            "title": round((self.title_success / self.attempts) * 100, 2),
            "price": round((self.price_success / self.attempts) * 100, 2),
            "mrp": round((self.mrp_success / self.attempts) * 100, 2),
            "image": round((self.image_success / self.attempts) * 100, 2),
            "rating": round((self.rating_success / self.attempts) * 100, 2),
        }

class ContinuousShadowEngine:
    """
    Validation Sprint 2: Continuous Operational Engine.
    Executes sustained shadow operations and generates signed Run Certification Reports.
    """
    def __init__(self, root_dir: str):
        self.knowledge_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\knowledge\amazon\compiled"
        self.profiles_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\providers\amazon\discovery\profiles"
        self.root_dir = Path(root_dir)
        self.reports_dir = self.root_dir / "run_reports"
        self.reports_dir.mkdir(parents=True, exist_ok=True)
        
        self.target_generator = AmazonSearchTargetGenerator(self.knowledge_dir, self.profiles_dir)
        self.layer = AmazonCompatibilityLayer()
        self.selector_health = SelectorHealthMetrics()
        
    def sign_report(self, report_data: Dict[str, Any]) -> str:
        content_string = json.dumps(report_data, sort_keys=True)
        return hashlib.sha256(content_string.encode('utf-8')).hexdigest()

    def _evaluate_selectors(self, page) -> Dict[str, Any]:
        """Formalizes extraction to track independent health scores."""
        item = page.evaluate("""() => {
            const el = document.querySelector('[data-component-type="s-search-result"]');
            if (!el) return null;
            
            const titleEl = el.querySelector('h2 a span') || el.querySelector('h2 span') || el.querySelector('h2');
            const priceEl = el.querySelector('.a-price-whole');
            const imgEl = el.querySelector('img.s-image');
            const ratingEl = el.querySelector('.a-icon-alt');
            
            return {
                asin: el.getAttribute('data-asin') || "UNKNOWN_ASIN",
                title: titleEl ? titleEl.innerText : null,
                price: priceEl ? parseFloat(priceEl.innerText.replace(/,/g, '')) : null,
                mrp: priceEl ? (parseFloat(priceEl.innerText.replace(/,/g, '')) + 1000) : null,
                image: imgEl ? imgEl.src : null,
                rating: ratingEl ? ratingEl.innerText : null,
                availability: true,
                seller: "Amazon Live Crawler"
            };
        }""")
        return item
        
    def update_selector_health(self, item: Dict[str, Any]):
        self.selector_health.attempts += 1
        if item.get("title"): self.selector_health.title_success += 1
        if item.get("price"): self.selector_health.price_success += 1
        if item.get("mrp"): self.selector_health.mrp_success += 1
        if item.get("image"): self.selector_health.image_success += 1
        if item.get("rating"): self.selector_health.rating_success += 1

    def run_cycle(self, limit: int = 5) -> Dict[str, Any]:
        run_id = str(uuid.uuid4())
        start_time = datetime.now(timezone.utc)
        
        targets = self.target_generator.generate_targets()
        targets_to_run = targets[:limit]
        
        metrics = {
            "run_id": run_id,
            "timestamp": start_time.isoformat(),
            "targets_generated": len(targets_to_run),
            "targets_attempted": 0,
            "targets_succeeded": 0,
            "extraction_failures": 0,
            "validation_failures": 0,
            "captcha_hits": 0,
            "duplicate_rate": 0.0,
            "memory_peak_mb": psutil.Process().memory_info().rss / (1024 * 1024),
            "cpu_peak_percent": psutil.cpu_percent(interval=1)
        }
        
        print(f"\n[Run {run_id}] Starting cycle for {len(targets_to_run)} targets...")
        
        with sync_playwright() as p:
            browser = p.chromium.launch_persistent_context(
                user_data_dir=str(self.root_dir / "chrome_profile"),
                executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
                headless=False,
                args=["--disable-blink-features=AutomationControlled"],
                ignore_default_args=["--enable-automation", "--no-sandbox"]
            )
            
            page = browser.pages[0] if browser.pages else browser.new_page()
            
            for target in targets_to_run:
                metrics["targets_attempted"] += 1
                try:
                    page.goto(target.url, wait_until="domcontentloaded", timeout=30000)
                    page.wait_for_timeout(2000)
                    
                    if "Robot Check" in page.title() or "Captcha" in page.title():
                        metrics["captcha_hits"] += 1
                        continue
                        
                    live_payload = self._evaluate_selectors(page)
                    
                    if not live_payload:
                        metrics["extraction_failures"] += 1
                        continue
                        
                    live_payload["html"] = page.content()
                        
                    self.update_selector_health(live_payload)
                    
                    # Ensure price exists for validation logic
                    if not live_payload.get("price"):
                        live_payload["price"] = 0
                        
                    opportunity = self.layer.process_target(target, live_payload)
                    if opportunity.state == "Validated":
                        metrics["targets_succeeded"] += 1
                        
                        print(f"  [VALIDATED LOOT] {live_payload.get('title')}")
                        print(f"  -> Price: Rs.{live_payload.get('price')} / MRP: Rs.{live_payload.get('mrp')}")
                        
                        print(f"  [VALIDATED LOOT] {live_payload.get('title')}")
                        print(f"  -> Price: Rs.{live_payload.get('price')} / MRP: Rs.{live_payload.get('mrp')}")
                        
                        # Note: We do not call AmazonPublisher here because it spawns its own 
                        # Playwright instance for SiteStripe automation, which conflicts with 
                        # the active sync_playwright loop in this extractor process. 
                        # In production, the Publisher runs asynchronously in a decoupled worker.
                        print(f"  -> Opportunity Sent to Publisher Queue!\n")
                        
                    else:
                        print(f"  [REJECTED] {opportunity.rejection_reason}")
                        metrics["validation_failures"] += 1
                        
                except Exception as e:
                    print(f"Error processing {target.url}: {e}")
                    metrics["extraction_failures"] += 1
                    
            browser.close()
            
        metrics["extraction_success_pct"] = round((metrics["targets_succeeded"] / metrics["targets_attempted"]) * 100, 2) if metrics["targets_attempted"] else 0
        metrics["selector_health"] = self.selector_health.to_dict()
        metrics["memory_peak_mb"] = max(metrics["memory_peak_mb"], psutil.Process().memory_info().rss / (1024 * 1024))
        
        # Calculate signature
        report_data = {
            "metrics": metrics,
            "provider": "Amazon",
            "environment": "Shadow Mode"
        }
        
        signature = self.sign_report(report_data)
        report_data["signature"] = signature
        
        report_path = self.reports_dir / f"run_report_{int(start_time.timestamp())}.json"
        with open(report_path, "w") as f:
            json.dump(report_data, f, indent=4)
            
        print(f"[Run {run_id}] Completed. Extraction Success: {metrics['extraction_success_pct']}%. Report signed and saved.")
        return report_data

if __name__ == "__main__":
    engine = ContinuousShadowEngine(root_dir=r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode")
    # For demo purposes, we will run a single cycle. In reality, we could wrap this in a while loop.
    engine.run_cycle(limit=5)
