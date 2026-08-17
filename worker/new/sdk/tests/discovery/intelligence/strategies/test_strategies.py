import unittest
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix
from worker.new.sdk.foundation.dto.models import ProviderManifestDTO
from worker.new.sdk.discovery.intelligence.strategies.mrp_error import MRPErrorStrategy
from worker.new.sdk.discovery.intelligence.strategies.mega_discount import MegaDiscountStrategy
from worker.new.sdk.discovery.intelligence.strategies.premium_brand import PremiumBrandStrategy

class MockPlanner:
    def generate_targets(self, **kwargs):
        # Return dummy targets list
        return [{"id": "t1"}, {"id": "t2"}]

class TestBuiltInStrategies(unittest.TestCase):
    def setUp(self):
        # 1. Setup Capabilities
        self.matrix = CapabilityMatrix()
        self.matrix.register_manifest(ProviderManifestDTO(
            name="amazon",
            version="1.0",
            supports_brand=True,
            supports_discount=True,
            supports_prime=True,
            supports_coupon=True,
            supports_seller=True,
            supports_node=True
        ))
        
        # 2. Setup Context
        self.context = DiscoveryContext(
            trace_id="test-trace",
            provider_name="amazon",
            provider=None,
            knowledge_base_path="",
            ontology_engine=None,
            capability_matrix=self.matrix,
            planner=MockPlanner(),
            configuration={},
            budget=100
        )

    def test_mrp_error_strategy(self):
        strategy = MRPErrorStrategy()
        strategy.initialize({"enabled": True, "min_discount": 90, "base_priority": 85})
        
        self.assertTrue(strategy.supports(self.context))
        
        result = strategy.generate(self.context)
        self.assertEqual(result.strategy_name, "MRP Error")
        self.assertEqual(len(result.generated_targets), 2)
        self.assertIn("90%", result.reason)
        self.assertEqual(result.confidence, 0.95)
        
    def test_mega_discount_strategy(self):
        strategy = MegaDiscountStrategy()
        strategy.initialize({"enabled": True, "minimum_discount": 80})
        
        self.assertTrue(strategy.supports(self.context))
        result = strategy.generate(self.context)
        self.assertEqual(result.strategy_name, "Mega Discount")
        self.assertEqual(len(result.generated_targets), 2)
        
    def test_premium_brand_strategy(self):
        strategy = PremiumBrandStrategy()
        strategy.initialize({"enabled": True, "brands": ["Apple", "Samsung"]})
        
        self.assertTrue(strategy.supports(self.context))
        result = strategy.generate(self.context)
        self.assertEqual(result.strategy_name, "Premium Brand")
        self.assertIn("Apple, Samsung", result.reason)

if __name__ == '__main__':
    unittest.main()
