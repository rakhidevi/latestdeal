import time
import random

class RateLimiter:
    """Provides unified request throttling for providers."""
    
    @staticmethod
    def sleep_random(min_seconds: float = 3.0, max_seconds: float = 7.0):
        time.sleep(random.uniform(min_seconds, max_seconds))
