import hashlib
from datetime import datetime
from .config import MAX_RETRIES

class RetryManager:
    @staticmethod
    def generate_observation_id(product_id: str, merchant: str, price: float) -> str:
        """
        Creates a deterministic price observation identity.
        Prevents collapsing multiple legitimate observations on the same day,
        while preventing accidental duplicate writes from retries of the same state.
        """
        raw_str = f"{product_id}-{merchant}-{price}-{datetime.utcnow().strftime('%Y-%m-%d')}"
        return hashlib.sha256(raw_str.encode('utf-8')).hexdigest()

    @staticmethod
    def should_retry(attempt_count: int, status_code: str) -> bool:
        """
        Determines if a failure should be retried based on status and attempt limits.
        """
        if attempt_count >= MAX_RETRIES:
            return False
            
        retryable_statuses = [
            "TEMPORARY_FAILURE",
            "RATE_LIMITED",
            "ACCESS_BLOCKED" # Captcha triggered, backoff and retry later
        ]
        
        return status_code in retryable_statuses
