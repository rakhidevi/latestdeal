import os
import sys
import pytest
from unittest.mock import MagicMock, patch

# Add parent directory to path to import services
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '../../new')))

from services.discovery.discovery_engine import DiscoveryEngine
from services.intelligence.deduplicator import Deduplicator
from services.intelligence.product_normalizer import ProductNormalizer
from services.intelligence.brand_resolver import BrandResolver
from services.intelligence.taxonomy_classifier import TaxonomyClassifier
from services.intelligence.deal_intelligence import DealIntelligence

# Mock HTML Response from Amazon
MOCK_AMAZON_HTML = """
<html>
    <head><title>Puma Men's Running Shoes - Buy Online</title></head>
    <body>
        <div id="productTitle">Puma Men's Running Shoes</div>
        <span class="a-price a-text-price" data-a-strike="true">
            <span class="a-offscreen">₹5,999</span>
        </span>
        <span class="a-price-whole">2,399</span>
        <div id="bylineInfo">Visit the Puma Store</div>
        <div id="wayfinding-breadcrumbs_feature_div">
            <ul>
                <li><a class="a-link-normal a-color-tertiary">Shoes</a></li>
                <li><a class="a-link-normal a-color-tertiary">Sports Shoes</a></li>
                <li><a class="a-link-normal a-color-tertiary">Running Shoes</a></li>
            </ul>
        </div>
    </body>
</html>
"""

def test_full_discovery_pipeline():
    # 1. Setup Mocked Engine
    discovery = DiscoveryEngine("http://mock-api", "token")
    discovery._fetch_html = MagicMock(return_value=MOCK_AMAZON_HTML)
    
    # We will simulate discovery returning one raw deal parsed from that HTML
    # (assuming the actual scraper logic parses it)
    raw_deal = {
        "title": "Puma Men's Running Shoes",
        "original_price": 5999,
        "discounted_price": 2399,
        "url": "https://amazon.in/dp/TESTPUMA123",
        "source_id": "TESTPUMA123",
        "brand_hint": "Puma",
        "breadcrumbs": ["Shoes", "Sports Shoes", "Running Shoes"],
        "merchant": "amazon"
    }

    # 2. Deduplication
    dedup = Deduplicator("http://mock-api", "token")
    # Mocking requests.get to simulate 'exists': False
    mock_response = MagicMock()
    mock_response.status_code = 200
    mock_response.json.return_value = {"exists": False}
    with patch('requests.get', return_value=mock_response):
        status = dedup.process(raw_deal)
    assert status == 'NEW', "Deal should be marked as NEW"

    # 3. Product Normalization
    normalizer = ProductNormalizer()
    deal = normalizer.process(raw_deal)
    assert deal['normalized_title'] == "Puma Men's Running Shoes", "Title should be cleaned"

    # 4. Brand Resolution
    resolver = BrandResolver("http://mock-api", "token")
    # Mock Laravel API returning Puma ID
    resolver.brands_cache = [{"id": 5, "name": "Puma", "slug": "puma"}]
    resolver._fetch_brands = MagicMock()
    
    deal = resolver.process(deal)
    assert deal['resolved_brand_name'] == "Puma", "Brand should be resolved correctly"

    # 5. Taxonomy Classification
    classifier = TaxonomyClassifier("http://mock-api", "token")
    # Mock Laravel API returning categories
    classifier.categories_cache = [
        {"id": 1, "name": "Footwear"},
        {"id": 2, "name": "Shoes"},
        {"id": 3, "name": "Running Shoes"}
    ]
    classifier._fetch_categories = MagicMock()
    
    deal = classifier.process(deal)
    assert deal['primary_category_id'] == 2, "Primary category should be mapped to Shoes based on internal mapping rules"
    assert len(deal['secondary_category_ids']) == 0, "Secondary categories are empty in deterministic mapping"

    # 6. Deal Intelligence (Math/validation)
    intel = DealIntelligence()
    deal = intel.process(deal)
    
    # Validate final intelligence calculations
    assert 'price_intelligence' in deal
    assert deal['price_intelligence']['calculated_discount'] == 60.01, "Discount should be calculated accurately"
    
    # Validate the final ingestion payload structure
    assert "normalized_title" in deal
    assert deal["original_price"] == 5999
    assert deal["discounted_price"] == 2399
    
    print("Integration test passed successfully. Pipeline output:", deal)
