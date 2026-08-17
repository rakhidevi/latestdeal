import sys
import os
import json
import asyncio

sys.path.append(os.path.abspath(os.path.dirname(__file__)))
from database import init_db, enqueue_discovery_job, get_next_pending, is_duplicate_discovery
from new.sdk.foundation.identity.generator import generate_uuid

def run_tests():
    init_db()
    
    # 1. Test Duplicate Detection
    job_uuid = generate_uuid()
    trace_id = generate_uuid()
    
    job = {
        "job_uuid": job_uuid,
        "trace_id": trace_id,
        "provider": "amazon",
        "provider_product_id": "TEST1234",
        "url": "https://www.amazon.in/dp/TEST1234",
        "deal_type": "deal",
        "strategy": "search",
        "discovery_profile": "Laptops",
        "opportunity_score": 88.5
    }
    
    success1 = enqueue_discovery_job(job)
    print(f"First enqueue (expected True): {success1}")
    
    success2 = enqueue_discovery_job(job)
    print(f"Second enqueue (expected False): {success2}")
    
    # 2. Test Priority Queue
    mega_loot_job = job.copy()
    mega_loot_job["job_uuid"] = generate_uuid()
    mega_loot_job["provider_product_id"] = "MEGA1234"
    mega_loot_job["url"] = "https://www.amazon.in/dp/MEGA1234"
    mega_loot_job["deal_type"] = "mega_loot"
    
    enqueue_discovery_job(mega_loot_job)
    
    pending1 = get_next_pending()
    print(f"First popped job type (expected mega_loot): {pending1['type'] if pending1 else 'None'}")
    
    pending2 = get_next_pending()
    print(f"Second popped job type (expected deal): {pending2['type'] if pending2 else 'None'}")

if __name__ == "__main__":
    run_tests()
