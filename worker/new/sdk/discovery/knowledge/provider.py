import json
from typing import Dict, Any, List
from pathlib import Path

class KnowledgeProvider:
    """
    Loads JSON Knowledge Bases with schema versioning.
    Provides standardized access to brands, categories, and priority rules.
    """
    def __init__(self, directory: str):
        self.directory = Path(directory)
        self._cache: Dict[str, Dict[str, Any]] = {}

    def _load_file(self, filename: str) -> Dict[str, Any]:
        if filename in self._cache:
            return self._cache[filename]

        filepath = self.directory / filename
        if not filepath.exists():
            return {}

        with open(filepath, 'r') as f:
            data = json.load(f)

        if data.get('schema') != 1:
            raise ValueError(f"Unsupported knowledge schema version in {filename}")

        self._cache[filename] = data
        return data

    def get_brands(self) -> List[Dict[str, Any]]:
        data = self._load_file("brands_v1.json")
        return data.get("brands", [])

    def get_categories(self) -> List[Dict[str, Any]]:
        data = self._load_file("categories_v1.json")
        return data.get("categories", [])
