from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.base import BaseDiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult
from worker.new.sdk.discovery.intelligence.explainability import StrategyExplainability
from worker.new.sdk.discovery.intelligence.scoring import StrategyScoringContribution

class MRPErrorStrategy(BaseDiscoveryStrategy):
    """
    Price Intelligence: MRP Error Strategy
    Detects impossible discounts, pricing glitches, and decimal mistakes.
    """
    
    def get_id(self) -> str:
        return "mrp_error"
        
    def get_name(self) -> str:
        return "MRP Error"
        
    def get_version(self) -> str:
        return "1.0.0"
        
    def supports(self, context: DiscoveryContext) -> bool:
        # Requires the provider to support 'discount' filtering
        return context.get_capabilities().supports(context.provider_name, "discount")
        
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        min_discount = self._config.get("min_discount", 85)
        
        # We instruct the planner to build the search space focusing on high discount
        base_params = {
            "discount_min": min_discount,
            # In a real scenario, we might also combine this with top categories
        }
        
        targets = context.get_planner().generate_targets(
            provider=context.provider_name,
            profile_name="mrp_loot",
            strategy=self.get_id(),
            base_priority=self._config.get("base_priority", 80),
            parameters=base_params,
            trace_id=context.trace_id
        )
        
        # Assume a high confidence for MRP errors based on the high discount constraint
        confidence = 0.95
        reason = f"Searching for items with >= {min_discount}% discount."
        
        self._record_metrics(len(targets), 10) # Mock 10ms
        
        return DiscoveryResult(
            strategy_name=self.get_name(),
            strategy_version=self.get_version(),
            generated_targets=targets,
            confidence=confidence,
            reason=reason,
            metrics=self.metrics()
        )
