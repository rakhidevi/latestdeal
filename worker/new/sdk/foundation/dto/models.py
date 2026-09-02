from typing import Any, Dict, List, Optional
from enum import Enum
from pydantic import BaseModel, Field
from datetime import datetime, timezone
from worker.new.sdk.foundation.identity.generator import generate_uuid

class TraceContext(BaseModel):
    """Immutable OTel-style context passed through every subsystem."""
    trace_id: str = Field(default_factory=generate_uuid)
    correlation_id: Optional[str] = None
    parent_id: Optional[str] = None
    run_id: Optional[str] = None
    worker_id: Optional[str] = None
    provider: Optional[str] = None
    strategy: Optional[str] = None
    profile: Optional[str] = None
    discovery_version: Optional[str] = None
    knowledge_version: Optional[str] = None
    policy_version: Optional[str] = None

class BaseDTO(BaseModel):
    """Base Data Transfer Object for all platform models."""
    class Config:
        arbitrary_types_allowed = True

class UniversalProductIdentity(BaseDTO):
    """A provider-agnostic identity for historical data unification."""
    provider: str
    provider_product_id: str
    brand: Optional[str] = None
    normalized_title: str
    category: Optional[str] = None
    fingerprint: str

class ProviderCapabilityDTO(BaseDTO):
    name: str
    version: str
    supported_filters: List[str] = Field(default_factory=list)
    rate_limit_req_per_sec: float = 1.0
    features: List[str] = Field(default_factory=list)

class DiscoveryProfileDTO(BaseDTO):
    id: str = Field(default_factory=generate_uuid)
    name: str
    provider: str
    strategy: str
    categories: List[str] = Field(default_factory=list)
    brands: List[str] = Field(default_factory=list)
    parameters: Dict[str, Any] = Field(default_factory=dict)
    priority: int = 1
    budget_pages: int = 10
    enabled: bool = True

class DiscoveryEntrypoint(BaseDTO):
    intent: str  # e.g., "LIGHTNING", "COUPONS", "WAREHOUSE"
    url: str
    priority: int = 50
    expected_latency: int = 2000
    supports_pagination: bool = True
    max_pages: int = 1

class PluginManifestV2(BaseDTO):
    name: str
    version: str
    description: Optional[str] = None
    author: Optional[str] = None
    
    # Provider Constraints
    supported_markets: List[str] = Field(default_factory=lambda: ["IN"])
    supported_languages: List[str] = Field(default_factory=lambda: ["en"])
    supported_currency: List[str] = Field(default_factory=lambda: ["INR"])
    supported_authentication: List[str] = Field(default_factory=list)
    known_limitations: List[str] = Field(default_factory=list)
    
    # Granular Capability Matrix v2
    supports_price_history: bool = False
    supports_reviews: bool = False
    supports_coupons: bool = False
    supports_images: bool = False
    supports_video: bool = False
    supports_inventory: bool = False
    supports_prime: bool = False
    supports_bank_offer: bool = False
    supports_lightning: bool = False
    supports_flash_sale: bool = False
    supports_bundles: bool = False
    supports_variants: bool = False
    supports_discount: bool = False
    supports_brand: bool = False
    supports_seller: bool = False
    supports_node: bool = False
    supports_price_band: bool = False
    supports_rating: bool = False
    
    # Provider Entrypoints
    entrypoints: Dict[str, DiscoveryEntrypoint] = Field(default_factory=dict)

class EvidenceType(str, Enum):
    PRICE = "PRICE"
    BRAND = "BRAND"
    SELLER = "SELLER"
    PROMOTION = "PROMOTION"
    HISTORICAL = "HISTORICAL"
    HISTORICAL_LOWEST = "HISTORICAL_LOWEST"
    HISTORICAL_AVERAGE = "HISTORICAL_AVERAGE"
    HISTORICAL_VOLATILITY = "HISTORICAL_VOLATILITY"
    PRICE_RECOVERY = "PRICE_RECOVERY"
    DAYS_SINCE_LOWEST = "DAYS_SINCE_LOWEST"
    INVENTORY = "INVENTORY"
    CATEGORY = "CATEGORY"
    TREND = "TREND"
    SEASONAL = "SEASONAL"
    AVAILABILITY = "AVAILABILITY"
    PAYMENT = "PAYMENT"
    MARKETPLACE = "MARKETPLACE"
    CUSTOM = "CUSTOM"
    BANK_OFFER = "BANK_OFFER"
    WAREHOUSE = "WAREHOUSE"
    POPULARITY = "POPULARITY"
    SELLER_QUALITY = "SELLER_QUALITY"
    SEASONALITY = "SEASONALITY"
    SUBSCRIBE = "SUBSCRIBE"
    BUNDLE = "BUNDLE"
    EXCHANGE = "EXCHANGE"
    CROSS_PROVIDER = "CROSS_PROVIDER"
    RESTOCK = "RESTOCK"
    ALERT = "ALERT"

