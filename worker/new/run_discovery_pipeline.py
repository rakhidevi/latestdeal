import os
import time
import json
import sqlite3
from datetime import datetime
import uuid
import requests
from dotenv import load_dotenv

from services.discovery.discovery_engine import DiscoveryEngine
from services.intelligence.deduplicator import Deduplicator
from services.intelligence.product_normalizer import ProductNormalizer
from services.intelligence.brand_resolver import BrandResolver
from services.intelligence.taxonomy_classifier import TaxonomyClassifier
from services.intelligence.deal_intelligence import DealIntelligence
from services.validation.profile_validator import ProfileValidator
from services.logger import PipelineLogger

load_dotenv()
LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "http://localhost:8000")
LARAVEL_API_TOKEN = os.getenv("LARAVEL_API_TOKEN", "local_worker_secret_token_123")
SCRAPER_VALIDATION_MODE = os.getenv("SCRAPER_VALIDATION_MODE", "false").lower() == "true"

pipeline_logger = PipelineLogger()

# Initialize Local SQLite for Quarantine Deduplication
QUARANTINE_DIR = os.path.join(os.path.dirname(__file__), 'quarantine')
os.makedirs(QUARANTINE_DIR, exist_ok=True)
DB_PATH = os.path.join(QUARANTINE_DIR, 'seen_asins.sqlite')

def init_local_db():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute('''CREATE TABLE IF NOT EXISTS seen_asins (asin TEXT PRIMARY KEY, timestamp TEXT)''')
    conn.commit()
    conn.close()

def is_local_duplicate(asin: str) -> bool:
    if not asin:
        return False
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT 1 FROM seen_asins WHERE asin = ?", (asin,))
    exists = c.fetchone() is not None
    conn.close()
    return exists

def mark_local_seen(asin: str):
    if not asin:
        return
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("INSERT OR IGNORE INTO seen_asins (asin, timestamp) VALUES (?, ?)", (asin, datetime.now().isoformat()))
    conn.commit()
    conn.close()

def quarantine_deal(deal: dict, pipeline_run_id: str, trace_id: str, validation_result: dict, profile_name: str):
    timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
    filename = f"{timestamp}_{pipeline_run_id}_{trace_id}.json"
    filepath = os.path.join(QUARANTINE_DIR, filename)
    
    price_intel = deal.get("price_intelligence", {})
    
    audit_record = {
        "pipeline_run_id": pipeline_run_id,
        "trace_id": trace_id,
        "profile_name": profile_name,
        "timestamp": datetime.now().isoformat(),
        "asin": deal.get("source_id"),
        "title": deal.get("normalized_title", deal.get("title")),
        "brand": deal.get("resolved_brand_name"),
        "product_url": deal.get("url"),
        
        # Price components
        "mrp": deal.get("original_price"),
        "selling_price": deal.get("discounted_price"),
        "coupon": price_intel.get("coupon", 0),
        "effective_price": price_intel.get("effective_price", deal.get("discounted_price")),
        "displayed_discount": price_intel.get("displayed_discount", 0),
        "calculated_discount": price_intel.get("calculated_discount"),
        
        "primary_category": deal.get("primary_category_id"),
        "secondary_categories": deal.get("secondary_category_ids", []),
        
        "is_duplicate": False, # If it reached here, it bypassed deduplication
        "validation_status": validation_result.get("validation_status", "FAIL"),
        "validation_checks": validation_result.get("validation_checks", {})
    }
    
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(audit_record, f, indent=4, ensure_ascii=False)
        
    pipeline_logger.info(f"Quarantined deal ({validation_result.get('validation_status')})", trace_id=trace_id, file=filename)

