from abc import ABC, abstractmethod
from typing import List
from worker.new.sdk.foundation.dto.models import SearchTargetDTO, UniversalProductDTO

class BaseExtractor(ABC):
    """
    Standard interface for all Provider Extractors.
    An extractor receives a generic SearchTargetDTO and returns parsed products.
    """
    
    @abstractmethod
    def extract(self, page_html: str, target: SearchTargetDTO) -> List[UniversalProductDTO]:
        """Parses the provider's specific DOM and extracts universal products."""
        pass
