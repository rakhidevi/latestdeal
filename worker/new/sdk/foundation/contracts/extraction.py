from abc import ABC, abstractmethod
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, ProductDTO

class ExtractionEngine(ABC):
    """
    Contract for the Extraction Engine.
    Takes a SearchTarget and extracts structured Products.
    """
    @abstractmethod
    def extract(self, target: SearchTargetDTO) -> ProductDTO:
        """Extracts structured product data from a Search Target."""
        pass
