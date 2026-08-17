from typing import List, Dict, Any, Callable
from worker.new.sdk.foundation.dto.models import OpportunityDecisionDTO, DecisionAction

class StrategyExperimentFramework:
    """
    A/B Testing framework for Discovery Strategies.
    Runs multiple strategies against the same dataset and measures 
    yield, CTR (simulated/historical), and profitability differences.
    """
    
    @staticmethod
    def compare_strategies(
        dataset: List[Dict[str, Any]], 
        strategy_a: Callable[[Dict[str, Any]], OpportunityDecisionDTO],
        strategy_b: Callable[[Dict[str, Any]], OpportunityDecisionDTO]
    ) -> Dict[str, Any]:
        """
        Runs strategy A and strategy B on a historical dataset of products
        and compares their outcomes.
        """
        results_a = {"published": 0, "rejected": 0, "avg_confidence": 0.0, "total_confidence": 0.0}
        results_b = {"published": 0, "rejected": 0, "avg_confidence": 0.0, "total_confidence": 0.0}
        
        for data in dataset:
            decision_a = strategy_a(data)
            decision_b = strategy_b(data)
            
            if decision_a.decision == DecisionAction.PUBLISH:
                results_a["published"] += 1
            else:
                results_a["rejected"] += 1
            results_a["total_confidence"] += decision_a.score.confidence
            
            if decision_b.decision == DecisionAction.PUBLISH:
                results_b["published"] += 1
            else:
                results_b["rejected"] += 1
            results_b["total_confidence"] += decision_b.score.confidence
            
        count = len(dataset)
        if count > 0:
            results_a["avg_confidence"] = results_a["total_confidence"] / count
            results_b["avg_confidence"] = results_b["total_confidence"] / count
            
        return {
            "strategy_a": results_a,
            "strategy_b": results_b,
            "winner": "A" if results_a["published"] > results_b["published"] else ("B" if results_b["published"] > results_a["published"] else "TIE")
        }
