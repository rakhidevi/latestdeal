import unittest
from worker.new.sdk.foundation.dto.models import EvidenceRecord, EvidenceType, EvidenceSource, DecisionAction
from worker.new.sdk.discovery.decision.aggregator import EvidenceAggregator
from worker.new.sdk.discovery.decision.engine import OpportunityEngine
from worker.new.sdk.discovery.decision.policy import DecisionPolicyEngine

class TestOpportunityDecisionEngine(unittest.TestCase):
    def setUp(self):
        self.aggregator = EvidenceAggregator()
        self.engine = OpportunityEngine(self.aggregator)
        
        policy_config = {
            "decision_policy": {
                "publish": {"minimum_score": 85, "minimum_trust": 10},
                "review": {"minimum_score": 60}
            }
        }
        self.policy = DecisionPolicyEngine(config=policy_config, version="v1")
        
    def test_aggregator_conflict_resolution(self):
        graph = [
            EvidenceRecord(
                trace_id="t1",
                strategy="mrp_error",
                type=EvidenceType.PRICE,
                weight=100,
                confidence=0.9,
                source=EvidenceSource.STRATEGY
            ),
            EvidenceRecord(
                trace_id="t1",
                strategy="bad_seller",
                type=EvidenceType.SELLER,
                weight=-30,
                confidence=0.8,
                source=EvidenceSource.STRATEGY
            )
        ]
        
        result = self.aggregator.aggregate(graph)
        
        self.assertEqual(result["base_score"], 66) # (100*0.9) - (30*0.8) = 90 - 24 = 66
        self.assertGreater(result["conflict_ratio"], 0)
        self.assertLess(result["confidence"], 1.0)
        
    def test_opportunity_engine_multidimensional(self):
        graph = [
            EvidenceRecord(
                trace_id="t2",
                strategy="mrp",
                type=EvidenceType.PRICE,
                weight=50,
                confidence=1.0,
                source=EvidenceSource.STRATEGY
            ),
            EvidenceRecord(
                trace_id="t2",
                strategy="premium_brand",
                type=EvidenceType.BRAND,
                weight=20,
                confidence=1.0,
                source=EvidenceSource.STRATEGY
            )
        ]
        
        score = self.engine.compute_score(graph)
        self.assertEqual(score.overall, 70)
        self.assertEqual(score.price, 50)
        self.assertEqual(score.trust, 20)
        
    def test_policy_engine(self):
        graph = [
            EvidenceRecord(
                trace_id="t3",
                strategy="mega_discount",
                type=EvidenceType.PRICE,
                weight=100,
                confidence=0.9,
                source=EvidenceSource.STRATEGY
            ),
            EvidenceRecord(
                trace_id="t3",
                strategy="premium_brand",
                type=EvidenceType.BRAND,
                weight=20,
                confidence=0.9,
                source=EvidenceSource.STRATEGY
            )
        ]
        
        score = self.engine.compute_score(graph) # overall = 90 + 18 = 108, trust = 18
        
        decision = self.policy.evaluate(score)
        self.assertEqual(decision, DecisionAction.PUBLISH)
        
        # Test review
        score.overall = 70
        decision_review = self.policy.evaluate(score)
        self.assertEqual(decision_review, DecisionAction.REVIEW)

        # Test reject
        score.overall = 50
        decision_reject = self.policy.evaluate(score)
        self.assertEqual(decision_reject, DecisionAction.REJECT)

if __name__ == '__main__':
    unittest.main()
