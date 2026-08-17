from sqlalchemy import Column, String, Integer, Float, JSON, DateTime, Boolean
from sqlalchemy.sql import func
from worker.new.sdk.discovery.knowledge.database.db import Base
from worker.new.sdk.foundation.identity.generator import generate_uuid

class PriceHistory(Base):
    __tablename__ = "price_history"

    id = Column(String, primary_key=True, default=generate_uuid)
    product_id = Column(String, index=True, nullable=False) # e.g. ASIN
    provider = Column(String, index=True, nullable=False)
    mrp = Column(Float)
    price = Column(Float, nullable=False)
    discount_percent = Column(Float)
    is_lightning = Column(Boolean, default=False)
    has_coupon = Column(Boolean, default=False)
    timestamp = Column(DateTime(timezone=True), server_default=func.now(), index=True)

class OpportunityHistory(Base):
    __tablename__ = "opportunity_history"

    id = Column(String, primary_key=True) # Matches OpportunityDTO ID
    trace_id = Column(String, index=True, nullable=False)
    product_id = Column(String, index=True, nullable=False)
    provider = Column(String, index=True)
    score = Column(Float, nullable=False)
    factors = Column(JSON) # Detailed breakdown of score
    timestamp = Column(DateTime(timezone=True), server_default=func.now())

class PublishLedgerRecord(Base):
    __tablename__ = "publish_ledger"

    id = Column(String, primary_key=True, default=generate_uuid)
    trace_id = Column(String, index=True, nullable=False)
    deal_id = Column(String, index=True, nullable=False)
    version = Column(Integer, default=1)
    worker = Column(String, index=True)
    provider = Column(String, index=True)
    profile = Column(String, index=True)
    strategy = Column(String, index=True)
    payload_before = Column(JSON)
    payload_after = Column(JSON, nullable=False)
    status = Column(String, index=True, nullable=False)
    duration_ms = Column(Integer)
    timestamp = Column(DateTime(timezone=True), server_default=func.now(), index=True)

class WorkerMetric(Base):
    __tablename__ = "worker_metrics"

    id = Column(String, primary_key=True, default=generate_uuid)
    worker_id = Column(String, index=True, nullable=False)
    metric_name = Column(String, index=True, nullable=False)
    metric_value = Column(Float, nullable=False)
    timestamp = Column(DateTime(timezone=True), server_default=func.now(), index=True)
