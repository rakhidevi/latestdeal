from typing import Dict, Any, List
from worker.new.sdk.discovery.engine.capability_matrix import CapabilityMatrix
from worker.new.sdk.discovery.engine.constraint_engine import ConstraintEngine
from worker.new.sdk.discovery.planning.permutation_engine import PermutationEngine

class SearchSpaceBuilder:
    """
    Discovery Planning Engine: Search Space Builder
    Orchestrates Knowledge -> Ontology -> Capabilities -> Constraints -> Heuristics.
    """
    
    def __init__(self, capability_matrix: CapabilityMatrix, constraint_engine: ConstraintEngine):
        self.capability_matrix = capability_matrix
        self.constraint_engine = constraint_engine
        self.permutation_engine = PermutationEngine()
        
    def build_space(self, provider: str, base_parameters: Dict[str, Any]) -> List[Dict[str, Any]]:
        """
        Builds the theoretical search space and immediately prunes it using constraints.
        """
        raw_permutations = self.permutation_engine.expand(base_parameters)
        
        # Prune via constraints
        valid_permutations = self.constraint_engine.filter_valid_permutations(provider, raw_permutations)
        
        return valid_permutations
