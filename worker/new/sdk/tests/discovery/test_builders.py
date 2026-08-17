import unittest
from worker.new.sdk.foundation.dto.models import DiscoveryProfileDTO
from worker.new.sdk.discovery.builders.search_generator import SearchGenerator

class TestSearchGenerator(unittest.TestCase):
    def test_generate_targets(self):
        profile = DiscoveryProfileDTO(
            name="test",
            provider="amazon",
            strategy="mrp",
            brands=["Samsung", "Apple"],
            categories=["Electronics"],
            priority=100,
            budget_pages=5
        )
        generator = SearchGenerator()
        targets = generator.generate(profile, "trc-123")
        
        # 2 brands * 1 category = 2 permutations
        self.assertEqual(len(targets), 2)
        self.assertEqual(targets[0].provider, "amazon")
        self.assertEqual(targets[0].parameters["brand"], "Samsung")
        self.assertEqual(targets[0].priority, 100) # Decays to 99 for the second one
        self.assertEqual(targets[1].priority, 99)

if __name__ == '__main__':
    unittest.main()
