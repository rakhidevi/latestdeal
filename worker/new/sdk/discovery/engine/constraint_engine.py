from typing import Dict, Any, List
from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix

class ConstraintEngine:
    """
    Commerce Capability Engine: Constraint Engine
    Prevents invalid permutations (e.g. do not generate a search with "Warehouse" for Flipkart).
    """
    
    def __init__(self, capability_matrix: CapabilityMatrix):
        self.capability_matrix = capability_matrix
        
    def filter_valid_permutations(self, provider: str, permutations: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """Filters out permutations that contain constraints unsupported by the provider."""
        valid = []
        for perm in permutations:
            if self.is_valid(provider, perm):
                valid.append(perm)
        return valid

    def is_valid(self, provider: str, permutation: Dict[str, Any]) -> bool:
        """Checks if a single permutation is valid for the given provider."""
        # Check specific known capability flags
        
        # E.g., if permutation requires 'is_prime' but provider doesn't support prime
        if permutation.get('is_prime') and not self.capability_matrix.supports(provider, 'prime'):
            return False
            
        if permutation.get('is_warehouse') and not self.capability_matrix.supports(provider, 'warehouse'): # We don't have this in DTO yet, but concept holds
            return False
            
        if permutation.get('brand') and not self.capability_matrix.supports(provider, 'brand'):
            return False
            
        if permutation.get('coupon_only') and not self.capability_matrix.supports(provider, 'coupon'):
            return False
            
        return True
