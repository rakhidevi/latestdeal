from typing import Dict, List, Optional
import os
import json

class OntologyEngine:
    """
    Commerce Capability Engine: Ontology Engine
    Manages relationships (Electronics -> Mobiles -> Android -> Samsung).
    """
    
    def __init__(self, knowledge_base_path: str):
        self.knowledge_base_path = knowledge_base_path
        self._brand_parent_map: Dict[str, str] = {}
        self._load_ontology()
        
    def _load_ontology(self) -> None:
        """Loads relationships from the knowledge graph."""
        brands_dir = os.path.join(self.knowledge_base_path, "brands")
        if os.path.exists(brands_dir):
            for filename in os.listdir(brands_dir):
                if filename.endswith('.json'):
                    filepath = os.path.join(brands_dir, filename)
                    with open(filepath, 'r') as f:
                        data = json.load(f)
                        name = data.get("name")
                        parent = data.get("parent")
                        if name and parent:
                            self._brand_parent_map[name] = parent

    def get_parent_category(self, brand: str) -> Optional[str]:
        """Resolves the parent category for a brand."""
        return self._brand_parent_map.get(brand)
