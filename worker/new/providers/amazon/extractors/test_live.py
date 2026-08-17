import sys
import os
import requests

project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))))
sys.path.insert(0, project_root)

from worker.new.providers.amazon.extractors.common_grid import parse_amazon_grid

def test_live_extraction():
    url = "https://www.amazon.in/gp/bestsellers"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    print(f"Fetching {url}...")
    try:
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()
        html = response.text
        
        products = parse_amazon_grid(html, grid_type="BESTSELLER")
        print(f"Found {len(products)} products:")
        for p in products[:5]:
            print(f"- {p.title} ({p.provider_product_id})")
            
        if len(products) == 1 and products[0].provider_product_id == "B0MOCKBESTSELLER":
            print("WARNING: Extractor fell back to MOCK product. Parsing failed to find real items.")
    except Exception as e:
        print(f"Failed to fetch or parse: {e}")

if __name__ == "__main__":
    test_live_extraction()
