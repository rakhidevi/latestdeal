from abc import ABC, abstractmethod
from typing import List
from worker.new.sdk.foundation.dto.models import DiscoveryProfileDTO, SearchTargetDTO

class DiscoveryEngine(ABC):
    """
    Contract for the Discovery Orchestrator.
    Generates SearchTargets based on DiscoveryProfiles.
    """
    @abstractmethod
    def generate_targets(self, profile: DiscoveryProfileDTO) -> List[SearchTargetDTO]:
        """Translates a Profile into actionable Search Targets."""
        pass

    @abstractmethod
    def dispatch_targets(self, targets: List[SearchTargetDTO]) -> None:
        """Publishes generated targets to the Event Bus (or queues them)."""
        pass
