from worker.new.sdk.storage.sqlite_manager import SQLiteManager

class Telemetry:
    """SDK for tracking performance and success metrics locally."""
    _db = SQLiteManager()
    
    @staticmethod
    def increment(metric_name: str, provider: str = "system"):
        Telemetry._db.record_metric(metric_name, 1.0, provider)
        
    @staticmethod
    def timer(metric_name: str, duration: float, provider: str = "system"):
        Telemetry._db.record_metric(metric_name, duration, provider)
        
    @staticmethod
    def record_latency(metric_name: str, ms: float, provider: str = "system"):
        Telemetry._db.record_metric(metric_name, ms, provider)
