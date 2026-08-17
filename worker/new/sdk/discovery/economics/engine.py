from sqlalchemy.orm import Session
from sqlalchemy import func
from typing import Dict, Any

from worker.new.sdk.foundation.database.models import (
    UCDP_CommerceLedger, UCDP_OpportunityDecision, UCDP_SearchTarget
)

class DiscoveryEconomicsEngine:
    """
    Epic 3.5: Discovery Economics Engine.
    The financial brain of the discovery platform. Aggregates data from the UCDP
    to determine the economic efficiency of strategies, profiles, and providers.
    """
    def __init__(self, session: Session):
        self.session = session
        
    def _compute_yield(self, target_count: int, opportunity_count: int) -> float:
        if target_count == 0:
            return 0.0
        return (opportunity_count / target_count) * 100
        
    def get_revenue_by_strategy(self) -> Dict[str, Any]:
        """
        Calculates Revenue, Yield, and Profitability per Discovery Strategy.
        """
        # Join Ledger -> Decision -> Target to group by strategy
        results = self.session.query(
            UCDP_SearchTarget.strategy,
            func.sum(UCDP_CommerceLedger.revenue).label('total_revenue'),
            func.sum(UCDP_CommerceLedger.clicks).label('total_clicks'),
            func.count(UCDP_CommerceLedger.id).label('opportunities_published')
        ).select_from(UCDP_CommerceLedger) \
         .join(UCDP_OpportunityDecision, UCDP_CommerceLedger.decision_id == UCDP_OpportunityDecision.id) \
         .join(UCDP_SearchTarget, UCDP_OpportunityDecision.target_id == UCDP_SearchTarget.id) \
         .group_by(UCDP_SearchTarget.strategy).all()
         
        report = {}
        for row in results:
            strategy = row.strategy or "unknown"
            # Get total generated targets for this strategy to calculate yield
            total_targets = self.session.query(UCDP_SearchTarget).filter_by(strategy=strategy).count()
            
            report[strategy] = {
                "revenue": float(row.total_revenue or 0.0),
                "clicks": int(row.total_clicks or 0),
                "published_deals": int(row.opportunities_published),
                "targets_generated": total_targets,
                "yield_percentage": self._compute_yield(total_targets, row.opportunities_published),
                "revenue_per_target": float(row.total_revenue / total_targets) if total_targets else 0.0
            }
        return report

    def get_revenue_by_provider(self) -> Dict[str, Any]:
        """
        Calculates Revenue per Provider (e.g. Amazon vs Flipkart).
        """
        results = self.session.query(
            UCDP_SearchTarget.provider,
            func.sum(UCDP_CommerceLedger.revenue).label('total_revenue')
        ).select_from(UCDP_CommerceLedger) \
         .join(UCDP_OpportunityDecision, UCDP_CommerceLedger.decision_id == UCDP_OpportunityDecision.id) \
         .join(UCDP_SearchTarget, UCDP_OpportunityDecision.target_id == UCDP_SearchTarget.id) \
         .group_by(UCDP_SearchTarget.provider).all()
         
        report = {}
        for row in results:
            provider = row.provider
            report[provider] = {
                "revenue": float(row.total_revenue or 0.0)
            }
        return report

    def get_overall_efficiency(self) -> Dict[str, Any]:
        """
        Calculates high-level platform efficiency metrics.
        """
        total_revenue = self.session.query(func.sum(UCDP_CommerceLedger.revenue)).scalar() or 0.0
        total_targets = self.session.query(UCDP_SearchTarget).count()
        total_published = self.session.query(UCDP_CommerceLedger).count()
        
        # Hypothetical cost assumption: 0.1 INR per search target (bandwidth + proxy + compute)
        estimated_cost = total_targets * 0.1
        profitability = total_revenue - estimated_cost
        roi = ((total_revenue - estimated_cost) / estimated_cost * 100) if estimated_cost > 0 else 0.0
        
        return {
            "total_revenue": total_revenue,
            "estimated_discovery_cost": estimated_cost,
            "net_profitability": profitability,
            "platform_roi_percentage": roi,
            "revenue_density_per_deal": (total_revenue / total_published) if total_published else 0.0,
            "overall_yield_percentage": self._compute_yield(total_targets, total_published)
        }
        
    def generate_financial_report(self) -> Dict[str, Any]:
        """
        Generates the complete Epic 3.5 Financial Brain output.
        """
        return {
            "efficiency": self.get_overall_efficiency(),
            "by_strategy": self.get_revenue_by_strategy(),
            "by_provider": self.get_revenue_by_provider()
        }
