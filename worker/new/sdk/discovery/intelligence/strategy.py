from abc import ABC, abstractmethod
from typing import Dict, Any, List
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult

class DiscoveryStrategy(ABC):
    """
    Opportunity Discovery Framework (ODF): Discovery Strategy Interface
    Every strategy plugin must implement this strict lifecycle contract.
    """
    
    @abstractmethod
    def get_id(self) -> str:
        """Unique identifier for this strategy (e.g. 'mrp_error')."""
        pass
        
    @abstractmethod
    def get_name(self) -> str:
        """Human-readable name."""
        pass
        
    @abstractmethod
    def get_version(self) -> str:
        """Strategy version."""
        pass
        
    @abstractmethod
    def initialize(self, configuration: Dict[str, Any]) -> None:
        """One-time initialization and configuration loading."""
        pass
        
    @abstractmethod
    def supports(self, context: DiscoveryContext) -> bool:
        """Determines if this strategy can run against the given context/provider."""
        pass
        
    @abstractmethod
    def generate(self, context: DiscoveryContext) -> DiscoveryResult:
        """Core logic to generate prioritized, explainable search targets."""
        pass
        
    @abstractmethod
    def validate(self, result: DiscoveryResult) -> bool:
        """Validates the generated results before submission."""
        pass
        
    @abstractmethod
    def enrich(self, result: DiscoveryResult) -> DiscoveryResult:
        """Optionally enriches the results with additional metadata."""
        pass
        
    @abstractmethod
    def explain(self, result: DiscoveryResult) -> str:
        """Provides a human-readable explanation of why these targets were generated."""
        pass
        
    @abstractmethod
    def metrics(self) -> Dict[str, Any]:
        """Returns internal metrics for this strategy's operation."""
        pass
