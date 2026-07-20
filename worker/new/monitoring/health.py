from enum import Enum
from typing import Dict, Any

class ProviderStatus(Enum):
    OK = "OK"
    ERROR = "ERROR"
    TIMEOUT = "TIMEOUT"

class HealthCheck:
    def __init__(self, provider_name: str):
        self.provider = provider_name
        
    def generate_report(self, browser_running: bool, api_reachable: bool, cookies_valid: bool, selectors_valid: bool) -> Dict[str, Any]:
        """
        Generates a standardized health matrix for a provider as per the Platform Specification.
        """
        status = ProviderStatus.OK
        if not (browser_running and api_reachable and cookies_valid and selectors_valid):
            status = ProviderStatus.ERROR
            
        return {
            "provider": self.provider,
            "status": status.value,
            "checks": {
                "browser_running": browser_running,
                "api_reachable": api_reachable,
                "cookies_valid": cookies_valid,
                "selectors_valid": selectors_valid
            }
        }
