from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class CouponStrategy(BaseDiscoveryStrategy):
    """
    Targets Coupon Hubs across providers.
    Uses DiscoveryEntrypoints defined in the provider's PluginManifestV2.
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_coupons",
            name="Coupon Explorer",
            priority=85,
            required_capabilities=["supports_coupons"],
            schedule_interval_minutes=120,
            cost_estimate=1.0,
            expected_yield=1.5,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="Initial shadow deployment for Wave 1"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        entrypoint = provider.entrypoints.get("COUPONS")
        if not entrypoint:
            return []
            
        targets = []
        # Pagination Policy
        pages_to_crawl = min(budget_allocation, entrypoint.max_pages if entrypoint.supports_pagination else 1)
        
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("cpn"),
            provider=provider.name,
            strategy="strat_coupons"
        )
        
        for page in range(1, pages_to_crawl + 1):
            targets.append(SearchTargetDTO(
                trace_context=trace_ctx,
                provider=provider.name,
                strategy="strat_coupons",
                priority=entrypoint.priority,
                url=entrypoint.url,
                parameters={"page": page},
                expected_content="COUPON_GRID",
                expected_duration=120, 
                expires_at=datetime.now(timezone.utc) + timedelta(hours=24) # Coupons live longer
            ))
            
        return targets
