import unittest
from bs4 import BeautifulSoup
from worker.new.tests.base_test import ProviderTestSuite
from worker.new.providers.amazon.scraper import AmazonScraper
from worker.new.providers.amazon.parser import AmazonParser

class TestAmazonProvider(ProviderTestSuite):
    
    def get_provider_instance(self):
        return AmazonScraper()
        
    def get_test_url(self):
        # Known stable iPhone 15 listing
        return "https://www.amazon.in/dp/B0CHX1W1XY"

    def test_fallback_selectors(self):
        """Verifies that the parser successfully cascades to fallback selectors when the primary DOM changes."""
        # Mock HTML representing a mutated DOM where the primary #productTitle is gone
        mock_html = '''
        <html>
            <body>
                <div id="titleSection">
                    <span id="productTitle">Primary Title</span>
                </div>
                <span class="a-size-large product-title-word-break">Fallback Title</span>
                <span class="a-price-whole">999.00</span>
                <img id="landingImage" src="img.png" />
            </body>
        </html>
        '''
        parser = AmazonParser()
        
        # Test 1: Primary extraction
        soup = BeautifulSoup(mock_html, "html.parser")
        raw = parser.extract_raw(soup)
        self.assertEqual(raw["title"], "Primary Title")
        
        # Test 2: Mutate DOM (remove primary title selector)
        mutated_html = mock_html.replace('<span id="productTitle">Primary Title</span>', '')
        soup_mutated = BeautifulSoup(mutated_html, "html.parser")
        raw_mutated = parser.extract_raw(soup_mutated)
        
        # Should correctly use the secondary/fallback CSS class
        self.assertEqual(raw_mutated["title"], "Fallback Title")
        
        # Ensure DTO stays fully valid
        dto = parser.to_dto(raw_mutated, "http://fake.com")
        self.assertEqual(dto.product.title, "Fallback Title")

if __name__ == "__main__":
    unittest.main()
