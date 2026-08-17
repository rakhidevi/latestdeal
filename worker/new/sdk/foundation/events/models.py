from datetime import datetime, timezone
from typing import Dict, Any, Optional
from worker.new.sdk.foundation.dto.models import BaseDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_uuid

class SystemEvent(BaseDTO):
    """
    Base Event Model for the Event Architecture (PRR Requirement).
    Every event contains mandatory tracking and versioning metadata.
    """
    event_id: str = BaseDTO.__fields__['id'].default_factory() if hasattr(BaseDTO, '__fields__') else "" # workaround since we can just use generate_uuid
    trace_context: TraceContext
    timestamp: datetime
    version: str = "1.0.0"
    correlation_id: Optional[str] = None
    source_module: str

    def __init__(self, **data):
        if "event_id" not in data:
            data["event_id"] = generate_uuid()
        if "timestamp" not in data:
            data["timestamp"] = datetime.now(timezone.utc)
        super().__init__(**data)

class SearchTargetCreated(SystemEvent):
    target_id: str
    provider: str
    strategy: str

class SearchTargetQueued(SystemEvent):
    target_id: str
    queue_name: str

class EvidenceGenerated(SystemEvent):
    target_id: str
    evidence_id: str
    strategy: str
    evidence_type: str

class OpportunityCalculated(SystemEvent):
    target_id: str
    overall_score: int
    confidence: int

class DecisionMade(SystemEvent):
    target_id: str
    decision_action: str
    policy_version: str
    engine_version: str

class PublishedEvent(SystemEvent):
    target_id: str
    decision_id: str
    published_url: str

class ExpiredEvent(SystemEvent):
    target_id: str
    reason: str

class RollbackEvent(SystemEvent):
    target_id: str
    decision_id: str
    reason: str

class ReplayStarted(SystemEvent):
    replay_id: str
    batch_size: int

class ReplayCompleted(SystemEvent):
    replay_id: str
    precision: float
    recall: float
