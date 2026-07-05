import requests
import os
from dotenv import load_dotenv

load_dotenv()

API_URL = os.getenv("API_URL", "http://localhost:8001/api/v1")
API_KEY = os.getenv("API_KEY", "dummy_token")

def push_to_production(payload: dict) -> bool:
    """
    Pushes the final constructed deal payload to the Laravel production server.
    """
    if not API_URL:
        print("Missing API configuration in .env")
        return False
        
    headers = {
        "Authorization": f"Bearer {API_KEY}",
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    endpoint = f"{API_URL}/deals/ingest"
    
    try:
        response = requests.post(endpoint, json=payload, headers=headers)
        response.raise_for_status()
        print("Successfully pushed deal to production!")
        return True
    except requests.RequestException as e:
        print(f"Failed to push deal: {e}")
        if hasattr(e, 'response') and e.response is not None:
            print(f"Server Response: {e.response.text}")
        return False
