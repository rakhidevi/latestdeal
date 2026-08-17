from worker.new.sdk.foundation.dto.models import (
    SearchTargetDTO, UniversalProductDTO, CanonicalDealDTO, OpportunityDTO, TraceContext, HistoricalMetricsDTO
)
from worker.new.providers.amazon.provider import AmazonProvider
import json

class AmazonCompatibilityLayer:
    """
    Validates that extraction and validation cleanly map to the Universal Object Model (Sprint 6).
    """
    def __init__(self):
        self.provider = AmazonProvider()
        
    def process_target(self, target: SearchTargetDTO, simulated_html_payload: dict) -> OpportunityDTO:
        extractor = self.provider.get_extractor()
        validator = self.provider.get_validator()
        
        # 1. Extraction (Product)
        product: UniversalProductDTO = extractor.extract_product(
            raw_payload=simulated_html_payload,
            trace_context=target.trace_context
        )
        if not product:
            raise ValueError("Failed to extract UniversalProductDTO")
            
        # 2. Extraction (Deal)
        deal: CanonicalDealDTO = extractor.extract_deal(
            raw_payload=simulated_html_payload,
            product_uuid=product.universal_product_uuid,
            trace_context=target.trace_context
        )
        if not deal:
            raise ValueError("Failed to extract CanonicalDealDTO")
            
        # 3. Validation
        is_valid = validator.validate(product, deal)
        state = "Validated" if is_valid else "Rejected"
        rejection_reason = validator.get_rejection_reason() if not is_valid else None
        
        # 4. Opportunity Generation
        opportunity = OpportunityDTO(
            universal_product_uuid=product.universal_product_uuid,
            deal_version_uuid=deal.deal_version_uuid,
            trace_context=target.trace_context,
            opportunity_score=85.0, # Dummy for now
            confidence_score=90.0,
            historical_metrics=HistoricalMetricsDTO(
                lowest_price_30d=deal.price - 100,
                price_velocity_7d=0.0
            ),
            state=state,
            rejection_reason=rejection_reason
        )
        
        return opportunity

if __name__ == "__main__":
    from worker.new.sdk.foundation.identity.generator import generate_uuid
    
    # Simulate a target
    trace = TraceContext(provider="amazon", profile="MRP Error")
    target = SearchTargetDTO(
        trace_context=trace,
        provider="amazon",
        url="https://www.amazon.in/s?k=apple"
    )
    
    # Simulate raw HTML/JSON payload from Amazon scraper
    mock_payload = {
        "asin": "B0CHX1WCGZ",
        "title": "Apple iPhone 15 (128 GB)",
        "price": 69990,
        "mrp": 79900,
        "availability": True,
        "seller": "Appario Retail Private Ltd"
    }
    
    # Process
    layer = AmazonCompatibilityLayer()
    opportunity = layer.process_target(target, mock_payload)
    
    print("=== Compatibility Validation Complete ===")
    print(f"Target URL: {target.url}")
    print(f"Opportunity State: {opportunity.state}")
    if opportunity.rejection_reason:
        print(f"Rejection Reason: {opportunity.rejection_reason}")
    print(f"Score: {opportunity.opportunity_score} (Confidence: {opportunity.confidence_score})")
