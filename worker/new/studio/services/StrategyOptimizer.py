from typing import List, Dict, Any
from .TraceService import TraceService

class StrategyOptimizer:
    """
    Analyzes Shadow Ledger results and provides tuning recommendations (Sprint 9).
    Calculates ROI, Confidence gaps, and Yield per Strategy.
    """
    def __init__(self, trace_service: TraceService):
        self.trace_service = trace_service
        
    def analyze_yield(self) -> Dict[str, Any]:
        traces = self.trace_service.get_recent_traces(limit=1000)
        
        # Aggregate by profile
        stats = {}
        for trace in traces:
            target_uuid = trace.get("search_target_uuid")
            decision = trace.get("decision", {})
            score = decision.get("score", {}).get("overall", 0)
            action = decision.get("decision")
            
            # For this demo, let's extract profile from context (if we stored it)
            # We didn't store profile at root, we stored it in trace_context
            profile = trace.get("trace_context", {}).get("profile", "Unknown")
            
            if profile not in stats:
                stats[profile] = {"total_targets": 0, "published": 0, "avg_score": 0.0}
                
            stats[profile]["total_targets"] += 1
            if action == "DecisionAction.PUBLISH" or action == "PUBLISH":
                stats[profile]["published"] += 1
            stats[profile]["avg_score"] += score
            
        # Finalize stats
        recommendations = []
        for profile, data in stats.items():
            if data["total_targets"] > 0:
                data["avg_score"] /= data["total_targets"]
                data["yield_percent"] = (data["published"] / data["total_targets"]) * 100
                
                if data["yield_percent"] == 0:
                    recommendations.append(f"[{profile}] 0% Yield. Recommendation: Loosen discount constraints or increase page budget.")
                elif data["yield_percent"] > 50:
                    recommendations.append(f"[{profile}] {data['yield_percent']}% Yield. Warning: Exceptionally high. Check for False Positives.")
                else:
                    recommendations.append(f"[{profile}] {data['yield_percent']}% Yield. Healthy.")
                    
        return {
            "stats": stats,
            "recommendations": recommendations
        }

if __name__ == "__main__":
    ts = TraceService(r"k:\WhatsAppUtility\LatestDeal\worker\new\shadow_mode\output")
    optimizer = StrategyOptimizer(ts)
    analysis = optimizer.analyze_yield()
    
    print("\n=== Strategy Optimization Report ===")
    for rec in analysis["recommendations"]:
        print(rec)
