from abc import ABC, abstractmethod
from worker.new.sdk.foundation.dto.models import ProductDTO, OpportunityDTO

class ValidationEngine(ABC):
    """
    Contract for the Validation Engine.
    Determines if an extracted Product is a genuine Opportunity.
    """
    @abstractmethod
    def validate(self, product: ProductDTO) -> OpportunityDTO:
        """Validates and scores the product, returning an OpportunityDTO."""
        pass
