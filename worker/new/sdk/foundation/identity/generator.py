import uuid
import time
from typing import Optional

def generate_uuid() -> str:
    """Generates a standard UUID v4."""
    return str(uuid.uuid4())

def generate_trace_id(prefix: Optional[str] = None) -> str:
    """
    Generates a unique Trace ID for end-to-end tracking.
    Format: [prefix]-[timestamp]-[uuid_segment]
    Example: disc-1698765432-8f92a
    """
    timestamp = int(time.time())
    short_uuid = generate_uuid().split("-")[0]
    
    if prefix:
        return f"{prefix}-{timestamp}-{short_uuid}"
    return f"trc-{timestamp}-{short_uuid}"
