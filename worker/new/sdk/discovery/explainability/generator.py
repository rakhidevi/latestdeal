from typing import List
from worker.new.sdk.foundation.dto.models import OpportunityDecisionDTO, EvidenceRecord, DecisionAction

class ExplanationGenerator:
    """
    Explainability Engine (Phase 11.7)
    Automatically generates human-readable rationales directly from the 
    Evidence Graph, eliminating the need for hardcoded text.
    """
    
    @staticmethod
    def generate(decision: OpportunityDecisionDTO) -> str:
        """
        Generates a paragraph explaining exactly why the decision was made,
        which strategies contributed, and what reduced confidence.
        """
        graph: List[EvidenceRecord] = decision.evidence_graph
        score = decision.score
        action = decision.decision
        
        if not graph:
            return f"The opportunity was {action.value.lower()} due to a lack of evidence."
            
        # 1. Answer: Why was this published/rejected?
        rationale = []
        if action == DecisionAction.PUBLISH:
            rationale.append(f"The opportunity was published because the overall score ({score.overall}) and trust ({score.trust}) met the required thresholds.")
        elif action == DecisionAction.REVIEW:
            rationale.append(f"The opportunity was sent for review because its overall score ({score.overall}) was borderline.")
        elif action == DecisionAction.REJECT:
            rationale.append(f"The opportunity was rejected because its overall score ({score.overall}) fell below minimum policy requirements.")
        elif action == DecisionAction.HOLD:
            rationale.append(f"The opportunity was held pending further changes.")
            
        # 2. Answer: Which strategies contributed?
        positive_strategies = set()
        negative_strategies = set()
        
        for record in graph:
            if record.weight > 0:
                positive_strategies.add(f"{record.strategy} ({record.type.value})")
            else:
                negative_strategies.add(f"{record.strategy} ({record.type.value})")
                
        if positive_strategies:
            rationale.append(f"It received strong positive signals from: {', '.join(sorted(positive_strategies))}.")
            
        # 3. Answer: Which evidence reduced confidence?
        if negative_strategies:
            rationale.append(f"However, confidence was reduced due to negative signals from: {', '.join(sorted(negative_strategies))}.")
            
        # 4. Add confidence context
        if score.confidence < 50:
            rationale.append(f"Overall confidence remains low ({score.confidence}%) due to conflicting evidence or lack of diversity in sources.")
        elif score.confidence >= 80:
            rationale.append(f"Overall confidence is high ({score.confidence}%) due to strong multi-source agreement.")
            
        return " ".join(rationale)
