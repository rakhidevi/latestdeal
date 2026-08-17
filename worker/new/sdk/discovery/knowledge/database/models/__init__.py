from worker.new.sdk.discovery.knowledge.database.db import Base
from .core import Brand, Category, Department
from .provider import SellerProfile, ProviderNode, ProviderManifestRecord
from .discovery import DiscoveryProfileRecord, SearchTargetRecord, SearchRunRecord
from .history import PriceHistory, OpportunityHistory, PublishLedgerRecord, WorkerMetric

# This allows Alembic or create_all() to discover all models
__all__ = [
    "Base",
    "Brand",
    "Category",
    "Department",
    "SellerProfile",
    "ProviderNode",
    "ProviderManifestRecord",
    "DiscoveryProfileRecord",
    "SearchTargetRecord",
    "SearchRunRecord",
    "PriceHistory",
    "OpportunityHistory",
    "PublishLedgerRecord",
    "WorkerMetric"
]
