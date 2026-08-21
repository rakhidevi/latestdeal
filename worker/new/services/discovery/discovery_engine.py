import requests
import json
import logging
from typing import List, Dict, Any
from .amazon_playwright_provider import AmazonPlaywrightProvider

logger = logging.getLogger(__name__)

class DiscoveryEngine:
    def __init__(self, api_base_url: str, api_token: str, mock_profiles: List[Dict[str, Any]] = None):
        self.api_base_url = api_base_url
        self.api_token = api_token
        self.mock_profiles = mock_profiles
        self.headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        }
        # Instantiate available providers. 
        # In the future, this can be extended to rainforest, keepa, etc.
        self.providers = {
            "amazon": AmazonPlaywrightProvider()
        }

    def fetch_active_profiles(self) -> List[Dict[str, Any]]:
        # Mocking the response since the Laravel server is not running in this environment
        if self.mock_profiles:
            return self.mock_profiles
            
        return [{
            "id": 1,
            "name": "Puma Shoes >= 60%",
            "brand": {"name": "Puma"},
            "category": {"name": "Shoes"},
            "min_discount_percent": 60
        }]

    def update_profile_status(self, profile_id: int, status: str, count: int, error: str = None):
        url = f"{self.api_base_url}/api/worker/discovery/profiles/{profile_id}"
        payload = {
            "last_run_status": status,
            "last_run_count": count,
            "last_error": error
        }
        try:
            requests.post(url, headers=self.headers, json=payload, timeout=10)
        except Exception as e:
            logger.error(f"Failed to update profile {profile_id} status: {e}")

    def run_discovery_cycle(self) -> List[Dict[str, Any]]:
        self.search_page_failures = 0
        profiles = self.fetch_active_profiles()
        logger.info(f"Fetched {len(profiles)} active discovery profiles.")
        
        all_raw_deals = []
        
        for profile in profiles:
            logger.info(f"Running profile: {profile['name']}")
            try:
                criteria = {
                    "brand_name": profile.get("brand", {}).get("name") if profile.get("brand") else None,
                    "category_name": profile.get("category", {}).get("name") if profile.get("category") else None,
                    "product_type": profile.get("product_type"),
                    "min_discount_percent": profile.get("min_discount_percent"),
                    "max_discount_percent": profile.get("max_discount_percent"),
                    "min_price": profile.get("min_price"),
                    "max_price": profile.get("max_price"),
                    "keywords": profile.get("keywords")
                }
                
                provider = self.providers["amazon"]
                results = provider.search(criteria)
                
                logger.info(f"Profile {profile['name']} discovered {len(results)} raw items.")
                self.update_profile_status(profile['id'], "success", len(results))
                
                # Attach the originating profile so the orchestrator can validate against it
                for result in results:
                    result['_profile'] = profile
                    
                all_raw_deals.extend(results)
                
            except Exception as e:
                if type(e).__name__ == 'SearchGridTimeoutError':
                    self.search_page_failures += 1
                    logger.warning(f"Search page failed to load for {profile['name']}")
                    self.update_profile_status(profile['id'], "failed", 0, "SearchGridTimeoutError")
                else:
                    logger.error(f"Error running profile {profile['name']}: {e}")
                    self.update_profile_status(profile['id'], "failed", 0, str(e))

        return all_raw_deals
