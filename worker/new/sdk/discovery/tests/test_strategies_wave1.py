import sys
import os

# Add project root to path
project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))))
sys.path.insert(0, project_root)

from worker.new.sdk.discovery.strategies.lightning import LightningStrategy
from worker.new.sdk.discovery.strategies.coupons import CouponStrategy
from worker.new.providers.amazon.provider import AmazonProvider

def test_wave1_strategies():
    provider = AmazonProvider()
    manifest = provider.get_manifest()
    
    print("\n--- Testing Lightning Strategy ---")
    lightning = LightningStrategy()
    targets = lightning.generate_targets(manifest, budget_allocation=2)
    
    for t in targets:
        print(f"Target Generated: {t.expected_content} - URL: {t.url} - Page: {t.parameters.get('page')}")
        
        # Test routing extraction
        extractor = provider.get_extractor()
        dummy_html = "<html><body><div class='a-section coupon-item-container'></div></body></html>"
        products = extractor.extract_grid({"html": dummy_html}, t)
        print(f"Extracted {len(products)} products (Mocked: {products[0].title if products else 'None'})")

    print("\n--- Testing Coupon Strategy ---")
    coupons = CouponStrategy()
    targets = coupons.generate_targets(manifest, budget_allocation=2)
    
    for t in targets:
        print(f"Target Generated: {t.expected_content} - URL: {t.url} - Page: {t.parameters.get('page')}")
        
        # Test routing extraction
        extractor = provider.get_extractor()
        dummy_html = "<html><body><div class='a-section coupon-item-container' data-asin='B0CPN123'></div></body></html>"
        products = extractor.extract_grid({"html": dummy_html}, t)
        print(f"Extracted {len(products)} products (First Title: {products[0].title if products else 'None'})")

if __name__ == "__main__":
    test_wave1_strategies()
