from pydantic import BaseModel
from typing import List, Dict, Any, Optional
from datetime import datetime

class StudioWidgetDTO(BaseModel):
    """Base DTO for all widgets sent to the UI."""
    widget_id: str
    widget_type: str  # e.g., 'StatCard', 'RadarChart', 'EvidenceGraph'
    title: str
    data: Dict[str, Any]
    refresh_interval_ms: Optional[int] = None

class StudioPageDTO(BaseModel):
    """Represents a full page composed of multiple widgets."""
    page_id: str
    title: str
    widgets: List[StudioWidgetDTO]
    layout_config: Dict[str, Any]  # e.g., grid coordinates for each widget

class StudioWorkspaceDTO(BaseModel):
    """Role-based workspace (e.g. Developer, Marketing)."""
    workspace_id: str
    role: str
    pages: List[str]  # IDs of available pages

class StudioTraceDTO(BaseModel):
    """End-to-end trace visualization DTO."""
    trace_id: str
    target_uuid: str
    events: List[Dict[str, Any]]
    final_decision: Optional[str]
    latency_ms: int
