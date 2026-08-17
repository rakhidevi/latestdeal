from typing import Dict, Any, Optional
from datetime import datetime

class RollbackThresholds:
    def __init__(self, raw_config: Dict[str, Any]):
        # e.g. 15.0 means 15%
        self.max_captcha_rate = raw_config.get("max_captcha_rate", 15.0) 
        # e.g. 90.0 means 90%
        self.min_extraction_success = raw_config.get("min_extraction_success", 90.0) 
        # Revenue drop percentage allowed, e.g. 25.0 means 25% drop is allowed
        self.max_revenue_drop = raw_config.get("max_revenue_drop", 25.0) 
        # Number of SearchTargets queued without being processed
        self.max_queue_latency = raw_config.get("max_queue_latency", 1000)

class LiveMetrics:
    def __init__(self, captcha_rate: float, extraction_success: float, revenue_drop: float, queue_latency: int):
        self.captcha_rate = captcha_rate
        self.extraction_success = extraction_success
        self.revenue_drop = revenue_drop
        self.queue_latency = queue_latency

class RollbackTriggeredEvent:
    def __init__(self, reason: str, timestamp: datetime, original_rollout: float):
        self.reason = reason
        self.timestamp = timestamp
        self.original_rollout = original_rollout
        self.new_rollout = 0.0

class AutomaticRollbackEngine:
    """
    Evaluates live platform metrics against safe thresholds.
    Triggers an automatic rollback if any guardrail is breached.
    """
    def __init__(self, thresholds: RollbackThresholds):
        self.thresholds = thresholds
        
    def evaluate(self, metrics: LiveMetrics, current_rollout_percentage: float) -> Optional[RollbackTriggeredEvent]:
        """
        Returns a RollbackTriggeredEvent if a threshold is breached. Otherwise Returns None.
        """
        if metrics.captcha_rate > self.thresholds.max_captcha_rate:
            return RollbackTriggeredEvent(
                reason=f"CAPTCHA rate {metrics.captcha_rate}% exceeded threshold {self.thresholds.max_captcha_rate}%",
                timestamp=datetime.utcnow(),
                original_rollout=current_rollout_percentage
            )
            
        if metrics.extraction_success < self.thresholds.min_extraction_success:
            return RollbackTriggeredEvent(
                reason=f"Extraction success {metrics.extraction_success}% dropped below threshold {self.thresholds.min_extraction_success}%",
                timestamp=datetime.utcnow(),
                original_rollout=current_rollout_percentage
            )
            
        if metrics.revenue_drop > self.thresholds.max_revenue_drop:
            return RollbackTriggeredEvent(
                reason=f"Revenue drop {metrics.revenue_drop}% exceeded threshold {self.thresholds.max_revenue_drop}%",
                timestamp=datetime.utcnow(),
                original_rollout=current_rollout_percentage
            )
            
        if metrics.queue_latency > self.thresholds.max_queue_latency:
            return RollbackTriggeredEvent(
                reason=f"Queue latency {metrics.queue_latency} exceeded threshold {self.thresholds.max_queue_latency}",
                timestamp=datetime.utcnow(),
                original_rollout=current_rollout_percentage
            )
            
        return None
