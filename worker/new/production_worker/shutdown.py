import signal
import asyncio
from .logging_config import logger

class GracefulShutdown:
    def __init__(self):
        self.stop_requested = False
        
        # Attach signal handlers for graceful termination
        try:
            signal.signal(signal.SIGINT, self.exit_gracefully)
            signal.signal(signal.SIGTERM, self.exit_gracefully)
        except ValueError:
            # Fails if not run in main thread, safe to ignore for testing
            pass

    def exit_gracefully(self, signum, frame):
        logger.info(f"Received termination signal {signum}. Initiating graceful shutdown...")
        self.stop_requested = True
