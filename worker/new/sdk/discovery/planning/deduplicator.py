from typing import Dict, Any, List
import hashlib
import json

class Deduplicator:
    """
    Discovery Planning Engine: Deduplicator
    Prevents duplicate search targets from entering the queue within the same TTL window.
    """
    
    def __init__(self):
        # In a real environment, this would use Redis for fast distributed state
        self._seen_hashes = set()
        
    def filter_duplicates(self, provider: str, strategy: str, permutations: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """Filters out already-seen permutations for this provider/strategy."""
        unique_perms = []
        
        for perm in permutations:
            perm_hash = self._hash_permutation(provider, strategy, perm)
            if perm_hash not in self._seen_hashes:
                self._seen_hashes.add(perm_hash)
                unique_perms.append(perm)
                
        return unique_perms
        
    def _hash_permutation(self, provider: str, strategy: str, permutation: Dict[str, Any]) -> str:
        """Deterministically hashes the parameters."""
        sorted_keys = sorted(permutation.keys())
        # Convert dictionary to a stable string representation
        # Ensure we only include relevant filtering keys
        parts = [f"{provider}:{strategy}"]
        for k in sorted_keys:
            parts.append(f"{k}={permutation[k]}")
            
        canonical_string = "|".join(parts)
        return hashlib.md5(canonical_string.encode('utf-8')).hexdigest()
