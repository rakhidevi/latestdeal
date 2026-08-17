import unittest
from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix
from worker.new.sdk.discovery.engine.constraint_engine import ConstraintEngine
from worker.new.sdk.discovery.planning.yield_estimator import YieldEstimator
from worker.new.sdk.discovery.planning.budget_optimizer import BudgetOptimizer
from worker.new.sdk.discovery.planning.priority_engine import PriorityEngine
from worker.new.sdk.discovery.planning.deduplicator import Deduplicator
from worker.new.sdk.discovery.planning.search_space_builder import SearchSpaceBuilder
from worker.new.sdk.discovery.planning.permutation_engine import PermutationEngine
from worker.new.sdk.discovery.planning.planner import DiscoveryPlanner
from worker.new.sdk.foundation.dto.models import ProviderManifestDTO

class TestDiscoveryPlanningEngine(unittest.TestCase):
    def setUp(self):
        # 1. Capabilities & Constraints
        self.matrix = CapabilityMatrix()
        self.matrix.register_manifest(ProviderManifestDTO(
            name="amazon", version="1.0", supports_brand=True, supports_discount=True, supports_prime=True
        ))
        self.constraint_engine = ConstraintEngine(self.matrix)
        
        # 2. Planning Modules
        self.search_builder = SearchSpaceBuilder(self.matrix, self.constraint_engine)
        self.yield_estimator = YieldEstimator()
        self.budget_optimizer = BudgetOptimizer()
        self.priority_engine = PriorityEngine()
        self.deduplicator = Deduplicator()
        
        self.planner = DiscoveryPlanner(
            self.search_builder,
            self.yield_estimator,
            self.budget_optimizer,
            self.priority_engine,
            self.deduplicator
        )
        
    def test_permutation_engine(self):
        engine = PermutationEngine()
        res = engine.expand({"brand": ["Samsung", "Apple"], "is_prime": True})
        self.assertEqual(len(res), 2)
        self.assertEqual(res[0]["brand"], "Samsung")
        self.assertEqual(res[1]["brand"], "Apple")
        
    def test_yield_estimator(self):
        score_base = self.yield_estimator.estimate_yield({"brand": "Generic"}, "test")
        score_premium = self.yield_estimator.estimate_yield({"brand": "Samsung", "is_prime": True}, "test")
        self.assertGreater(score_premium, score_base)
        
    def test_deduplicator(self):
        perms = [{"brand": "Samsung"}, {"brand": "Samsung"}]
        unique = self.deduplicator.filter_duplicates("amazon", "mrp", perms)
        self.assertEqual(len(unique), 1)

    def test_planner_end_to_end(self):
        targets = self.planner.generate_targets(
            provider="amazon",
            profile_name="mrp_loot",
            strategy="mrp_error",
            base_priority=50,
            parameters={"brand": ["Samsung", "Samsung", "Apple"], "is_prime": True},
            trace_id="trc-test-123"
        )
        
        # Should be 2 targets (Samsung is duplicated in the params list, Apple is novel)
        self.assertEqual(len(targets), 2)
        
        samsung_target = [t for t in targets if t.parameters["brand"] == "Samsung"][0]
        
        self.assertEqual(samsung_target.trace_id, "trc-test-123")
        self.assertGreater(samsung_target.priority, 50) # Boosted by yield

if __name__ == '__main__':
    unittest.main()
