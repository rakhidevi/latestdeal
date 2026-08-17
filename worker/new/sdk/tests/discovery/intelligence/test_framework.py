import unittest
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult
from worker.new.sdk.discovery.intelligence.registry import DiscoveryStrategyRegistry
from worker.new.sdk.discovery.intelligence.explainability import StrategyExplainability
from worker.new.sdk.discovery.intelligence.scoring import StrategyScoringContribution
from worker.new.sdk.foundation.dto.models import SearchTargetDTO

class MockStrategy(BaseDiscoveryStrategy):
    def get_id(self) -> str:
        return "mock_strategy"
        
    def get_name(self) -> str:
        return "Mock Strategy"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return True
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        targets = [
            SearchTargetDTO(
                trace_id=context.trace_id,
                provider=context.provider_name,
                parameters={"brand": "TestBrand"}
            )
        ]
        
        # Use explainability
        explanation = StrategyExplainability.build_explanation(
            self.get_name(),
            ["Test Reason"],
            0.9,
            0.8,
            50
        )
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=0.9,
            reason="Test Reason",
            metrics=self.metrics()
        )

class TestOpportunityDiscoveryFramework(unittest.TestCase):
    def setUp(self):
        self.registry = DiscoveryStrategyRegistry()
        self.strategy = MockStrategy()
        self.strategy.initialize({"enabled": True, "weight": 30})
        self.registry.register(self.strategy)
        
        # We mock context partially for framework tests
        self.context = DiscoveryContext(
            trace_id="test-trace",
            provider_name="test_provider",
            provider=None,
            knowledge_base_path="",
            ontology_engine=None,
            capability_matrix=None,
            planner=None,
            configuration={},
            budget=100
        )

    def test_registry_registration(self):
        self.assertIsNotNone(self.registry.get("mock_strategy"))
        self.assertEqual(len(self.registry.get_all()), 1)
        
    def test_strategy_lifecycle(self):
        # 1. Supports
        self.assertTrue(self.strategy.supports(self.context))
        
        # 2. Generate
        result = self.strategy.generate(self.context)
        self.assertEqual(len(result.generated_targets), 1)
        self.assertEqual(result.confidence, 0.9)
        
        # 3. Validate
        self.assertTrue(self.strategy.validate(result))
        
        # 4. Enrich
        enriched = self.strategy.enrich(result)
        self.assertEqual(enriched, result)
        
        # 5. Explain
        explanation = self.strategy.explain(result)
        self.assertIn("Mock Strategy", explanation)
        
        # 6. Metrics
        metrics = self.strategy.metrics()
        self.assertEqual(metrics["invocations"], 1)
        self.assertEqual(metrics["generated_targets"], 1)
        
    def test_scoring_contribution(self):
        score = StrategyScoringContribution.calculate_contribution("mock_strategy", 0.9, {"weight": 30})
        self.assertEqual(score, 27) # 30 * 0.9

    def test_explainability_formatter(self):
        explanation = StrategyExplainability.build_explanation(
            "Mock", ["Factor A", "Factor B"], 0.95, 0.85, 100
        )
        self.assertIn("Strategy: Mock", explanation)
        self.assertIn("• Factor A", explanation)
        self.assertIn("Confidence: 95%", explanation)

if __name__ == '__main__':
    unittest.main()
