import requests
from .config import LARAVEL_API_URL, LARAVEL_API_KEY
from .logging_config import logger

class PersistenceException(Exception):
    pass

class PersistenceLayer:
    @staticmethod
    def save_deal(deal_data: dict, affiliate_url: str):
        """
        Saves the validated, monetized deal to the Laravel backend as 'AUTO'.
        """
        payload = {
            "title": deal_data.get('title'),
            "original_price": deal_data.get('mrp'),
            "discounted_price": deal_data.get('price'),
            "url": deal_data.get('url'),
            "image_url": deal_data.get('image'),
            "short_url": affiliate_url,
            "observation_id": deal_data.get('observation_id'),
            "editorial_status": "AUTO" # ALWAYS AUTO per invariants
        }
        
        # If in Dry Run or no API URL, just log it
        if not LARAVEL_API_URL or not LARAVEL_API_KEY:
            logger.info("Dry-run/No config: Skipping DB persistence", extra={"payload": payload})
            return True
            
        try:
            headers = {
                "Authorization": f"Bearer {LARAVEL_API_KEY}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            }
            resp = requests.post(f"{LARAVEL_API_URL}/api/worker/ingest", json=payload, headers=headers, timeout=10)
            resp.raise_for_status()
            return True
        except Exception as e:
            logger.error(f"Failed to persist deal to Laravel: {e}")
            raise PersistenceException(f"Laravel API Error: {e}")

    @staticmethod
    def claim_jobs():
        if not LARAVEL_API_URL or not LARAVEL_API_KEY:
            # Fallback for dry run
            return ["https://www.amazon.in/dp/B0BDHX8Z63"]
            
        try:
            headers = {
                "Authorization": f"Bearer {LARAVEL_API_KEY}",
                "Accept": "application/json"
            }
            resp = requests.get(f"{LARAVEL_API_URL}/api/worker/jobs/claim?worker_id=worker-01", headers=headers, timeout=10)
            resp.raise_for_status()
            data = resp.json()
            return data.get('jobs', [])
        except Exception as e:
            logger.error(f"Failed to claim jobs from Laravel: {e}")
            return []

    @staticmethod
    def update_job_status(job_id: int, status: str):
        if not LARAVEL_API_URL or not LARAVEL_API_KEY:
            return True
            
        try:
            headers = {
                "Authorization": f"Bearer {LARAVEL_API_KEY}",
                "Content-Type": "application/json"
            }
            resp = requests.post(f"{LARAVEL_API_URL}/api/worker/jobs/{job_id}/status", json={"status": status, "worker_id": "worker-01"}, headers=headers, timeout=5)
            resp.raise_for_status()
            return True
        except Exception as e:
            logger.error(f"Failed to update job {job_id} status to {status}: {e}")
            return False

    @staticmethod
    def heartbeat(job_id: int) -> bool:
        """Returns True if the worker should continue processing. False if cancellation requested."""
        if not LARAVEL_API_URL or not LARAVEL_API_KEY:
            return True
            
        try:
            headers = {
                "Authorization": f"Bearer {LARAVEL_API_KEY}",
                "Content-Type": "application/json"
            }
            resp = requests.post(f"{LARAVEL_API_URL}/api/worker/jobs/{job_id}/heartbeat", json={"worker_id": "worker-01"}, headers=headers, timeout=5)
            resp.raise_for_status()
            data = resp.json()
            return not data.get('cancel_requested', False)
        except Exception as e:
            logger.error(f"Failed to send heartbeat for job {job_id}: {e}")
            return True # Continue on transient network error
