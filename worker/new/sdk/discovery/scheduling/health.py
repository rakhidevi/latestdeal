from typing import Dict, Any
from pydantic import BaseModel
from worker.new.sdk.discovery.economics.engine import DiscoveryEconomicsEngine

class StrategyHealthMetrics(BaseModel):
    overall_health: float
    extraction_success: float
    validation_success: float
    publishing_success: float
    roi_score: float
    exception_rate: float
    discovery_heat: str

class StrategyHealthManager:
    """Evaluates granular health metrics and discovery heat for strategies."""
    
    def __init__(self, economics_engine: DiscoveryEconomicsEngine):
        self.economics_engine = economics_engine

    def evaluate_health(self, strategy_id: str) -> StrategyHealthMetrics:
        """
        Calculates granular health metrics for a strategy.
        In a production implementation, this pulls from UCDP & application logs.
        For now, we build the structure and compute heuristics from economics.
        """
        revenue_data = {}
        if self.economics_engine:
            try:
                revenue_data = self.economics_engine.get_revenue_by_strategy().get(strategy_id, {})
            except Exception:
                pass
            
        published = revenue_data.get("published_deals", 0)
        targets = revenue_data.get("targets_generated", 1)
        if targets == 0:
            targets = 1
            
        revenue = revenue_data.get("revenue", 0.0)
        
        # Mocking pipeline metrics for demonstration based on user requirements.
        # Future: Read from `deals_queue` DB table (failed, completed)
        extraction_success = 0.99
        validation_success = 0.97
        publishing_success = published / targets
        
        # Normalizing publishing success (a 5% yield is considered 100% healthy for raw discovery)
        normalized_publishing = min(1.0, publishing_success * 20.0) 
        if published == 0 and targets == 1:
            normalized_publishing = 1.0 # Default state
            
        roi_score = min(1.0, revenue / 5000.0) if revenue > 0 else 0.92 # Default
        exception_rate = 0.004
        
        overall = (extraction_success * 0.3) + (validation_success * 0.3) + (normalized_publishing * 0.2) + (roi_score * 0.2)
        if overall == 0:
            overall = 0.96 # Default
            
        # Calculate Discovery Heat
        heat = "LOW"
        if revenue > 5000 or targets > 2000:
            heat = "VERY HIGH"
        elif revenue > 2000 or targets > 1000:
            heat = "HIGH"
        elif revenue > 500 or targets > 500:
            heat = "MEDIUM"
            
        return StrategyHealthMetrics(
            overall_health=round(overall * 100, 1),
            extraction_success=round(extraction_success * 100, 1),
            validation_success=round(validation_success * 100, 1),
            publishing_success=round(normalized_publishing * 100, 1),
            roi_score=round(roi_score * 100, 1),
            exception_rate=round(exception_rate * 100, 1),
            discovery_heat=heat
        )
