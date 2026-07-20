import time
import requests
import threading
from worker.new.sdk.storage.sqlite_manager import SQLiteManager

class HeartbeatEngine:
    """Periodically aggregates local SQLite metrics and pushes them to Laravel."""
    
    def __init__(self, endpoint: str = "http://localhost:8000/api/telemetry/push", interval: int = 300):
        self.endpoint = endpoint
        self.interval = interval # default 5 minutes
        self.db = SQLiteManager()
        self.running = False
        
    def start(self):
        self.running = True
        threading.Thread(target=self._loop, daemon=True).start()
        
    def stop(self):
        self.running = False
        
    def _loop(self):
        while self.running:
            time.sleep(self.interval)
            self.push_metrics()
            
    def push_metrics(self):
        rows = self.db.get_unpushed_metrics()
        if not rows:
            return
            
        payload = []
        ids_to_clear = []
        for row in rows:
            ids_to_clear.append(row[0])
            payload.append({
                "metric_name": row[1],
                "metric_value": row[2],
                "provider": row[3],
                "timestamp": row[4]
            })
            
        try:
            # Push payload to Laravel API
            response = requests.post(self.endpoint, json={"metrics": payload}, timeout=5)
            if response.status_code == 200:
                self.db.clear_metrics(ids_to_clear)
        except Exception as e:
            # On failure, they remain in SQLite and will be retried next heartbeat
            print(f"Failed to push telemetry to Laravel: {e}")
