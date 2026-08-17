from typing import List, Dict, Any
from worker.new.sdk.foundation.dto.models import StrategyMetricsDTO, ShadowDecisionRecord, DecisionAction

class AnalyticsAggregator:
    """
    Strategy Analytics Engine (Phase 11.8)
    Aggregates granular business outcomes (Generated, Published, CTR, Revenue) 
    per strategy to measure true performance and ROI.
    """
    
    @staticmethod
    def aggregate_strategy_metrics(
        strategy_name: str,
        records: List[ShadowDecisionRecord],
        business_data: List[Dict[str, Any]]
    ) -> StrategyMetricsDTO:
        """
        Merges shadow decisions with downstream business data (clicks, conversions).
        """
        metrics = StrategyMetricsDTO(strategy_name=strategy_name)
        
        metrics.generated_targets = len(records)
        if metrics.generated_targets == 0:
            return metrics
            
        total_score = 0
        total_confidence = 0
        total_runtime = 0
        
        for r in records:
            # We filter for records where this specific strategy contributed positively
            # For simplicity in this demo, we assume the batch passed in is already
            # pre-filtered for 'strategy_name'.
            
            if r.decision.decision == DecisionAction.PUBLISH:
                metrics.accepted_targets += 1
                
            if r.legacy_published:
                metrics.published_deals += 1
                
            total_score += r.decision.score.overall
            total_confidence += r.decision.score.confidence
            total_runtime += r.runtime_ms
            
        metrics.avg_overall_score = total_score / metrics.generated_targets
        metrics.avg_confidence = total_confidence / metrics.generated_targets
        metrics.avg_runtime_ms = total_runtime / metrics.generated_targets
        
        # Merge business data
        total_clicks = 0
        total_impressions = 0
        total_conversions = 0
        total_revenue = 0.0
        
        for b in business_data:
            total_impressions += b.get("impressions", 0)
            total_clicks += b.get("clicks", 0)
            total_conversions += b.get("conversions", 0)
            total_revenue += b.get("revenue", 0.0)
            
        if total_impressions > 0:
            metrics.ctr = (total_clicks / total_impressions) * 100
            
        if total_clicks > 0:
            metrics.conversion_rate = (total_conversions / total_clicks) * 100
            
        metrics.affiliate_revenue = total_revenue
        
        return metrics
