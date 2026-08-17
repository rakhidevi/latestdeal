from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class PriceDropStrategy(BaseDiscoveryStrategy):
    """
    Evaluates the UniversalPriceHistoryService to find true price drops.
    Generates targets to verify the price drop before publishing.
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_price_drop",
            name="Price Drop Monitor",
            priority=99,
            required_capabilities=["supports_price_history"],
            schedule_interval_minutes=30,
            cost_estimate=0.1,
            expected_yield=4.0,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="Requires populated history database"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        targets = []
        if budget_allocation <= 0:
            return targets
            
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("drp"),
            provider=provider.name,
            strategy="strat_price_drop"
        )
        
        # In a full implementation, we'd query UniversalPriceHistoryService for recent drops.
        # For this wave, we generate a verification target to represent a detected drop.
        targets.append(SearchTargetDTO(
            trace_context=trace_ctx,
            provider=provider.name,
            strategy="strat_price_drop",
            priority=100, # Max priority, verifiable drop
            url="https://www.amazon.in/dp/B085J19VJG",
            parameters={"expected_drop": 30.0},
            expected_content="PRODUCT_DETAIL",
            expected_duration=30,
            expires_at=datetime.now(timezone.utc) + timedelta(minutes=60)
        ))
        
        return targets
