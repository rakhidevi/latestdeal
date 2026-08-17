import sys
import os

# Ensure worker package is in PYTHONPATH
sys.path.insert(0, os.path.abspath(os.path.dirname(__file__)))

from worker.models import Deal
from worker.ai_enricher import enrich_deal

def test_deterministic_scoring():
    print("Testing deterministic scoring logic...")
    
    # Create a fixed, dummy deal that requires enrichment
    deal = Deal(
        merchant="amazon",
        title="Apple iPhone 15 (128 GB)",
        price=69990.0,
        original_price=79900.0,
        discount_percent=12.4,
        image_url="https://example.com/iphone.jpg",
        canonical_url="https://www.amazon.in/dp/B0CHX1WCGZ",
        affiliate_url="https://www.amazon.in/dp/B0CHX1WCGZ?tag=kridaymart-21",
        coupon="SAVE1000",
        brand="Apple",
        rating=4.6,
        availability="In Stock",
        source="test"
    )

    first_score = None
    
    # We will test the scoring part 100 times.
    # Note: enrich_deal also triggers an LLM call which would be slow for 100 times,
    # so we will isolate just the scoring engine part for the 100 iterations.
    
    from worker.evidence_builder import EvidenceBuilder
    from worker.new.sdk.discovery.decision.engine import OpportunityEngine
    from worker.new.sdk.discovery.decision.aggregator import EvidenceAggregator
    from worker.new.sdk.foundation.dto.models import TraceContext
    
    trace_context = TraceContext(provider="TestScraper", strategy="TestPipeline")
    aggregator = EvidenceAggregator()
    engine = OpportunityEngine(aggregator=aggregator)
    
    for i in range(100):
        # Build evidence
        evidence_graph = EvidenceBuilder.build(deal, trace_context)
        
        # Calculate score
        opportunity_score = engine.compute_score(evidence_graph)
        current_score = opportunity_score.publishability
        
        if first_score is None:
            first_score = current_score
            print(f"First run score: {first_score}")
            
        assert current_score == first_score, f"Nondeterminism detected! Run {i+1} got score {current_score} instead of {first_score}"

    print(f"Success: 100 runs completed identically. Score = {first_score}")
    
if __name__ == "__main__":
    test_deterministic_scoring()
