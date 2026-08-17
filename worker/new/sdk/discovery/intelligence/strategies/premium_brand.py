from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class PremiumBrandStrategy(BaseDiscoveryStrategy):
    """
    Brand Intelligence: Premium Brand Strategy
    Targets high-priority premium brands (Samsung, Apple, Sony, LG, Bosch, Dyson).
    """
    
    def get_id(self) -> str:
        return "premium_brand"
        
    def get_name(self) -> str:
        return "Premium Brand"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return context.get_capabilities().supports(context.provider_name, "brand")
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        premium_brands = self._config.get("brands", ["Samsung", "Apple", "Sony", "LG", "Bosch", "Dyson"])
        
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="premium_brands",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 75),
            parameters={"brand": premium_brands},
            trace_id=context.trace_id
        )
        
        confidence = 0.80
        reason = f"Targeting premium brands: {', '.join(premium_brands[:3])}..."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
