import os
import sys

from run_discovery_pipeline import DiscoveryEngine, ProfileValidator, Deduplicator, ProductNormalizer, BrandResolver, TaxonomyClassifier, DealIntelligence, pipeline_logger

LARAVEL_API_URL = "http://localhost:8000"
LARAVEL_API_TOKEN = "test-worker-token-123"

def run_single_ingestion():
    print("Testing Single Deal Ingestion to Laravel...")
    
    dedup = Deduplicator(LARAVEL_API_URL, LARAVEL_API_TOKEN)
    normalizer = ProductNormalizer()
    resolver = BrandResolver(LARAVEL_API_URL, LARAVEL_API_TOKEN)
    classifier = TaxonomyClassifier(LARAVEL_API_URL, LARAVEL_API_TOKEN)
    intel = DealIntelligence()
    
    active_profile = {
        "id": 1,
        "name": "Puma Shoes >= 60%",
        "brand": {"name": "Puma"},
        "category": {"name": "Shoes"},
        "min_discount_percent": 60,
        "keywords": "Puma Shoes"
    }
    
    validator = ProfileValidator(active_profile)
    discovery = DiscoveryEngine(LARAVEL_API_URL, LARAVEL_API_TOKEN, [active_profile])
    
    raw_deals = discovery.run_discovery_cycle()
    print(f"Discovered {len(raw_deals)} deals")
    
    for raw in raw_deals:
        asin = raw.get('source_id', raw.get('asin'))
        
        # Deduplication check removed to force test the ingestion
        
        # Normalization
        normalized = normalizer.process(raw)
        
        # Brand Resolution
        normalized = resolver.process(normalized)
        
        # Taxonomy Classification
        normalized = classifier.process(normalized)
        
        # Intelligence (Discount Calculation)
        normalized = intel.process(normalized)
        
        # Validation
        val = validator.validate(normalized)
        import uuid
        import datetime
        trace_id = f"ld_{datetime.datetime.now().strftime('%Y%m%d')}_{uuid.uuid4().hex[:8]}"
        
        if val['validation_status'] == 'PASS':
            print(f"Found valid deal! ASIN: {asin}")
            print(f"Title: {normalized['title']}")
            
            validation_mode = os.getenv("SCRAPER_VALIDATION_MODE", "true").lower() == "true"
            if validation_mode:
                print("SCRAPER_VALIDATION_MODE is true. Deal is valid, but skipping ingestion.")
                return
                
            # INGEST TO LARAVEL
            payload = {
                "asin": asin,
                "trace_id": trace_id,
                "pipeline_run_id": "test-single-run",
                "title": normalized['title'],
                "original_price": normalized.get('original_price'),
                "discounted_price": normalized.get('discounted_price'),
                "calculated_discount": normalized.get('calculated_discount'),
                "url": normalized.get('url'),
                "brand": normalized.get('brand'),
                "observation_id": trace_id
            }
            
            import requests
            headers = {
                "Authorization": f"Bearer {LARAVEL_API_TOKEN}",
                "Accept": "application/json",
                "Content-Type": "application/json"
            }
            try:
                print("Sending to Laravel...")
                resp = requests.post(f"{LARAVEL_API_URL}/api/worker/ingest", json=payload, headers=headers)
                print(f"Status Code: {resp.status_code}")
                if resp.status_code == 200:
                    print(f"Response: {resp.json()}")
                    print("\nSuccess! Deal ingested. Stopping script.")
                    return
                else:
                    print(f"Failed response: {resp.text}")
                    return
            except Exception as e:
                print(f"Exception calling Laravel: {e}")
                return

if __name__ == "__main__":
    run_single_ingestion()
