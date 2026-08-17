from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class HistoricalLowStrategy(BaseDiscoveryStrategy):
    """
    Price Intelligence: Historical Low Strategy
    Detects products at their lowest ever price (requires Phase 12 history).
    """
    
    def get_id(self) -> str:
        return "historical_low"
        
    def get_name(self) -> str:
        return "Historical Low"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return True # Depends more on our internal historical DB than provider capabilities
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        # In a real implementation, this would query the Historical Intelligence DB
        # to find products approaching their low, and then queue targets to check them.
        
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="historical_low",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 90),
            parameters={"check_history": True},
            trace_id=context.trace_id
        )
        
        confidence = 0.90
        reason = "Targeting products flagged as nearing historical low prices."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
