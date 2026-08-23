import unittest
import os
import sys
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
from unittest.mock import MagicMock, patch

from services.intelligence.deduplicator import Deduplicator
from services.intelligence.product_normalizer import ProductNormalizer
from services.intelligence.brand_resolver import BrandResolver
from services.intelligence.taxonomy_classifier import TaxonomyClassifier
from services.intelligence.deal_intelligence import DealIntelligence

class TestPhase13Intelligence(unittest.TestCase):

    def test_deduplicator(self):
        dedup = Deduplicator("http://test", "token")
        
        # Test new item
        with patch('requests.get') as mock_get:
            mock_response = MagicMock()
            mock_response.status_code = 200
            mock_response.json.return_value = {"exists": False}
            mock_get.return_value = mock_response
            
            res = dedup.process({"source_id": "NEW123", "url": "http://test.com/new"})
            self.assertEqual(res, "NEW")
            
        # Test existing item
        with patch('requests.get') as mock_get:
            mock_response = MagicMock()
            mock_response.status_code = 200
            mock_response.json.return_value = {"exists": True}
            mock_get.return_value = mock_response
            
            res = dedup.process({"source_id": "OLD456"})
            self.assertEqual(res, "EXISTING_PRODUCT")

    def test_product_normalizer(self):
        norm = ProductNormalizer()
        
        # Test title cleaning
        deal = {"title": "PUMA Men's Tazon 6 Fracture FM Sneaker, Black/White, 10 M US"}
        res = norm.process(deal)
        self.assertNotIn("Black/White", res['normalized_title'])
        self.assertNotIn("10 M US", res['normalized_title'])
        self.assertTrue("Tazon 6 Fracture" in res['normalized_title'])

    def test_brand_resolver(self):
        resolver = BrandResolver("http://test", "token")
        
        # Mock the cache
        resolver.brands_cache = [
            {"id": 1, "name": "Puma"},
            {"id": 2, "name": "LG"},
            {"id": 3, "name": "Apple"}
        ]
        
        # Test direct match
        res = resolver.process({"normalized_title": "PUMA Men's Running Shoes"})
        self.assertEqual(res['resolved_brand_id'], 1)
        
        # Test no incorrect substring match ("Apple" in "Pineapple")
        res = resolver.process({"normalized_title": "Fresh Pineapple juice maker"})
        self.assertIsNone(res['resolved_brand_id'])
        
        # Test LG match
        res = resolver.process({"normalized_title": "LG 655L Frost Free Refrigerator"})
        self.assertEqual(res['resolved_brand_id'], 2)

    def test_taxonomy_classifier(self):
        classifier = TaxonomyClassifier("http://test", "token")
        
        # Mock taxonomy
        classifier.categories_cache = [
            {"id": 1, "name": "Footwear"},
            {"id": 2, "name": "Shoes"},
            {"id": 3, "name": "Running Shoes"},
            {"id": 4, "name": "Sports"}
        ]
        
        # Mock LLM response
        with patch('services.ai.clients.create_llm_client') as mock_llm:
            mock_client = MagicMock()
            mock_client.generate.return_value = '''
            {
                "primary": "Footwear",
                "secondary": ["Shoes", "Running Shoes", "Sports"]
            }
            '''
            classifier.llm = mock_client
            
            res = classifier.process({"normalized_title": "PUMA Men's Running Shoes"})
            
            self.assertEqual(res['primary_category_id'], 1)
            self.assertIn(2, res['secondary_category_ids'])
            self.assertIn(3, res['secondary_category_ids'])
            self.assertIn(4, res['secondary_category_ids'])

    def test_deal_intelligence(self):
        intel = DealIntelligence()
        
        # Test standard discount
        deal = {
            "original_price": 20000,
            "discounted_price": 8000,
            "coupon_discount": 500,
            "displayed_discount_percent": 60
        }
        
        res = intel.process(deal)
        
        self.assertEqual(res['calculated_discount_percent'], 60.0)
        self.assertEqual(res['effective_price'], 7500)
        self.assertEqual(res['effective_discount_percent'], 62.5)
        self.assertEqual(res['amount_saved'], 12500)

if __name__ == '__main__':
    unittest.main()
