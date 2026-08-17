from typing import Dict, Any, List
from datetime import datetime

class DiscoveryResultRecord:
    def __init__(
        self,
        engine_type: str, # 'LEGACY' or 'NEW'
        deals_found: int,
        best_discount: float,
        best_mrp: float,
        duplicate_count: int,
        runtime_ms: int,
        opportunity_score: float,
        decision: str, # e.g. 'PUBLISH', 'REJECT'
        final_publish_outcome: bool
    ):
        self.engine_type = engine_type
        self.deals_found = deals_found
        self.best_discount = best_discount
        self.best_mrp = best_mrp
        self.duplicate_count = duplicate_count
        self.runtime_ms = runtime_ms
        self.opportunity_score = opportunity_score
        self.decision = decision
        self.final_publish_outcome = final_publish_outcome

class CanaryComparator:
    """
    Compares the outcomes of the Legacy Discovery engine versus the New Discovery engine
    for the exact same SearchTarget to objectively prove superiority.
    """
    
    def compare(self, legacy: DiscoveryResultRecord, new: DiscoveryResultRecord) -> Dict[str, Any]:
        """
        Takes the results of both engines processing the same target and returns a comparison.
        """
        better_discount = new.best_discount > legacy.best_discount
        found_more_deals = new.deals_found > legacy.deals_found
        faster_runtime = new.runtime_ms < legacy.runtime_ms
        less_duplicates = new.duplicate_count < legacy.duplicate_count
        
        # New engine is considered 'superior' for this target if it found a better deal
        # or found the same deal much faster with fewer duplicates
        is_superior = False
        if better_discount or (new.best_discount == legacy.best_discount and found_more_deals):
            is_superior = True
        elif new.best_discount == legacy.best_discount and faster_runtime and less_duplicates:
            is_superior = True
            
        return {
            "is_new_engine_superior": is_superior,
            "metrics_diff": {
                "deals_found_diff": new.deals_found - legacy.deals_found,
                "discount_diff": new.best_discount - legacy.best_discount,
                "runtime_diff_ms": new.runtime_ms - legacy.runtime_ms,
                "opportunity_score_diff": new.opportunity_score - legacy.opportunity_score
            },
            "new_decision": new.decision,
            "legacy_decision": legacy.decision,
            "new_published": new.final_publish_outcome,
            "legacy_published": legacy.final_publish_outcome,
            "timestamp": datetime.utcnow().isoformat()
        }

    def aggregate_comparisons(self, comparisons: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Aggregates a list of comparison results into a final Canary Report segment.
        """
        total = len(comparisons)
        if total == 0:
            return {}
            
        superior_count = sum(1 for c in comparisons if c["is_new_engine_superior"])
        new_published = sum(1 for c in comparisons if c["new_published"])
        legacy_published = sum(1 for c in comparisons if c["legacy_published"])
        
        avg_discount_diff = sum(c["metrics_diff"]["discount_diff"] for c in comparisons) / total
        avg_runtime_diff = sum(c["metrics_diff"]["runtime_diff_ms"] for c in comparisons) / total
        
        return {
            "total_comparisons": total,
            "new_engine_win_rate": (superior_count / total) * 100.0,
            "new_deals_published": new_published,
            "legacy_deals_published": legacy_published,
            "average_discount_improvement": avg_discount_diff,
            "average_runtime_improvement_ms": avg_runtime_diff,
            "recommendation": "PASS" if superior_count > (total * 0.5) else "WARNING"
        }
