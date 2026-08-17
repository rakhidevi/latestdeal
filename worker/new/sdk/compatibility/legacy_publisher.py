from typing import Any, Dict
from worker.new.sdk.foundation.dto.models import PublishRequestDTO, PublishResultDTO
from worker.new.sdk.foundation.events.bus import EventBus, Event
from worker.new.sdk.foundation.events.types import DiscoveryEvent

class LegacyPublisherAdapter:
    """
    Compatibility Layer Adapter for Publishing.
    Listens to the legacy pipeline's output and translates legacy published deals into the new Event-Sourced architecture.
    """
    
    def record_legacy_publish(self, legacy_deal: Dict[str, Any], trace_id: str = "legacy-unknown") -> PublishResultDTO:
        """
        Translates a legacy deal publication into a PublishResultDTO and emits the DEAL_PUBLISHED event.
        """
        result = PublishResultDTO(
            publish_request_id="legacy-req", # Legacy deals won't have a new-gen request ID
            trace_id=trace_id,
            success=True,
            published_url=legacy_deal.get("affiliate_url", "")
        )
        
        EventBus.publish(Event(
            type=DiscoveryEvent.DEAL_PUBLISHED.value,
            trace_id=trace_id,
            source="LegacyPublisherAdapter",
            payload={"legacy_deal": legacy_deal, "result": result.model_dump()}
        ))
        
        return result
