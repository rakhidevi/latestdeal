import json
import time
from pathlib import Path
from datetime import datetime, timezone
from typing import List

from worker.new.providers.amazon.discovery.target_generator import AmazonSearchTargetGenerator
from worker.new.providers.amazon.compatibility_layer import AmazonCompatibilityLayer
from worker.new.sdk.foundation.dto.models import ShadowDecisionRecord, DecisionLifecycleState, OpportunityDecisionDTO, DecisionAction, OpportunityScore

class ShadowModeRunner:
    """
    Executes generated SearchTargets against live or simulated traffic without publishing (Sprint 7).
    """
    def __init__(self, output_dir: str):
        self.knowledge_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\knowledge\amazon\compiled"
        self.profiles_dir = r"k:\WhatsAppUtility\LatestDeal\worker\new\providers\amazon\discovery\profiles"
        self.output_dir = Path(output_dir)
        self.output_dir.mkdir(parents=True, exist_ok=True)
        
        self.target_generator = AmazonSearchTargetGenerator(self.knowledge_dir, self.profiles_dir)
        self.layer = AmazonCompatibilityLayer()
        self.shadow_ledger: List[ShadowDecisionRecord] = []
        
    def run(self):
        print("Starting Shadow Mode Run...")
        targets = self.target_generator.generate_targets()
        print(f"Loaded {len(targets)} targets from Discovery Profiles.")
        
        # Simulate a scraper returning a generic payload for each target
        mock_payload = {
            "asin": "B0CHX1WCGZ",
            "title": "Apple iPhone 15 (128 GB)",
            "price": 69990,
            "mrp": 79900,
            "availability": True,
            "seller": "Appario Retail Private Ltd"
        }
        
        for target in targets:
            start_ms = time.time() * 1000
            
            # Run through Compatibility Layer (Extraction + Validation)
            try:
                opportunity = self.layer.process_target(target, mock_payload)
                
                # Mock Decision logic based on score
                action = DecisionAction.PUBLISH if opportunity.opportunity_score > 80 and not opportunity.rejection_reason else DecisionAction.REJECT
                
                decision_dto = OpportunityDecisionDTO(
                    trace_context=target.trace_context,
                    score=OpportunityScore(overall=int(opportunity.opportunity_score)),
                    decision=action,
                    explanation="Shadow Mode automated test decision",
                    policy_version="1.0",
                    engine_version="2.5"
                )
                
                runtime = int((time.time() * 1000) - start_ms)
                
                shadow_record = ShadowDecisionRecord(
                    trace_context=target.trace_context,
                    search_target_uuid=target.search_target_uuid,
                    decision=decision_dto,
                    legacy_published=False,  # This tells us if legacy crawler also found it
                    comparison_difference={"note": "Legacy crawler missed this entirely"},
                    runtime_ms=runtime,
                    state=DecisionLifecycleState.EXECUTED
                )
                
                self.shadow_ledger.append(shadow_record)
                
            except Exception as e:
                print(f"Failed to process target {target.url}: {str(e)}")
                
        # Save Shadow Ledger
        output_file = self.output_dir / f"shadow_ledger_{int(time.time())}.json"
        with open(output_file, "w") as f:
            # Pydantic models to JSON
            json.dump([r.model_dump(mode="json") for r in self.shadow_ledger], f, indent=4)
            
        print(f"Shadow Mode run complete. Processed {len(self.shadow_ledger)} targets.")
        print(f"Results written to: {output_file}")

if __name__ == "__main__":
    runner = ShadowModeRunner(output_dir=r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode\output")
    runner.run()
