from typing import Dict, Any, Optional
from worker.new.sdk.foundation.contracts.provider import CommerceProvider
from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix
from worker.new.sdk.discovery.engine.ontology_engine import OntologyEngine
from worker.new.sdk.discovery.planning.planner import DiscoveryPlanner

class DiscoveryContext:
    """
    Opportunity Discovery Framework (ODF): Discovery Context
    Provides a unified interface for strategies to interact with platform services.
    Strategies must never instantiate these services directly.
    """
    def __init__(
        self,
        trace_id: str,
        provider_name: str,
        provider: CommerceProvider,
        knowledge_base_path: str,
        ontology_engine: OntologyEngine,
        capability_matrix: CapabilityMatrix,
        planner: DiscoveryPlanner,
        configuration: Dict[str, Any],
        budget: int,
        telemetry: Any = None,
        history: Any = None
    ):
        self.trace_id = trace_id
        self.provider_name = provider_name
        self._provider = provider
        self._knowledge_base_path = knowledge_base_path
        self._ontology_engine = ontology_engine
        self._capability_matrix = capability_matrix
        self._planner = planner
        self.configuration = configuration
        self.budget = budget
        self._telemetry = telemetry
        self._history = history
        
    def get_provider(self) -> CommerceProvider:
        return self._provider
        
    def get_ontology(self) -> OntologyEngine:
        return self._ontology_engine
        
    def get_capabilities(self) -> CapabilityMatrix:
        return self._capability_matrix
        
    def get_planner(self) -> DiscoveryPlanner:
        return self._planner
        
    def log_event(self, message: str, level: str = "info", metadata: Optional[Dict[str, Any]] = None) -> None:
        """Centralized telemetry logging for strategies."""
        if self._telemetry:
            # Implement real telemetry later
            pass
        else:
            print(f"[{level.upper()}] [{self.trace_id}] {message} - {metadata or {}}")
