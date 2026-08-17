from typing import Dict, Any

class StrategyMetricsAggregator:
    """
    Opportunity Discovery Framework (ODF): Metrics Aggregator
    Collects and formats metrics across all strategies for operational dashboards.
    """
    def __init__(self):
        self._strategy_metrics: Dict[str, Dict[str, Any]] = {}
        
    def aggregate(self, strategy_id: str, metrics: Dict[str, Any]) -> None:
        """Aggregates metrics for a specific strategy."""
        if strategy_id not in self._strategy_metrics:
            self._strategy_metrics[strategy_id] = {
                "invocations": 0,
                "generated_targets": 0,
                "execution_time_ms_total": 0,
                "errors": 0
            }
            
        for k, v in metrics.items():
            if k in self._strategy_metrics[strategy_id]:
                self._strategy_metrics[strategy_id][k] += v
                
    def get_report(self) -> Dict[str, Any]:
        """Returns the full metrics report."""
        report = {}
        for strategy_id, metrics in self._strategy_metrics.items():
            avg_time = 0
            if metrics["invocations"] > 0:
                avg_time = metrics["execution_time_ms_total"] / metrics["invocations"]
                
            report[strategy_id] = {
                "invocations": metrics["invocations"],
                "generated_targets": metrics["generated_targets"],
                "errors": metrics["errors"],
                "average_execution_time_ms": avg_time
            }
        return report
