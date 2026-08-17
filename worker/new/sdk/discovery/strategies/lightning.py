from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class LightningStrategy(BaseDiscoveryStrategy):
    """
    Targets Lightning Deals across providers.
    Uses DiscoveryEntrypoints defined in the provider's PluginManifestV2.
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_lightning",
            name="Lightning Deals Explorer",
            priority=95,
            required_capabilities=["supports_lightning"],
            schedule_interval_minutes=5,
            cost_estimate=0.5,
            expected_yield=2.0,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="Initial shadow deployment for Wave 1"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        entrypoint = provider.entrypoints.get("LIGHTNING")
        if not entrypoint:
            return []
            
        targets = []
        # Pagination Policy
        pages_to_crawl = min(budget_allocation, entrypoint.max_pages if entrypoint.supports_pagination else 1)
        
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("ltn"),
            provider=provider.name,
            strategy="strat_lightning"
        )
        
        for page in range(1, pages_to_crawl + 1):
            targets.append(SearchTargetDTO(
                trace_context=trace_ctx,
                provider=provider.name,
                strategy="strat_lightning",
                priority=entrypoint.priority,
                url=entrypoint.url,
                parameters={"page": page},
                expected_content="LIGHTNING_DEAL_GRID",
                expected_duration=60, # Expected to run quickly (e.g. 60 seconds)
                expires_at=datetime.now(timezone.utc) + timedelta(minutes=15) # Lightning deals expire fast
            ))
            
        return targets