class EvidenceSource(str, Enum):
    KNOWLEDGE_BASE = "KNOWLEDGE_BASE"
    STRATEGY = "STRATEGY"
    HISTORICAL_DB = "HISTORICAL_DB"
    PROVIDER = "PROVIDER"
    MARKETPLACE = "MARKETPLACE"
    RULE_ENGINE = "RULE_ENGINE"
    MANUAL = "MANUAL"
    FUTURE_AI = "FUTURE_AI"

class CouponEvidence(BaseDTO):
    discount_value: float = 0.0
    discount_percentage: float = 0.0
    auto_applied: bool = False
    clip_required: bool = True
    coupon_code: Optional[str] = None

class BankOfferEvidence(BaseDTO):
    bank: str
    discount: float = 0.0
    cashback: float = 0.0
    emi: bool = False
    minimum_order: float = 0.0
    card_type: str = "ALL" # e.g. CREDIT, DEBIT, ALL
    expires_at: Optional[datetime] = None

class SubscribeEvidence(BaseDTO):
    recurring_discount_percentage: float = 0.0
    final_effective_price: float = 0.0

class BundleEvidence(BaseDTO):
    components: List[str] = Field(default_factory=list)
    bundle_savings: float = 0.0
    bundle_value: float = 0.0

class ExchangeEvidence(BaseDTO):
    max_exchange_value: float = 0.0
    effective_price_with_exchange: float = 0.0

class CrossProviderEvidence(BaseDTO):
    competitor: str
    competitor_price: float = 0.0
    is_winner: bool = False
    price_difference: float = 0.0


class EvidenceRecord(BaseDTO):
    id: str = Field(default_factory=generate_uuid)
    trace_context: TraceContext
    strategy: str
    type: EvidenceType
    weight: int
    confidence: float
    source: EvidenceSource
    metadata: Dict[str, Any] = Field(default_factory=dict)
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    version: str = "1.0.0"

class OpportunityScore(BaseDTO):
    overall: int = 0
    price: int = 0
    trust: int = 0
    popularity: int = 0
    profitability: int = 0
    urgency: int = 0
    confidence: int = 0
    publishability: int = 0
    metadata: dict = Field(default_factory=dict)

class OpportunityScoreAudit(BaseDTO):
    """Stores every intermediate score for deep auditability."""
    opportunity_uuid: str
    trace_context: TraceContext
    raw_price_score: float = 0.0
    raw_brand_score: float = 0.0
    raw_seller_score: float = 0.0
    raw_inventory_score: float = 0.0
    raw_coupon_score: float = 0.0
    raw_velocity_score: float = 0.0
    raw_popularity_score: float = 0.0
    raw_trust_score: float = 0.0
    raw_urgency_score: float = 0.0
    final_overall_score: int = 0
    computed_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class SearchTargetExplainabilityGraph(BaseDTO):
    """Exactly WHY this SearchTarget existed."""
    search_target_uuid: str
    trace_context: TraceContext
    generation_path: List[str] = Field(default_factory=list) # e.g. ["Profile", "Strategy", "Rule", "Constraint"]
    profile_name: Optional[str] = None
    strategy_name: Optional[str] = None
    rule_name: Optional[str] = None
    constraint_name: Optional[str] = None
    capability_used: Optional[str] = None
    planner_name: Optional[str] = None
    budget_allocated: int = 0
    priority_assigned: int = 0
    metadata: Dict[str, Any] = Field(default_factory=dict)

