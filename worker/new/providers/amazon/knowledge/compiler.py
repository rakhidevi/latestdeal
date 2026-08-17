import yaml
import json
from pathlib import Path
from typing import Dict, List, Any
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("AmazonKnowledgeCompiler")

class AmazonKnowledgeCompiler:
    """
    Sprint A1: Amazon Knowledge Platform.
    Compiles human-readable YAML definitions of Brands, Categories, Node IDs, and Sellers
    into highly optimized, validated JSON datasets for the Query Builder.
    """
    def __init__(self, root_dir: str):
        self.root_dir = Path(root_dir)
        self.source_dir = self.root_dir / "source"
        self.compiled_dir = self.root_dir / "compiled"
        
        self.compiled_dir.mkdir(parents=True, exist_ok=True)
        
    def _load_yaml(self, file_name: str) -> Dict[str, Any]:
        path = self.source_dir / file_name
        if not path.exists():
            logger.warning(f"Source file {file_name} does not exist.")
            return {}
        with open(path, "r", encoding="utf-8") as f:
            return yaml.safe_load(f) or {}

    def _save_json(self, data: Any, file_name: str):
        path = self.compiled_dir / file_name
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2, ensure_ascii=False)
        logger.info(f"Compiled and saved {file_name}")

    def compile_brands(self):
        """Compiles brands.yaml, expanding aliases and calculating normalized names."""
        raw_brands = self._load_yaml("brands.yaml")
        compiled = {}
        for brand_key, details in raw_brands.items():
            normalized = brand_key.upper()
            compiled[normalized] = {
                "id": brand_key,
                "display_name": details.get("name", brand_key.title()),
                "aliases": [a.upper() for a in details.get("aliases", [])],
                "tier": details.get("tier", "STANDARD"),
                "amazon_filter_value": details.get("filter_value", details.get("name", brand_key.title()))
            }
            # Add aliases to point to the canonical brand ID for fast O(1) lookup
            for alias in details.get("aliases", []):
                compiled[alias.upper()] = {"redirect": normalized}
                
        self._save_json(compiled, "brands.json")
        return compiled

    def compile_nodes(self):
        """Compiles nodes.yaml into a fast lookup tree."""
        raw_nodes = self._load_yaml("nodes.yaml")
        compiled = {}
        for category, details in raw_nodes.items():
            compiled[category] = {
                "node_id": details.get("node_id"),
                "department": details.get("department", "All Departments"),
                "priority": details.get("priority", 50),
                "has_subnodes": bool(details.get("subnodes")),
                "subnodes": details.get("subnodes", {})
            }
        self._save_json(compiled, "nodes.json")
        return compiled

    def compile_all(self):
        logger.info("Starting Amazon Knowledge Compilation...")
        self.compile_brands()
        self.compile_nodes()
        logger.info("Compilation complete.")

if __name__ == "__main__":
    import sys
    base_path = sys.argv[1] if len(sys.argv) > 1 else str(Path(__file__).parent)
    compiler = AmazonKnowledgeCompiler(base_path)
    compiler.compile_all()
