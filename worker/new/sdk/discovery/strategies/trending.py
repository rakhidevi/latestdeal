from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class TrendingStrategy(BaseDiscoveryStrategy):
    """
    Aggregates multi-signal trending parameters.
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_trending",
            name="Trending Monitor",
            priority=80,
            required_capabilities=[],
            schedule_interval_minutes=240,
            cost_estimate=1.5,
            expected_yield=4.0,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="Requires multiple signals (BSR, Recent Reviews)"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        entrypoint = provider.entrypoints.get("TRENDING")
        if not entrypoint or budget_allocation <= 0:
            return []
            
        targets = []
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("trd"),
            provider=provider.name,
            strategy="strat_trending"
        )
        
        pages_to_crawl = min(budget_allocation, entrypoint.max_pages if entrypoint.supports_pagination else 1)
        for page in range(1, pages_to_crawl + 1):
            targets.append(SearchTargetDTO(
                trace_context=trace_ctx,
                provider=provider.name,
                strategy="strat_trending",
                priority=entrypoint.priority,
                url=entrypoint.url,
                parameters={"page": page},
                expected_content="TRENDING_GRID",
                expected_duration=120,
                expires_at=datetime.now(timezone.utc) + timedelta(hours=6)
            ))
            
        return targets
