import requests
import logging
from typing import Dict, Any

logger = logging.getLogger(__name__)

class Deduplicator:
    def __init__(self, api_base_url: str, api_token: str):
        self.api_base_url = api_base_url
        self.api_token = api_token
        self.headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json"
        }

    def process(self, raw_deal: Dict[str, Any]) -> str:
        """
        Returns the status of the deal: NEW, EXISTING_PRODUCT, PRICE_CHANGED, or REJECTED.
        For now, we just return NEW or EXISTING_PRODUCT based on a simple check.
        """
        source_id = raw_deal.get('source_id')
        url = raw_deal.get('url')
        
        try:
            check_url = f"{self.api_base_url}/api/worker/intelligence/check-exists"
            params = {}
            if source_id: params['asin'] = source_id
            if url: params['url'] = url
                
            response = requests.get(check_url, headers=self.headers, params=params, timeout=10)
            if response.status_code == 200:
                data = response.json()
                if data.get('exists'):
                    return 'EXISTING_PRODUCT'
            return 'NEW'
        except Exception as e:
            logger.error(f"Deduplicator API error: {e}")
            return 'NEW' # Fail open for now
