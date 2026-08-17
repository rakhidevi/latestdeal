from typing import Dict, Any

class PriorityEngine:
    """
    Discovery Planning Engine: Priority Engine
    Calculates final execution priority based on yield, history, freshness, etc.
    """
    
    def calculate(self, base_priority: int, estimated_yield: float, parameters: Dict[str, Any]) -> int:
        """
        Returns a final priority score (0-100). Higher is executed sooner.
        """
        priority = base_priority
        
        # Boost based on yield
        # E.g. +20 priority for 90% yield
        yield_boost = int(estimated_yield * 20)
        priority += yield_boost
        
        # Boost for urgent temporal factors (e.g. Flash Crash, Lightning)
        if parameters.get("is_lightning") or parameters.get("is_flash"):
            priority += 30
            
        return min(100, max(0, priority))
