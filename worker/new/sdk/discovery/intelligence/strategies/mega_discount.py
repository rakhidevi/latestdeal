from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class MegaDiscountStrategy(BaseDiscoveryStrategy):
    """
    Price Intelligence: Mega Discount Strategy
    Detects highly discounted products across categories (80-99%).
    """
    
    def get_id(self) -> str:
        return "mega_discount"
        
    def get_name(self) -> str:
        return "Mega Discount"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return context.get_capabilities().supports(context.provider_name, "discount")
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        min_discount = self._config.get("minimum_discount", 80)
        
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="high_discount",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 70),
            parameters={"discount_min": min_discount},
            trace_id=context.trace_id
        )
        
        confidence = 0.85
        reason = f"Searching for items with >= {min_discount}% discount across categories."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
