from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class WarehouseClearanceStrategy(BaseDiscoveryStrategy):
    """
    Promotion Intelligence: Warehouse Clearance Strategy
    Finds Warehouse deals, outlet, and clearance items.
    """
    
    def get_id(self) -> str:
        return "warehouse"
        
    def get_name(self) -> str:
        return "Warehouse Clearance"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        # Assuming provider manifest has supports_warehouse or supports_node
        return context.get_capabilities().supports(context.provider_name, "node")
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="warehouse_deals",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 65),
            parameters={"is_warehouse": True},
            trace_id=context.trace_id
        )
        
        confidence = 0.75
        reason = "Targeting warehouse/open-box inventory."
        
        self._record_metrics(len(targets), 10)
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
