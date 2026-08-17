from sqlalchemy import Column, String, Integer, Float, Boolean, JSON, DateTime, ForeignKey, Enum as SQLEnum
from sqlalchemy.orm import declarative_base, relationship
import enum
from datetime import datetime, timezone

Base = declarative_base()

# ---------------------------------------------------------
# Knowledge Domain
# ---------------------------------------------------------
class KnowledgeBrand(Base):
    __tablename__ = "knowledge_brands"
    id = Column(String, primary_key=True)
    name = Column(String, unique=True, index=True)
    aliases = Column(JSON)
    trust_score = Column(Integer, default=50)
    version = Column(String)

class KnowledgeCategory(Base):
    __tablename__ = "knowledge_categories"
    id = Column(String, primary_key=True)
    name = Column(String, unique=True)
    parent_id = Column(String, ForeignKey("knowledge_categories.id"), nullable=True)
    version = Column(String)

class KnowledgeSeller(Base):
    __tablename__ = "knowledge_sellers"
    id = Column(String, primary_key=True)
    name = Column(String)
    provider_id = Column(String)
    trust_score = Column(Integer)
    is_authorized = Column(Boolean, default=False)
    version = Column(String)

class KnowledgeNode(Base):
    __tablename__ = "knowledge_nodes"
    id = Column(String, primary_key=True)
    node_id = Column(String, index=True)
    provider = Column(String)
    category_id = Column(String, ForeignKey("knowledge_categories.id"))
    version = Column(String)

# ---------------------------------------------------------
# Discovery Domain
# ---------------------------------------------------------
class SearchTarget(Base):
    __tablename__ = "search_targets"
    id = Column(String, primary_key=True)
    trace_id = Column(String, unique=True, index=True)
    provider = Column(String, index=True)
    strategy = Column(String)
    priority = Column(Integer)
    parameters = Column(JSON)
    state = Column(String, index=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    expires_at = Column(DateTime)
    
class SearchHistory(Base):
    __tablename__ = "search_history"
    id = Column(String, primary_key=True)
    target_id = Column(String, ForeignKey("search_targets.id"))
    pages_crawled = Column(Integer)
    products_found = Column(Integer)
    runtime_ms = Column(Integer)
    created_at = Column(DateTime)

# ---------------------------------------------------------
# Evidence Domain
# ---------------------------------------------------------
class EvidenceRecord(Base):
    __tablename__ = "evidence_records"
    id = Column(String, primary_key=True)
    trace_id = Column(String, index=True)
    strategy = Column(String)
    evidence_type = Column(String)
    weight = Column(Integer)
    confidence = Column(Float)
    source = Column(String)
    metadata_json = Column(JSON)
    created_at = Column(DateTime)

# ---------------------------------------------------------
# Decision Domain
# ---------------------------------------------------------
class OpportunityScore(Base):
    __tablename__ = "opportunity_scores"
    id = Column(String, primary_key=True)
    trace_id = Column(String, unique=True)
    overall = Column(Integer)
    confidence = Column(Integer)
    price_score = Column(Integer)
    trust_score = Column(Integer)

class OpportunityDecision(Base):
    __tablename__ = "opportunity_decisions"
    id = Column(String, primary_key=True)
    trace_id = Column(String, unique=True, index=True)
    score_id = Column(String, ForeignKey("opportunity_scores.id"))
    action = Column(String, index=True) # PUBLISH, REJECT, REVIEW
    explanation = Column(String)
    policy_version = Column(String)
    engine_version = Column(String)
    created_at = Column(DateTime)

# ---------------------------------------------------------
# Publishing Domain
# ---------------------------------------------------------
class PublishLedger(Base):
    __tablename__ = "publish_ledger"
    id = Column(String, primary_key=True)
    decision_id = Column(String, ForeignKey("opportunity_decisions.id"))
    published_url = Column(String)
    status = Column(String, index=True) # Queued, Extracted, Published, Rollback
    duration_ms = Column(Integer)
    created_at = Column(DateTime)
    updated_at = Column(DateTime)

# ---------------------------------------------------------
# Analytics & Simulation Domain
# ---------------------------------------------------------
class StrategyMetric(Base):
    __tablename__ = "strategy_metrics"
    id = Column(String, primary_key=True)
    strategy_name = Column(String, index=True)
    date = Column(DateTime)
    generated = Column(Integer)
    published = Column(Integer)
    ctr = Column(Float)
    revenue = Column(Float)

class ShadowDecision(Base):
    __tablename__ = "shadow_decisions"
    id = Column(String, primary_key=True)
    trace_id = Column(String, index=True)
    new_action = Column(String)
    legacy_published = Column(Boolean)
    agreed = Column(Boolean)
    runtime_ms = Column(Integer)
    created_at = Column(DateTime)

class LifecycleEvent(Base):
    __tablename__ = "lifecycle_events"
    id = Column(String, primary_key=True)
    entity_id = Column(String, index=True)
    entity_type = Column(String) # Target, Decision, Publish
    from_state = Column(String)
    to_state = Column(String)
    timestamp = Column(DateTime)
