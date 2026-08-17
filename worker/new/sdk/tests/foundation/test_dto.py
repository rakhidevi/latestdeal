import unittest
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, OpportunityDTO

class TestDTOModels(unittest.TestCase):
    def test_search_target_dto(self):
        target = SearchTargetDTO(
            trace_id="test-123",
            provider="amazon",
            profile="samsung-loot",
            strategy="mrp_error",
            priority=100,
            budget=50,
            url="https://amazon.in/s?k=test"
        )
        self.assertEqual(target.state, "Generated")
        self.assertEqual(target.provider, "amazon")
        self.assertIsNotNone(target.id)

    def test_opportunity_dto(self):
        opp = OpportunityDTO(
            product_id="prod-123",
            trace_id="test-123",
            opportunity_score=95.5,
            confidence_score=90.0,
            discount_percentage=85.0
        )
        self.assertEqual(opp.state, "Detected")
        self.assertEqual(opp.opportunity_score, 95.5)
        self.assertIsNotNone(opp.id)

if __name__ == '__main__':
    unittest.main()
