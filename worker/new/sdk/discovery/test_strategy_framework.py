import sys
import os
import unittest
from datetime import datetime, timezone, timedelta

# Adjust path for testing
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..', '..')))
from worker.new.sdk.foundation.dto.models import PluginManifestV2
from worker.new.sdk.discovery.registry.strategy import StrategyRegistry, BaseDiscoveryStrategy, StrategyMetadata
from worker.new.sdk.discovery.scheduling.budget_manager import CrawlBudgetManager

class MockLightningStrategy(BaseDiscoveryStrategy):
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_lightning",
            name="Lightning Deals",
            priority=100,
            required_capabilities=["supports_lightning"],
            schedule_interval_minutes=5
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> list:
        return []
        
class MockSearchStrategy(BaseDiscoveryStrategy):
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_search",
            name="Keyword Search",
            priority=50,
            required_capabilities=["supports_brand"],
            schedule_interval_minutes=60
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> list:
        return []

class TestStrategyFramework(unittest.TestCase):
    def setUp(self):
        StrategyRegistry._strategies.clear()
        StrategyRegistry.register(MockLightningStrategy)
        StrategyRegistry.register(MockSearchStrategy)
        
    def test_compatible_strategies(self):
        # Amazon supports lightning and search
        amazon = PluginManifestV2(
            id="plugin_amazon",
            name="Amazon India",
            version="1.0",
            author="Test",
            supports_brand=True,
            supports_lightning=True
        )
        
        # Flipkart supports search but NOT lightning
        flipkart = PluginManifestV2(
            id="plugin_flipkart",
            name="Flipkart India",
            version="1.0",
            author="Test",
            supports_brand=True,
            supports_lightning=False
        )
        
        amazon_strats = StrategyRegistry.get_compatible_strategies(amazon)
        self.assertEqual(len(amazon_strats), 2)
        
        flipkart_strats = StrategyRegistry.get_compatible_strategies(flipkart)
        self.assertEqual(len(flipkart_strats), 1)
        self.assertEqual(flipkart_strats[0].get_metadata().id, "strat_search")

    def test_budget_manager(self):
        manager = CrawlBudgetManager(economics_engine=None) # Use heuristic
        
        metadata_list = [
            MockLightningStrategy.get_metadata(), # priority 100
            MockSearchStrategy.get_metadata() # priority 50
        ]
        
        allocations = manager.allocate_budget(metadata_list, total_daily_budget=3000)
        
        self.assertEqual(allocations["strat_lightning"], 2000)
        self.assertEqual(allocations["strat_search"], 1000)

if __name__ == "__main__":
    unittest.main()
