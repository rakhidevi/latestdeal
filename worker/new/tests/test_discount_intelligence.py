import unittest
from services.intelligence.deal_intelligence import DealIntelligence

class TestDiscountIntelligence(unittest.TestCase):
    def setUp(self):
        self.intel = DealIntelligence()

    def test_valid_discount(self):
        # 5499 / 2099 -> 61.83%
        deal = {"original_price": "5499", "discounted_price": "2099"}
        result = self.intel.process(deal)
        self.assertEqual(result["calculated_discount_percent"], 61.83)

    def test_sixty_percent(self):
        # 5000 / 2000 -> 60%
        deal = {"original_price": 5000, "discounted_price": 2000}
        result = self.intel.process(deal)
        self.assertEqual(result["calculated_discount_percent"], 60.0)
        
        # 1000 / 400 -> 60%
        deal2 = {"original_price": 1000, "discounted_price": 400}
        result2 = self.intel.process(deal2)
        self.assertEqual(result2["calculated_discount_percent"], 60.0)

    def test_zero_discount(self):
        # 1000 / 1000 -> 0%
        deal = {"original_price": 1000, "discounted_price": 1000}
        result = self.intel.process(deal)
        self.assertEqual(result["calculated_discount_percent"], 0.0)

    def test_invalid_discount(self):
        # 1000 / 1200 -> invalid (None)
        deal = {"original_price": 1000, "discounted_price": 1200}
        result = self.intel.process(deal)
        self.assertIsNone(result["calculated_discount_percent"])

    def test_unknown_discount(self):
        # null / 500 -> unknown (None)
        deal = {"original_price": None, "discounted_price": 500}
        result = self.intel.process(deal)
        self.assertIsNone(result["calculated_discount_percent"])

if __name__ == '__main__':
    unittest.main()
