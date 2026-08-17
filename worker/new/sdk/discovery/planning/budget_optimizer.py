from typing import Dict, Any

class BudgetOptimizer:
    """
    Discovery Planning Engine: Budget Optimizer
    Dynamically allocates page limits (budget) based on estimated yield and priority.
    """
    
    def __init__(self, max_budget_per_run: int = 1000):
        self.max_budget_per_run = max_budget_per_run
        
    def allocate(self, estimated_yield: float, base_priority: int, profile_config: Dict[str, Any]) -> int:
        """
        Calculates the allocated budget (e.g. number of pages to scrape).
        High yield + high priority = higher budget.
        """
        # Base budget requested by profile (or default to 10)
        requested_budget = profile_config.get("budget", 10)
        if str(requested_budget).lower() == "auto":
            requested_budget = 50
            
        # Scale budget by yield
        # If yield is > 0.8, we are willing to spend more budget (e.g. up to 3x)
        # If yield is < 0.2, we throttle it (e.g. 0.2x)
        multiplier = max(0.1, estimated_yield * 2.0)
        
        # Priority boost (0-100 scale)
        priority_boost = 1.0 + (base_priority / 100.0)
        
        allocated = int(requested_budget * multiplier * priority_boost)
        
        # Ensure we don't return 0 if there was some budget
        return max(1, allocated)
