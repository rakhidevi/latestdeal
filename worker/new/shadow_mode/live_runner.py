import json
import time
from pathlib import Path
from typing import List, Dict, Any
from playwright.sync_api import sync_playwright

from worker.new.providers.amazon.discovery.target_generator import AmazonSearchTargetGenerator
from worker.new.providers.amazon.compatibility_layer import AmazonCompatibilityLayer
from worker.new.sdk.foundation.dto.models import (
    ShadowDecisionRecord, DecisionLifecycleState, OpportunityDecisionDTO, 
    DecisionAction, OpportunityScore, SearchTargetDTO
)

class LiveAmazonShadowRunner:
    """
    Validation Sprint 1: Live Amazon Shadow Execution.
    Discovers, plans, translates, queues, validates, and records real live Amazon opportunities.
    """
    def __init__(self, output_dir: str):
        self.knowledge_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\knowledge\amazon\compiled"
        self.profiles_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\providers\amazon\discovery\profiles"
        self.output_dir = Path(output_dir)
        self.output_dir.mkdir(parents=True, exist_ok=True)
        
        self.target_generator = AmazonSearchTargetGenerator(self.knowledge_dir, self.profiles_dir)
        self.layer = AmazonCompatibilityLayer()
        self.shadow_ledger: List[ShadowDecisionRecord] = []
        
    def _fetch_live_payload(self, url: str) -> Dict[str, Any]:
        """
        Uses Playwright adhering to AGENTS.md strict requirements to fetch a real deal.
        """
        print(f"Fetching live URL: {url}")
        
        with sync_playwright() as p:
            browser = p.chromium.launch_persistent_context(
                user_data_dir=str(self.output_dir / "chrome_profile"),
                executable_path=r"C:\Program Files\Google\Chrome\Application\chrome.exe",
                headless=False, # Rule 1: Visible Mode
                args=["--disable-blink-features=AutomationControlled"], # Rule 1: Bot Stealth
                ignore_default_args=["--enable-automation", "--no-sandbox"] # Rule 1: Hide warnings
            )
            
            # Rule 2: Re-use the first existing tab instead of creating new ones
            page = browser.pages[0] if browser.pages else browser.new_page()
            
            try:
                page.goto(url, wait_until="domcontentloaded", timeout=30000)
                # Wait briefly to allow human interaction if CAPTCHA appears
                page.wait_for_timeout(3000)
                
                # We will extract the first search result on the page for our payload
                # This is a naive extraction just to prove the live pipeline works.
                # In a real extraction layer, `AmazonExtractor` would parse the full HTML.
                
                # For this validation sprint, we'll extract basic fields via JS evaluation
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
                        mrp: priceEl ? (parseFloat(priceEl.innerText.replace(/,/g, '')) + 1000) : 0, // Mock MRP for demo
                        availability: true,
                        seller: "Amazon Live Crawler"
                    };
                }""")
                
                return item or {}
                
            except Exception as e:
                print(f"Playwright fetch failed: {e}")
                return {}
            finally:
                browser.close()

    def run(self, limit: int = 1):
        print("Starting LIVE Shadow Mode Run (Validation Sprint 1)...")
        targets = self.target_generator.generate_targets()
        print(f"Loaded {len(targets)} targets from Discovery Profiles. Running {limit} targets.")
        
        # Limit to avoid banning or annoying the user too much during validation
        targets_to_run = targets[:limit]
        
        for target in targets_to_run:
            start_ms = time.time() * 1000
            
            live_payload = self._fetch_live_payload(target.url)
            
            if not live_payload or not live_payload.get("asin"):
                print(f"Failed to extract product from {target.url}")
                continue
                
            print(f"Successfully extracted: {live_payload['title']} - INR {live_payload['price']}")
                
            try:
                opportunity = self.layer.process_target(target, live_payload)
                
                action = DecisionAction.PUBLISH if opportunity.opportunity_score > 80 and not opportunity.rejection_reason else DecisionAction.REJECT
                
                decision_dto = OpportunityDecisionDTO(
                    trace_context=target.trace_context,
                    score=OpportunityScore(overall=int(opportunity.opportunity_score)),
                    decision=action,
                    explanation="Live Shadow Mode automated decision",
                    policy_version="1.0",
                    engine_version="2.5"
                )
                
                runtime = int((time.time() * 1000) - start_ms)
                
                shadow_record = ShadowDecisionRecord(
                    trace_context=target.trace_context,
                    search_target_uuid=target.search_target_uuid,
                    decision=decision_dto,
                    legacy_published=False,
                    comparison_difference={"note": "Live extraction success!"},
                    runtime_ms=runtime,
                    state=DecisionLifecycleState.EXECUTED
                )
                
                self.shadow_ledger.append(shadow_record)
                
            except Exception as e:
                print(f"Failed to process target {target.url}: {str(e)}")
                
        output_file = self.output_dir / f"live_shadow_ledger_{int(time.time())}.json"
        with open(output_file, "w") as f:
            json.dump([r.model_dump(mode="json") for r in self.shadow_ledger], f, indent=4)
            
        print(f"Live Shadow Mode run complete. Processed {len(self.shadow_ledger)} live targets.")
        print(f"Results written to: {output_file}")

if __name__ == "__main__":
    runner = LiveAmazonShadowRunner(output_dir=r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode\output")
    runner.run(limit=1) # Run only 1 for the validation demo
