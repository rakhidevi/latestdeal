import requests
import json
import uuid

LARAVEL_API_URL = "http://localhost:8000"
LARAVEL_API_TOKEN = "test-worker-token-123"

headers = {
    "Authorization": f"Bearer {LARAVEL_API_TOKEN}",
    "Accept": "application/json",
    "Content-Type": "application/json"
}

payload = {
    "asin": "B0TEST" + str(uuid.uuid4())[:8],
    "trace_id": str(uuid.uuid4()),
    "pipeline_run_id": "test-run-01",
    "title": "Test Puma Shoe",
    "original_price": 5000,
    "discounted_price": 2000,
    "calculated_discount": 60,
    "url": "https://www.amazon.in/dp/B0TEST",
    "brand": "Puma",
    "observation_id": str(uuid.uuid4())
}

print(f"Sending payload: {json.dumps(payload, indent=2)}")

try:
    response = requests.post(
        f"{LARAVEL_API_URL}/api/worker/ingest",
        json=payload,
        headers=headers
    )
    print(f"Status Code: {response.status_code}")
    print(f"Response: {response.text}")
except Exception as e:
    print(f"Failed: {e}")
