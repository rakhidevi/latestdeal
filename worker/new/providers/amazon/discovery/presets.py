import json
from pathlib import Path
from typing import Dict, Any, List

class AmazonPresets:
    """
    Loads YAML/JSON driven search archetypes (e.g., 'MRP Error', 'Premium Brand').
    Instead of hardcoding logic, presets define the configuration of Lego-block filters.
    """
    def __init__(self, presets_dir: str):
        self.presets_dir = Path(presets_dir)
        self.presets = self._load_presets()
        
    def _load_presets(self) -> Dict[str, Any]:
        # Dummy static presets for now. In production, loads from YAML.
        return {
            "mrp_error": {
                "name": "MRP Error",
                "filters": {
                    "discount_min": 90
                }
            },
            "premium_brand": {
                "name": "Premium Brand",
                "filters": {
                    "brands": ["apple", "samsung", "sony"]
                }
            },
            "laptop_loot": {
                "name": "Laptop Loot",
                "filters": {
                    "node": "1375424031",
                    "discount_min": 70,
                    "prime_only": True
                }
            }
        }
        
    def get_preset(self, preset_id: str) -> Dict[str, Any]:
        return self.presets.get(preset_id, {})
