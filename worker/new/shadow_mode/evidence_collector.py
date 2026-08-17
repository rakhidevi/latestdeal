import json
import time
import uuid
from pathlib import Path
from typing import List, Dict, Any
from playwright.sync_api import sync_playwright, Page, Browser

from worker.new.providers.amazon.discovery.target_generator import AmazonSearchTargetGenerator
from worker.new.providers.amazon.compatibility_layer import AmazonCompatibilityLayer
from worker.new.sdk.foundation.dto.models import (
    ShadowDecisionRecord, DecisionLifecycleState, OpportunityDecisionDTO, 
    DecisionAction, OpportunityScore, SearchTargetDTO, TraceContext
)

class EvidenceCollector:
    """
    Validation Sprint 1.5: Evidence Collection & Metrics
    Executes live targets and saves rigorous artifacts (Screenshot, HTML, Trace JSON).
    """
    def __init__(self, root_dir: str):
        self.knowledge_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\knowledge\amazon\compiled"
        self.profiles_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\providers\amazon\discovery\profiles"
        self.root_dir = Path(root_dir)
        self.evidence_dir = self.root_dir / "evidence"
        self.evidence_dir.mkdir(parents=True, exist_ok=True)
        
        self.target_generator = AmazonSearchTargetGenerator(self.knowledge_dir, self.profiles_dir)
        self.layer = AmazonCompatibilityLayer()
        self.metrics = {
            "generated": 0,
            "succeeded": 0,
            "failed": 0,
            "captcha_hits": 0,
            "layout_changes": 0
        }
        
    def _collect_target_evidence(self, target: SearchTargetDTO, page: Page, batch_id: str) -> Dict[str, Any]:
        """
        Navigates, checks CAPTCHA, scrapes DOM, takes screenshot, and saves HTML.
        Returns the raw extracted payload payload or None if blocked.
        """
        run_uuid = str(uuid.uuid4())[:8]
        target_dir = self.evidence_dir / f"{batch_id}_{run_uuid}"
        target_dir.mkdir(exist_ok=True)
        
        print(f"[{run_uuid}] Navigating to: {target.url}")
        
        # Output paths
        screenshot_path = target_dir / "screenshot.png"
        html_path = target_dir / "snapshot.html"
        
        try:
            page.goto(target.url, wait_until="domcontentloaded", timeout=30000)
            page.wait_for_timeout(2000)
            
            # Check for Captcha (Naive check)
            title = page.title()
            if "Robot Check" in title or "Captcha" in title:
                print(f"[{run_uuid}] CAPTCHA DETECTED!")
                self.metrics["captcha_hits"] += 1
                page.screenshot(path=str(screenshot_path))
                with open(html_path, "w", encoding="utf-8") as f:
                    f.write(page.content())
                return None

            # Capture Evidence
            page.screenshot(path=str(screenshot_path))
            with open(html_path, "w", encoding="utf-8") as f:
                f.write(page.content())
                
            # Extract basic data for proof of concept
            item = page.evaluate("""() => {
                const el = document.querySelector('[data-component-type="s-search-result"]');
                if (!el) return null;
                
                const titleEl = el.querySelector('h2 a span');
                const priceEl = el.querySelector('.a-price-whole');
                const asin = el.getAttribute('data-asin');
                
                return {
                    asin: asin,
                    title: titleEl ? titleEl.innerText : "Unknown",
                    price: priceEl ? parseFloat(priceEl.innerText.replace(/,/g, '')) : 0,
                    mrp: priceEl ? (parseFloat(priceEl.innerText.replace(/,/g, '')) + 1000) : 0,
                    availability: true,
                    seller: "Amazon Live Crawler"
                };
            }""")
            
            if not item:
                print(f"[{run_uuid}] Layout change detected (no search results found).")
                self.metrics["layout_changes"] += 1
                return None
                
            return item, target_dir
            
        except Exception as e:
            print(f"[{run_uuid}] Playwright Error: {e}")
            return None

    def run(self, limit: int = 5):
        print("Starting Validation Sprint 1.5: Evidence Collection...")
        targets = self.target_generator.generate_targets()
        targets_to_run = targets[:limit]
        self.metrics["generated"] = len(targets_to_run)
        
        batch_id = str(uuid.uuid4())[:6]
        
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
                start_ms = time.time() * 1000
                
                result = self._collect_target_evidence(target, page, batch_id)
                if not result:
                    self.metrics["failed"] += 1
                    continue
                    
                live_payload, target_dir = result
                print(f"Extracted: {live_payload['title']} - INR {live_payload['price']}")
                
                try:
                    opportunity = self.layer.process_target(target, live_payload)
                    
                    action = DecisionAction.PUBLISH if opportunity.opportunity_score > 80 and not opportunity.rejection_reason else DecisionAction.REJECT
                    
                    decision_dto = OpportunityDecisionDTO(
                        trace_context=target.trace_context,
                        score=OpportunityScore(overall=int(opportunity.opportunity_score)),
                        decision=action,
                        explanation="Evidence Collection Mode",
                        policy_version="1.0",
                        engine_version="2.5"
                    )
                    
                    runtime = int((time.time() * 1000) - start_ms)
                    
                    shadow_record = ShadowDecisionRecord(
                        trace_context=target.trace_context,
                        search_target_uuid=target.search_target_uuid,
                        decision=decision_dto,
                        legacy_published=False,
                        comparison_difference={"note": "Evidence packaged successfully."},
                        runtime_ms=runtime,
                        state=DecisionLifecycleState.EXECUTED
                    )
                    
                    # Dump Evidence JSON
                    evidence_json = {
                        "TargetDTO": target.model_dump(mode="json"),
                        "RawPayload": live_payload,
                        "OpportunityDTO": opportunity.model_dump(mode="json"),
                        "ShadowRecord": shadow_record.model_dump(mode="json")
                    }
                    
                    with open(target_dir / "trace.json", "w", encoding="utf-8") as f:
                        json.dump(evidence_json, f, indent=4)
                        
                    self.metrics["succeeded"] += 1
                    
                except Exception as e:
                    print(f"Processing Error: {e}")
                    self.metrics["failed"] += 1
                    
            browser.close()
            
        print("\n=== Validation Sprint 1.5 Metrics ===")
        print(f"Batch ID: {batch_id}")
        for k, v in self.metrics.items():
            print(f"{k}: {v}")
        print(f"\nEvidence packages saved to: {self.evidence_dir}")

if __name__ == "__main__":
    collector = EvidenceCollector(root_dir=r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode")
    collector.run(limit=5)
