from typing import List, Dict, Any
from worker.new.sdk.foundation.dto.models import EvidenceRecord, OpportunityScore, EvidenceType
from worker.new.sdk.discovery.decision.aggregator import EvidenceAggregator

class OpportunityEngine:
    """
    Opportunity Engine
    Computes a multi-dimensional OpportunityScore from the aggregated evidence graph.
    """
    
    def __init__(self, aggregator: EvidenceAggregator):
        self.aggregator = aggregator
        
    def compute_score(self, evidence_graph: List[EvidenceRecord], base_price: float = 0.0) -> OpportunityScore:
        """
        Calculates the final multi-dimensional score and effective price.
        """
        agg_result = self.aggregator.aggregate(evidence_graph)
        
        score = OpportunityScore()
        score.overall = agg_result["base_score"]
        score.confidence = int(agg_result["confidence"] * 100)
        
        effective_price = base_price
        
        # Calculate dimensional scores by filtering the graph
        for record in evidence_graph:
            contribution = int(record.weight * record.confidence)
            
            if record.type == EvidenceType.PRICE or record.type in [EvidenceType.HISTORICAL_LOWEST, EvidenceType.PRICE_RECOVERY]:
                score.price += contribution
            elif record.type == EvidenceType.BRAND:
                score.trust += contribution
            elif record.type in [EvidenceType.SELLER, EvidenceType.SELLER_QUALITY]:
                score.trust += (contribution // 2)
            elif record.type in [EvidenceType.TREND, EvidenceType.POPULARITY]:
                score.popularity += contribution
            elif record.type == EvidenceType.PROMOTION:
                score.profitability += contribution
            elif record.type == EvidenceType.INVENTORY or record.type == EvidenceType.WAREHOUSE:
                score.urgency += contribution
                
            # Bank Offer and Coupon logic for Effective Price
            if record.type == EvidenceType.BANK_OFFER and "metadata" in record.model_dump():
                meta = record.metadata
                discount = meta.get("discount", 0.0)
                cashback = meta.get("cashback", 0.0)
                effective_price -= (discount + cashback)
                score.profitability += contribution
                
            if record.type == EvidenceType.PROMOTION and "metadata" in record.model_dump():
                meta = record.metadata
                # If it's a coupon
                coupon_val = meta.get("discount_value", 0.0)
                effective_price -= coupon_val
                
            if record.type == EvidenceType.SUBSCRIBE and "metadata" in record.model_dump():
                meta = record.metadata
                # E.g. recurring_discount_percentage
                discount_pct = meta.get("recurring_discount_percentage", 0.0)
                if discount_pct > 0:
                    effective_price -= (effective_price * (discount_pct / 100.0))
                score.profitability += contribution
                
            if record.type == EvidenceType.EXCHANGE and "metadata" in record.model_dump():
                meta = record.metadata
                exchange_val = meta.get("max_exchange_value", 0.0)
                effective_price -= exchange_val
                score.profitability += contribution
                
        # Prevent negative prices
        score.metadata["effective_price"] = max(0.0, effective_price)
                
        # Publishability is a heuristic combining overall score, trust, and confidence
        score.publishability = int((score.overall * 0.5) + (score.trust * 0.3) + (score.confidence * 0.2))
        
        return score