def submit_to_ingestion(deal: dict, trace_id: str, pipeline_run_id: str):
    if SCRAPER_VALIDATION_MODE:
        raise RuntimeError("FATAL: Attempted to ingest deal while SCRAPER_VALIDATION_MODE is true. Failing closed.")
        
    url = f"{LARAVEL_API_URL}/api/worker/ingest"
    headers = {
        "Authorization": f"Bearer {LARAVEL_API_TOKEN}",
        "Accept": "application/json"
    }
    
    payload = {
        "asin": deal.get('source_id'),
        "pipeline_run_id": pipeline_run_id,
        "title": deal.get("normalized_title", deal.get("title")),
        "original_price": deal.get("original_price"),
        "discounted_price": deal.get("discounted_price"),
        "calculated_discount": deal.get("price_intelligence", {}).get("calculated_discount"),
        "url": deal.get("url"),
        "short_url": deal.get("short_url"),
        "observation_id": f"disc_{int(time.time())}_{deal.get('source_id', 'xx')}",
        "trace_id": trace_id,
        "editorial_status": "DRAFT",
        "category_id": deal.get("primary_category_id"),
        "secondary_category_ids": deal.get("secondary_category_ids", []),
        "brand": deal.get("resolved_brand_name"),
        "merchant_id": None,
        "image_url": deal.get("image_url"),
        "price_intelligence": deal.get("price_intelligence", {})
    }
    
    try:
        response = requests.post(url, json=payload, headers=headers, timeout=15)
        response.raise_for_status()
        resp_data = response.json()
        status = resp_data.get('status', 'created')
        deal_id = resp_data.get('deal_id', None)
        pipeline_logger.info(f"Ingestion result: {status}", trace_id=trace_id, title=payload['title'])
        return status, deal_id
    except requests.exceptions.HTTPError as e:
        pipeline_logger.error("Failed to ingest deal", trace_id=trace_id, error=str(e), response=e.response.text)
        return "failed", None
    except Exception as e:
        pipeline_logger.error("Failed to ingest deal", trace_id=trace_id, error=str(e))
        return "failed", None

def check_source_confidence(raw_deal: dict) -> tuple[bool, str]:
    if not raw_deal.get('source_id'):
        return False, "Missing ASIN/Source ID"
    if not raw_deal.get('title'):
        return False, "Missing Title"
    if not raw_deal.get('url'):
        return False, "Missing URL"
    if not raw_deal.get('discounted_price'):
        return False, "Missing Selling Price"
    if not raw_deal.get('original_price'):
        return False, "Missing MRP"
    return True, "PASS"

