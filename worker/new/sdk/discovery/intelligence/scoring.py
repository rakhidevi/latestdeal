from typing import Dict, Any

class StrategyScoringContribution:
    """
    Opportunity Discovery Framework (ODF): Strategy Contribution
    Defines how much evidence a strategy contributes to the final Opportunity Score.
    """
    
    @staticmethod
    def calculate_contribution(strategy_id: str, confidence: float, config: Dict[str, Any]) -> int:
        """
        Calculates the point contribution of this strategy based on its base weight and confidence.
        """
        base_weight = config.get("weight", 0)
        return int(base_weight * confidence)
