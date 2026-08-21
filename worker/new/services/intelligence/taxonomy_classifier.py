import requests
import logging
from typing import Dict, Any, List
import json
import asyncio
from ..ai.ai_router import router

logger = logging.getLogger(__name__)

class TaxonomyClassifier:
    def __init__(self, api_base_url: str, api_token: str):
        self.api_base_url = api_base_url
        self.api_token = api_token
        self.headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json"
        }
        self.categories_cache = []
        self.category_names = []

    def _fetch_categories(self):
        if not self.categories_cache:
            try:
                url = f"{self.api_base_url}/api/worker/intelligence/categories"
                response = requests.get(url, headers=self.headers, timeout=10)
                if response.status_code == 200:
                    self.categories_cache = response.json()
                    self.category_names = [c['name'] for c in self.categories_cache]
            except Exception as e:
                logger.error(f"Failed to fetch categories: {e}")

    def process(self, deal: Dict[str, Any]) -> Dict[str, Any]:
        """
        Uses LLM to propose categories based on normalized title, then validates
        them strictly against the taxonomy.
        """
        self._fetch_categories()
        
        title = deal.get('normalized_title', deal.get('title', ''))
        title_lower = title.lower()
        
        # 1. Deterministic Accessory / Exclusion Check
        is_accessory = False
        tv_exclusions = ['remote', 'cover', 'case', 'wall mount', 'stand', 'bracket', 'screen protector', 'cable', 'adapter', 'replacement', 'knob']
        appliance_exclusions = ['cover', 'replacement', 'spare', 'knob', 'filter', 'hose', 'stand', 'rack', 'accessory']
        shoe_exclusions = ['lace', 'laces', 'cleaner', 'brush', 'insole', 'socks']
        
        # Check against exclusions
        if any(ex in title_lower for ex in tv_exclusions + appliance_exclusions + shoe_exclusions):
            is_accessory = True
            
        deal['is_accessory'] = is_accessory

        # 2. Deterministic Product Type mapping
        shoe_synonyms = ['sneaker', 'sneakers', 'trainer', 'trainers', 'running shoe', 'walking shoe', 'football shoe', 'sports shoe', 'shoes']
        tv_synonyms = ['tv', 'television', 'smart tv', 'oled tv', 'qled tv', 'led tv', 'google tv', 'android tv']
        appliance_synonyms = ['refrigerator', 'fridge', 'washing machine', 'air conditioner', 'microwave', 'dishwasher', 'appliance', 'appliances']
        
        # We also want to expose names for the validator
        deal['category_names'] = []
        deal['product_type'] = None
        
        primary_cat = None
        
        if is_accessory:
            deal['product_type'] = 'Accessory'
            primary_cat = 'Accessories' # We can map this to a general category later
        elif any(syn in title_lower for syn in shoe_synonyms):
            deal['product_type'] = 'Shoes'
            primary_cat = 'Shoes'
        elif any(syn in title_lower for syn in tv_synonyms):
            deal['product_type'] = 'TV'
            primary_cat = 'TV'
        elif any(syn in title_lower for syn in appliance_synonyms):
            # Try to be more specific
            for app in ['refrigerator', 'washing machine', 'air conditioner', 'microwave', 'dishwasher']:
                if app in title_lower:
                    deal['product_type'] = app.title()
                    break
            if not deal['product_type']:
                deal['product_type'] = 'Appliance'
            primary_cat = 'Appliances'
            
        if primary_cat:
            cat_id = None
            for cat in self.categories_cache:
                if cat['name'].lower() == primary_cat.lower():
                    cat_id = cat['id']
                    break
            
            deal['primary_category_id'] = cat_id
            deal['secondary_category_ids'] = []
            deal['category_names'] = [primary_cat]
            return deal

        if not self.categories_cache:
            deal['primary_category_id'] = None
            deal['secondary_category_ids'] = []
            return deal
            
        prompt = f"""
Given the product title: "{title}"
And the following available taxonomy categories:
{json.dumps(self.category_names)}

Select the most appropriate PRIMARY category (exactly 1).
Then select any relevant SECONDARY categories (0 to 5 max).

Output strictly in this JSON format and nothing else:
{{
    "primary": "Category Name",
    "secondary": ["Category Name 1", "Category Name 2"]
}}
"""
        try:
            # Check if there is a running event loop, otherwise run_until_complete
            try:
                loop = asyncio.get_event_loop()
                if loop.is_running():
                    # Fallback for sync context running inside async (not ideal, but works for script)
                    # We will just run a new loop if needed, or use run_coroutine_threadsafe
                    pass
            except RuntimeError:
                loop = asyncio.new_event_loop()
                asyncio.set_event_loop(loop)
                
            llm_response = asyncio.run(router.chat([{'role': 'user', 'content': prompt}], {'capabilities': ['JSON']}))
            llm_content = llm_response.get('content', '')
            # Find the JSON block if the LLM added markdown formatting
            import re
            json_match = re.search(r'\{.*\}', llm_content, re.DOTALL)
            if json_match:
                parsed = json.loads(json_match.group(0))
                
                # Validation against taxonomy
                primary_name = parsed.get("primary", "")
                secondary_names = parsed.get("secondary", [])
                
                primary_id = None
                secondary_ids = []
                
                for cat in self.categories_cache:
                    if cat['name'].lower() == primary_name.lower():
                        primary_id = cat['id']
                    
                    for sec in secondary_names:
                        if cat['name'].lower() == sec.lower():
                            secondary_ids.append(cat['id'])
                            
                deal['primary_category_id'] = primary_id
                deal['secondary_category_ids'] = list(set(secondary_ids))
            else:
                logger.warning(f"Failed to parse JSON from LLM: {llm_content}")
                deal['primary_category_id'] = None
                deal['secondary_category_ids'] = []
                
        except Exception as e:
            logger.error(f"Taxonomy classification error: {e}")
            deal['primary_category_id'] = None
            deal['secondary_category_ids'] = []

        return deal