def main():
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument("--brand", default=None, help="Brand name for ad-hoc search")
    parser.add_argument("--category", default=None, help="Category name for ad-hoc search")
    parser.add_argument("--discount", type=int, default=60, help="Minimum discount percent")
    parser.add_argument("--query", default=None, help="Optional search query override")
    parser.add_argument("--fresh-dedup", action="store_true", help="Use a fresh SQLite db for this run to test without prior deduplication")
    args = parser.parse_args()
    
    global DB_PATH
    if args.fresh_dedup:
        test_runs_dir = os.path.join(QUARANTINE_DIR, 'test_runs')
        os.makedirs(test_runs_dir, exist_ok=True)
        db_name = f"{args.brand.lower()}_{args.category.lower()}_{args.discount}.sqlite"
        DB_PATH = os.path.join(test_runs_dir, db_name)
        if os.path.exists(DB_PATH):
            os.remove(DB_PATH) # Start truly fresh for this run
        pipeline_logger.info("Using fresh deduplication database", db_path=DB_PATH)
        
    init_local_db()
    
    pipeline_run_id = f"run_{datetime.now().strftime('%Y%m%d_%H%M%S')}_{uuid.uuid4().hex[:6]}"
    pipeline_logger.info("Starting Discovery Pipeline", pipeline_run_id=pipeline_run_id, validation_mode=SCRAPER_VALIDATION_MODE)
    
    # Instantiate services
    dedup = Deduplicator(LARAVEL_API_URL, LARAVEL_API_TOKEN)
    normalizer = ProductNormalizer()
    resolver = BrandResolver(LARAVEL_API_URL, LARAVEL_API_TOKEN)
    classifier = TaxonomyClassifier(LARAVEL_API_URL, LARAVEL_API_TOKEN)
    intel = DealIntelligence()
    
    if args.brand or args.category:
        # Mode A: Ad-hoc CLI Hunt
        active_profile = {
            "id": 1,
            "name": f"{args.brand or ''} {args.category or ''} >= {args.discount}%".strip(),
            "brand": {"name": args.brand} if args.brand else None,
            "category": {"name": args.category} if args.category else None,
            "min_discount_percent": args.discount,
            "keywords": args.query # Used by discovery engine
        }
        discovery = DiscoveryEngine(LARAVEL_API_URL, LARAVEL_API_TOKEN, [active_profile])
        profiles_to_run = [active_profile]
        pipeline_logger.info("Running in Mode A: Ad-hoc CLI Hunt")
    else:
        # Mode B: Production Worker (Fetch from Laravel)
        discovery = DiscoveryEngine(LARAVEL_API_URL, LARAVEL_API_TOKEN, None)
        profiles_to_run = discovery.fetch_active_profiles()
        pipeline_logger.info("Running in Mode B: Production Worker", profile_count=len(profiles_to_run))
        
    all_raw_deals = discovery.run_discovery_cycle()
    pipeline_logger.info("Discovery cycle completed", pipeline_run_id=pipeline_run_id, total_found=len(all_raw_deals))
    
    metrics = {
        "discovered": len(all_raw_deals),
        "actual_product_match": 0,
        "accessories": 0,
        "wrong_product": 0,
        "accepted": 0,
        "rejected_accessories": 0,
        "rejected_wrong_product": 0,
        "rejected_discount": 0,
        "ingested_created": 0,
        "ingested_updated": 0,
        "ingested_existing": 0,
        "ingested_failed": 0,
        "duplicates": 0,
        "search_page_failures": getattr(discovery, 'search_page_failures', 0),
        "source_extraction_failures": 0,
        "ingested_deal_ids": []
    }

    for raw_deal in all_raw_deals:
        active_profile = raw_deal.pop('_profile', None)
        if not active_profile:
            continue
            
        validator = ProfileValidator(active_profile)
        
        trace_id = f"ld_{datetime.now().strftime('%Y%m%d')}_{uuid.uuid4().hex[:8]}"
        asin = raw_deal.get('source_id')
        
        # 1. Deduplication (Always deduplicate against local in Validation mode, or API otherwise)
        if SCRAPER_VALIDATION_MODE:
            if is_local_duplicate(asin):
                metrics["duplicates"] += 1
                continue
        else:
            status = dedup.process(raw_deal)
            if status != 'NEW':
                metrics["duplicates"] += 1
                continue
                
        # 2. Source Extraction Confidence Check
        conf_pass, conf_reason = check_source_confidence(raw_deal)
        if not conf_pass:
            metrics["source_extraction_failures"] += 1
            if SCRAPER_VALIDATION_MODE:
                quarantine_deal(raw_deal, pipeline_run_id, trace_id, {"validation_status": "FAIL", "validation_checks": {"source": {"passed": False, "reason": conf_reason}}}, active_profile['name'])
            continue
            
        # 3. Product Normalization
        deal = normalizer.process(raw_deal)
        
        # 4. Brand Resolution
        deal = resolver.process(deal)
        
        # 5. Taxonomy Classification
        deal = classifier.process(deal)
        
        # Populate initial stats
        if deal.get('is_accessory'):
            metrics['accessories'] += 1
        elif deal.get('product_type') and active_profile.get('category') and active_profile['category'].get('name') and str(active_profile['category']['name']).lower() != 'all' and str(active_profile['category']['name']).lower() not in [c.lower() for c in deal.get('category_names', [])] and str(active_profile['category']['name']).lower() != str(deal.get('product_type')).lower():
            metrics['wrong_product'] += 1
        else:
            metrics['actual_product_match'] += 1

        # 6. Deal Intelligence
        deal = intel.process(deal)
        
        # 7. Validation / Ingestion (ALWAYS Validate!)
        val_result = validator.validate(deal)
        
        if SCRAPER_VALIDATION_MODE:
            mark_local_seen(asin)
            quarantine_deal(deal, pipeline_run_id, trace_id, val_result, active_profile['name'])
            
        if val_result.get('validation_status') == 'PASS':
            metrics['accepted'] += 1
            if not SCRAPER_VALIDATION_MODE:
                # Automate SiteStripe extraction
                pipeline_logger.info("Fetching SiteStripe shortlink automatically...", trace_id=trace_id)
                try:
                    import sys
                    if ".." not in sys.path:
                        sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))
                    from sitestripe_scraper import get_sitestripe_link_and_data
                    
                    stripe_data = get_sitestripe_link_and_data(deal.get('url'))
                    if stripe_data and stripe_data.get('sitestripe_url'):
                        deal['short_url'] = stripe_data.get('sitestripe_url')
                        pipeline_logger.info(f"Generated Short URL: {deal['short_url']}", trace_id=trace_id)
                    else:
                        pipeline_logger.warning("Failed to generate short URL. Ingesting without it.", trace_id=trace_id)
                except Exception as e:
                    pipeline_logger.error(f"SiteStripe automation error: {e}", trace_id=trace_id)
                
                ingest_status, deal_id = submit_to_ingestion(deal, trace_id, pipeline_run_id)
                metrics[f'ingested_{ingest_status}'] += 1
                if deal_id:
                    metrics['ingested_deal_ids'].append(deal_id)
        else:
            # Determine reason for rejection
            checks = val_result.get('validation_checks', {})
            if not checks.get('product_intent', {}).get('passed', True) and checks.get('product_intent', {}).get('actual') == 'Accessory':
                metrics['rejected_accessories'] += 1
            elif not checks.get('category', {}).get('passed', True):
                metrics['rejected_wrong_product'] += 1
            elif not checks.get('discount', {}).get('passed', True):
                metrics['rejected_discount'] += 1
        
    pipeline_logger.info(
        "Discovery Pipeline completed",
        pipeline_run_id=pipeline_run_id,
        metrics=metrics
    )
    
    print("\n" + "="*50)
    print(f" PIPELINE SUMMARY (Run: {pipeline_run_id})")
    print("="*50)
    profile_name = profiles_to_run[0]['name'] if len(profiles_to_run) == 1 else f"Multiple ({len(profiles_to_run)} profiles)"
    print(f" Profile:                     {profile_name}")
    print(f" Discovered:                  {metrics['discovered']}")
    print(f" Actual Product Match:        {metrics['actual_product_match']}")
    print(f" Accessories:                 {metrics['accessories']}")
    print(f" Other Products:              {metrics['wrong_product']}")
    print(f" Duplicates (Filtered):       {metrics['duplicates']}")
    print(f" Search page failures:        {metrics['search_page_failures']}")
    print(f" Extraction failures:         {metrics['source_extraction_failures']}")
    print("-" * 50)
    print(f" Accepted:                    {metrics['accepted']}")
    print(f" Rejected (Accessories):      {metrics['rejected_accessories']}")
    print(f" Rejected (Wrong Product):    {metrics['rejected_wrong_product']}")
    print(f" Rejected (Discount):         {metrics['rejected_discount']}")
    if not SCRAPER_VALIDATION_MODE:
        print("-" * 50)
        print(f" Ingested (New):              {metrics['ingested_created']}")
        print(f" Ingested (Updated):          {metrics['ingested_updated']}")
        print(f" Ingested (Existing):         {metrics['ingested_existing']}")
        print(f" Ingested (Failed):           {metrics['ingested_failed']}")
        if metrics.get('ingested_deal_ids'):
            print(f" INGESTED_DEAL_IDS:           {','.join(map(str, metrics['ingested_deal_ids']))}")
    print("="*50 + "\n")

if __name__ == "__main__":
    main()
