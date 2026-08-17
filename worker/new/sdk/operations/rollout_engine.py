import random
from typing import Dict, Any, List
from datetime import datetime

class RolloutConfig:
    def __init__(self, raw_config: Dict[str, Any]):
        self.global_emergency_stop = raw_config.get("global_emergency_stop", False)
        self.global_percentage = raw_config.get("global_percentage", 0) # 0 to 100
        
        self.allowed_providers = raw_config.get("allowed_providers", [])
        self.allowed_strategies = raw_config.get("allowed_strategies", [])
        self.allowed_profiles = raw_config.get("allowed_profiles", [])
        
        # Format "10:00-18:00"
        self.allowed_time_window = raw_config.get("allowed_time_window", None)

class CanaryDecisionEngine:
    """
    Decides if a target is eligible for the new pipeline based on business segments
    instead of just a random percentage.
    """
    def __init__(self, config: RolloutConfig):
        self.config = config
        
    def _is_within_time_window(self) -> bool:
        if not self.config.allowed_time_window:
            return True
            
        try:
            start_str, end_str = self.config.allowed_time_window.split("-")
            now = datetime.now().time()
            
            start_h, start_m = map(int, start_str.split(":"))
            end_h, end_m = map(int, end_str.split(":"))
            
            import datetime as dt
            start_time = dt.time(start_h, start_m)
            end_time = dt.time(end_h, end_m)
            
            return start_time <= now <= end_time
        except Exception:
            return True # Fallback on safe

    def is_eligible(self, target_provider: str, target_strategy: str, target_profile: str) -> bool:
        if self.config.global_emergency_stop:
            return False
            
        if self.config.allowed_providers and target_provider not in self.config.allowed_providers:
            return False
            
        if self.config.allowed_strategies and target_strategy not in self.config.allowed_strategies:
            return False
            
        if self.config.allowed_profiles and target_profile not in self.config.allowed_profiles:
            return False
            
        if not self._is_within_time_window():
            return False
            
        return True

class RolloutEngine:
    """
    Configuration-driven traffic shaping.
    Combines percentage-based routing with segment-based canary rules.
    """
    def __init__(self, config: RolloutConfig):
        self.config = config
        self.decision_engine = CanaryDecisionEngine(config)
        
    def should_route_to_new_engine(self, target_provider: str, target_strategy: str, target_profile: str) -> bool:
        """
        Returns True if the target should be processed by the New Discovery Platform.
        Returns False if it should fall back to the Legacy System.
        """
        # 1. Segment-based eligibility
        if not self.decision_engine.is_eligible(target_provider, target_strategy, target_profile):
            return False
            
        # 2. Percentage-based shaping
        roll_value = random.uniform(0, 100)
        if roll_value > self.config.global_percentage:
            return False
            
        return True
