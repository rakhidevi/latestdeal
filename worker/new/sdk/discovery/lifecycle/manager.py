from worker.new.sdk.foundation.dto.models import SearchTargetDTO, ShadowDecisionRecord, TargetLifecycleState, DecisionLifecycleState
from datetime import datetime, timezone

class LifecycleManager:
    """
    Entity Lifecycle Management (Phase 11.9)
    Provides deterministic state transitions for targets and decisions.
    """
    
    @staticmethod
    def transition_target(target: SearchTargetDTO, new_state: TargetLifecycleState) -> None:
        """
        Transitions a SearchTarget to a new lifecycle state.
        In a full implementation, this would enforce state machine rules 
        (e.g., cannot go from CREATED directly to PUBLISHED) and log the transition.
        """
        # Example validation rule
        if target.state == TargetLifecycleState.ARCHIVED:
            raise ValueError("Cannot transition an ARCHIVED target.")
            
        target.state = new_state
        
    @staticmethod
    def transition_decision(record: ShadowDecisionRecord, new_state: DecisionLifecycleState) -> None:
        """
        Transitions a Decision to a new lifecycle state.
        """
        if record.state == DecisionLifecycleState.ARCHIVED:
            raise ValueError("Cannot transition an ARCHIVED decision.")
            
        record.state = new_state
