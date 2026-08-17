from typing import Dict, Any, List
import itertools

class PermutationEngine:
    """
    Discovery Planning Engine: Permutation Engine
    Generates the raw search space.
    """
    
    def expand(self, base_parameters: Dict[str, Any]) -> List[Dict[str, Any]]:
        """
        Expands list values in parameters into a flat list of distinct permutations.
        E.g. {"brand": ["A", "B"], "category": "C"} -> [{"brand": "A", "category": "C"}, {"brand": "B", "category": "C"}]
        """
        # Separate list values from scalar values
        list_params = {}
        scalar_params = {}
        
        for k, v in base_parameters.items():
            if isinstance(v, list):
                list_params[k] = v
            else:
                scalar_params[k] = v
                
        if not list_params:
            return [base_parameters]
            
        keys = list(list_params.keys())
        values = [list_params[k] for k in keys]
        
        permutations = []
        for combo in itertools.product(*values):
            perm = scalar_params.copy()
            for i, k in enumerate(keys):
                perm[k] = combo[i]
            permutations.append(perm)
            
        return permutations
