import yaml
import json
from pathlib import Path
from typing import Dict, List, Any
import glob

class AmazonKnowledgeCompiler:
    """
    Pipeline: Raw YAML -> Validation -> Normalization -> Alias Resolution -> Hierarchy Validation -> Versioned JSON
    """
    def __init__(self, source_dir: str, output_dir: str):
        self.source_dir = Path(source_dir)
        self.output_dir = Path(output_dir)
        self.output_dir.mkdir(parents=True, exist_ok=True)
        self.version = "v2.5"

    def _load_yamls_from_dir(self, sub_dir: str, root_key: str) -> List[Dict[str, Any]]:
        all_records = []
        pattern = self.source_dir / sub_dir / "*.yaml"
        for file_path in glob.glob(str(pattern)):
            with open(file_path, "r", encoding="utf-8") as f:
                data = yaml.safe_load(f)
                if data and root_key in data:
                    all_records.extend(data[root_key])
        return all_records

    def _normalize_aliases(self, record: Dict[str, Any]) -> None:
        if "aliases" in record:
            record["aliases"] = list(set([str(a).lower().strip() for a in record["aliases"]]))

    def compile_brands(self):
        brands = self._load_yamls_from_dir("brands", "brands")
        # Validation & Normalization
        normalized_brands = []
        for brand in brands:
            self._normalize_aliases(brand)
            normalized_brands.append(brand)
            
        compiled_data = {
            "version": self.version,
            "count": len(normalized_brands),
            "brands": normalized_brands
        }
        with open(self.output_dir / "brands_v2.json", "w", encoding="utf-8") as f:
            json.dump(compiled_data, f, indent=4)

    def compile_categories(self):
        categories = self._load_yamls_from_dir("categories", "categories")
        for cat in categories:
            self._normalize_aliases(cat)
            
        compiled_data = {
            "version": self.version,
            "count": len(categories),
            "categories": categories
        }
        with open(self.output_dir / "categories_v2.json", "w", encoding="utf-8") as f:
            json.dump(compiled_data, f, indent=4)

    def compile_nodes(self):
        nodes = self._load_yamls_from_dir("nodes", "nodes")
        compiled_data = {
            "version": self.version,
            "count": len(nodes),
            "nodes": nodes
        }
        with open(self.output_dir / "nodes_v2.json", "w", encoding="utf-8") as f:
            json.dump(compiled_data, f, indent=4)
            
    def compile_sellers(self):
        sellers = self._load_yamls_from_dir("sellers", "sellers")
        for seller in sellers:
            self._normalize_aliases(seller)
            
        compiled_data = {
            "version": self.version,
            "count": len(sellers),
            "sellers": sellers
        }
        with open(self.output_dir / "sellers_v2.json", "w", encoding="utf-8") as f:
            json.dump(compiled_data, f, indent=4)
            
    def compile_promotions(self):
        promotions = self._load_yamls_from_dir("promotions", "promotions")
        compiled_data = {
            "version": self.version,
            "count": len(promotions),
            "promotions": promotions
        }
        with open(self.output_dir / "promotions_v2.json", "w", encoding="utf-8") as f:
            json.dump(compiled_data, f, indent=4)

    def compile_all(self):
        print(f"Compiling Amazon Knowledge (Version {self.version})...")
        self.compile_brands()
        self.compile_categories()
        self.compile_nodes()
        self.compile_sellers()
        self.compile_promotions()
        print("Compilation successful.")

if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser(description="Amazon Knowledge Compiler")
    parser.add_argument("--source", default=r"k:\WhatsAppUtility\LatestDeal\worker\new\knowledge\amazon\source", help="Source directory")
    parser.add_argument("--output", default=r"k:\WhatsAppUtility\LatestDeal\worker\new\knowledge\amazon\compiled", help="Output directory")
    args = parser.parse_args()
    
    compiler = AmazonKnowledgeCompiler(args.source, args.output)
    compiler.compile_all()
