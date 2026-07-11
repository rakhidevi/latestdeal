import requests
import os
from dotenv import load_dotenv

load_dotenv(override=True)

def get_api_config():
    api_url = os.getenv("LARAVEL_API_URL") or os.getenv("API_URL") or "http://localhost:9000/api"
    api_key = os.getenv("LARAVEL_API_KEY") or os.getenv("API_KEY") or "dummy_token"
    return api_url, api_key

def push_to_production(payload: dict) -> bool:
    """
    Pushes the final constructed deal payload to the Laravel production server.
    """
    api_url, api_key = get_api_config()
    
    if not api_url:
        print("Missing API configuration in .env")
        return False
        
    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    endpoint = f"{api_url}/deals/ingest"
    
    try:
        response = requests.post(endpoint, json=payload, headers=headers)
        response.raise_for_status()
        print("Successfully pushed deal to production!")
        return True
    except requests.RequestException as e:
        print(f"Failed to push deal: {e}")
        if hasattr(e, 'response') and e.response is not None:
            print(f"Server Response: {e.response.text[:200]}...")
            with open("last_laravel_error.html", "w", encoding="utf-8") as f:
                f.write(e.response.text)
        return False

def create_job(name: str, job_type: str = 'ingestion') -> int:
    api_url, api_key = get_api_config()
    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
    try:
        res = requests.post(f"{api_url}/scraper/jobs", json={"name": name, "type": job_type}, headers=headers, timeout=15)
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
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
    payload = kwargs
    if status: payload['status'] = status
    if logs: payload['logs'] = logs
    try:
        requests.put(f"{api_url}/scraper/jobs/{job_id}", json=payload, headers=headers, timeout=15)
    except Exception as e:
        print(f"Failed to update job {job_id}: {e}")

