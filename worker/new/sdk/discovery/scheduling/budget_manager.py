from typing import Dict, Any, List
from worker.new.sdk.discovery.economics.engine import DiscoveryEconomicsEngine
from worker.new.sdk.discovery.registry.strategy import StrategyMetadata

class CrawlBudgetManager:
    """Allocates search target budget dynamically based on economics or heuristics."""
    
    def __init__(self, economics_engine: DiscoveryEconomicsEngine = None):
        self.economics_engine = economics_engine
        
    def allocate_budget(self, strategies: List[StrategyMetadata], total_daily_budget: int = 10000) -> Dict[str, int]:
        """
        Distributes total_daily_budget across strategies.
        If economics data is missing, falls back to priority-based heuristic.
        """
        revenue_data = {}
        if self.economics_engine:
            try:
                revenue_data = self.economics_engine.get_revenue_by_strategy()
            except Exception:
                pass
            
        allocations = {}
        
        # First pass: Allocate manual overrides
        remaining_budget = total_daily_budget
        auto_strategies = []
        
        for strategy in strategies:
            if strategy.manual_budget is not None:
                allocations[strategy.id] = strategy.manual_budget
                remaining_budget -= strategy.manual_budget
            else:
                auto_strategies.append(strategy)
                
        if remaining_budget <= 0:
            remaining_budget = 0
            
        # Determine if we have enough historical data to use purely ROI-based allocation for the rest
        total_revenue = sum(data.get("revenue", 0.0) for data in revenue_data.values())
        
        if total_revenue > 1000.0:
            # ROI Based Allocation (Proportional)
            for strategy in auto_strategies:
                strat_rev = revenue_data.get(strategy.id, {}).get("revenue", 0.0)
                # Give every strategy at least 1% baseline for exploration
                share = (strat_rev / total_revenue) * 0.90 + 0.01
                allocations[strategy.id] = max(10, int(remaining_budget * share))
        else:
            # Heuristic / Priority Based Allocation
            total_priority = sum(strategy.priority for strategy in auto_strategies)
            if total_priority == 0:
                total_priority = 1
                
            for strategy in auto_strategies:
                share = strategy.priority / total_priority
                allocations[strategy.id] = max(10, int(remaining_budget * share))
                
        # Normalize to not exceed budget
        allocated = sum(allocations.values())
        if allocated > total_daily_budget and auto_strategies:
            # Subtract excess from highest priority auto-strategy
            highest_prio = max(auto_strategies, key=lambda s: s.priority)
            allocations[highest_prio.id] -= (allocated - total_daily_budget)
            
        return allocations
