import unittest
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, TargetLifecycleState, ShadowDecisionRecord, OpportunityDecisionDTO, DecisionAction, OpportunityScore, DecisionLifecycleState
from worker.new.sdk.discovery.lifecycle.manager import LifecycleManager

class TestLifecycleManager(unittest.TestCase):
    def test_transition_target(self):
        target = SearchTargetDTO(
            trace_id="trc-test",
            provider="amazon",
            priority=50
        )
        self.assertEqual(target.state, TargetLifecycleState.CREATED)
        
        LifecycleManager.transition_target(target, TargetLifecycleState.PLANNED)
        self.assertEqual(target.state, TargetLifecycleState.PLANNED)
        
        LifecycleManager.transition_target(target, TargetLifecycleState.ARCHIVED)
        self.assertEqual(target.state, TargetLifecycleState.ARCHIVED)
        
        with self.assertRaises(ValueError):
            LifecycleManager.transition_target(target, TargetLifecycleState.PUBLISHED)
            
    def test_transition_decision(self):
        score = OpportunityScore(overall=80, confidence=90)
        decision = OpportunityDecisionDTO(
            trace_id="t1",
            score=score,
            decision=DecisionAction.PUBLISH,
            explanation="",
            policy_version="1",
            engine_version="1"
        )
        
        record = ShadowDecisionRecord(
            trace_id="t1",
            target_id="tgt1",
            decision=decision,
            legacy_published=True,
            runtime_ms=100
        )
        self.assertEqual(record.state, DecisionLifecycleState.CREATED)
        
        LifecycleManager.transition_decision(record, DecisionLifecycleState.EVALUATED)
        self.assertEqual(record.state, DecisionLifecycleState.EVALUATED)
        
        LifecycleManager.transition_decision(record, DecisionLifecycleState.ARCHIVED)
        self.assertEqual(record.state, DecisionLifecycleState.ARCHIVED)
        
        with self.assertRaises(ValueError):
            LifecycleManager.transition_decision(record, DecisionLifecycleState.EXECUTED)

if __name__ == '__main__':
    unittest.main()
