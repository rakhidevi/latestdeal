from typing import List, Optional
from sqlalchemy.orm import Session
import uuid

from worker.new.sdk.foundation.database.models import (
    UCDP_SearchTarget, UCDP_EventStore, UCDP_HistoricalStore, 
    UCDP_CommerceLedger, UCDP_UniversalProduct, UCDP_OpportunityDecision
)

class UCDPRepository:
    """
    Sprint B10: Integration Layer for UCDP.
    Provides strict abstractions over the UCDP database schema, ensuring no business
    logic interacts directly with the ORM.
    """
    def __init__(self, session: Session):
        self.session = session
        
    def save_search_target(self, trace_id: str, provider: str, profile: str, strategy: str, url: str, parameters: dict) -> UCDP_SearchTarget:
        target = UCDP_SearchTarget(
            trace_id=trace_id,
            provider=provider,
            profile=profile,
            strategy=strategy,
            url=url,
            parameters=parameters
        )
        self.session.add(target)
        self.session.commit()
        return target
        
    def record_event(self, trace_id: str, event_type: str, entity_id: str, entity_type: str, payload: dict) -> UCDP_EventStore:
        """
        Sprint B2: Event Store append.
        """
        event = UCDP_EventStore(
            trace_id=trace_id,
            event_type=event_type,
            entity_id=entity_id,
            entity_type=entity_type,
            payload=payload
        )
        self.session.add(event)
        self.session.commit()
        return event
        
    def record_historical_change(self, entity_id: str, entity_type: str, change_type: str, old_value: dict, new_value: dict):
        """
        Sprint B3: Historical Platform append.
        """
        history = UCDP_HistoricalStore(
            entity_id=entity_id,
            entity_type=entity_type,
            change_type=change_type,
            old_value=old_value,
            new_value=new_value
        )
        self.session.add(history)
        self.session.commit()
        return history
        
    def get_events_for_trace(self, trace_id: str) -> List[UCDP_EventStore]:
        """
        Used for Replay Engine to reconstruct the exact decision path.
        """
        return self.session.query(UCDP_EventStore).filter_by(trace_id=trace_id).order_by(UCDP_EventStore.sequence_id).all()
        
    def save_commerce_ledger_entry(self, decision_id: str, discovered_at, extracted_at, validated_at) -> UCDP_CommerceLedger:
        """
        Sprint B4: Commerce Ledger
        """
        ledger = UCDP_CommerceLedger(
            decision_id=decision_id,
            discovered_at=discovered_at,
            extracted_at=extracted_at,
            validated_at=validated_at
        )
        self.session.add(ledger)
        self.session.commit()
        return ledger
