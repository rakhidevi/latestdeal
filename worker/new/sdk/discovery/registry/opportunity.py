from typing import Dict, Type
from worker.new.sdk.foundation.dto.models import OpportunityDTO

class OpportunityRegistry:
    """Registry mapping opportunity types to DTOs and handlers."""
    _opportunities: Dict[str, Type[OpportunityDTO]] = {}

    @classmethod
    def register(cls, name: str, dto_class: Type[OpportunityDTO]) -> None:
        cls._opportunities[name] = dto_class

    @classmethod
    def get(cls, name: str) -> Type[OpportunityDTO]:
        return cls._opportunities.get(name, OpportunityDTO)
