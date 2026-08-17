from typing import List
from datetime import datetime, timezone, timedelta
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO, TraceContext
from worker.new.sdk.foundation.identity.generator import generate_trace_id
from worker.new.sdk.discovery.registry.strategy import (
    BaseDiscoveryStrategy, StrategyMetadata, StrategyLifecycle, ExecutionMode
)

class UserAlertStrategy(BaseDiscoveryStrategy):
    """
    Monitors user alerts (e.g. Samsung TV < 30,000)
    """
    @classmethod
    def get_metadata(cls) -> StrategyMetadata:
        return StrategyMetadata(
            id="strat_user_alert",
            name="User Alerts",
            priority=100,
            required_capabilities=[],
            schedule_interval_minutes=30,
            cost_estimate=0.1,
            expected_yield=9.0, # Highly targeted
            lifecycle=StrategyLifecycle.SHADOW,
            execution_mode=ExecutionMode.SHADOW_ONLY,
            notes="First class evaluation of user watchlists"
        )
        
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        if budget_allocation <= 0:
            return []
            
        targets = []
        trace_ctx = TraceContext(
            trace_id=generate_trace_id("alt"),
            provider=provider.name,
            strategy="strat_user_alert"
        )
        
        # Mocking checking a user's alert for a specific product
        targets.append(SearchTargetDTO(
            trace_context=trace_ctx,
            provider=provider.name,
            strategy="strat_user_alert",
            priority=100,
            url="https://www.amazon.in/dp/B0ALERTMOCK",
            parameters={"target_price": 30000.0},
            expected_content="PRODUCT_DETAIL",
            expected_duration=30,
            expires_at=datetime.now(timezone.utc) + timedelta(minutes=60)
        ))
            
        return targets
