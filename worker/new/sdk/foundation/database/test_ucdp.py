import unittest
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from datetime import datetime

from worker.new.sdk.foundation.database.models import Base
from worker.new.sdk.foundation.database.repositories import UCDPRepository

class TestUCDP(unittest.TestCase):
    def setUp(self):
        # In-memory SQLite for fast testing
        self.engine = create_engine('sqlite:///:memory:')
        Base.metadata.create_all(self.engine)
        Session = sessionmaker(bind=self.engine)
        self.session = Session()
        self.repo = UCDPRepository(self.session)
        
    def tearDown(self):
        self.session.close()
        Base.metadata.drop_all(self.engine)

    def test_oltp_save_search_target(self):
        target = self.repo.save_search_target(
            trace_id="trace-123",
            provider="amazon",
            profile="premium_electronics",
            strategy="mrp_error",
            url="https://amazon.in/s?k=test",
            parameters={"brand": "samsung"}
        )
        self.assertIsNotNone(target.id)
        self.assertEqual(target.trace_id, "trace-123")
        self.assertEqual(target.state.name, "CREATED")
        
    def test_event_store_immutable_append(self):
        event = self.repo.record_event(
            trace_id="trace-456",
            event_type="SearchTargetCreated",
            entity_id="target-999",
            entity_type="SearchTarget",
            payload={"url": "https://amazon.in"}
        )
        self.assertIsNotNone(event.sequence_id)
        
        events = self.repo.get_events_for_trace("trace-456")
        self.assertEqual(len(events), 1)
        self.assertEqual(events[0].event_type, "SearchTargetCreated")

    def test_commerce_ledger(self):
        now = datetime.utcnow()
        ledger = self.repo.save_commerce_ledger_entry(
            decision_id="decision-777",
            discovered_at=now,
            extracted_at=now,
            validated_at=now
        )
        self.assertIsNotNone(ledger.id)
        self.assertEqual(ledger.decision_id, "decision-777")
        self.assertEqual(ledger.revenue, 0.0)
        
if __name__ == '__main__':
    unittest.main()
