from typing import List, Dict, Any

class SimulationMetrics:
    """
    Computes precision, recall, false positives, and missed opportunities
    from a batch of simulation replay results.
    """
    
    @staticmethod
    def compute(replay_results: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Assumes legacy_published = True is the "ground truth" of what a human/legacy 
        engine deemed a valid deal (for relative comparison purposes).
        """
        true_positives = 0
        false_positives = 0 # New engine published, but legacy rejected
        false_negatives = 0 # New engine rejected, but legacy published
        true_negatives = 0
        
        for res in replay_results:
            legacy_pub = res["legacy_published"]
            new_pub = (res["new_decision"] == "PUBLISH")
            
            if new_pub and legacy_pub:
                true_positives += 1
            elif new_pub and not legacy_pub:
                false_positives += 1
            elif not new_pub and legacy_pub:
                false_negatives += 1
            else:
                true_negatives += 1
                
        total_published_by_new = true_positives + false_positives
        total_published_by_legacy = true_positives + false_negatives
        
        precision = 0.0
        if total_published_by_new > 0:
            precision = true_positives / total_published_by_new
            
        recall = 0.0
        if total_published_by_legacy > 0:
            recall = true_positives / total_published_by_legacy
            
        return {
            "total_processed": len(replay_results),
            "true_positives": true_positives,
            "false_positives": false_positives,   # "Additional Opportunities Found" (or noise)
            "false_negatives": false_negatives,   # "Missed Opportunities"
            "true_negatives": true_negatives,
            "precision": precision,
            "recall": recall
        }
