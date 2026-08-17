import unittest
from typing import Any
from worker.new.sdk.foundation.contracts.shadow import ShadowStore
from worker.new.sdk.foundation.dto.models import OpportunityDecisionDTO, SearchTargetDTO, OpportunityScore, DecisionAction, EvidenceRecord
from worker.new.sdk.discovery.shadow.engine import ShadowModeEngine

class MockShadowStore(ShadowStore):
    def __init__(self):
        self.records = []
        
    def save_record(self, record: Any) -> None:
        self.records.append(record)

class TestShadowModeEngine(unittest.TestCase):
    def setUp(self):
        self.store = MockShadowStore()
        self.engine = ShadowModeEngine(self.store)
        
    def test_execute_and_record(self):
        target = SearchTargetDTO(
            trace_id="trc-123",
            provider="amazon",
            profile="test",
            strategy="mrp_error",
            priority=90
        )
        
        score = OpportunityScore(overall=95)
        decision = OpportunityDecisionDTO(
            trace_id="trc-123",
            score=score,
            decision=DecisionAction.PUBLISH,
            explanation="Test explanation",
            policy_version="1.0",
            engine_version="1.0"
        )
        
        # Test case where both new engine and legacy engine agree to publish
        record = self.engine.execute_and_record(
            target=target,
            decision=decision,
            legacy_published=True,
            legacy_url="http://amazon.in/dp/B01"
        )
        
        self.assertEqual(len(self.store.records), 1)
        saved_record = self.store.records[0]
        
        self.assertEqual(saved_record.trace_id, "trc-123")
        self.assertTrue(saved_record.comparison_difference["agreed"])
        self.assertEqual(saved_record.legacy_url, "http://amazon.in/dp/B01")
        self.assertGreaterEqual(saved_record.runtime_ms, 0)
        
    def test_execute_and_record_disagreement(self):
        target = SearchTargetDTO(
            trace_id="trc-124",
            provider="amazon",
            profile="test",
            strategy="mega_discount",
            priority=40
        )
        
        score = OpportunityScore(overall=45)
        decision = OpportunityDecisionDTO(
            trace_id="trc-124",
            score=score,
            decision=DecisionAction.REJECT,
            explanation="Low score",
            policy_version="1.0",
            engine_version="1.0"
        )
        
        # Test case where new engine rejects, but legacy engine published it (False negative/True positive disagreement)
        record = self.engine.execute_and_record(
            target=target,
            decision=decision,
            legacy_published=True
        )
        
        self.assertFalse(record.comparison_difference["agreed"])
        self.assertEqual(record.comparison_difference["new_decision"], "REJECT")
        self.assertTrue(record.comparison_difference["legacy_published"])

if __name__ == '__main__':
    unittest.main()
