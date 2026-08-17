from typing import List, Dict, Any
from worker.new.sdk.foundation.dto.models import EvidenceRecord

class EvidenceAggregator:
    """
    Evidence Aggregator
    Fuses multiple evidence records, resolves conflicts via weighted voting,
    and calculates mathematical confidence for the overall opportunity.
    """
    
    def aggregate(self, evidence_graph: List[EvidenceRecord]) -> Dict[str, Any]:
        """
        Takes a list of immutable EvidenceRecords and computes the aggregated base values.
        """
        if not evidence_graph:
            return {
                "base_score": 0,
                "confidence": 0.0,
                "evidence_count": 0,
                "conflict_ratio": 0.0,
                "diversity_score": 0.0
            }
            
        total_weight = 0
        positive_weight = 0
        negative_weight = 0
        
        types_seen = set()
        sources_seen = set()
        
        for record in evidence_graph:
            total_weight += abs(record.weight)
            
            # Weighted voting
            contribution = record.weight * record.confidence
            if contribution >= 0:
                positive_weight += contribution
            else:
                negative_weight += abs(contribution)
                
            types_seen.add(record.type)
            sources_seen.add(record.source)
            
        base_score = int(positive_weight - negative_weight)
        
        # Conflict Ratio (0.0 means perfect agreement, 1.0 means perfect disagreement)
        conflict_ratio = 0.0
        if positive_weight > 0 and negative_weight > 0:
            smaller = min(positive_weight, negative_weight)
            larger = max(positive_weight, negative_weight)
            conflict_ratio = smaller / larger
            
        # Diversity (more types/sources = higher confidence)
        diversity_score = min((len(types_seen) + len(sources_seen)) / 5.0, 1.0)
        
        # Mathematical Confidence Formula
        # 1. Base confidence from diversity and volume
        # 2. Penalty for conflict
        raw_confidence = (0.4 * diversity_score) + (0.6 * min(len(evidence_graph) / 5.0, 1.0))
        final_confidence = raw_confidence * (1.0 - (conflict_ratio * 0.5))
        
        return {
            "base_score": base_score,
            "confidence": min(max(final_confidence, 0.0), 1.0),
            "evidence_count": len(evidence_graph),
            "conflict_ratio": conflict_ratio,
            "diversity_score": diversity_score
        }
