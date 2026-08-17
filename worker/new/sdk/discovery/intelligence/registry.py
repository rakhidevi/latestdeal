from typing import Dict, List, Optional, Any
from worker.new.sdk.discovery.intelligence.strategy import DiscoveryStrategy

class DiscoveryStrategyRegistry:
    """
    Opportunity Discovery Framework (ODF): Strategy Registry
    Manages the lifecycle and discovery of all strategy plugins.
    """
    def __init__(self):
        self._strategies: Dict[str, DiscoveryStrategy] = {}
        
    def register(self, strategy: DiscoveryStrategy) -> None:
        """Registers a strategy plugin."""
        self._strategies[strategy.get_id()] = strategy
        
    def get(self, strategy_id: str) -> Optional[DiscoveryStrategy]:
        """Retrieves a strategy by its ID."""
        return self._strategies.get(strategy_id)
        
    def get_all(self) -> List[DiscoveryStrategy]:
        """Returns all registered strategies."""
        return list(self._strategies.values())
        
    def get_supported_for_provider(self, context: Any) -> List[DiscoveryStrategy]:
        """Returns all strategies that support the given context/provider."""
        return [s for s in self._strategies.values() if s.supports(context)]
