from sqlalchemy import Column, String, Integer, Float, Boolean, JSON, DateTime, ForeignKey, Enum as SQLEnum
from sqlalchemy.orm import declarative_base, relationship
import enum
from datetime import datetime
import uuid

Base = declarative_base()

class LifecycleState(enum.Enum):
    CREATED = "CREATED"
    DISPATCHED = "DISPATCHED"
    EXTRACTED = "EXTRACTED"
    DECISIONED = "DECISIONED"
    PUBLISHED = "PUBLISHED"
    EXPIRED = "EXPIRED"

class UCDP_SearchTarget(Base):
    """
    OLTP: Tracks active SearchTargets running through the engine.
    """
    __tablename__ = 'ucdp_search_targets'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    trace_id = Column(String, nullable=False, index=True)
    provider = Column(String, nullable=False)
    profile = Column(String, nullable=False)
    strategy = Column(String, nullable=True, index=True)
    url = Column(String, nullable=False)
    priority = Column(Integer, default=50)
    state = Column(SQLEnum(LifecycleState), default=LifecycleState.CREATED)
    
    parameters = Column(JSON, nullable=False, default={})
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    decisions = relationship("UCDP_OpportunityDecision", back_populates="target")

class UCDP_UniversalProduct(Base):
    """
    OLTP: Canonical representation of a physical product.
    """
    __tablename__ = 'ucdp_universal_products'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    title = Column(String, nullable=False)
    brand = Column(String, nullable=False, index=True)
    category = Column(String, nullable=False)
    image_url = Column(String)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    deals = relationship("UCDP_CanonicalDeal", back_populates="product")

class UCDP_CanonicalDeal(Base):
    """
    OLTP: Versioned offer attached to a Universal Product.
    """
    __tablename__ = 'ucdp_canonical_deals'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    product_id = Column(String, ForeignKey('ucdp_universal_products.id'))
    provider = Column(String, nullable=False)
    provider_reference = Column(String, nullable=False, index=True)
    url = Column(String, nullable=False)
    
    price = Column(Float, nullable=False)
    mrp = Column(Float, nullable=True)
    is_prime = Column(Boolean, default=False)
    seller_name = Column(String)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    product = relationship("UCDP_UniversalProduct", back_populates="deals")
    decisions = relationship("UCDP_OpportunityDecision", back_populates="deal")

class UCDP_OpportunityDecision(Base):
    """
    OLTP: Stores the final decision computed by the Opportunity Engine.
    """
    __tablename__ = 'ucdp_opportunity_decisions'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    target_id = Column(String, ForeignKey('ucdp_search_targets.id'))
    deal_id = Column(String, ForeignKey('ucdp_canonical_deals.id'))
    
    score = Column(Float, nullable=False)
    is_approved = Column(Boolean, nullable=False)
    reason_code = Column(String, nullable=False)
    
    evidence_graph = Column(JSON, nullable=False)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    
    target = relationship("UCDP_SearchTarget", back_populates="decisions")
    deal = relationship("UCDP_CanonicalDeal", back_populates="decisions")
    
class UCDP_CommerceLedger(Base):
    """
    OLTP: The single source of truth for business reporting.
    """
    __tablename__ = 'ucdp_commerce_ledger'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    decision_id = Column(String, ForeignKey('ucdp_opportunity_decisions.id'))
    
    discovered_at = Column(DateTime, nullable=False)
    extracted_at = Column(DateTime, nullable=False)
    validated_at = Column(DateTime, nullable=False)
    published_at = Column(DateTime, nullable=True)
    expired_at = Column(DateTime, nullable=True)
    
    affiliate_url = Column(String)
    revenue = Column(Float, default=0.0)
    clicks = Column(Integer, default=0)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

class UCDP_EventStore(Base):
    """
    Sprint B2: Event Store
    Immutable, traceable, replayable log of every critical system transition.
    """
    __tablename__ = 'ucdp_event_store'
    
    sequence_id = Column(Integer, primary_key=True, autoincrement=True)
    event_id = Column(String, default=lambda: str(uuid.uuid4()), unique=True)
    trace_id = Column(String, nullable=False, index=True)
    
    event_type = Column(String, nullable=False, index=True) # e.g. SearchTargetCreated
    entity_id = Column(String, nullable=False, index=True) # The ID of the affected entity
    entity_type = Column(String, nullable=False) # e.g. SearchTarget, OpportunityDecision
    
    payload = Column(JSON, nullable=False)
    
    recorded_at = Column(DateTime, default=datetime.utcnow, index=True)

class UCDP_HistoricalStore(Base):
    """
    Sprint B3: Historical Platform
    Append-only history for prices, inventory, and scores.
    """
    __tablename__ = 'ucdp_historical_store'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    entity_id = Column(String, nullable=False, index=True)
    entity_type = Column(String, nullable=False) # e.g. Deal, Policy, Target
    
    change_type = Column(String, nullable=False) # e.g. PRICE_CHANGE, STATUS_CHANGE
    old_value = Column(JSON, nullable=True)
    new_value = Column(JSON, nullable=False)
    
    recorded_at = Column(DateTime, default=datetime.utcnow, index=True)

class UCDP_Telemetry(Base):
    """
    Sprint B6 & B7: Telemetry Platform and Time-Series Engine
    Stores chronological metrics for Provider Health and Crawl Latency.
    """
    __tablename__ = 'ucdp_telemetry'
    
    id = Column(String, primary_key=True, default=lambda: str(uuid.uuid4()))
    metric_name = Column(String, nullable=False, index=True)
    metric_value = Column(Float, nullable=False)
    
    provider = Column(String, nullable=True, index=True)
    tags = Column(JSON, default={})
    
    recorded_at = Column(DateTime, default=datetime.utcnow, index=True)
