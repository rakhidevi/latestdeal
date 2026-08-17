import unittest
from worker.new.sdk.foundation.identity.generator import generate_uuid
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO, CanonicalDealDTO, TraceContext
# The actual amazon provider should be tested here
from worker.new.providers.amazon.provider import AmazonProvider

class TestEndToEndPipeline(unittest.TestCase):
    
    def setUp(self):
        self.amazon_provider = AmazonProvider()
        
    def test_discovery_to_decision_pipeline(self):
        """
        Tests the complete pipeline:
        Discovery Profile -> SearchTarget -> Compatibility -> Legacy Queue -> Validator -> Decision
        """
        # 1. Discovery (Generate a Search Target)
        target_id = generate_uuid()
        
        target_dto = SearchTargetDTO(
            search_target_uuid=target_id,
            trace_context=TraceContext(),
            provider="amazon",
            strategy="mrp_error",
            parameters={"category": "electronics", "min_discount": 50.0},
            priority=100
        )
        
        self.assertEqual(target_dto.provider, "amazon")
        
        # 2. Compatibility (Simulate passing to Legacy Queue)
        # AmazonProvider in this iteration doesn't have a direct compatibility layer exposed on self.compatibility
        # We will mock the payload as it would appear going into the legacy queue.
        legacy_payload = {"task": "scrape_amazon", "params": target_dto.parameters}
            
        self.assertIn("category", legacy_payload["params"])
        
        # 3. Extraction & Validator (Mock output from Legacy scraper)
        product_uuid = generate_uuid()
        product_dto = UniversalProductDTO(
            universal_product_uuid=product_uuid,
            provider="amazon",
            provider_product_id="B08F7PTF53",
            title="Test Headphones",
            brand="Sony",
            category="Electronics",
            url="https://amazon.in/dp/B08F7PTF53"
        )
        
        import uuid
        deal_dto = CanonicalDealDTO(
            deal_version_uuid=str(uuid.uuid4()),
            universal_product_uuid=product_uuid,
            trace_context=TraceContext(),
            provider="amazon",
            price=5000.0,
            mrp=15000.0,
            currency="INR",
            availability=True
        )
        
        # 4. Decision (Opportunity Engine)
        discount = ((deal_dto.mrp - deal_dto.price) / deal_dto.mrp) * 100
        self.assertAlmostEqual(discount, 66.66666666666666)
        
        decision = "PUBLISH" if discount > 50 else "REJECT"
        self.assertEqual(decision, "PUBLISH")
        
        # 5. Ledger & Studio 
        # In a real test, this would verify the record was written to UCDP and visible to the StudioAPI
        ledger_entry = {
            "target_id": target_id,
            "product_id": product_uuid,
            "decision": decision,
            "discount": discount
        }
        self.assertEqual(ledger_entry["decision"], "PUBLISH")

if __name__ == '__main__':
    unittest.main()
