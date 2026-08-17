import json
import yaml
import glob
from pathlib import Path
from typing import List

from worker.new.sdk.foundation.dto.models import SearchTargetDTO, TraceContext
from worker.new.providers.amazon.discovery.query_builder import AmazonQueryBuilder

class AmazonSearchTargetGenerator:
    """
    Sprint A4: Live Discovery (Target Generator)
    Consumes Production Discovery Profiles (YAML) and Compiled Knowledge (JSON)
    to yield precise SearchTargetDTOs for the legacy compatibility layer.
    """
    def __init__(self, knowledge_dir: str, profiles_dir: str):
        self.knowledge_dir = Path(knowledge_dir)
        self.profiles_dir = Path(profiles_dir)
        
        # Load Compiled Knowledge
        brands_path = self.knowledge_dir / "brands.json"
        nodes_path = self.knowledge_dir / "nodes.json"
        
        self.brands_data = {}
        self.nodes_data = {}
        
        if brands_path.exists():
            with open(brands_path, "r", encoding="utf-8") as f:
                self.brands_data = json.load(f)
                
        if nodes_path.exists():
            with open(nodes_path, "r", encoding="utf-8") as f:
                self.nodes_data = json.load(f)

    def generate_targets(self) -> List[SearchTargetDTO]:
        targets = []
        pattern = self.profiles_dir / "*.yaml"
        
        for profile_path in glob.glob(str(pattern)):
            with open(profile_path, "r", encoding="utf-8") as f:
                profile = yaml.safe_load(f)
                
            profile_name = profile.get("name", "Unknown Profile")
            profile_id = profile.get("id", "unknown")
            filters = profile.get("filters", {})
            target_brands = filters.get("brands", [])
            target_nodes = filters.get("nodes", [])
            discount = filters.get("discount", {})
            prime_req = filters.get("prime", False)
            sort = profile.get("sort", "discount-desc-rank")
            
            # If nodes list is empty, default to a fallback node or global search
            if not target_nodes:
                target_nodes = [""] # Empty node generates global search
                
            # If brands list is empty, just iterate once with no brand
            if not target_brands:
                target_brands = [None]
                
            # Generate Cartesian Product of Brands x Nodes
            for node_id in target_nodes:
                for brand in target_brands:
                    builder = AmazonQueryBuilder()
                    
                    if node_id:
                        builder.with_node(node_id)
                        
                    if brand:
                        # Use compiled brand name if exists, else raw
                        compiled_brand = self.brands_data.get(brand.upper())
                        if compiled_brand and "redirect" in compiled_brand:
                            # It's an alias, get canonical
                            compiled_brand = self.brands_data.get(compiled_brand["redirect"])
                            
                        if compiled_brand:
                            builder.with_brands([compiled_brand.get("amazon_filter_value", brand.title())])
                        else:
                            builder.with_brands([brand.title()])
                            
                    if discount:
                        builder.with_discount(discount.get("min", 10), discount.get("max"))
                        
                    if prime_req:
                        builder.with_prime(True)
                        
                    if sort == "discount-desc-rank":
                        builder.sort_by_discount()
                    elif sort == "date-desc-rank":
                        builder.sort_by_newest()
                        
                    url = builder.build()
                    
                    dto = SearchTargetDTO(
                        trace_context=TraceContext(provider="amazon", profile=profile_id),
                        provider="amazon",
                        profile=profile_id,
                        priority=profile.get("priority", 50),
                        budget_cost=1, # Default 1 page
                        url=url,
                        parameters={
                            "brand": brand,
                            "node": node_id,
                            "discount_min": discount.get("min")
                        }
                    )
                    targets.append(dto)
                    
        return targets

if __name__ == "__main__":
    import sys
    # Default paths mapped to the new Amazon Production Epic layout
    knowledge_path = r"k:\WhatsAppUtility\LatestDeal\worker\new\providers\amazon\knowledge\compiled"
    profiles_path = r"k:\WhatsAppUtility\LatestDeal\worker\new\providers\amazon\discovery\profiles"
    
    generator = AmazonSearchTargetGenerator(knowledge_path, profiles_path)
    targets = generator.generate_targets()
    
    print(f"Generated {len(targets)} Search Targets from Epic 1 Foundation:")
    for t in targets:
        print(f"[{t.profile}] Priority {t.priority} | URL: {t.url}")
