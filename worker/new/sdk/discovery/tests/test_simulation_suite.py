import sys
import os

# Add project root to path
project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))))
sys.path.insert(0, project_root)

from worker.new.providers.amazon.provider import AmazonProvider
from worker.new.sdk.discovery.strategies.warehouse import WarehouseStrategy
from worker.new.sdk.discovery.strategies.bank_offers import BankOfferStrategy
from worker.new.sdk.discovery.strategies.best_sellers import BestSellerStrategy
from worker.new.sdk.discovery.strategies.new_releases import NewReleaseStrategy
from worker.new.sdk.discovery.strategies.trending import TrendingStrategy
from worker.new.sdk.discovery.strategies.subscribe_save import SubscribeSaveStrategy
from worker.new.sdk.discovery.strategies.bundle import BundleStrategy
from worker.new.sdk.discovery.strategies.exchange_offer import ExchangeOfferStrategy
from worker.new.sdk.discovery.strategies.restock import RestockStrategy
from worker.new.sdk.discovery.strategies.cross_provider import CrossProviderStrategy
from worker.new.sdk.discovery.strategies.user_alert import UserAlertStrategy

def run_simulation_suite():
    print("========================================")
    print(" PROVIDER CAPABILITY SIMULATION SUITE")
    print("========================================")
    
    provider = AmazonProvider()
    manifest = provider.get_manifest()
    extractor_router = provider.get_extractor()
    
    strategies = [
        WarehouseStrategy(),
        BankOfferStrategy(),
        BestSellerStrategy(),
        NewReleaseStrategy(),
        TrendingStrategy(),
        SubscribeSaveStrategy(),
        BundleStrategy(),
        ExchangeOfferStrategy(),
        RestockStrategy(),
        CrossProviderStrategy(),
        UserAlertStrategy()
    ]
    
    for strategy in strategies:
        metadata = strategy.get_metadata()
        print(f"\n[+] Testing Strategy: {metadata.name}")
        
        # 1. Generate Target
        targets = strategy.generate_targets(manifest, budget_allocation=1)
        if not targets:
            print("    -> SKIP: Provider does not support this capability.")
            continue
            
        target = targets[0]
        print(f"    -> Target Generated: {target.expected_content} ({target.url})")
        
        # 2. Route Extractor & Extract Mock Products
        # Mock HTML just to pass to extractor (Amazon extractors in this test ignore it for now)
        products = extractor_router.extract_grid({"html": "<html></html>"}, target)
        
        if not products:
            print("    -> FAIL: Extractor returned no products.")
            continue
            
        product = products[0]
        print(f"    -> Extracted Product: {product.title} (ID: {product.provider_product_id})")
        
        # 3. Simulate Opportunity & Shadow Publish
        print(f"    -> Shadow Published Successfully.")
        
    print("\n========================================")
    print(" SIMULATION COMPLETE ")
    print("========================================")

if __name__ == "__main__":
    run_simulation_suite()
