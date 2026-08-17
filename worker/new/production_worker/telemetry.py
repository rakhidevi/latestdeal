import requests
import json
from .config import LARAVEL_API_URL, LARAVEL_API_KEY
from .logging_config import logger

class TelemetryClient:
    def __init__(self, worker_id="worker-01"):
        self.worker_id = worker_id
        self.base_url = LARAVEL_API_URL
        self.api_key = LARAVEL_API_KEY
        
    def send_heartbeat(self, status, metrics=None):
        if not self.base_url or not self.api_key:
            return # Dry-run or missing config
            
        payload = {
            "worker_id": self.worker_id,
            "status": status,
            "metrics": metrics or {}
        }
        
        try:
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            }
            # Assuming endpoint /api/worker/heartbeat exists in Laravel
            resp = requests.post(f"{self.base_url}/api/worker/heartbeat", json=payload, headers=headers, timeout=5)
            resp.raise_for_status()
        except Exception as e:
            logger.error(f"Failed to send telemetry heartbeat: {e}")

telemetry = TelemetryClient()
