from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class WarehouseStrategy(BaseDiscoveryStrategy):
    """
    Crawls Warehouse / Refurbished hubs.
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_warehouse",
            name="Warehouse Deals",
            priority=85,
            required_capabilities=[],
            schedule_interval_minutes=180,
            cost_estimate=1.0,
            expected_yield=3.5,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="High ROI on electronics"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        entrypoint = provider.entrypoints.get("WAREHOUSE")
        if not entrypoint or budget_allocation <= 0:
            return []
            
        targets = []
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("whs"),
            provider=provider.name,
            strategy="strat_warehouse"
        )
        
        pages_to_crawl = min(budget_allocation, entrypoint.max_pages if entrypoint.supports_pagination else 1)
        for page in range(1, pages_to_crawl + 1):
            targets.append(SearchTargetDTO(
                trace_context=trace_ctx,
                provider=provider.name,
                strategy="strat_warehouse",
                priority=entrypoint.priority,
                url=entrypoint.url,
                parameters={"page": page},
                expected_content="WAREHOUSE_GRID",
                expected_duration=120,
                expires_at=datetime.now(timezone.utc) + timedelta(hours=12)
            ))
            
        return targets
