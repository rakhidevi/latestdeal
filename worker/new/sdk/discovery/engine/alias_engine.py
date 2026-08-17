from typing import Dict, List
import json
import os

class AliasEngine:
    """
    Commerce Capability Engine: Alias Engine
    Normalizes brands, categories, and sellers (e.g. Samsung India -> Samsung).
    """
    
    def __init__(self, knowledge_base_path: str):
        self.knowledge_base_path = knowledge_base_path
        self._brand_aliases: Dict[str, str] = {}
        self._category_aliases: Dict[str, str] = {}
        self._load_aliases()
        
    def _load_aliases(self) -> None:
        """Loads aliases from the knowledge graph."""
        # For brands
        brands_dir = os.path.join(self.knowledge_base_path, "brands")
        if os.path.exists(brands_dir):
            for filename in os.listdir(brands_dir):
                if filename.endswith('.json'):
                    filepath = os.path.join(brands_dir, filename)
                    with open(filepath, 'r') as f:
                        data = json.load(f)
                        canonical_name = data.get("name")
                        if not canonical_name:
                            continue
                        
                        # Add self mapping
                        self._brand_aliases[canonical_name.lower()] = canonical_name
                        
                        # Add alias mappings
                        for alias in data.get("aliases", []):
                            self._brand_aliases[alias.lower()] = canonical_name
                            
        # A full implementation would do the same for categories and sellers
                            
    def normalize_brand(self, brand_name: str) -> str:
        """Normalizes a brand name to its canonical form."""
        normalized = brand_name.lower().strip()
        return self._brand_aliases.get(normalized, brand_name) # Fallback to original if unknown

    def normalize_category(self, category_name: str) -> str:
        """Normalizes a category name to its canonical form."""
        # Stub for full implementation
        return category_name
