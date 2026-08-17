import unittest
from datetime import datetime
from worker.new.sdk.operations.rollout_engine import RolloutEngine, RolloutConfig, CanaryDecisionEngine
from worker.new.sdk.operations.rollback_engine import AutomaticRollbackEngine, RollbackThresholds, LiveMetrics
from worker.new.sdk.operations.comparator import CanaryComparator, DiscoveryResultRecord
from worker.new.sdk.operations.certification_report import CanaryCertificationReport

class TestCanaryOperations(unittest.TestCase):
    def test_canary_decision_engine(self):
        config = RolloutConfig({
            "global_percentage": 0, # Rely entirely on segment
            "allowed_providers": ["amazon"],
            "allowed_strategies": ["mrp_error"]
        })
        engine = CanaryDecisionEngine(config)
        
        # Should allow amazon + mrp_error
        self.assertTrue(engine.is_eligible("amazon", "mrp_error", "electronics"))
        
        # Should reject flipkart
        self.assertFalse(engine.is_eligible("flipkart", "mrp_error", "electronics"))
        
        # Should reject coupon_stack
        self.assertFalse(engine.is_eligible("amazon", "coupon_stack", "electronics"))

    def test_automatic_rollback(self):
        thresholds = RollbackThresholds({
            "max_captcha_rate": 15.0,
            "min_extraction_success": 90.0
        })
        engine = AutomaticRollbackEngine(thresholds)
        
        # Safe metrics
        safe_metrics = LiveMetrics(captcha_rate=5.0, extraction_success=95.0, revenue_drop=0.0, queue_latency=10)
        self.assertIsNone(engine.evaluate(safe_metrics, 5.0))
        
        # Unsafe metrics (Captcha spike)
        unsafe_metrics = LiveMetrics(captcha_rate=16.0, extraction_success=95.0, revenue_drop=0.0, queue_latency=10)
        rollback = engine.evaluate(unsafe_metrics, 5.0)
        self.assertIsNotNone(rollback)
        self.assertIn("CAPTCHA", rollback.reason)
        
    def test_comparator(self):
        comp = CanaryComparator()
        legacy = DiscoveryResultRecord("LEGACY", 1, 10.0, 1000, 5, 2000, 50, "REJECT", False)
        # New engine found a much better discount
        new = DiscoveryResultRecord("NEW", 1, 50.0, 1000, 0, 1500, 95, "PUBLISH", True)
        
        result = comp.compare(legacy, new)
        self.assertTrue(result["is_new_engine_superior"])
        self.assertEqual(result["metrics_diff"]["discount_diff"], 40.0)
        
    def test_certification_report(self):
        report = CanaryCertificationReport(
            rollout_config={"global_percentage": 5},
            duration_hours=24.0,
            total_targets=5000,
            success_rate=98.5,
            rollback_events=[],
            provider_health={"amazon": 100.0},
            economic_metrics={"revenue": 45000},
            comparator_results={"new_engine_win_rate": 85.0, "average_discount_improvement": 12.5}
        )
        md = report.generate_markdown()
        self.assertIn("✅ PASS", md)
        self.assertIn("85.0%", md)
        
        # Test warning
        report.success_rate = 92.0
        md_warn = report.generate_markdown()
        self.assertIn("⚠️ WARNING", md_warn)

if __name__ == '__main__':
    unittest.main()
