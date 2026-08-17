from typing import Dict, Any, List, Optional
from worker.new.sdk.foundation.dto.models import SearchTargetDTO

class DiscoveryResult:
    """
    Opportunity Discovery Framework (ODF): Discovery Result
    The standardized output for every strategy, containing metadata, 
    confidence, generated targets, and metrics.
    """
    def __init__(
        self,
        strategy_name: str,
        strategy_version: str,
        generated_targets: List[SearchTargetDTO],
        confidence: float,
        reason: str,
        metrics: Dict[str, Any],
        warnings: Optional[List[str]] = None,
        execution_time_ms: int = 0
    ):
        self.strategy_name = strategy_name
        self.strategy_version = strategy_version
        self.generated_targets = generated_targets
        self.confidence = confidence # 0.0 to 1.0
        self.reason = reason
        self.metrics = metrics
        self.warnings = warnings or []
        self.execution_time_ms = execution_time_ms
        
    def to_dict(self) -> Dict[str, Any]:
        return {
            "strategy": self.strategy_name,
            "version": self.strategy_version,
            "targets_count": len(self.generated_targets),
            "confidence": self.confidence,
            "reason": self.reason,
            "metrics": self.metrics,
            "warnings": self.warnings,
            "execution_time_ms": self.execution_time_ms
        }
