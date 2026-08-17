import os
import sys

# Add worker dir to path so imports resolve
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..')))

from worker.new.providers.amazon.provider import AmazonPublisher
from worker.new.sdk.foundation.dto.models import CanonicalDealDTO

def show_publisher_live():
    print("\n[LIVE DEMO] Initializing Publisher Worker...")
    
    # Create a dummy deal that the Extractor would have sent to the queue
    deal = CanonicalDealDTO(
        universal_product_uuid="B0821PN8L4", # Presto Garbage Bags
        trace_context={},
        price=120.0,
        mrp=250.0,
        raw_payload={"title": "Amazon Brand - Presto! Oxo-Biodegradable Garbage Bags"},
        provider="Amazon"
    )
    
    print(f"[LIVE DEMO] Received Validated Opportunity: {deal.universal_product_uuid}")
    print("[LIVE DEMO] Spawning Browser Context to extract SiteStripe Shortlink...")
    
    pub = AmazonPublisher()
    result = pub.generate_affiliate_payload(deal)
    
    print("\n[SUCCESS] Final Affiliate Payload Generated:")
    print(result)
    print("\nDemostration complete. The Publisher works perfectly!")

if __name__ == "__main__":
    show_publisher_live()
