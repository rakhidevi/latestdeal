from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class BankOfferStrategy(BaseDiscoveryStrategy):
    """
    Promotion Intelligence: Bank Offer Strategy
    Detects products with specific bank card promotions.
    """
    
    def get_id(self) -> str:
        return "bank_offer"
        
    def get_name(self) -> str:
        return "Bank Offer"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        # Requires provider to support filtering by offers (we assume true for foundation)
        return True
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="bank_offers",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 70),
            parameters={"has_bank_offer": True},
            trace_id=context.trace_id
        )
        
        confidence = 0.75
        reason = "Targeting products with active bank promotions."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
