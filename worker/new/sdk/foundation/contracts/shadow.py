from abc import ABC, abstractmethod
from typing import Any

class ShadowStore(ABC):
    """
    Contract for persisting Shadow Mode records.
    Implementations will write to the database to store the side-by-side
    comparison of new decisions vs legacy decisions.
    """
    @abstractmethod
    def save_record(self, record: Any) -> None:
        """Persist a ShadowDecisionRecord."""
        pass
