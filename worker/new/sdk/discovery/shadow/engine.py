from typing import Dict, Any, Optional
import time
from worker.new.sdk.foundation.dto.models import OpportunityDecisionDTO, SearchTargetDTO, ShadowDecisionRecord
from worker.new.sdk.foundation.contracts.shadow import ShadowStore

class ShadowModeEngine:
    """
    Shadow Mode Engine (Phase 11.5)
    Runs the entire decision pipeline in the background and compares outcomes 
    to the legacy production system without actually publishing.
    """
    
    def __init__(self, shadow_store: ShadowStore):
        self.shadow_store = shadow_store
        
    def execute_and_record(
        self,
        target: SearchTargetDTO,
        decision: OpportunityDecisionDTO,
        legacy_published: bool,
        legacy_url: Optional[str] = None
    ) -> ShadowDecisionRecord:
        """
        Records the new engine's decision alongside the legacy engine's actual result.
        """
        start_time = time.time()
        
        # In a real implementation, you might do deeper JSON diffs between
        # the legacy item payload and the new Opportunity features.
        comparison_diff = {
            "agreed": (decision.decision.value == "PUBLISH") == legacy_published,
            "new_decision": decision.decision.value,
            "legacy_published": legacy_published
        }
        
        runtime_ms = int((time.time() - start_time) * 1000)
        
        record = ShadowDecisionRecord(
            trace_context=target.trace_context,
            search_target_uuid=target.search_target_uuid,
            decision=decision,
            legacy_published=legacy_published,
            legacy_url=legacy_url,
            comparison_difference=comparison_diff,
            runtime_ms=runtime_ms
        )
        
        # Persist to Shadow Store
        self.shadow_store.save_record(record)
        
        return record
