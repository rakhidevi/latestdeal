import datetime
import uuid
import requests
import threading

class AlertSeverity:
    INFO = "INFO"
    WARNING = "WARNING"
    ERROR = "ERROR"
    CRITICAL = "CRITICAL"

class AlertDispatcher:
    """Dispatches asynchronous alerts to the Laravel backend."""
    
    @staticmethod
    def dispatch(provider: str, severity: str, category: str, message: str, metadata: dict = None):
        payload = {
            "provider": provider,
            "severity": severity,
            "category": category,
            "message": message,
            "timestamp": datetime.datetime.utcnow().isoformat() + "Z",
            "correlation_id": str(uuid.uuid4()),
            "metadata": metadata or {}
        }
        
        # Fire and forget asynchronously so scraping is NEVER blocked
        def _send():
            try:
                requests.post("http://localhost:8000/api/events/alert", json=payload, timeout=5)
            except Exception:
                pass
                
        threading.Thread(target=_send, daemon=True).start()
