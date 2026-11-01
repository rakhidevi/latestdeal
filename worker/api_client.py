import requests
import os
from dotenv import load_dotenv

load_dotenv(override=True)

def get_api_config():
    api_url = os.getenv("LARAVEL_API_URL") or os.getenv("API_URL") or "http://localhost:8000/api"
    api_key = os.getenv("LARAVEL_API_KEY") or os.getenv("API_KEY") or "dummy_token"
    return api_url, api_key

def push_to_production(payload: dict, max_retries: int = 3) -> bool:
    """
    Pushes the final constructed deal payload to the Laravel production server
    with explicit timeout, exponential backoff, and retry handling.
    """
    import time
    import random
    
    api_url, api_key = get_api_config()
    
    if not api_url:
        print("Missing API configuration in .env")
        return False
        
    headers = {
        "Authorization": f"Bearer {api_key}",
        "X-Worker-Key": api_key,
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    endpoint = f"{api_url}/worker/ingest"
    payload["worker_api_key"] = api_key
    
    for attempt in range(1, max_retries + 1):
        try:
            response = requests.post(endpoint, json=payload, headers=headers, timeout=15)
            response.raise_for_status()
            print(f"Successfully pushed deal to production on attempt {attempt}!")
            return True
        except requests.RequestException as e:
            status_code = getattr(getattr(e, 'response', None), 'status_code', None)
            is_transient = isinstance(e, (requests.Timeout, requests.ConnectionError)) or status_code in (500, 502, 503, 504)
            
            print(f"[api_client] Push attempt {attempt}/{max_retries} failed: {e}")
            if is_transient and attempt < max_retries:
                backoff = (2 ** attempt) + random.uniform(0.5, 1.5)
                print(f"[api_client] Retrying transient failure in {backoff:.1f}s...")
                time.sleep(backoff)
                continue
                
            if hasattr(e, 'response') and e.response is not None:
                print(f"Server Response: {e.response.text[:200]}...")
                with open("last_laravel_error.html", "w", encoding="utf-8") as f:
                    f.write(e.response.text)
            return False
            
    return False

def create_job(name: str, job_type: str = 'ingestion') -> int:
    api_url, api_key = get_api_config()
    headers = {
        "Authorization": f"Bearer {api_key}",
        "X-Worker-Key": api_key,
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
    }
    try:
        res = requests.post(f"{api_url}/scraper/jobs", json={"name": name, "type": job_type, "worker_api_key": api_key}, headers=headers, timeout=15)
        if res.status_code == 200:
            return res.json().get('id')
    except Exception as e:
        print(f"Failed to create job: {e}")
    return None

def update_job(job_id: int, status: str = None, logs: list = None, **kwargs):
    if not job_id: return
    api_url, api_key = get_api_config()
    headers = {
        "Authorization": f"Bearer {api_key}",
        "X-Worker-Key": api_key,
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
    }
    payload = kwargs
    payload["worker_api_key"] = api_key
    if status: payload['status'] = status
    if logs: payload['logs'] = logs
    try:
        requests.put(f"{api_url}/scraper/jobs/{job_id}", json=payload, headers=headers, timeout=15)
    except Exception as e:
        print(f"Failed to update job {job_id}: {e}")

