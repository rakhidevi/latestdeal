from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class SellerIntelligenceStrategy(BaseDiscoveryStrategy):
    """
    Marketplace Intelligence: Seller Intelligence Strategy
    Prefers trusted sellers, official stores, or fulfilled products.
    """
    
    def get_id(self) -> str:
        return "seller_intelligence"
        
    def get_name(self) -> str:
        return "Seller Intelligence"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return context.get_capabilities().supports(context.provider_name, "seller")
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="trusted_sellers",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 70),
            parameters={"is_trusted_seller": True},
            trace_id=context.trace_id
        )
        
        confidence = 0.75
        reason = "Targeting products from official/trusted sellers."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
