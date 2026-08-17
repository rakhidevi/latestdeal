import unittest
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
import uuid

from worker.new.sdk.foundation.database.models import Base, UCDP_EventStore, UCDP_Telemetry
from worker.new.sdk.foundation.database.repositories import UCDPRepository
from worker.new.sdk.discovery.shadow.ucdp_store import UCDPShadowStore
from worker.new.sdk.discovery.shadow.engine import ShadowModeEngine
from worker.new.sdk.foundation.dto.models import OpportunityDecisionDTO, DecisionAction, SearchTargetDTO, TraceContext

class TestUCDPShadowIntegration(unittest.TestCase):
    def setUp(self):
        self.engine = create_engine('sqlite:///:memory:')
        Base.metadata.create_all(self.engine)
        Session = sessionmaker(bind=self.engine)
        self.session = Session()
        self.repo = UCDPRepository(self.session)
        self.shadow_store = UCDPShadowStore(self.repo)
        self.shadow_engine = ShadowModeEngine(self.shadow_store)

    def tearDown(self):
        self.session.close()
        Base.metadata.drop_all(self.engine)

    def test_shadow_mode_writes_to_ucdp(self):
        trace_id = f"trace-{uuid.uuid4()}"
        
        target = SearchTargetDTO(
            trace_context=TraceContext(provider="amazon", profile="test"),
            provider="amazon",
            profile="test",
            priority=50,
            budget_cost=1,
            url="https://amazon.in/test"
        )
        target.trace_context.trace_id = trace_id
        target.search_target_uuid = f"target-{uuid.uuid4()}"
        
        decision = OpportunityDecisionDTO(
            opportunity_uuid=str(uuid.uuid4()),
            trace_context=TraceContext(provider="amazon", profile="test"),
            decision=DecisionAction.PUBLISH,
            score={"overall": 95, "confidence": 99},
            evidence_graph=[],
            explanation="Test",
            policy_version="1.0",
            engine_version="1.0",
            metadata={}
        )
        
        # Execute shadow mode (Engine decided PUBLISH, legacy decided NOT PUBLISHED)
        record = self.shadow_engine.execute_and_record(
            target=target,
            decision=decision,
            legacy_published=False
        )
        
        self.assertFalse(record.comparison_difference["agreed"])
        
        # Verify it wrote to the UCDP Event Store
        events = self.repo.get_events_for_trace(trace_id)
        self.assertEqual(len(events), 1)
        self.assertEqual(events[0].event_type, "ShadowDecisionRecorded")
        self.assertEqual(events[0].payload["agreed"], False)
        self.assertEqual(events[0].payload["legacy_published"], False)
        
        # Verify it wrote Telemetry
        telemetry = self.session.query(UCDP_Telemetry).filter_by(metric_name="shadow_decision_runtime_ms").first()
        self.assertIsNotNone(telemetry)
        self.assertEqual(telemetry.tags["trace_id"], trace_id)

if __name__ == '__main__':
    unittest.main()
