from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class CouponStackStrategy(BaseDiscoveryStrategy):
    """
    Promotion Intelligence: Coupon Stack Strategy
    Detects combinations of coupons, cashback, and offers.
    """
    
    def get_id(self) -> str:
        return "coupon_stack"
        
    def get_name(self) -> str:
        return "Coupon Stack"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return context.get_capabilities().supports(context.provider_name, "coupon")
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="coupons",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 75),
            parameters={"has_coupon": True},
            trace_id=context.trace_id
        )
        
        confidence = 0.80
        reason = "Targeting products with active coupons/cashback."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
