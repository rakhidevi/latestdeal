from typing import Any, Dict
from worker.new.sdk.foundation.dto.models import SearchTargetDTO
from worker.new.sdk.foundation.events.bus import EventBus, Event
from worker.new.sdk.foundation.events.types import DiscoveryEvent

class LegacyExtractionAdapter:
    """
    Compatibility Layer Adapter for Extraction.
    Translates SearchTargetDTOs into the payload format expected by the legacy scraper queue (deals.db).
    NEVER makes business decisions. NEVER modifies legacy behavior.
    """

    def enqueue_target(self, target: SearchTargetDTO) -> bool:
        """
        Translates the SearchTargetDTO and pushes it to the legacy queue.
        """
        legacy_payload = self._translate_to_legacy(target)
        
        # In shadow mode or real mode, we would write this legacy_payload to the database or Redis queue
        # For Phase 1, we just simulate enqueueing and emit an event.
        
        EventBus.publish(Event(
            type=DiscoveryEvent.SEARCH_TARGET_QUEUED.value,
            trace_id=target.trace_id,
            source="LegacyExtractionAdapter",
            payload={"legacy_payload": legacy_payload, "target_id": target.id}
        ))
        
        return True

    def _translate_to_legacy(self, target: SearchTargetDTO) -> Dict[str, Any]:
        """
        Maps the strict DTO fields into the unstructured dictionary the legacy scraper expects.
        """
        return {
            "url": target.url,
            "source": target.provider,
            "strategy": target.strategy,
            "priority": target.priority,
            # Pass trace_id silently in a metadata field so we can track it when it exits the legacy system
            "_trace_id": target.trace_id 
        }
