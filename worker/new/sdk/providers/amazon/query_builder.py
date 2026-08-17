import urllib.parse
from typing import Dict, Any

class AmazonQueryBuilder:
    """Translates abstract target parameters into Amazon search URLs."""
    
    BASE_URL = "https://www.amazon.in/s"
    
    def build(self, parameters: Dict[str, Any]) -> str:
        query_params = {}
        
        # Keyword mapping
        if "keyword" in parameters:
            query_params["k"] = parameters["keyword"]
        
        # The complex RH (Refinement) codes mapping would go here
        # E.g. Brand filters, discount filters
        rh_parts = []
        if "brand" in parameters and parameters["brand"] != "*":
            # Real implementation would map to Amazon's brand node ID
            rh_parts.append(f"p_89:{parameters['brand']}")
            
        if "discount_min" in parameters:
            rh_parts.append(f"p_8:{parameters['discount_min']}-")
            
        if rh_parts:
            query_params["rh"] = "%2C".join(rh_parts) # Simplistic join for example
            
        if "page" in parameters:
            query_params["page"] = str(parameters["page"])
            
        query_string = urllib.parse.urlencode(query_params, safe="%:,")
        return f"{self.BASE_URL}?{query_string}"
