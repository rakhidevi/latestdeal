from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class InventorySpikeStrategy(BaseDiscoveryStrategy):
    """
    Marketplace Intelligence: Inventory Spike Strategy
    Detects products that just came back in stock or restocked premium products.
    """
    
    def get_id(self) -> str:
        return "inventory_spike"
        
    def get_name(self) -> str:
        return "Inventory Spike"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        return True
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="restocked",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 80),
            parameters={"recently_restocked": True},
            trace_id=context.trace_id
        )
        
        confidence = 0.80
        reason = "Targeting products recently restocked."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
