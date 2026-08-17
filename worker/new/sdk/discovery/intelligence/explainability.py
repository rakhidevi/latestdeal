from typing import Dict, Any, List

class StrategyExplainability:
    """
    Opportunity Discovery Framework (ODF): Strategy Explainability
    Formats detailed rationales for why targets were generated.
    """
    
    @staticmethod
    def build_explanation(
        strategy_name: str,
        reasoning_factors: List[str],
        confidence: float,
        expected_yield: float,
        priority: int
    ) -> str:
        """
        Builds a standardized, human-readable explanation block.
        """
        factors_str = "\n".join([f"• {f}" for f in reasoning_factors])
        
        return f"""Strategy: {strategy_name}

Reason:
{factors_str}

Confidence: {int(confidence * 100)}%
Expected Yield: {int(expected_yield * 100)}%
Priority: {priority}"""
