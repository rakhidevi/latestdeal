import unittest
from worker.new.sdk.foundation.dto.models import ShadowDecisionRecord, OpportunityDecisionDTO, DecisionAction, OpportunityScore
from worker.new.sdk.discovery.analytics.aggregator import AnalyticsAggregator

class TestAnalyticsAggregator(unittest.TestCase):
    def test_aggregate_strategy_metrics(self):
        score = OpportunityScore(overall=80, confidence=90)
        decision = OpportunityDecisionDTO(
            trace_id="t1",
            score=score,
            decision=DecisionAction.PUBLISH,
            explanation="",
            policy_version="1",
            engine_version="1"
        )
        
        record1 = ShadowDecisionRecord(
            trace_id="t1",
            target_id="tgt1",
            decision=decision,
            legacy_published=True,
            runtime_ms=100
        )
        
        business_data = [
            {"impressions": 1000, "clicks": 50, "conversions": 5, "revenue": 10.50}
        ]
        
        metrics = AnalyticsAggregator.aggregate_strategy_metrics(
            strategy_name="mrp_error",
            records=[record1, record1], # simulate 2 generated targets
            business_data=business_data
        )
        
        self.assertEqual(metrics.strategy_name, "mrp_error")
        self.assertEqual(metrics.generated_targets, 2)
        self.assertEqual(metrics.accepted_targets, 2)
        self.assertEqual(metrics.published_deals, 2)
        
        self.assertEqual(metrics.avg_overall_score, 80.0)
        self.assertEqual(metrics.avg_confidence, 90.0)
        self.assertEqual(metrics.avg_runtime_ms, 100.0)
        
        self.assertEqual(metrics.ctr, 5.0) # (50/1000)*100
        self.assertEqual(metrics.conversion_rate, 10.0) # (5/50)*100
        self.assertEqual(metrics.affiliate_revenue, 10.50)

if __name__ == '__main__':
    unittest.main()
