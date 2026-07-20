from enum import IntEnum
from typing import Dict, List, Any

class JobPriority(IntEnum):
    HIGH = 1       # Flash deals, Lightning deals, Price changes
    MEDIUM = 2     # New products, Normal ingestion
    LOW = 3        # SEO generation, Old price refreshes, Image optimization
    RETRY = 4      # Failed jobs backing off
    DEAD_LETTER = 5# Permanently failed jobs awaiting review

class QueueDispatcher:
    """
    Manages job routing based on priority levels defined in the architecture spec.
    Ensures LOW priority tasks (like SEO) never block HIGH priority tasks (like Price Drops).
    """
    def __init__(self):
        self.queues: Dict[JobPriority, List[Any]] = {p: [] for p in JobPriority}
        
    def enqueue(self, job_payload: Dict[str, Any], priority: JobPriority = JobPriority.MEDIUM) -> None:
        """Adds a job to the corresponding priority queue."""
        self.queues[priority].append(job_payload)
        
    def get_next_job(self) -> Any:
        """Retrieves the highest priority job available."""
        for priority in JobPriority:
            if self.queues[priority]:
                return self.queues[priority].pop(0)
        return None
