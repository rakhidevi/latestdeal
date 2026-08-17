from typing import Dict, Any, List
from worker.new.sdk.foundation.dto.models import OpportunityScore, DecisionAction

class DecisionPolicyEngine:
    """
    Decision Policy Engine
    Determines whether an opportunity is PUBLISHED, HELD, REVIEWED, or REJECTED
    based on configurable thresholds (which can be loaded from YAML/DB).
    """
    
    def __init__(self, config: Dict[str, Any], version: str = "1.0.0"):
        self.config = config
        self.version = version
        
    def evaluate(self, score: OpportunityScore) -> DecisionAction:
        """
        Evaluates the multidimensional score against policy thresholds.
        """
        policy = self.config.get("decision_policy", {})
        
        publish_threshold = policy.get("publish", {}).get("minimum_score", 85)
        review_threshold = policy.get("review", {}).get("minimum_score", 60)
        
        # We can also check specific dimensions (e.g. must have trust > 20)
        trust_minimum = policy.get("publish", {}).get("minimum_trust", 0)
        
        if score.overall >= publish_threshold and score.trust >= trust_minimum:
            return DecisionAction.PUBLISH
            
        if score.overall >= review_threshold:
            return DecisionAction.REVIEW
            
        return DecisionAction.REJECT
        
    def generate_explanation(self, score: OpportunityScore, decision: DecisionAction) -> str:
        """
        Generates a human-readable explanation of why the decision was made.
        """
        if decision == DecisionAction.PUBLISH:
            return f"Published automatically. Overall Score ({score.overall}) exceeded publish threshold."
        elif decision == DecisionAction.REVIEW:
            return f"Queued for review. Overall Score ({score.overall}) did not meet publish threshold."
        else:
            return f"Rejected. Overall Score ({score.overall}) was below the review threshold."
