from typing import List, Dict, Any, Optional
from worker.new.sdk.foundation.dto.models import ShadowDecisionRecord, OpportunityDecisionDTO, SearchTargetDTO
from worker.new.sdk.discovery.decision.engine import OpportunityEngine
from worker.new.sdk.discovery.decision.policy import DecisionPolicyEngine

class SimulationEngine:
    """
    Simulation & Replay Engine (Phase 11.6)
    Replays historical shadow decisions against a new DecisionPolicyEngine 
    to observe changes in publishing decisions without production impact.
    """
    
    def __init__(self, opportunity_engine: OpportunityEngine):
        self.opportunity_engine = opportunity_engine
        
    def replay_decision(
        self,
        historical_record: ShadowDecisionRecord,
        new_policy: DecisionPolicyEngine
    ) -> Dict[str, Any]:
        """
        Re-evaluates the historical evidence graph against a new policy.
        """
        old_decision = historical_record.decision.decision
        evidence_graph = historical_record.decision.evidence_graph
        
        # We re-score the graph just in case scoring logic changed
        new_score = self.opportunity_engine.compute_score(evidence_graph)
        
        # Apply the new policy
        new_decision_action = new_policy.evaluate(new_score)
        
        changed = (old_decision != new_decision_action)
        
        return {
            "trace_id": historical_record.trace_id,
            "old_decision": old_decision.value,
            "new_decision": new_decision_action.value,
            "changed": changed,
            "legacy_published": historical_record.legacy_published
        }
        
    def replay_batch(
        self,
        historical_records: List[ShadowDecisionRecord],
        new_policy: DecisionPolicyEngine
    ) -> List[Dict[str, Any]]:
        """
        Replays a large batch of historical decisions.
        """
        results = []
        for record in historical_records:
            results.append(self.replay_decision(record, new_policy))
        return results