class DecisionAction(str, Enum):
    PUBLISH = "PUBLISH"
    HOLD = "HOLD"
    REVIEW = "REVIEW"
    REJECT = "REJECT"

class TargetLifecycleState(str, Enum):
    CREATED = "CREATED"
    PLANNED = "PLANNED"
    SCHEDULED = "SCHEDULED"
    DISPATCHED = "DISPATCHED"
    QUEUED = "QUEUED"
    EXTRACTED = "EXTRACTED"
    VALIDATED = "VALIDATED"
    SCORED = "SCORED"
    DECISIONED = "DECISIONED"
    PUBLISHED = "PUBLISHED"
    UPDATED = "UPDATED"
    EXPIRED = "EXPIRED"
    ARCHIVED = "ARCHIVED"

class DecisionLifecycleState(str, Enum):
    CREATED = "CREATED"
    EVALUATED = "EVALUATED"
    APPROVED = "APPROVED"
    REJECTED = "REJECTED"
    EXECUTED = "EXECUTED"
    ARCHIVED = "ARCHIVED"

class OpportunityDecisionDTO(BaseDTO):
    opportunity_uuid: str = Field(default_factory=generate_uuid)
    trace_context: TraceContext
    score: OpportunityScore
    decision: DecisionAction
    evidence_graph: List[EvidenceRecord] = Field(default_factory=list)
    explanation: str
    policy_version: str
    engine_version: str
    metadata: Dict[str, Any] = Field(default_factory=dict)

class SearchTargetDTO(BaseDTO):
    search_target_uuid: str = Field(default_factory=generate_uuid)
    trace_context: TraceContext
    provider: str
    profile: Optional[str] = None
    strategy: Optional[str] = None
    priority: int = 50
    budget_cost: int = 0
    estimated_runtime_ms: int = 0
    expected_content: Optional[str] = None
    expected_duration: Optional[int] = None
    evidence_graph: List[EvidenceRecord] = Field(default_factory=list)
    url: str = ""
    parameters: Dict[str, Any] = Field(default_factory=dict)
    constraints: Optional[Dict[str, Any]] = None

    ttl: int = 3600
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    expires_at: Optional[datetime] = None
    state: TargetLifecycleState = TargetLifecycleState.CREATED
    metadata: Optional[Dict[str, Any]] = None

class ShadowDecisionRecord(BaseDTO):
    id: str = Field(default_factory=generate_uuid)
    trace_context: TraceContext
    search_target_uuid: str
    decision: OpportunityDecisionDTO
    legacy_published: bool = False
    legacy_url: Optional[str] = None
    comparison_difference: Dict[str, Any] = Field(default_factory=dict)
    runtime_ms: int = 0
    state: DecisionLifecycleState = DecisionLifecycleState.CREATED
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class StrategyMetricsDTO(BaseDTO):
    strategy_name: str
    generated_targets: int = 0
    accepted_targets: int = 0
    published_deals: int = 0
    expired_deals: int = 0
    avg_overall_score: float = 0.0
    avg_confidence: float = 0.0
    avg_runtime_ms: float = 0.0
    ctr: float = 0.0
    conversion_rate: float = 0.0
    affiliate_revenue: float = 0.0
    captcha_rate: float = 0.0
    duplicate_rate: float = 0.0
    rollbacks: int = 0

