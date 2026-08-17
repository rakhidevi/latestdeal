import unittest
import json
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from datetime import datetime

from worker.new.sdk.foundation.database.models import Base
from worker.new.sdk.foundation.database.repositories import UCDPRepository
from worker.new.sdk.discovery.economics.engine import DiscoveryEconomicsEngine
from worker.new.sdk.foundation.database.models import UCDP_OpportunityDecision, UCDP_CanonicalDeal, UCDP_UniversalProduct

class TestEconomicsEngine(unittest.TestCase):
    def setUp(self):
        self.engine = create_engine('sqlite:///:memory:')
        Base.metadata.create_all(self.engine)
        Session = sessionmaker(bind=self.engine)
        self.session = Session()
        self.repo = UCDPRepository(self.session)
        self.economics = DiscoveryEconomicsEngine(self.session)
        self._seed_data()
        
    def tearDown(self):
        self.session.close()
        Base.metadata.drop_all(self.engine)
        
    def _seed_data(self):
        # Create Search Targets
        t1 = self.repo.save_search_target("t1", "amazon", "prof1", "mrp_error", "http://amazon.in/1", {})
        t2 = self.repo.save_search_target("t2", "amazon", "prof2", "coupon_stack", "http://amazon.in/2", {})
        t3 = self.repo.save_search_target("t3", "amazon", "prof3", "warehouse", "http://amazon.in/3", {})
        
        # Product and Deal for relations
        prod = UCDP_UniversalProduct(title="Test", brand="Samsung", category="Electronics")
        deal = UCDP_CanonicalDeal(provider="amazon", provider_reference="ASIN1", url="http", price=100)
        self.session.add(prod)
        self.session.add(deal)
        self.session.flush()
        
        # Opportunity Decisions
        d1 = UCDP_OpportunityDecision(target_id=t1.id, deal_id=deal.id, score=95, is_approved=True, reason_code="OK", evidence_graph=[])
        d2 = UCDP_OpportunityDecision(target_id=t2.id, deal_id=deal.id, score=90, is_approved=True, reason_code="OK", evidence_graph=[])
        d3 = UCDP_OpportunityDecision(target_id=t3.id, deal_id=deal.id, score=99, is_approved=True, reason_code="OK", evidence_graph=[])
        self.session.add_all([d1, d2, d3])
        self.session.flush()
        
        # Ledgers (Revenue)
        now = datetime.utcnow()
        # MRP Error makes 320
        self.repo.save_commerce_ledger_entry(d1.id, now, now, now).revenue = 320.0
        # Coupon Stack makes 41
        self.repo.save_commerce_ledger_entry(d2.id, now, now, now).revenue = 41.0
        # Warehouse makes 2100
        self.repo.save_commerce_ledger_entry(d3.id, now, now, now).revenue = 2100.0
        
        self.session.commit()

    def test_revenue_by_strategy(self):
        report = self.economics.get_revenue_by_strategy()
        
        self.assertIn("mrp_error", report)
        self.assertEqual(report["mrp_error"]["revenue"], 320.0)
        
        self.assertIn("coupon_stack", report)
        self.assertEqual(report["coupon_stack"]["revenue"], 41.0)
        
        self.assertIn("warehouse", report)
        self.assertEqual(report["warehouse"]["revenue"], 2100.0)

    def test_overall_efficiency(self):
        report = self.economics.get_overall_efficiency()
        self.assertEqual(report["total_revenue"], 2461.0) # 320 + 41 + 2100
        # 3 targets * 0.1 cost = 0.3
        self.assertAlmostEqual(report["estimated_discovery_cost"], 0.3)
        self.assertAlmostEqual(report["net_profitability"], 2460.7)

if __name__ == '__main__':
    unittest.main()
