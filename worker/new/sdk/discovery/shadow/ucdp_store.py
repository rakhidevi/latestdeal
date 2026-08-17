import json
from worker.new.sdk.foundation.contracts.shadow import ShadowStore
from worker.new.sdk.foundation.dto.models import ShadowDecisionRecord
from worker.new.sdk.foundation.database.repositories import UCDPRepository
from worker.new.sdk.foundation.database.models import UCDP_EventStore

class UCDPShadowStore(ShadowStore):
    """
    Integrates the Shadow Mode Engine with the Epic 2 Universal Commerce Data Platform (UCDP).
    Instead of writing to flat JSON files or isolated SQLite, this writes the Shadow Decision
    and all associated telemetry to the UCDP Event Store and Ledger.
    """
    def __init__(self, ucdp_repo: UCDPRepository):
        self.repo = ucdp_repo
        
    def save_record(self, record: ShadowDecisionRecord):
        # 1. Persist the Shadow Decision as an immutable event
        self.repo.record_event(
            trace_id=record.trace_context.trace_id,
            event_type="ShadowDecisionRecorded",
            entity_id=record.search_target_uuid,
            entity_type="SearchTarget",
            payload={
                "decision": record.decision.decision.value,
                "score": record.decision.score.model_dump() if hasattr(record.decision.score, "model_dump") else record.decision.score,
                "legacy_published": record.legacy_published,
                "agreed": record.comparison_difference.get("agreed", False)
            }
        )
        
        # 2. Record the shadow runtime to UCDP Telemetry
        # In a full implementation, we'd have a specific save_telemetry method in UCDPRepository.
        # For now, we simulate writing telemetry directly via the session.
        from worker.new.sdk.foundation.database.models import UCDP_Telemetry
        telemetry = UCDP_Telemetry(
            metric_name="shadow_decision_runtime_ms",
            metric_value=float(record.runtime_ms),
            tags={"trace_id": record.trace_context.trace_id, "agreed": record.comparison_difference.get("agreed", False)}
        )
        self.repo.session.add(telemetry)
        self.repo.session.commit()
