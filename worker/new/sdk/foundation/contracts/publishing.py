from abc import ABC, abstractmethod
from worker.new.sdk.foundation.dto.models import PublishRequestDTO, PublishResultDTO

class PublishingEngine(ABC):
    """
    Contract for the Publishing Engine.
    Handles the final delivery and lifecycle of a validated Opportunity.
    """
    @abstractmethod
    def publish(self, request: PublishRequestDTO) -> PublishResultDTO:
        """Publishes the deal and records it in the Publishing Ledger."""
        pass
