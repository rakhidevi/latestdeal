from datetime import datetime, timezone
from worker.new.sdk.discovery.registry.strategy import StrategyMetadata

class AdaptiveDiscoveryOptimizer:
    """Modifies base schedule intervals dynamically based on environment signals."""
    
    @staticmethod
    def get_adaptive_interval(metadata: StrategyMetadata, provider_name: str) -> int:
        base_interval = metadata.schedule_interval_minutes
        
        # Example dynamic rule: Shorter intervals during busy hours (e.g. 10 AM to 10 PM IST)
        # Using UTC for consistency: IST is UTC+5:30. 10AM IST = 4:30AM UTC. 10PM IST = 16:30 UTC.
        now_utc = datetime.now(timezone.utc)
        hour = now_utc.hour
        
        # Busy hours rule
        is_busy_hour = 4 <= hour <= 16
        
        if is_busy_hour:
            # Increase frequency by reducing interval by 50%
            return max(1, int(base_interval * 0.5))
            
        return base_interval
