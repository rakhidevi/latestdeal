from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)
from worker.new.sdk.discovery.knowledge.brand_catalog import BrandCatalogService

class BrandStoreStrategy(BaseDiscoveryStrategy):
    """
    Crawls Brand Stores to seed high quality universal targets into the Price History Collector.
    """
    def __init__(self):
        self.catalog = BrandCatalogService()
        
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_brand_store",
            name="Brand Store Seeder",
            priority=100, # Highest priority to seed history
            required_capabilities=["supports_brand"],
            schedule_interval_minutes=720, # Runs twice a day
            cost_estimate=2.0,
            expected_yield=5.0,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="Seeds Price History Collector"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        stores = self.catalog.get_brand_stores_for_provider(provider.name)
        if not stores:
            return []
            
        targets = []
        
        for store in stores:
            if budget_allocation <= 0:
                break
                
            trace_ctx = TraceContext(
                trace_id=generate_trace_id(f"brd-{store.brand_name[:3].lower()}"),
                provider=provider.name,
                strategy="strat_brand_store"
            )
            
            pages_to_crawl = min(budget_allocation, 3) 
            
            for page in range(1, pages_to_crawl + 1):
                targets.append(SearchTargetDTO(
                    trace_context=trace_ctx,
                    provider=provider.name,
                    strategy="strat_brand_store",
                    priority=store.priority,
                    url=store.store_url,
                    parameters={"page": page, "brand": store.brand_name},
                    expected_content="BRAND_STORE_GRID",
                    expected_duration=120,
                    expires_at=datetime.now(timezone.utc) + timedelta(hours=12)
                ))
                budget_allocation -= 1
                if budget_allocation <= 0:
                    break
                    
        return targets
