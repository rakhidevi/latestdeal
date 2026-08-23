import os
import sys
sys.path.insert(0, os.path.abspath(os.path.dirname(__file__)))
import json
import asyncio
from dotenv import load_dotenv

# Load env vars
load_dotenv()

from services.discovery.amazon_playwright_provider import AmazonPlaywrightProvider

def run_test(brand, category=None):
    print(f"\n==============================================")
    print(f"Testing Profile: {brand}" + (f" + {category}" if category else ""))
    print(f"==============================================\n")
    
    provider = AmazonPlaywrightProvider()
    criteria = {
        'brand_name': brand,
        'min_discount_percent': 60
    }
    if category:
        criteria['category_name'] = category
        
    try:
        # We only care about discovery numbers for this test
        # We don't need to actually ingest them, just run the provider and taxonomy
        results = provider.search(criteria)
        print(f"\nDISCOVERED: {len(results)}")
        
        if len(results) > 0:
            print("First 3 items:")
            for item in results[:3]:
                print(f" - {item.get('title')[:60]}... | {item.get('price')} | ASIN: {item.get('source_id')}")
    except Exception as e:
        print(f"Error during discovery: {e}")

if __name__ == "__main__":
    # Force headless=True for faster bulk testing, or let env var decide
    
    run_test("LG")
    run_test("Samsung")
    run_test("LG", "Appliances")
    run_test("Samsung", "TV")
    
    print("\nTests completed.")
