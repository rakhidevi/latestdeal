import sys
import os
import time

# Add project root to path
project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))))
sys.path.insert(0, project_root)

from worker.new.sdk.discovery.strategies.brand_store import BrandStoreStrategy
from worker.new.sdk.discovery.strategies.price_drop import PriceDropStrategy
from worker.new.providers.amazon.provider import AmazonProvider
from worker.new.sdk.discovery.intelligence.price_history import UniversalPriceHistoryService
from worker.new.sdk.discovery.intelligence.price_analysis import PriceAnalysisService
from worker.new.sdk.foundation.dto.models import UniversalProductIdentity

def test_wave2():
    provider = AmazonProvider()
    manifest = provider.get_manifest()
    
    print("\n--- Testing Brand Store Strategy ---")
    brand_strat = BrandStoreStrategy()
    targets = brand_strat.generate_targets(manifest, budget_allocation=1)
    
    products_extracted = []
    for t in targets:
        print(f"Target Generated: {t.expected_content} - URL: {t.url} - Brand: {t.parameters.get('brand')}")
        
        extractor = provider.get_extractor()
        dummy_html = "<html><body><div class='style__item__3sU51' data-asin='B0SAMSUNG123'></div></body></html>"
        products = extractor.extract_grid({"html": dummy_html}, t)
        print(f"Extracted {len(products)} products (First Title: {products[0].title if products else 'None'})")
        products_extracted.extend(products)

    print("\n--- Testing Price History Ingestion ---")
    history_service = UniversalPriceHistoryService()
    
    # Ingest the extracted product
    if products_extracted:
        p = products_extracted[0]
        identity = UniversalProductIdentity(
            provider=p.provider,
            provider_product_id=p.provider_product_id,
            brand=p.brand,
            normalized_title=p.title,
            fingerprint=f"{p.provider}_{p.provider_product_id}"
        )
        
        # Simulate price drops over time
        history_service.record_price(identity, 1000.0, 1500.0)
        time.sleep(0.1)
        # Drop price
        history_service.record_price(identity, 700.0, 1500.0)
        
        obs = history_service.get_history(identity)
        print(f"Recorded {len(obs)} price points for {identity.provider_product_id}")
        print(f"Latest price: {obs[-1].price}")

        print("\n--- Testing Price Analysis Service ---")
        analysis_service = PriceAnalysisService(history_service)
        # Current price dropped further to 650
        result = analysis_service.analyze(identity, 650.0)
        print(f"Lowest 30d: {result.lowest_30_days}")
        print(f"Average 30d: {result.average_30_days}")
        print(f"Is Buy Signal: {result.is_buy_signal}")
        print(f"Drop %: {result.drop_percentage:.2f}%")

    print("\n--- Testing Price Drop Strategy ---")
    drop_strat = PriceDropStrategy()
    drop_targets = drop_strat.generate_targets(manifest, budget_allocation=1)
    for t in drop_targets:
        print(f"Target Generated: {t.expected_content} - URL: {t.url} - Expected Drop: {t.parameters.get('expected_drop')}%")

if __name__ == "__main__":
    test_wave2()
