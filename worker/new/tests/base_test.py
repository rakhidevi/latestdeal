import unittest
from abc import ABC, abstractmethod

class ProviderTestSuite(ABC, unittest.TestCase):
    """Abstract base test suite that every provider must pass."""
    
    @abstractmethod
    def get_provider_instance(self):
        """Must return an instance of StoreScraper."""
        pass
        
    @abstractmethod
    def get_test_url(self):
        """Must return a valid test URL for the provider."""
        pass
        
    def setUp(self):
        self.provider = self.get_provider_instance()
        self.provider.initialize()
        self.url = self.get_test_url()

    def test_lifecycle(self):
        """Runs the entire 8-step lifecycle and asserts contracts."""
        # 1. Health check
        health = self.provider.health()
        self.assertEqual(health.get("status"), "OK")
        
        # 2. Authenticate
        self.assertTrue(self.provider.authenticate())
        
        # 3. Search
        try:
            results = self.provider.search(self.url)
            self.assertTrue(len(results) > 0, "Search returned empty results")
            html = results[0]
            
            # 4. Extract
            raw_data = self.provider.extract(html)
            self.assertIn("title", raw_data)
            self.assertIn("discounted_price", raw_data)
            
            # 5. Normalize
            dto = self.provider.normalize(raw_data)
            self.assertIsNotNone(dto)
            self.assertEqual(dto.provider, self.provider.__class__.__name__.lower().replace("scraper", ""))
            
            # 6. Validate
            self.assertTrue(self.provider.validate(dto))
            
        finally:
            # 7 & 8 are implicit cleanup
            self.provider.cleanup()
