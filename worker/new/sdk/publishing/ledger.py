from pydantic import BaseModel, Field
from datetime import datetime, timezone
from typing import Dict, Any, Optional
from worker.new.sdk.foundation.identity.generator import generate_uuid

class LedgerEntry(BaseModel):
    id: str = Field(default_factory=generate_uuid)
    event_type: str
    deal_id: str
    version: int
    trace_id: str
    worker: str
    provider: str
    profile: str
    strategy: str
    payload_before: Optional[Dict[str, Any]] = None
    payload_after: Dict[str, Any]
    status: str
    duration_ms: int
    timestamp: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class PublishingLedger:
    """
    Immutable Event-Sourced Publishing Ledger.
    Records every state change of a deal (Discovered ➔ Published).
    Currently implemented as an in-memory append-only log for Phase 1 testing.
    Production implementation will use a persistent data store.
    """
    def __init__(self):
        self._ledger: list[LedgerEntry] = []

    def append(self, entry: LedgerEntry) -> None:
        """Appends an immutable record to the ledger."""
        self._ledger.append(entry)

    def get_history(self, deal_id: str) -> list[LedgerEntry]:
        """Returns the full lifecycle history of a specific deal."""
        return [entry for entry in self._ledger if entry.deal_id == deal_id]

    def get_latest_version(self, deal_id: str) -> int:
        """Gets the latest version number for a deal."""
        history = self.get_history(deal_id)
        if not history:
            return 0
        return max(entry.version for entry in history)
