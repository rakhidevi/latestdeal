import json
import logging
import sys
from datetime import datetime

class PipelineLogger:
    def __init__(self, name="pipeline"):
        self.logger = logging.getLogger(name)
        self.logger.setLevel(logging.INFO)
        
        # Avoid duplicate handlers if instantiated multiple times
        if not self.logger.handlers:
            handler = logging.StreamHandler(sys.stdout)
            handler.setFormatter(logging.Formatter('%(message)s'))
            self.logger.addHandler(handler)

    def _log(self, level, event_name, **kwargs):
        log_entry = {
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "level": level,
            "event": event_name
        }
        log_entry.update(kwargs)
        self.logger.log(getattr(logging, level.upper()), json.dumps(log_entry))

    def info(self, event_name, **kwargs):
        self._log("info", event_name, **kwargs)

    def error(self, event_name, **kwargs):
        self._log("error", event_name, **kwargs)

    def warning(self, event_name, **kwargs):
        self._log("warning", event_name, **kwargs)
