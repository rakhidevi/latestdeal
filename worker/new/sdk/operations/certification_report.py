from typing import Dict, Any, List
from datetime import datetime
import json

class CanaryCertificationReport:
    """
    Sprint 4A.5: Generates the immutable audit artifact for a Canary Rollout.
    """
    def __init__(
        self,
        rollout_config: Dict[str, Any],
        duration_hours: float,
        total_targets: int,
        success_rate: float,
        rollback_events: List[Dict[str, Any]],
        provider_health: Dict[str, float],
        economic_metrics: Dict[str, Any],
        comparator_results: Dict[str, Any]
    ):
        self.rollout_config = rollout_config
        self.duration_hours = duration_hours
        self.total_targets = total_targets
        self.success_rate = success_rate
        self.rollback_events = rollback_events
        self.provider_health = provider_health
        self.economic_metrics = economic_metrics
        self.comparator_results = comparator_results
        
    def _calculate_recommendation(self) -> str:
        if len(self.rollback_events) > 0:
            return "FAIL"
            
        if self.success_rate < 95.0:
            return "WARNING"
            
        if self.comparator_results.get("new_engine_win_rate", 0) < 50.0:
            return "WARNING"
            
        return "PASS"
        
    def generate_markdown(self) -> str:
        recommendation = self._calculate_recommendation()
        
        icon = "✅" if recommendation == "PASS" else ("⚠️" if recommendation == "WARNING" else "❌")
        
        md = f"# Epic 4A: Canary Certification Report\n\n"
        md += f"**Generated:** {datetime.utcnow().isoformat()}\n"
        md += f"**Recommendation:** {icon} {recommendation}\n\n"
        
        md += "## Canary Profile\n"
        md += f"- **Duration:** {self.duration_hours} hours\n"
        md += f"- **Targets Processed:** {self.total_targets}\n"
        md += f"- **Extraction Success Rate:** {self.success_rate}%\n"
        md += f"- **Rollback Events Triggered:** {len(self.rollback_events)}\n\n"
        
        md += "## Configuration\n"
        md += f"```json\n{json.dumps(self.rollout_config, indent=2)}\n```\n\n"
        
        md += "## Economic Impact (vs Legacy)\n"
        new_win = self.comparator_results.get("new_engine_win_rate", 0)
        avg_discount = self.comparator_results.get("average_discount_improvement", 0)
        md += f"- **New Engine Win Rate:** {new_win:.1f}%\n"
        md += f"- **Average Discount Improvement:** {avg_discount:.2f}%\n"
        md += f"- **Total Revenue Generated:** INR {self.economic_metrics.get('revenue', 0)}\n\n"
        
        if self.rollback_events:
            md += "## 🛑 Rollback Events\n"
            for ev in self.rollback_events:
                md += f"- **{ev['timestamp']}**: {ev['reason']}\n"
                
        return md
