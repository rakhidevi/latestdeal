from typing import Dict, Any, Optional, List, Type
from pydantic import BaseModel, Field
from abc import ABC, abstractmethod
from enum import Enum
from datetime import datetime
from worker.new.sdk.foundation.dto.models import PluginManifestV2, SearchTargetDTO

class StrategyLifecycle(str, Enum):
    EXPERIMENTAL = "EXPERIMENTAL"
    SHADOW = "SHADOW"
    CERTIFIED = "CERTIFIED"
    PRODUCTION = "PRODUCTION"
    DEPRECATED = "DEPRECATED"

class ExecutionMode(str, Enum):
    RUNNING = "RUNNING"
    PAUSED = "PAUSED"
    SHADOW_ONLY = "SHADOW_ONLY"
    DISABLED = "DISABLED"
    
class StrategyVersion(BaseModel):
    version: str
    deployment_date: str
    operator: str
    notes: str

class StrategyMetadata(BaseModel):
    id: str
    name: str
    priority: int = 50
    required_capabilities: List[str] = Field(default_factory=list)
    schedule_interval_minutes: int = 60
    cost_estimate: float = 0.1
    expected_yield: float = 1.0
    feature_flag: str = "default"
    
    # New additions
    lifecycle: StrategyLifecycle = StrategyLifecycle.EXPERIMENTAL
    execution_mode: ExecutionMode = ExecutionMode.RUNNING
    dependencies: List[str] = Field(default_factory=list)
    manual_budget: Optional[int] = None
    strategy_version: str = "1.0.0"
    engine_version: str = "1.0.0"
    knowledge_version: str = "1.0.0"
    version_history: List[StrategyVersion] = Field(default_factory=list)
    notes: str = ""
    
class BaseDiscoveryStrategy(ABC):
    @classmethod
    @abstractmethod
    def get_metadata(cls) -> StrategyMetadata:
        pass
        
    @abstractmethod
    def generate_targets(self, provider: PluginManifestV2, budget_allocation: int) -> List[SearchTargetDTO]:
        pass

class StrategyRegistry:
    """Registry for Capability-Driven Discovery Strategies."""
    _strategies: Dict[str, Type[BaseDiscoveryStrategy]] = {}

    @classmethod
    def register(cls, strategy_class: Type[BaseDiscoveryStrategy]) -> None:
        metadata = strategy_class.get_metadata()
        cls._strategies[metadata.id] = strategy_class

    @classmethod
    def get(cls, strategy_id: str) -> Optional[Type[BaseDiscoveryStrategy]]:
        return cls._strategies.get(strategy_id)
        
    @classmethod
    def get_all(cls) -> List[Type[BaseDiscoveryStrategy]]:
        return list(cls._strategies.values())

    @classmethod
    def get_compatible_strategies(cls, provider: PluginManifestV2) -> List[Type[BaseDiscoveryStrategy]]:
        """Filters strategies where the provider meets all required capabilities."""
        compatible = []
        for strategy in cls._strategies.values():
            metadata = strategy.get_metadata()
            is_compatible = True
            for capability in metadata.required_capabilities:
                # Check if provider manifest has the capability set to True
                if getattr(provider, capability, False) is not True:
                    is_compatible = False
                    break
            if is_compatible:
                compatible.append(strategy)
        
        # Sort by priority descending
        return sorted(compatible, key=lambda s: s.get_metadata().priority, reverse=True)
