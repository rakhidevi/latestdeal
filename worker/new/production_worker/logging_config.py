import logging
import json
import traceback
from datetime import datetime

class JSONFormatter(logging.Formatter):
    def __init__(self, worker_id="worker-01"):
        super().__init__()
        self.worker_id = worker_id

    def format(self, record):
        log_obj = {
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "level": record.levelname,
            "worker_id": self.worker_id,
            "message": record.getMessage(),
            "module": record.module,
        }
        
        if hasattr(record, 'job_id'):
            log_obj['job_id'] = record.job_id
        if hasattr(record, 'url'):
            log_obj['url'] = record.url
        if hasattr(record, 'duration_ms'):
            log_obj['duration_ms'] = record.duration_ms
        if hasattr(record, 'result'):
            log_obj['result'] = record.result
            
        if record.exc_info:
            log_obj['exception'] = self.formatException(record.exc_info)
            
        return json.dumps(log_obj)

def setup_logger(worker_id="worker-01"):
    logger = logging.getLogger("production_worker")
    logger.setLevel(logging.INFO)
    
    # Avoid duplicate handlers if setup_logger is called multiple times
    if not logger.handlers:
        ch = logging.StreamHandler()
        ch.setFormatter(JSONFormatter(worker_id=worker_id))
        logger.addHandler(ch)
        
    return logger

logger = setup_logger()
