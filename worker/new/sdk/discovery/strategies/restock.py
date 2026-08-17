from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class RestockStrategy(BaseDiscoveryStrategy):
    """
    Monitors out-of-stock items for restocks.
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_restock",
            name="Restock Intelligence",
            priority=90,
            required_capabilities=[],
            schedule_interval_minutes=60,
            cost_estimate=0.2,
            expected_yield=2.0,
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="Restocks frequently coincide with price drops"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        # Typically this strategy queries the database for OOS items and re-crawls their Product Details
        if budget_allocation <= 0:
            return []
            
        targets = []
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("rst"),
            provider=provider.name,
            strategy="strat_restock"
        )
        
        # Mocking a known OOS ASIN check
        targets.append(SearchTargetDTO(
            trace_context=trace_ctx,
            provider=provider.name,
            strategy="strat_restock",
            priority=100,
            url="https://www.amazon.in/dp/B0OOSMOCK",
            parameters={"expected_status": "in_stock"},
            expected_content="PRODUCT_DETAIL",
            expected_duration=30,
            expires_at=datetime.now(timezone.utc) + timedelta(hours=1)
        ))
            
        return targets
