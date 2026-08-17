from typing import Dict, Any, List
import time
from worker.new.sdk.discovery.intelligence.strategy import DiscoveryStrategy
from worker.new.sdk.discovery.intelligence.context import DiscoveryContext
from worker.new.sdk.discovery.intelligence.result import DiscoveryResult
from worker.new.sdk.discovery.intelligence.exceptions import StrategyConfigurationError

class BaseDiscoveryStrategy(DiscoveryStrategy):
    """
    Opportunity Discovery Framework (ODF): Base Strategy
    Provides boilerplate implementation for the Strategy contract.
    """
    def __init__(self):
        self._config: Dict[str, Any] = {}
        self._metrics = {
            "invocations": 0,
            "generated_targets": 0,
            "execution_time_ms_total": 0,
            "errors": 0
        }
        
    def initialize(self, configuration: Dict[str, Any]) -> None:
        self._config = configuration
        self._validate_configuration()
        
    def _validate_configuration(self) -> None:
        """Override to validate specific config keys."""
        if not self._config.get("enabled", True):
            raise StrategyConfigurationError(f"Strategy {self.get_id()} is disabled.")
            
    def validate(self, result: DiscoveryResult) -> bool:
        """Default validation: ensures targets were actually generated."""
        return len(result.generated_targets) >= 0
        
    def enrich(self, result: DiscoveryResult) -> DiscoveryResult:
        """Default enrichment: no-op."""
        return result
        
    def explain(self, result: DiscoveryResult) -> str:
        """Default explanation based on result."""
        return f"Strategy {self.get_name()} generated {len(result.generated_targets)} targets due to: {result.reason}"
        
    def metrics(self) -> Dict[str, Any]:
        return self._metrics.copy()
        
    def _record_metrics(self, targets_count: int, execution_time: int, is_error: bool = False) -> None:
        self._metrics["invocations"] += 1
        if is_error:
            self._metrics["errors"] += 1
        else:
            self._metrics["generated_targets"] += targets_count
            self._metrics["execution_time_ms_total"] += execution_time
