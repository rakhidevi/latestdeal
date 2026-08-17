from typing import Any, Callable, Dict, List
from datetime import datetime, timezone
from pydantic import BaseModel, Field
from worker.new.sdk.foundation.identity.generator import generate_uuid

class Event(BaseModel):
    id: str = Field(default_factory=generate_uuid)
    type: str
    trace_id: str
    timestamp: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    payload: Dict[str, Any] = Field(default_factory=dict)
    source: str

class EventBus:
    """
    A simple in-memory Event Bus for the Universal Commerce Discovery Platform.
    In a distributed production environment, this would interface with Redis/RabbitMQ.
    """
    _subscribers: Dict[str, List[Callable[[Event], None]]] = {}

    @classmethod
    def subscribe(cls, event_type: str, handler: Callable[[Event], None]) -> None:
        if event_type not in cls._subscribers:
            cls._subscribers[event_type] = []
        cls._subscribers[event_type].append(handler)

    @classmethod
    def publish(cls, event: Event) -> None:
        handlers = cls._subscribers.get(event.type, [])
        for handler in handlers:
            try:
                handler(event)
            except Exception as e:
                # Fallback to local logging (to be replaced by telemetry module)
                print(f"Error handling event {event.type} in {handler.__name__}: {e}")

    @classmethod
    def clear(cls) -> None:
        """For testing purposes."""
        cls._subscribers.clear()
