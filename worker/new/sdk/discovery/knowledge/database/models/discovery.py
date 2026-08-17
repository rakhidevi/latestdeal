from sqlalchemy import Column, String, Integer, Float, JSON, DateTime, Boolean
from sqlalchemy.sql import func
from worker.new.sdk.discovery.knowledge.database.db import Base
from worker.new.sdk.foundation.identity.generator import generate_uuid

class DiscoveryProfileRecord(Base):
    """Database representation of a Discovery Profile for historical tracking."""
    __tablename__ = "discovery_profiles"

    id = Column(String, primary_key=True, default=generate_uuid)
    name = Column(String, unique=True, index=True, nullable=False)
    provider = Column(String, nullable=False)
    strategy = Column(String, nullable=False)
    priority = Column(Integer, default=50)
    config = Column(JSON, nullable=False) # Full profile YAML payload dumped to JSON
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), onupdate=func.now())

class SearchTargetRecord(Base):
    """Persistent storage for Search Targets."""
    __tablename__ = "search_targets"

    id = Column(String, primary_key=True) # Usually matches the DTO ID
    trace_id = Column(String, index=True, nullable=False)
    provider = Column(String, index=True, nullable=False)
    profile_name = Column(String, index=True)
    strategy = Column(String, index=True)
    priority = Column(Integer, index=True)
    url = Column(String, nullable=False)
    parameters = Column(JSON, nullable=False)
    state = Column(String, index=True, default="Generated")
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    expires_at = Column(DateTime(timezone=True), nullable=True)

class SearchRunRecord(Base):
    """Tracks a specific execution of a Discovery Profile."""
    __tablename__ = "search_runs"

    id = Column(String, primary_key=True, default=generate_uuid)
    profile_name = Column(String, index=True, nullable=False)
    trace_id = Column(String, index=True, nullable=False)
    targets_generated = Column(Integer, default=0)
    targets_queued = Column(Integer, default=0)
    targets_removed = Column(Integer, default=0)
    constraint_failures = Column(Integer, default=0)
    duplicate_failures = Column(Integer, default=0)
    budget_reduction = Column(Integer, default=0)
    yield_estimate = Column(Float, default=0.0)
    execution_time_ms = Column(Integer, default=0)
    status = Column(String, default="Running") # Running, Completed, Failed
    started_at = Column(DateTime(timezone=True), server_default=func.now())
    completed_at = Column(DateTime(timezone=True), nullable=True)
