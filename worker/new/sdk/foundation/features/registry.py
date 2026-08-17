from typing import Dict
import logging

class FeatureFlagRegistry:
    """
    Feature Flag Registry (UCOM Requirement).
    Everything new should be behind feature flags.
    No code changes required to enable/disable features.
    """
    
    def __init__(self):
        self._flags: Dict[str, bool] = {
            "adaptive_learning": False,
            "historical_engine": False,
            "new_scoring": True,
            "shadow_mode": True,
            "provider_amazon_v2": False,
            "provider_flipkart": False,
            "simulation_engine": True
        }
        
    def is_enabled(self, flag_name: str) -> bool:
        """Returns True if the feature flag is enabled."""
        if flag_name not in self._flags:
            logging.warning(f"Feature flag '{flag_name}' is not registered. Defaulting to False.")
            return False
        return self._flags[flag_name]
        
    def enable(self, flag_name: str) -> None:
        """Enables a feature flag at runtime."""
        self._flags[flag_name] = True
        
    def disable(self, flag_name: str) -> None:
        """Disables a feature flag at runtime."""
        self._flags[flag_name] = False
        
    def register(self, flag_name: str, default_value: bool = False) -> None:
        """Registers a new feature flag."""
        if flag_name not in self._flags:
            self._flags[flag_name] = default_value

# Singleton instance
feature_flags = FeatureFlagRegistry()
