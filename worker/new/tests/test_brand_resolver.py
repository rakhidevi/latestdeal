import unittest
from services.intelligence.brand_resolver import BrandResolver

class TestBrandResolver(unittest.TestCase):
    def setUp(self):
        # Mocking the fetch_brands behavior for offline testing
        self.resolver = BrandResolver("http://localhost", "token")
        self.resolver.brands_cache = [
            {"id": 1, "name": "Puma"},
            {"id": 2, "name": "Nike"}
        ]
        self.resolver._fetch_brands = lambda: None # Override to not hit network

    def test_structured_source_brand(self):
        deal = {
            "source_brand": "PUMA",
            "title": "Unisex-Adult Skyvolt Sneaker"
        }
        result = self.resolver.process(deal)
        self.assertEqual(result["resolved_brand_name"], "Puma")
        self.assertEqual(result["resolved_brand_id"], 1)

    def test_title_fallback(self):
        deal = {
            "source_brand": None,
            "title": "PUMA Running Shoes"
        }
        result = self.resolver.process(deal)
        self.assertEqual(result["resolved_brand_name"], "Puma")
        self.assertEqual(result["resolved_brand_id"], 1)
        
    def test_unknown_structured_brand(self):
        deal = {
            "source_brand": "UnknownBrand",
            "title": "Some Product"
        }
        result = self.resolver.process(deal)
        self.assertEqual(result["resolved_brand_name"], "UnknownBrand")
        self.assertIsNone(result["resolved_brand_id"])

if __name__ == '__main__':
    unittest.main()