class UniversalProductDTO(BaseDTO):
    universal_product_uuid: str = Field(default_factory=generate_uuid)
    provider: str
    provider_product_id: str # e.g. ASIN, SKU
    url: str
    title: str
    brand: Optional[str] = None
    category: Optional[str] = None
    features: List[str] = Field(default_factory=list)
    image_url: Optional[str] = None
    extracted_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class CanonicalDealDTO(BaseDTO):
    deal_version_uuid: str = Field(default_factory=generate_uuid)
    universal_product_uuid: str
    trace_context: TraceContext
    price: float
    effective_price: Optional[float] = None
    mrp: Optional[float] = None
    discount_percentage: float = 0.0
    coupon_details: Optional[str] = None
    seller: Optional[str] = None
    availability: bool = True
    prime_eligible: bool = False
    raw_payload: Dict[str, Any] = Field(default_factory=dict)
    version_timestamp: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class DealChangeHistoryDTO(BaseDTO):
    """Tracks exactly what changed between two Deal Versions."""
    history_uuid: str = Field(default_factory=generate_uuid)
    universal_product_uuid: str
    previous_deal_version_uuid: Optional[str] = None
    new_deal_version_uuid: str
    trace_context: TraceContext
    
    # Granular changes
    price_changed: bool = False
    mrp_changed: bool = False
    coupon_changed: bool = False
    availability_changed: bool = False
    seller_changed: bool = False
    
    # Delta details
    old_price: Optional[float] = None
    new_price: Optional[float] = None
    old_coupon: Optional[str] = None
    new_coupon: Optional[str] = None
    
    timestamp: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class HistoricalMetricsDTO(BaseDTO):
    lowest_price_30d: Optional[float] = None
    highest_price_30d: Optional[float] = None
    price_velocity_7d: float = 0.0
    volatility_index: float = 0.0

class OpportunityDTO(BaseDTO):
    opportunity_uuid: str = Field(default_factory=generate_uuid)
    universal_product_uuid: str
    deal_version_uuid: str
    trace_context: TraceContext
    opportunity_score: float # 0.0 to 100.0
    confidence_score: float # 0.0 to 100.0
    historical_metrics: Optional[HistoricalMetricsDTO] = None
    state: str = "Detected" # Detected, Validated, Rejected, Scored, Published
    rejection_reason: Optional[str] = None
    validated_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class PublishingLedgerV2DTO(BaseDTO):
    publish_request_id: str = Field(default_factory=generate_uuid)
    opportunity_uuid: str
    trace_context: TraceContext
    provider: str
    strategy: str
    affiliate_url: Optional[str] = None
    final_payload: Dict[str, Any] = Field(default_factory=dict)
    
    # Latency Tracking
    discovery_time_ms: int = 0
    extraction_time_ms: int = 0
    validation_time_ms: int = 0
    publishing_time_ms: int = 0
    
    # Audit trail
    actor: str = "system"
    version: str = "1.0"
    success: bool = False
    error_message: Optional[str] = None
    
    state: str = "Queued" # Queued, Publishing, Published, Rolled Back, Archived
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    published_at: Optional[datetime] = None
    expired_at: Optional[datetime] = None
    rolled_back_at: Optional[datetime] = None

class DiscoveryRunDTO(BaseDTO):
    run_uuid: str = Field(default_factory=generate_uuid)
    trace_context: TraceContext
    start_time: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    end_time: Optional[datetime] = None
    status: str = "IN_PROGRESS"
    targets_generated: int = 0
    deals_extracted: int = 0
    opportunities_scored: int = 0
    deals_published: int = 0

class ValidationResultDTO(BaseDTO):
    product_id: str
    trace_id: str
    is_valid: bool
    reason: Optional[str] = None

class DiscoveryJob(BaseDTO):
    """Rich DTO enqueued from the Discovery Engine to the Publisher."""
    job_uuid: str = Field(default_factory=generate_uuid)
    trace_id: str
    provider: str
    provider_product_id: str
    url: str
    deal_type: str = "deal"
    strategy: Optional[str] = None
    discovery_profile: Optional[str] = None
    opportunity_score: float = 0.0
    evidence_summary: Dict[str, Any] = Field(default_factory=dict)
    
    # Versioning
    engine_version: str = "1.0.0"
    policy_version: str = "1.0.0"
    knowledge_version: str = "1.0.0"
    provider_version: str = "1.0.0"
    
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    status: str = "QUEUED"

class PublishingContext(BaseDTO):
    """Context wrapper for the publisher to enrich without mutating the DiscoveryJob."""
    job: DiscoveryJob
    affiliate_url: Optional[str] = None
    caption: Optional[str] = None
    category_name: Optional[str] = None
    publisher_metadata: Dict[str, Any] = Field(default_factory=dict)

class PublishRequestDTO(BaseDTO):
    trace_id: str
    target_id: str
    deal_payload: dict

class PublishResultDTO(BaseDTO):
    publish_request_id: str
    trace_id: str
    success: bool
    published_url: Optional[str] = None

